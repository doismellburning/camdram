<?php
namespace Acts\CamdramSecurityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Acts\CamdramSecurityBundle\Entity\AccessControlEntry,
    Acts\CamdramSecurityBundle\Service\EmailDispatcher;

/**
 * AccessControlEntryListener
 *
 * Functions triggered by events generated by the Security Bundle. These functions
 * result in automated emails being sent by Camdram.
 *
 */
class AccessControlEntryListener
{
    private $email_dispatcher;

    public function __construct(EmailDispatcher $dispatcher)
    {
        $this->email_dispatcher = $dispatcher;
    }

    /**
     * Inform the person that they have been granted access to a resource on the
     * site, pending creating an account.
     */
    public function postPersist(AccessControlEntry $ace, LifecycleEventArgs $event)
    {
        switch ($ace->getType()) {
            case 'show':
            case 'society':
                $this->email_dispatcher->sendAceEmail($ace);
                break;
            case 'request-show':
                $this->email_dispatcher->sendShowAdminReqEmail($ace);
                break;
        }
    }
}