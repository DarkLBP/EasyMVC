<?php

namespace Core;

class Utils {
    /**
     * Generates an URL from a controller, an action, and the given params
     * @param string $controller The controller name
     * @param string $action The action name
     * @param array $params The params that should be passed to the controller's action
     * @return string The generated URL
     */
    public static function getURL(string $controller = '', string $action = '', array $params = []) {
        $url = ($_SERVER["HTTPS"] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
        if (!empty($controller)) {
            $url .= "$controller";
        }
        if (!empty($action)) {
            $url .= "/$action";
        }
        if (!empty($params)) {
            if (empty($action)) {
                $url .= "//" . implode('/', $params);
            } else {
                $url .= "/" . implode('/', $params);
            }
        }
        return $url;
    }
}