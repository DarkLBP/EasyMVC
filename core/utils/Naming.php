<?php

namespace Core\Utils;


class Naming
{
    public static function getAction($name)
    {
        return 'action' . ucfirst($name);
    }

    public static function getController($name)
    {
        return 'Controllers\\' . ucfirst($name) . 'Controller';
    }

    public static function getControllerPseudo($controller)
    {
        $fullControllerName = explode('\\', $controller)[1];
        return strtolower(substr($fullControllerName, 0, strlen($fullControllerName) - 10));
    }

    public static function getModel($name)
    {
        return 'Models\\' . ucfirst($name) . 'Model';
    }

    public static function getModelPseudo($model)
    {
        $fullModelName = explode('\\', $model)[1];
        return strtolower(substr($fullModelName, 0, strlen($fullModelName) - 5));
    }
}