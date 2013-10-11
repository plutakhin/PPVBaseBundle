<?php
namespace PPV\BaseBundle\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * BaseController
 * 
 * @author Pavel Plutakhin <pavel.plutakhin@simbirsoft.com>
 */
class BaseController extends Controller
{
    /** Environment develop */
    const ENV_DEV = 'dev';
    /** Error domain */
    const ERROR_DOMAIN = 'error';
    /** Flash bag prop */
    const ERROR_FLASH_BUG = 'error';

    /**
     * Translation of a message
     * 
     * @param string $message
     * @param string $domain
     * @return string
     */
    protected function getTranslate($message, $domain = null)
    {
        return $this->get('translator')->trans($message, array(), $domain);
    }

    /**
     * Add error message
     * 
     * @param string $message
     * @param \Exception $e
     * @throws \Exception
     */
    protected function generateError($message, \Exception $e = null, FormInterface &$form = null)
    {
        $translatedMessage = $this->getTranslate($message, self::ERROR_DOMAIN);

        if (is_null($e) || ($this->getEnvironment() != self::ENV_DEV)) {
            if (is_null($form)) {
                $this->get('session')->getFlashBag()->add(self::ERROR_FLASH_BUG, $translatedMessage);
            } else {
                $form->addError(new FormError($translatedMessage));
            }
        } else {
            throw new \Exception ($translatedMessage, 0, $e);
        }
    }

    /**
     * Custom user authentication
     */
    protected function _authenticateUser(UserInterface $user, $credentials = null, $providerKey = 'main')
    {
        $token = new UsernamePasswordToken($user, $credentials, $providerKey, $user->getRoles());
        $this->get('security.context')->setToken($token);
    }

    /**
     * @return string
     */
    protected function getEnvironment()
    {
        return $this->get('service_container')->getParameter('kernel.environment');
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getRepository($entityName)
    {
        return $this->getDoctrine()->getManager()->getRepository($entityName);
    }
}
