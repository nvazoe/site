<?php

namespace App\AppBundle\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AppSubscriber implements EventSubscriberInterface
{
    private $authorization;
    private $requestStack;
    private $config;

    /**
     * AppSubscriber constructor.
     *
     * @param AuthorizationChecker $authorization
     * @param RequestStack $requestStack
     * @param ConfigManager $config
     */
    public function __construct(AuthorizationCheckerInterface $authorization, RequestStack $requestStack, ConfigManager $config)
    {
        $this->authorization = $authorization;
        $this->requestStack = $requestStack;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            EasyAdminEvents::PRE_NEW => 'checkUserRights',
            EasyAdminEvents::PRE_LIST => 'checkUserRights',
            EasyAdminEvents::PRE_EDIT => 'checkUserRights',
            EasyAdminEvents::PRE_SHOW => 'checkUserRights',
            EasyAdminEvents::PRE_DELETE => 'checkUserRights',
        );
     }

    /**
     * show an error if user is not superadmin and tries to manage restricted stuff
     *
     * @param GenericEvent $event event
     * @return null
     * @throws AccessDeniedException
     */
    public function checkUserRights(GenericEvent $event)
    {
        // if super admin, allow all
        $request = $this->requestStack->getCurrentRequest()->query;
        if ($this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }

        $entity = $request->get('entity');
        $action = $request->get('action');

        $config = $this->config->getBackendConfig(); 
        // check for permission for each action
        foreach ($config['entities'] as $k => $v) {
            if ($entity == $k && !$this->authorization->isGranted($v[$action]['role'])) {
                throw new AccessDeniedException();
            }
        }
    }
}
