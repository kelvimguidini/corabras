<?php
/**
 * Configuração de Banco de Dados de Produção - Subdomínio Blocos
 * 
 * Banco de Dados: fddd5815_blocos
 * Usuário:        fddd5815_blocos
 * Senha:          3{Vg2J9%5q6O3UzJ
 * Host (cPanel):  localhost (ou 69.6.212.253)
 */

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'fddd5815_blocos',
                    'password' => '3{Vg2J9%5q6O3UzJ',
                    'dbname'   => 'fddd5815_blocos',
                    'charset'  => 'utf8',
                ],
            ],
        ],
    ],
];
