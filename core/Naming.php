<?php

namespace Core;

class Naming
{
    /**
     * Generates an action method name
     * @param string $name Name of the action
     * @return string The action's method name
     */
    public static function getAction(string $name): string
    {
        return 'action' . ucfirst($name);
    }

    /**
     * Generates a controller class name
     * @param string $name The name of the controller
     * @return string The controller's class name
     */
    public static function getController(string $name): string
    {
        return 'Controllers\\' . ucfirst($name) . 'Controller';
    }

    /**
     * Generates the controller's class from the controller's class name
     * @param string $controller The controller's class name
     * @return string The controller's name
     */
    public static function getControllerPseudo(string $controller): string
    {
        $fullControllerName = explode('\\', $controller)[1];
        return lcfirst(substr($fullControllerName, 0, strlen($fullControllerName) - 10));
    }

    /**
     * Generates a model class name
     * @param string $name The name of the model
     * @return string The model's class name
     */
    public static function getModel(string $name): string
    {
        return 'Models\\' . ucfirst($name) . 'Model';
    }

    /**
     * Generates the model name from a model's class name
     * @param string $model The model class name
     * @return string The model's name
     */
    public static function getModelPseudo(string $model): string
    {
        $fullModelName = explode('\\', $model)[1];
        return lcfirst(substr($fullModelName, 0, strlen($fullModelName) - 5));
    }
}