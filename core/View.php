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
        if (!empty($controller)) {
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
        } else {
            $contentView = __DIR__ . '/../views/' . ucfirst($view) . 'View.php';
            if (file_exists($contentView)) {
                $this->contentFile = $contentView;
            }
        }
    }

    /**
     * Sets an array of params to be passed to the view
     * @param array $params The array of params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Renders the view
     * @param bool $header Whether the header will be shown
     * @param bool $footer Whether the footer will be shown
     */
    public function show(bool $header = true, bool $footer = true): void
    {
        extract($this->params);
        if (file_exists($this->headerFile) && $header) {
            include_once $this->headerFile;
        }
        if (file_exists($this->contentFile)) {
            include_once $this->contentFile;
        }
        if (file_exists($this->footerFile) && $footer) {
            include_once $this->footerFile;
        }
    }

    /**
     * Includes an additional view without header and footer
     * @param string $view The view to be loaded
     * @param string $controller The controller that owns the view
     */
    private function renderExtra(string $view, string $controller = ''): void
    {
        $view = new View($view, $controller);
        $view->setParams($this->params);
        $view->show(false, false);
    }
}