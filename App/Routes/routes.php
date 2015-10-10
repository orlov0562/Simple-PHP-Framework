<?php

return [

    'frontend' => [
        'baseRoute' => null,
        'routes' => [
            'home' => [
                'route' => '/',
                'resolver' => ['App\Controllers\Frontend\IndexController', 'IndexAction'],
            ],

            'test' => [
                'route' => '/test',
                'middleware' => [['ip', [['128.0.0.1', '129.0.0.1']]]],
                'resolver' => function(){ echo 'Frontend test hello world'; },
            ],

            'login' => [
                'route' => '/login',
                'middleware' => [['autologin']],
                'resolver' => ['App\Controllers\Frontend\LoginController', 'IndexAction'],
            ],

            '*' => [
                'resolver' => ['App\Controllers\Frontend\ErrorsController', 'NotFoundAction'],
            ]
        ],
    ],

    'backend' => [
        'baseRoute' => '/admin',
        'middleware' => [['autologin'], ['role', ['admin']], ['test',['from-group-1', 'from-group-2']]],
        'routes' => [
            'home' => [
                'route' => '/',
                'resolver' => ['App\Controllers\Backend\IndexController', 'IndexAction'],
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
                'resolver' => ['App\Controllers\Backend\PostController', 'ListAction'],
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
                'resolver' => ['App\Controllers\Backend\PostController', 'EditAction'],
                'path' => ['admin', 'posts', 'edit'],
            ],

            'test' => [
                'route' => '/test',
                'middleware' => [['test',['from-route-1', 'from-route-2']]],
                'resolver' => function() {die('Backend test hello world');},
            ],

            '*' => [
                'resolver' => ['App\Controllers\Backend\ErrorsController', 'NotFoundAction'],
            ],
        ],
    ],

];