<?php

namespace Controller;

use Controller;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function indexAction()
    {
        return new Response($this->container->get('twig')->render('default/projects/index.html.twig'));
    }
}
