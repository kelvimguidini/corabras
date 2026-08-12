<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'login',
                    ),
                ),
            ),
            'sair' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/sair',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'sair',
                    ),
                ),
            ),
            'excluirproduto' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/excluirproduto[/:id]',
                    
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'excluirproduto',
                    ],
                ],
            ],
            'excluircusto' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/excluircusto[/:id]',
                    
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'excluircusto',
                    ],
                ],
            ],
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => [
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller'    => 'Application\Controller\Index',
                        'action'        => 'index',
                    ],
                ],
            ],
            'cadastrar' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/cadastrar[/:id]',
                    
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'cadastrar',
                    ],
                ],
            ],
            'custo' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/custo[/:id]',
                    
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'custo',
                    ],
                ],
            ],
            'excluir' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/excluir[/:id]',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'excluir',
                    ],
                ],
            ],
            'cadastrarcliente' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/cadastrarcliente[/:id]',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'cadastrarcliente',
                    ],
                ],
            ],
            'carregarclientes' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/carregarclientes[/:id]',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'carregarclientes',
                    ],
                ],
            ],
            'excluircliente' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/excluircliente[/:id]',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'excluircliente',
                    ],
                ],
            ],
            'visualizar' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/visualizar[/:id]',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'visualizar',
                    ],
                ],
            ],
            'relatorio' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/relatorio[/:id]',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action' => 'relatorio',
                    ],
                ],
            ],
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => Controller\IndexController::class,
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            'my_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . "/src/Application/Model"
                ),
            ),
            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    'Application\Model' => 'my_annotation_driver'
                )
            )
        )
    )
    
);