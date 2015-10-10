<?php

return [

    'frontend' => [
        'baseRoute' => null,
        'routes' => [
            'home' => [
                'route' => '/',
                'resolver' => ['Index', 'Index'],
            ],

            'test' => [
                'route' => '/test',
                'middleware' => [['ip', ['127.0.0.1']]],
                'resolver' => function(){ echo 'test'; },
            ],

            'login' => [
                'route' => '/login',
                'middleware' => [['autologin']],
                'resolver' => ['Login', 'Index'],
            ],

            '*' => [
                'resolver' => ['Errors', 'NotFound'],
            ]
        ],
    ],

    'backend' => [
        'baseRoute' => '/admin',
        'middleware' => [['autologin'], ['role', ['admin']]],
        'routes' => [
            'home' => [
                'route' => '/',
                'resolver' => ['App\Controllers\Admin\IndexController', 'IndexAction'],
                'path' => ['admin'],
            ],

            'posts' => [
                'route' => '/posts[/page/@page]',
                'validation' => [
                    'page'=>[
                        ['regexp', ['~^\d+$~']],
                        ['unsigned'],
                    ],
                ],
                'default' => ['page'=>1],
                'resolver' => ['App\Controllers\Admin\PostController', 'ListAction'],
                'path' => ['admin', 'posts'],
            ],

            'posts_edit' => [
                'route' => '/posts/edit/@id',
                'validation' => [
                    'id'=> [
                        ['regexp', ['~^\d+$~']],
                        ['unsigned'],
                    ]
                ],
                'resolver' => ['App\Controllers\Admin\PostController', 'EditAction'],
                'path' => ['admin', 'posts', 'edit'],
            ],

            '*' => [
                'resolver' => ['Errors', 'NotFound'],
            ],
        ],
    ],

];