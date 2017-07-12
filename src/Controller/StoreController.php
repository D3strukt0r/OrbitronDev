<?php

namespace Controller;

use Controller;

class StoreController extends Controller
{
    public function indexAction()
    {
        return $this->render('store/index.html.twig');
    }
}