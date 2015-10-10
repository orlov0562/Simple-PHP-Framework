<?php

    namespace Orlov0562\Simple;

    class Router
    {

        private $routes;
        private $validators;
        private $middleware;
        private $currentRoute;

        public function __construct($routes, $validators, $middleware)
        {
            $this->routes = $routes;
            $this->validators = $validators;
            $this->middleware = $middleware;
        }

        public function resolve()
        {
            $r = $this->getR();

            // вначале проверяем группы у которых указан baseUrl
            foreach($this->routes as $groupName=>$groupData) {
                if (isset($groupData['baseRoute'])) {
                    $baseRoute = rtrim($groupData['baseRoute'],'/').'/';
                    if (substr($r, 0, strlen($baseRoute)) == $baseRoute)
                    {
                        $isRouteFound = false;
                        $gr = substr($r, strlen($baseRoute)-1);
                        // нашли группу, пробуем искать роут
                        foreach($groupData['routes'] as $routeName=>$routeData) {

                            if (!isset($routeData['route'])) continue;

                            $routeMatcher = $routeData['route'];

                            if (strpos($routeMatcher, '['))
                            { // есть необязательные элементы, заменяем их на группы
                                $routeMatcher = str_replace(['[',']'],['(?:',')?'], $routeMatcher);
                            }

                            if (strpos($routeMatcher, '@'))
                            { // есть переменные, захватываем их
                                $routeMatcher = preg_replace('~/@([a-z0-9]+)~i','/(?<$1>[^/]+)', $routeMatcher);

                            }
//echo $gr.'<br>';
//echo htmlspecialchars($routeMatcher).'<hr>';
                            if (preg_match('~^'.$routeMatcher.'$~', $gr, $routeVars))
                            { // нашли совпадение по роуту
//echo '-'.$routeMatcher.'<br>';
                                $isValidationPassed = true;

                                if (isset($routeData['validation']))
                                { // проверяем валидаторы
                                    foreach($routeData['validation'] as $validationVar=>$validators)
                                    {
                                        if (isset($routeVars[$validationVar]))
                                        { // нашли переменную для валидации в роутере
                                            $isVarValid = true;

                                            foreach($validators as $validator)
                                            {
                                                if (!isset($this->validators[$validator[0]])) throw new \Exception('Undefined validator');
                                                $validatorClass = $this->validators[$validator[0]][0];
                                                $validatorMethod = $this->validators[$validator[0]][1];
                                                $validatorParams = isset($validator[1]) ? $validator[1] : [];
                                                array_unshift($validatorParams, $routeVars[$validationVar]);
                                                $isVarValid = call_user_func_array([new $validatorClass, $validatorMethod], $validatorParams);
                                                if (!$isVarValid)
                                                { // если хотя бы один валидатор не сработал выходим
                                                    break;
                                                }
                                            }

                                            if (!$isVarValid)
                                            { // не прошли проверку валидотрами
                                                $isValidationPassed = false;
                                                break;
                                            }

                                        }
                                    }
                                } //

                                if ($isValidationPassed)
                                { // прошли проверку валидаторами
                                    $isRouteFound = true;

Проверка Middleware

                                    $resolverClass = $routeData['resolver'][0];
                                    $resolverMethod = $routeData['resolver'][1];
//echo $routeData['route'].'<br>';
                                    $resolverParams = [];
                                    if (preg_match_all('~/@([^/\]]+)~', $routeData['route'], $regs)) {
                                        for ($i=0; $i<count($regs[0]); $i++) {
                                            $routeVar = $regs[1][$i];
//echo '<br>'.$routeVar.'<br>';
                                            if (isset($routeVars[$routeVar])) {
                                                $resolverParams[ $routeVar ] = $routeVars[ $routeVar ];
                                            } elseif (isset($routeData['default'][$routeVar])) {
                                                $resolverParams[ $routeVar ] = $routeData['default'][$routeVar];
                                            }
                                        }
                                    }
//print_r($resolverParams);
//echo $resolverClass;
                                    $this->currentRoute = [
                                        'routeGroup' => $groupName,
                                        'routeName' => $routeName,
                                        'route' => $routeData,
                                    ];

                                    $resolverObject = new $resolverClass;

                                    if (method_exists($resolverObject, '_before')) {
                                        call_user_func_array([$resolverObject, '_before']);
                                    }

                                    call_user_func_array([$resolverObject, $resolverMethod], $resolverParams);

                                    if (method_exists($resolverObject, '_after')) {
                                        call_user_func_array([$resolverObject, '_after']);
                                    }
                                }
                            }

/*
                'route' => '/posts[/page/@page]',
                'validation' => [
                    'page'=>[
                        ['regexp', ['~^\d+$~']],
                        ['unsigned'],
                    ],
                ],
                'default' => ['page'=>1],
                'resolver' => ['Admin\Posts', 'List'],
                'path' => ['admin', 'posts'],
 *
 *  */
                        }

                        if (!$isRouteFound)
                        {
роут не найден проверяем есть ли роут * и если есть перенаправляем на него
если такого нет просто идем дальше
                            die('Route not found in this group');
                        }
                    }
                }
            }

проверяем группу без baseUrl


            //print_r($this->routes);
        }

        public function getR()
        {
            return isset($_REQUEST['r']) ? '/'.$_REQUEST['r'] : '';
        }

        public function getCurrentRoute()
        {
            return $this->currentRoute;
        }
    }