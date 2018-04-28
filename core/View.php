<?php

namespace Core;

class View
{
    private $params = [];
    private $headerFile = __DIR__ . '/../views/HeaderView.php';
    private $contentFile = __DIR__ . '/../views/IndexView.php';
    private $footerFile = __DIR__ . '/../views/FooterView.php';

    public function __construct(string $view, string $controller = '')
    {
        //If controller is specified check if that controller has the view if not try to load it in the root
        $headerView = __DIR__ . '/../views/' . $controller . '/HeaderView.php';
        $contentView = __DIR__ . '/../views/' . $controller . '/' . ucfirst($view) . 'View.php';
        $footerView = __DIR__ . '/../views/' . $controller . '/FooterView.php';
        if (file_exists($headerView)) {
            $this->headerFile = $headerView;
        }
        if (file_exists($contentView)) {
            $this->contentFile = $contentView;
        } else {
            $fallBack = __DIR__ . '/../views/' . ucfirst($view) . 'View.php';
            if (file_exists($fallBack)) {
                $this->contentFile = $fallBack;
            }
        }
        if (file_exists($footerView)) {
            $this->footerFile = $footerView;
        }
    }

    /**
     * Sets an array of params to be passed to the view
     * @param array $params The array of params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Renders the full view
     */
    public function show()
    {
        extract($this->params);
        include_once $this->headerFile;
        include_once $this->contentFile;
        include_once $this->footerFile;
    }
}