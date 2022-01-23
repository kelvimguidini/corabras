 <?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    //'host' => 'dftelhas.mysql.dbaas.com.br',
                    //'port' => '3306',
                    //'user' => 'dftelhas',
                    //'password' => 'df102030@',
                    //'dbname' => 'corabras'
                    'host' => 'corabras.mysql.dbaas.com.br',
                    'port' => '3306',
                    'user' => 'corabras',
                    'password' => 'Corabras*2020',
                    'dbname' => 'corabras'
                )
            )
        )
    )
);