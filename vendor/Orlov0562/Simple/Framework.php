<?php

    namespace Orlov0562\Simple;

    use Orlov0562\Simple\Config;
    use Orlov0562\Simple\Router;

    class Framework {

        private $conf;
        private $services;
        private $helpers = [];
        private $router;

        public function __construct($conf) {
            $this->loadConf($conf);
            Config::setConfigDir($conf['appDir'].'/Configs/');
            $this->services = $this->getConfigFile('services');

            $routes = include $this->conf['appDir'].'/Routes/routes.php';
            $validators = include $this->conf['appDir'].'/Routes/validators.php';
            $middleware = include $this->conf['appDir'].'/Routes/middleware.php';
            $this->router = new Router($routes, $validators, $middleware);

            $this->response = new Response;

        }

        protected function loadConf($conf)
        {
            if (!isset($conf['env'])) throw new \Exception('Undefined Environment');
            if (!isset($conf['baseUrl'])) throw new \Exception('Undefined baseUrl');
            if (!isset($conf['webDir']) || !is_dir($conf['webDir'])) throw new \Exception('Undefined Web Directory');
            if (!isset($conf['appDir']) || !is_dir($conf['appDir'])) throw new \Exception('Undefined App Directory');
            $this->conf = $conf;
        }

        public function run()
        {
            $this->router->resolve();
        }

        public function getConfigFile($confPath)
        {
            return Config::get($confPath);
        }

        public function getConfig()
        {
            return $this->conf;
        }


        public function getService($serviceName, array $constructParams=[])
        {
            if (!isset($this->services[$serviceName])) throw new \Exception ('Undefined service');
            $class = $this->services[$serviceName];
            return new $class($constructParams);
        }

        public function getModel($modelName, array $constructParams=[])
        {
            $modelClassName = 'App\Models\\'.$modelName.'Model.php';
            return new $modelClassName($constructParams);
        }

        public function getHelper($helperName)
        {
            if (!isset($this->helpers[$helperName]))
            {
                $helperClassName = 'App\Helpers\\'.$helperName.'Helper.php';
                $this->helpers[$helperName] = new $helperClassName;
            }
            return $this->helpers[$helperName];
        }

        public function getRouter(){
            return $this->router;
        }

        public function getResponse(){
            return $this->response;
        }
/*
        public function getRequest($type='all')
        {
            switch($type)
            {
                default: return $_REQUEST; break;
                case 'GET': return $_GET; break;
                case 'POST': return $_POST; break;
            }
        }
 *
 */
    }

