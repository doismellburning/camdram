<?php
namespace Acts\CamdramBundle\Service;

use Acts\CamdramBundle\Entity\Show;
use Acts\CamdramSecurityBundle\Entity\User;
use Acts\CamdramSecurityBundle\Event\UserEvent;
use Acts\CamdramSecurityBundle\Service\EmailConfirmationTokenGenerator;
use Symfony\Component\Routing\RouterInterface;

class EmailDispatcher
{
    private $mailer;
    private $twig;
    private $from_address;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, $from_address)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->from_address = $from_address;
    }

    /**
     * Send an email to the relevant moderators when this show is created.
     * @param $creator The creator of the show entry.
     */
    public function sendShowCreatedEmail(Show $show, $creator, array $owners, array $moderators, array $admins)
    {
        $emails = array();
        foreach ($moderators as $user) {
            $emails[$user->getFullEmail()] = $user->getName();
        }

        $bccs = array();
        foreach ($admins as $user) {
            if (!isset($emails[$user->getFullEmail()])) {
                $bccs[$user->getFullEmail()] = $user->getName();
            }
        }

        $message = \Swift_Message::newInstance()
            ->setSubject('New show needs authorization on Camdram: '.$show->getName())
            ->setFrom(array($this->from_address => 'camdram.net'))
            ->setTo($emails)
            ->setBcc($bccs)
            ->setBody(
                $this->twig->render(
                    'ActsCamdramBundle:Email:show_created.txt.twig',
                    array(
                        'creator' => $creator,
                        'owners' => $owners,
                        'show' => $show,
                    )
                )
            )
        ;
        $this->mailer->send($message);
    }

    public function sendShowApprovedEmail(Show $show, array $owners)
    {
        $emails = array();
        foreach ($owners as $user) {
            $emails[$user->getFullEmail()] = $user->getName();
        }

        $message = \Swift_Message::newInstance()
            ->setSubject('Show authorised on Camdram: '.$show->getName())
            ->setFrom(array($this->from_address => 'camdram.net'))
            ->setTo($emails)
            ->setBody(
                $this->twig->render(
                    'ActsCamdramBundle:Email:show_approved.txt.twig',
                    array(
                        'show' => $show,
                    )
                )
            )
        ;
        $this->mailer->send($message);
    }

    public function sendContactUsEmail($from, $subject, $message)
    {

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setReplyTo($from)
            ->setTo($this->from_address)
            ->setBody($message)
        ;
        $this->mailer->send($message);
    }
}
