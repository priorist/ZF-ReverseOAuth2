<?php
namespace ReverseOAuth2\Client;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class YoutubeFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cf = $serviceLocator->get('Config');

        $me = new Youtube();
        $me->setOptions(new \ReverseOAuth2\ClientOptions($cf['reverseoauth2']['youtube']));

        return $me;
    }
}
