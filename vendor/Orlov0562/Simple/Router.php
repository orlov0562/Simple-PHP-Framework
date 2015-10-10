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
            $ret = null;

            $r = $this->getR();

            $isRouteFound = false;

            // вначале проверяем группы у которых указан baseUrl
            // так отсеем целые группы роутов у которых не совпадает baseUrl
            foreach($this->routes as $groupName=>$groupData) {
                if (isset($groupData['baseRoute'])) {
                    $baseRoute = rtrim($groupData['baseRoute'],'/').'/';
                    if (substr($r, 0, strlen($baseRoute)) == $baseRoute)
                    {
                        $gr = substr($r, strlen($baseRoute)-1);
                        $isRouteFound = $this->findRouteInGroup($gr, $groupName, $groupData);
                    }
                }
            }

            // теперь проверяем группы у которых НЕ указан baseUrl
            if (!$isRouteFound)
            {
                foreach($this->routes as $groupName=>$groupData) {
                    if (!isset($groupData['baseRoute'])) {
                        $isRouteFound = $this->findRouteInGroup($r, $groupName, $groupData);
                    }
                }
            }

            return $ret;
        }

        protected function findRouteInGroup($r, $groupName, $groupData)
        {
            $isRouteFound = false;

            if (isset($groupData['middleware'])) {
                $this->executeMiddleware($groupData['middleware']);
            }

            // нашли группу, пробуем искать роут
            foreach($groupData['routes'] as $routeName=>$routeData) {
                if ($routeName=='*') continue;

                $routeMatcherRegExp = $this->getRouteMatcherRegExp($routeData['route']);

                if (preg_match($routeMatcherRegExp, $r, $matcherVars))
                { // нашли совпадение по роуту

                    $isValidationPassed = true;
                    if (isset($routeData['validation']))
                    { // проверяем валидаторы
                        $isValidationPassed = $this->isValidationRulesPassed($matcherVars, $routeData['validation']);
                    }

                    if ($isValidationPassed)
                    { // прошли проверку валидаторами
                        $isRouteFound = true;

                        $routeVars = $this->getRouteVars($routeData['route']);

                        $resolverParams = $this->prepareResolverParams(
                                $routeVars,
                                $matcherVars,
                                isset($routeData['default']) ? $routeData['default'] : []
                        );

                        $this->currentRoute = [
                            'routeGroup' => $groupName,
                            'routeName' => $routeName,
                            'route' => $routeData,
                            'resolver' => $routeData['resolver'],
                            'resolverParams' => $resolverParams,
                        ];

                        if (isset($routeData['middleware'])) {
                            $this->executeMiddleware($routeData['middleware']);
                        }

                        $ret = $this->callResolver($this->currentRoute['resolver'], $this->currentRoute['resolverParams']);

                        break;
                    }
                }
            }

            if (!$isRouteFound)
            {
                if (isset($groupData['routes']['*'])) { // Роут который ловит все остальное
                    $isRouteFound = true;
                    $this->currentRoute = [
                            'routeGroup' => $groupName,
                            'routeName' => '*',
                            'route' => $groupData['routes']['*'],
                            'resolver' => $groupData['routes']['*']['resolver'],
                            'resolverParams' => [],
                    ];

                    if (isset($routeData['middleware'])) {
                        $this->executeMiddleware($routeData['middleware']);
                    }

                    $ret = $this->callResolver($this->currentRoute['resolver'], $this->currentRoute['resolverParams']);
                }
                        }
            return $isRouteFound;
        }

        protected function getRouteMatcherRegExp($route)
        {
            $routeMatcher = $route;
            if (strpos($routeMatcher, '['))
            { // есть необязательные элементы, заменяем их на группы
                $routeMatcher = str_replace(['[',']'],['(?:',')?'], $routeMatcher);
            }

            if (strpos($routeMatcher, '@'))
            { // есть переменные, захватываем их
                $routeMatcher = preg_replace('~/@([a-z0-9]+)~i','/(?<$1>[^/]+)', $routeMatcher);
            }
            return '~^'.$routeMatcher.'$~';
        }

        protected function isValidationRulesPassed(array $matcherVars, array $validationRules)
        {
            $ret = true;
            foreach($validationRules as $validationVar=>$validators)
            {
                if (isset($matcherVars[$validationVar]))
                // тут надо проверять на обязательные и не обязательные переменные
                // например такой путь /hello/@var/world[/@page]
                //  будет valid, если в matchedVars не будет @var
                { // нашли переменную для валидации в роутере
                    $isVarValid = true;

                    foreach($validators as $validator)
                    {
                        if (!isset($this->validators[$validator[0]])) throw new \Exception('Undefined validator');
                        $validatorClass = $this->validators[$validator[0]][0];
                        $validatorMethod = $this->validators[$validator[0]][1];
                        $validatorParams = isset($validator[1]) ? $validator[1] : [];
                        array_unshift($validatorParams, $matcherVars[$validationVar]);
                        $isVarValid = call_user_func_array([new $validatorClass, $validatorMethod], $validatorParams);
                        if (!$isVarValid)
                        { // если хотя бы один валидатор не сработал выходим
                            break;
                        }
                    }

                    if (!$isVarValid)
                    { // не прошли проверку валидотрами
                        $ret = false;
                        break;
                    }

                }
            }
            return $ret;
        }

        protected function getRouteVars($route)
        {
            $ret = [];
            if (preg_match_all('~/@([^/\]]+)~', $route, $regs)) {
                for ($i=0; $i<count($regs[0]); $i++) {
                    $ret = $regs[1][$i];
                }
            }
            return $ret;
        }

        protected function prepareResolverParams(array $routeVars, array $matcherVars, array $defaultVars)
        {
            $ret = [];
            foreach($routeVars as $routeVar)
            {
                if (isset($matcherVars[$routeVar])) {
                    $ret[ $routeVar ] = $matcherVars[ $routeVar ];
                } elseif (isset($defaultVars[$routeVar])) {
                    $ret[ $routeVar ] = $defaultVars[$routeVar];
                }
            }
            return $ret;
        }

        protected function callResolver($resolver, $resolverParams=[])
        {
            if (is_array($resolver))
            {
                $resolverClass = $resolver[0];
                $resolverMethod = $resolver[1];
                $resolverObject = new $resolverClass;
                $callBack = [$resolverObject, $resolverMethod];

                if (method_exists($resolverObject, '_before')) {
                    call_user_func_array([$resolverObject, '_before']);
                }

                $ret = call_user_func_array($callBack, $resolverParams);

                if (method_exists($resolverObject, '_after')) {
                    call_user_func_array([$resolverObject, '_after']);
                }

            } elseif (is_callable($resolver)) {
                $ret = call_user_func_array($resolver, $resolverParams);
            } else {
                throw new \Exception('Undefined resolver');
            }

            return $ret;
        }

        protected function executeMiddleware($middleware)
        {
            foreach($middleware as $mw)
            {
                if(!isset($this->middleware[$mw[0]])) throw new \Exception('Undefined middleware');
                $callBack = $this->middleware[$mw[0]];
                if (is_array($callBack)) $callBack[0] = new $callBack[0];
                $callBackParams = isset($mw[1]) ? $mw[1] : [];
                call_user_func_array($callBack, $callBackParams);
            }
        }

        public function getR()
        {
            return isset($_REQUEST['r']) ? '/'.$_REQUEST['r'] : '';
        }

        public function getCurrentRoute()
        {
            return $this->currentRoute;
        }

        public function getBaseUrl(){
            // output: /myproject/index.php
            $currentPath = $_SERVER['PHP_SELF'];

            // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
            $pathInfo = pathinfo($currentPath);

            // output: localhost
            $hostName = $_SERVER['HTTP_HOST'];

            // output: http://
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

            // return: http://localhost/myproject/
            return $protocol.$hostName.$pathInfo['dirname'];

        }

        public function getHomeUrl()
        {
            $baseUrl = \App\S::conf()['baseUrl'];
            if ($baseUrl=='auto') $baseUrl = $this->getBaseUrl();
            return $baseUrl;
        }

        public function getUrlByRoute($groupName, $routeName, array $params=[]){
            if (!isset($this->routes[$groupName]['routes'][$routeName])) throw new \Exception('Route not found');

            $routeData = $this->routes[$groupName]['routes'][$routeName];
            $route = $routeData['route'];

            if (strpos($route,'@')) {
                if (isset($routeData['validation'])) {
                    if (!$this->isValidationRulesPassed($params, $routeData['validation']))
                    {
                        throw new \Exception('Validation rules for route params not passed');
                    }

                    if ($params) { // если были указаны какие-то параметры
                        foreach($params as $var=>$val)
                        {
                            $route = preg_replace('~@'.preg_quote($var).'([^a-z0-9-]|$)~i', $val.'$1',$route);
                        }

                        // убираем квадратные скобки с диапозонами в которых заменены все переменные
                        while(preg_match('~\[([^@\]]+)\]~',$route)) {
                            $route = preg_replace('~\[([^\]]+)\]~','$1',$route);
                        }

                        // если остались не замененные переменные, пробуем заполнить значениями по-цмолчанию
                        if (strpos($route,'@') && isset($routeData['default'])) {
                            foreach($routeData['default'] as $var=>$val)
                            {
                                $route = preg_replace('~@'.preg_quote($var).'([^a-z0-9-]|$)~i', $val.'$1',$route);
                            }

                            // убираем квадратные скобки с диапозонами в которых заменены все переменные
                            while(preg_match('~\[([^@\]]+)\]~',$route)) {
                                $route = preg_replace('~\[([^\]]+)\]~','$1',$route);
                            }
                        }

                        if (strpos($route,'@')) {
                            throw new \Exception('The route needed additional variables: '.$route);
                        }
                    } elseif (strpos($route,'@')) { // если параметры не были указаны, но в роуте остаются переменные

                        // убираем квадратные "неважные" скобки
                        while(preg_match('~\[([^\]]+)\]~',$route)) {
                            $route = preg_replace('~\[([^\]]+)\]~','',$route);
                        }

                        if (strpos($route,'@')) {
                            throw new \Exception('The route needed additional variables: '.$route);
                        }

                    }

                }
            }

            $ret = $this->getHomeUrl()
                    .(isset($this->routes[$groupName]['baseRoute']) ? $this->routes[$groupName]['baseRoute']:'')
                    .$route;

            return $ret;
        }


    }