<?php

namespace Core;

class Request
{
    private $controller = '';
    private $action = '';
    private $actionParameters = [];

    public function __construct()
    {
        $request = trim($_SERVER["REQUEST_URI"], '/');
        $request = explode("?", $request)[0];
        $requestSegments = explode("/", $request);

        if (!empty($requestSegments[0])) {
            $this->controller = Naming::getController($requestSegments[0]);
        } else {
            $this->controller = Naming::getController(DEFAULT_CONTROLLER);
        }

        if (!empty($requestSegments[1])) {
            $this->action = Naming::getAction($requestSegments[1]);
        } else {
            $this->action = Naming::getAction(DEFAULT_ACTION);
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
        if (class_exists($this->controller)) {
            $instance = new $this->controller($this);
            if (method_exists($instance, $this->action)) {
                $return = $instance->{$this->action}($this->actionParameters);
                if ($return !== false) {
                    return;
                }
            }
        }
        $defaultClass = Naming::getController(DEFAULT_CONTROLLER);
        $defaultMethod = Naming::getAction("error");
        (new $defaultClass($this))->$defaultMethod();
    }

    public function getGetParam(string $param, bool $trim = true): string
    {
        if (isset($_GET[$param])) {
            if ($trim) {
                return trim($_GET[$param]);
            }
            return $_GET[$param];
        }
        return '';
    }

    public function getPostParam(string $param, bool $trim = true): string
    {
        if (isset($_POST[$param])) {
            if ($trim) {
                return trim($_POST[$param]);
            }
            return $_POST[$param];
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

    public function redirect($target)
    {
        header("Location: $target");
        exit;
    }
}