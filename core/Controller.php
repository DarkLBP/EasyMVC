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
     */
    protected function renderView($view = 'index'): void
    {
        $view = new View($view, Naming::getControllerPseudo(get_called_class()));
        $view->setParams($this->request->getViewParams());
        $view->show();
        exit;
    }

    /**
     * Returns a model instance
     * @param string $name Name of the model
     * @return Model The model instance
     */
    protected function getModel(string $name): Model
    {
        $modelName = Naming::getModel($name);
        return new $modelName();
    }
}