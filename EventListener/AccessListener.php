<?php
namespace PPV\BaseBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Annotations\Reader;

use PPV\BaseBundle\Annotations\Access;
use PPV\BaseBundle\Annotations\User\UserAccessInterface;

/**
 * AccessListener
 *
 * @author Pavel Plutakhin <pavel.plutakhin@simbirsoft.com>
 */
class AccessListener implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var SecurityContext
     */
    private $context;

    /**
     * Constructor.
     *
     * @param Reader $reader An Reader instance
     */
    public function __construct(Reader $reader, SecurityContext $context)
    {
        $this->reader = $reader;
        $this->context = $context;
    }

    /**
     * Modifies the Request object to apply configuration information found in
     * controllers annotations like the template to render or HTTP caching
     * configuration.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);
        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof Access) {
                $user = $this->getUser();
                if (!$user instanceof UserInterface) {
                    throw new UnauthorizedHttpException('Пользователь не авторизован');
                }
                if (!$user instanceof UserAccessInterface) {
                    throw new \InvalidArgumentException('User must be an instance of UserAccessInterface');
                }

                if ((!$user->hasRoles($annotation->getRoles())) || // не имеет списка ролей
                    (!$user->hasPermissions($annotation->getPerms()))) { // не имеет списка прав
                    throw new AccessDeniedHttpException('Доступ запрещен');
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    private function getUser()
    {
        return $this->context->getToken()->getUser();
    }
}
