<?php

namespace Core;

class Request
{
    private $controller = DEFAULT_CONTROLLER;
    private $action = DEFAULT_ACTION;
    private $actionParameters = [];
    private $viewParams = [];

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

    /**
     * Forwards the request to the proper controller and action
     */
    public function dispatch(): void
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
        $errorView->setParams($this->viewParams);
        $errorView->show();
    }

    /**
     * Gets the action name
     * @return string The action name
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Gets the target controller name
     * @return string The controller name
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Returns all view params
     * @return array An array with all params and values
     */
    public function getViewParams(): array
    {
        return $this->viewParams;
    }

    /**
     * Returns a cookie value
     * @param string $param Cookie name
     * @param bool $trim If you want to trim that value
     * @return string The cookie value
     */
    public function getCookieParam(string $param, bool $trim = false): string
    {
        if (isset($_COOKIE[$param])) {
            return $trim ? trim($_COOKIE[$param]) : $_COOKIE[$param];
        }
        return '';
    }

    /**
     * Returns the requested get param value
     * @param string $param Param name
     * @param bool $trim If you want to trim that value. Only applies to strings.
     * @return string | array The get param value
     */
    public function getGetParam(string $param, bool $trim = false)
    {
        if (!empty($_GET[$param])) {
            $value = $_GET[$param];
            if (is_string($value) && $trim) {
                return trim($value);
            }
            return $value;
        }
        return '';
    }

    /**
     * Returns the requested post param value
     * @param string $param Param name
     * @param bool $trim If you want to trim that value. Only applies to strings.
     * @return string | array The post param value
     */
    public function getPostParam(string $param, bool $trim = false)
    {
        if (!empty($_POST[$param])) {
            $value = $_POST[$param];
            if (is_string($value) && $trim) {
                return trim($value);
            }
            return $value;
        }
        return '';
    }

    /**
     * Returns the requested view param value
     * @param string $param Param name
     * @param bool $trim If you want to trim that value. Only applies to strings.
     * @return mixed The view param value
     */
    public function getViewParam(string $param, bool $trim = false)
    {
        if (!empty($this->viewParams[$param])) {
            $value = $this->viewParams[$param];
            if (is_string($value) && $trim) {
                return trim($value);
            }
            return $value;
        }
        return '';
    }

    /**
     * Returns an uploaded file
     * @param string $file File index
     * @return array Files or file data
     */
    public function getSentFile(string $file): array
    {
        return !empty($_FILES[$file]) ? $_FILES[$file] : [];
    }

    /**
     * Returns a session param
     * @param string $param The requested param
     * @return mixed The session param value or an empty string if not found
     */
    public function getSessionParam(string $param)
    {
        if (!empty($_SESSION[$param])) {
            return $_SESSION[$param];
        }
        return '';
    }

    /**
     * Returns whether the request uses the POST method
     * @return bool If it is POST
     */
    public function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    /**
     * Returns whether the request uses the GET method
     * @return bool If it is GET
     */
    public function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === "GET";
    }

    /**
     * Generates a cookie
     * @param string $param Param name
     * @param mixed $value Param value
     * @param int $seconds Number of seconds of live
     * @param string $path Path where this cookie will be used
     */
    public function setCookieParam(string $param, $value, int $seconds = 3600, string $path = '/'): void
    {
        setcookie($param, $value, $seconds, $path);
    }

    /**
     * Sets a session param
     * @param string $param Param name
     * @param mixed $value Param value. If not set the param will be removed.
     */
    public function setSessionParam(string $param, $value = null): void
    {
        if ($value === null) {
            unset($_SESSION[$param]);
            return;
        }
        $_SESSION[$param] = $value;
    }

    /**
     * Sets a param to be sent to the view
     * @param string $param Param name
     * @param mixed $value Param value. If not set the param will be removed.
     */
    public function setViewParam(string $param, $value = null): void
    {
        if ($value === null) {
            unset($this->viewParams[$param]);
            return;
        }
        $this->viewParams[$param] = $value;
    }

    /**
     * Redirects the request to another URL
     * @param string $target The target URL
     */
    public function redirect(string $target): void
    {
        $this->setHeader('Location', $target);
        exit;
    }

    /**
     * Sets a header
     * @param string $name The name of the header
     * @param string $value The value of the header
     */
    public function setHeader(string $name, string $value)
    {
        if (!headers_sent()) {
            header("$name: $value");
        }
    }
}