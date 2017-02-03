<?php

namespace Container;

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;

class RoutingContainer
{
    /**
     * RoutingContainer constructor.
     *
     * @param \Kernel $kernel
     */
    function __construct($kernel)
    {
        // look inside *this* directory
        $configLocations = array(
            __DIR__ . '/../../app/config',
        );
        $locator = new FileLocator($configLocations);
        $loader = new YamlFileLoader($locator);
        $routes = $loader->load('routing.yml');
        $kernel->set('routing.routes', $routes);

        $context = new RequestContext();
        $context->fromRequest($kernel->getRequest());
        $kernel->set('routing.context', $context);

        $matcher = new UrlMatcher($routes, $context);

        try {
            $parameters = $matcher->match($kernel->getRequest()->getPathInfo());
            //$parameters = $matcher->match(strtok($_SERVER["REQUEST_URI"],'?')); // "strtok" is here to only get everything before the "?"
            $kernel->set('routing', $parameters);

            $kernel->getRequest()->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $kernel->getRequest()->attributes->set('_route_params', $parameters);

            $generator = new UrlGenerator($routes, $context);
            $kernel->set('router', $generator);
        } catch (ResourceNotFoundException $error) {
            $kernel->set('routing.error', $error);
        } catch (MethodNotAllowedException $error) {
            $kernel->set('routing.error', $error);
        }
    }
}