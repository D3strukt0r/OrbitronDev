<?php

namespace Controller;

use Controller;

class ProductController extends Controller
{
    public function indexAction()
    {
        return $this->render('default/product/index.html.twig');
    }

    public function productAction()
    {
        return $this->render('default/product/type/' . $this->parameters['type'] . '.html.twig');
    }
}
