<?php

namespace Acts\CamdramAdminBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use Acts\CamdramSecurityBundle\Entity\User;
use Acts\CamdramAdminBundle\Form\Type\UserType;
use Acts\CamdramAdminBundle\Form\Type\AddAclType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @RouteResource("User")
 */
class UserController extends FOSRestController
{
    protected function getRouteParams($user)
    {
        return array('identifier' => $user->getId());
    }

    protected function checkAuthenticated()
    {
        $this->get('camdram.security.utils')->ensureRole('IS_AUTHENTICATED_FULLY');
        $this->get('camdram.security.utils')->ensureRole('ROLE_SUPER_ADMIN');
    }

    protected function getEntity($identifier)
    {
        $entity = $this->getRepository()->findOneBy(array('id' => $identifier));

        if (!$entity) {
            throw $this->createNotFoundException('That user does not exist');
        }

        return $entity;
    }

    protected function getRepository()
    {
        return $this->getDoctrine()->getManager()->getRepository('ActsCamdramSecurityBundle:User');
    }

    protected function getForm($society = null)
    {
        return $this->createForm(new UserType(), $society);
    }

    /**
     * Action which returns a list of entities.
     *
     * If a search term 'q' is provided, then a text search is performed against Sphinx. Otherwise, a paginated
     * collection of all entities is returned.
     */
    public function cgetAction(Request $request)
    {
        $this->checkAuthenticated();
        if ($request->get('q')) {
            /** @var $search_provider \Acts\CamdramBundle\Service\Search\ProviderInterface */
            $search_provider = $this->get('acts.camdram.search_provider');
            $data = $search_provider->executeUserSearch($request->get('q'), 10);
        } else {
            $repo = $this->getRepository();
            $qb = $repo->createQueryBuilder('e');
            $adapter = new DoctrineORMAdapter($qb);
            $data = new Pagerfanta($adapter);
            $data->setMaxPerPage(25);
        }

        return $this->view($data, 200)
            ->setTemplateVar('result')
            ->setTemplate('ActsCamdramAdminBundle:User:index.html.twig')
        ;
    }

    public function getAction($identifier)
    {
        $this->checkAuthenticated();
        $entity = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('VIEW', $entity, false);
        $ids = $this->get('camdram.security.acl.provider')->getOrganisationIdsByUser($entity);
        $orgs = $this->getDoctrine()->getManager()->getRepository('ActsCamdramBundle:Organisation')->findById($ids);
        $ids = $this->get('camdram.security.acl.provider')->getEntitiesByUser($entity, '\\Acts\\CamdramBundle\\Entity\\Show');
        $shows = $this->getDoctrine()->getRepository('ActsCamdramBundle:Show')->findIdsByDate($ids);
        $view = $this->view(array(
            'user' => $entity,
            'organisations' => $orgs,
            'shows' => $shows
            ), 200)
            ->setTemplate('ActsCamdramAdminBundle:User:show.html.twig')
            ->setTemplateVar('user')
        ;

        return $view;
    }

    public function editAction($identifier)
    {
        $this->checkAuthenticated();
        $entity = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('EDIT', $entity);

        $form = $this->getForm($entity);

        return $this->view($form, 200)
            ->setTemplateVar('form')
            ->setTemplate('ActsCamdramAdminBundle:User:edit.html.twig');
    }

    public function putAction(Request $request, $identifier)
    {
        $this->checkAuthenticated();
        $entity = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('EDIT', $entity);

        $form = $this->getForm($entity);

        $form->bind($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->routeRedirectView('get_user', $this->getRouteParams($form->getData()));
        } else {
            return $this->view($form, 400)
                ->setTemplateVar('form')
                ->setTemplate('ActsCamdramAdminBundle:User:edit.html.twig');
        }
    }

    public function removeAction($identifier)
    {
        $this->checkAuthenticated();
        $entity = $this->getEntity($identifier);
        $this->get('camdram.security.acl.helper')->ensureGranted('DELETE', $entity);

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        return $this->routeRedirectView('get_user');
    }

    public function newAceAction(Request $request, $identifier)
    {
        $form = $this->createForm(new AddAclType(), array('identifier' => $identifier));

        return $this->view($form, 200)
            ->setTemplateVar('form')
            ->setTemplate('ActsCamdramAdminBundle:User:ace-new-form.html.twig');
    }

    public function postAceAction(Request $request, $identifier)
    {
        $form = $this->createForm(new AddAclType(), array('identifier' => $identifier));
        $form->bind($request);
        if ($form->isValid()) {
            $user = $this->getEntity($identifier);
            $data = $form->getData();
            $this->get('camdram.security.acl.provider')->grantAccess($data['entity'], $user, $this->getUser());

            return $this->routeRedirectView('get_'.$this->type, $this->getRouteParams($user));
        } else {
            return $this->view($form, 400)
                ->setTemplateVar('user')
                ->setTemplate('ActsCamdramAdminBundle:User:ace-new.html.twig');
        }
    }

    /**
     * @param $identifier
     * @Get("/users/{identifier}/reset-password")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordAction($identifier)
    {
        $this->checkAuthenticated();
        $user = $this->getEntity($identifier);

        $token = $this->get('camdram.security.token_generator')->generatePasswordResetToken($user);
        $this->get('camdram.security.email_dispatcher')->sendPasswordResetEmail($user, $token);
        $url = $this->generateUrl('acts_camdram_security_reset_password',
            array('email' => $user->getEmail(), 'token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('ActsCamdramAdminBundle:User:reset-password-complete.html.twig',
              array('user' => $user, 'url' => $url));
    }

    public function getMergeAction($identifier)
    {
        $this->checkAuthenticated();
        $user = $this->getEntity($identifier);

        return $this->render('ActsCamdramAdminBundle:User:merge.html.twig', array(
            'user' => $user,
            'form' => $this->get('acts_camdram_admin.user_merger')->createForm()->createView()
        ));
    }

    /**
     * @param $identifier
     * @param $request Request
     *
     * @return $this
     */
    public function mergeAction($identifier, Request $request)
    {
        $this->checkAuthenticated();
        $this->get('camdram.security.acl.helper')->ensureGranted('ROLE_ADMIN');
        $user = $this->getEntity($identifier);
        $merger = $this->get('acts_camdram_admin.user_merger');

        $form = $merger->createForm();
        $form->submit($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $otherUser = $this->get('doctrine.orm.entity_manager')->getRepository('ActsCamdramSecurityBundle:User')
                ->findOneByFullEmail($data['email']);
            if ($otherUser) {
                if ($otherUser == $user) {
                    $form->addError(new FormError('You cannot merge a user with itself'));
                } else {
                    $newUser = $merger->mergeUsers($user, $otherUser, $data['keep_user'] == 'this');

                    return $this->redirectToRoute('get_user', array('identifier' => $newUser->getId()));
                }
            } else {
                $form->addError(new FormError('User not found'));
            }
        }

        return $this->render('ActsCamdramAdminBundle:User:merge.html.twig', array(
            'user' => $user,
            'form' => $form->createView()
        ));
    }
}
