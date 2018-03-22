<?php

namespace Controllers;

use Core\Controller;

class MainController extends Controller
{
    public function actionIndex()
    {
        $this->renderView('index');
    }

    public function actionError()
    {
        $this->renderView('error');
    }
}