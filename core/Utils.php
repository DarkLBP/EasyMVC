<?php

namespace Core;

class Utils {
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