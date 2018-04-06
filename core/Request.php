<?php

namespace Core;

class Request
{
    private $controller = DEFAULT_CONTROLLER;
    private $action = DEFAULT_ACTION;
    private $actionParameters = [];

    public function __construct()
    {
        $request = trim($_SERVER["REQUEST_URI"], '/');
        $request = explode("?", $request)[0];
        $requestSegments = explode("/", $request);

        if (!empty($requestSegments[0])) {
            $this->controller = $requestSegments[0];
        }

        if (!empty($requestSegments[1])) {
            $this->action = $requestSegments[1];
        }

        if (count($requestSegments) > 2) {
            unset($requestSegments[0]);
            unset($requestSegments[1]);
            //Action parameters found
            foreach ($requestSegments as $segment) {
                $this->actionParameters[] = $segment;
            }
        }
    }

    public function dispatch()
    {
        /**
         * @var $instance Controller
         */
        $controller = Naming::getController($this->controller);
        $action = Naming::getAction($this->action);
        if (class_exists($controller)) {
            $instance = new $controller($this);
            if (method_exists($instance, $action)) {
                $return = $instance->{$action}($this->actionParameters);
                if ($return !== false) {
                    return;
                }
            }
        }
        $errorView = new View('error');
        $errorView->show();
    }

    public function getGetParam(string $param, bool $trim = false): string
    {
        if (!empty($_GET[$param])) {
            return $trim ? trim($_GET[$param]) : $_GET[$param];
        }
        return '';
    }

    public function getPostParam(string $param, bool $trim = false): string
    {
        if (!empty($_POST[$param])) {
            return $trim ? trim($_POST[$param]) : $_POST[$param];
        }
        return '';
    }

    public function getSessionParam(string $param)
    {
        if (!empty($_SESSION[$param])) {
            return $_SESSION[$param];
        }
        return '';
    }

    public function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    public function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === "GET";
    }

    public function setSessionParam(string $param, $value)
    {
        $_SESSION[$param] = $value;
    }

    public function redirect($target)
    {
        header("Location: $target");
        exit;
    }
}