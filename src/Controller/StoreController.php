<?php

namespace Controller;

use Controller;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function indexAction()
    {
        return new Response($this->container->get('templating')->render('store/index.html.twig'));
    }
}