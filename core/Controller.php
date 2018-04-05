<?php

namespace Core;

abstract class Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Renders a view using the controller that triggered the method
     * @param string $view
     * @param array $params
     */
    protected function renderView($view = 'index', $params = [])
    {
        new View($view, Naming::getControllerPseudo(get_called_class()), $params);
    }

    protected function getModel(string $name): Model
    {
        $modelName = Naming::getModel($name);
        return new $modelName();
    }
}