<?php

namespace App;

class S {
    private static $framework = null;

    protected function framework()
    {
        if (is_null($this->framework)) {
            throw new \Exception('Framework not initialized');
        }
        return $this->framework;
    }

    public static function run(array $configuration)
    {
        self::$framework = new \Orlov0562\Simple\Framework($configuration);
        self::$framework->run();
    }

    public static function service($serviceName)
    {
        return $this->framework()->getService($serviceName);
    }

    public static function model($modelName, array $constructParams=[])
    {
        return $this->framework()->getModel($modelName, $constructParams);
    }

    public static function helper($helperName)
    {
        return $this->framework()->getHelper($helperName);
    }

}

