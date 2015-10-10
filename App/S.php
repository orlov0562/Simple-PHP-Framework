<?php

namespace App;

class S {
    private static $framework = null;

    protected static function framework()
    {
        if (is_null(self::$framework)) {
            throw new \Exception('Framework not initialized');
        }
        return self::$framework;
    }

    public static function run(array $configuration)
    {
        self::$framework = new \Orlov0562\Simple\Framework($configuration);
        self::$framework->run();
    }

    public static function service($serviceName)
    {
        return self::framework()->getService($serviceName);
    }

    public static function model($modelName, array $constructParams=[])
    {
        return self::framework()->getModel($modelName, $constructParams);
    }

    public static function helper($helperName)
    {
        return self::framework()->getHelper($helperName);
    }

    public static function router(){
            return self::framework()->getRouter();
    }

    public static function conf(){
            return self::framework()->getConfig();
    }


    public static function currentRoute(){
            return self::framework()->getRouter()->getCurrentRoute();
    }

    public static function response(){
            return self::framework()->getResponse();
    }
}

