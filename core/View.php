<?php

namespace Core;


class View
{
    public function __construct($view, $controller, $params)
    {
        extract($params);
        //If controller is specified check if that controller has the view if not try to load it in the root
        $headerView = __DIR__ . '/../views/' . $controller . '/HeaderView.php';
        $contentView = __DIR__ . '/../views/' . $controller . '/' . ucfirst($view) . 'View.php';
        $footerView = __DIR__ . '/../views/' . $controller . '/FooterView.php';
        if (file_exists($headerView)) {
            include_once $headerView;
        } else {
            include_once __DIR__ . '/../views/HeaderView.php';
        }
        if (file_exists($contentView)) {
            include_once $contentView;
        } else {
            include_once __DIR__ . '/../views/' . ucfirst($view) . 'View.php';
        }
        if (file_exists($footerView)) {
            include_once $footerView;
        } else {
            include_once __DIR__ . '/../views/FooterView.php';
        }
    }
}