<?php

namespace Acts\CamdramSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{

    public function redirectAction($service)
    {
        return new RedirectResponse($this->container->get('camdram.security.utils')->getAuthorizationUrl($service));
    }

    public function connectUsersAction()
    {
        $token = $this->get('security.context')->getToken();
        $name = $token->getLastService()->getUserInfo('name');


        $possible_users = $this->getDoctrine()->getRepository('ActsCamdramBundle:User')
            ->findUsersWithSimilarName($name);

        $possible_users = $this->get('camdram.security.name_utils')
                ->filterPossibleUsers($name, $possible_users);

        if (count($possible_users) == 0) {
            return $this->redirect($this->generateUrl('camdram_security_no_existing_user'));
        }

        if ($this->getRequest()->getMethod() == 'POST') {
            $user_ids = array_keys($this->getRequest()->get('link_users', array()));
            $users = array();
            foreach ($user_ids as $id)
            {
                foreach ($possible_users as $u) {
                    if ($u->getId() == $id) $users[] = $u;
                }
            }

            if (count($users) == 0) return $this->redirect($this->generateUrl('camdram_security_no_existing_user'));
            else {
                $token->setPotentialUsers($users);
                return $this->redirect($this->generateUrl('camdram_security_connect_users_process'));
            }
        }
        else {
            return $this->render('ActsCamdramSecurityBundle:Default:connect_users.html.twig',
                array(
                    'service_name' => $token->getLastService()->getName(),
                    'name' => $name,
                    'possible_users' => $possible_users
                )
            );
        }
    }

    public function connectUsersProcessAction()
    {
        $token = $this->get('security.context')->getToken();
        $users = $token->getPotentialUsers();
        if (!$users) return $this->redirect($this->generateUrl('camdram_security_no_existing_user'));

        $user_status = array();
        $picked_next_user = false;
        $provider = $this->get('camdram.security.user.provider');

        foreach ($users as &$user) {
            $user = $provider->refreshUser($user);

            if ($token->isUserValidated($user)) {
                $user_status[$user->getId()] = 'validated';
            }
            elseif ($picked_next_user == false) {
                $user_status[$user->getId()] = 'next';
                $picked_next_user = true;
            }
            else {
                $user_status[$user->getId()] = 'pending';
            }
        }

        if ($picked_next_user == false) {
            $token->setPotentialUsers(array());
            $token->setUser($users[0]);
            $token->setAuthenticated(true);
            return $this->redirect($this->generateUrl('camdram_security_login'));
        }

        return $this->render('ActsCamdramSecurityBundle:Default:connect_users_process.html.twig', array(
            'user_status' => $user_status,
            'users' => $users
        ));
    }

    public function noExistingUserAction()
    {

    }

    public function confirmAddIdentityAction()
    {

    }

}
