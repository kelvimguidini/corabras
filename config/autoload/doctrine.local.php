<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => \Doctrine\DBAL\Driver\PDOMySql\Driver::class,
                'params' => array(
                    'host' => 'corabras.mysql.dbaas.com.br',
                    'port' => '3306',
                    'user' => 'fddd5815_corabras',
                    'password' => 'Corabras*2020',
                    'dbname' => 'fddd5815_corabras'
                )
            )
        )
    )
);
