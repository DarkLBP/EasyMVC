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
        $url = (!empty($_SERVER["HTTPS"]) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
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

    /**
     * Escapes the given data for HTML printing
     * @param array | string $data The data to be escaped
     * @return string | array The escaped data
     */
    public static function escapeData($data)
    {
        if (is_string($data)) {
            return htmlspecialchars($data);
        } else if (is_array($data)) {
            foreach ($data as &$value) {
                $value = self::escapeData($value);
            }
        }
        return $data;
    }
}