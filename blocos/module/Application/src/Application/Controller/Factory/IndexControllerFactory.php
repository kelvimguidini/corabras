<?php

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Psr\Container\ContainerInterface;

class IndexControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entitymanager.orm_default');
        return new IndexController($em);
    }
}
