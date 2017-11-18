<?php

namespace Controller;

class ProjectController extends \Controller
{
    public function indexAction()
    {
        return $this->render('default/projects/index.html.twig');
    }
}
