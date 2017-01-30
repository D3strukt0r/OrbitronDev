<?php

namespace Controller;

use Controller;

class BlogController extends Controller
{
    public function indexAction()
    {
        return $this->render('blog/index.html.twig');
    }
}