<?php

namespace Container;

use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\YamlExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Validator\Validation;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

class TemplatingContainer
{
    /** @var \Twig_Environment $defaultFormTheme */
    private $twig = null;

    /**
     * Templating constructor.
     *
     * @param \Kernel $kernel
     * @param bool    $cache
     */
    function __construct($kernel, $cache = false)
    {
        ///////// Form extension //////
        // the path to TwigBridge library so Twig can locate the
        // form_div_layout.html.twig file
        $appVariableReflection = new \ReflectionClass('\Symfony\Bridge\Twig\AppVariable');
        $vendorTwigBridgeDir = dirname($appVariableReflection->getFileName());

        ///////// Init ///////////
        $twigVariables = array();
        if ($cache) {
            $twigVariables['cache'] = __DIR__ . '/../var/cache/templating';
        }
        if ($kernel->environment == 'development' || $kernel->environment == 'dev') {
            $twigVariables['debug'] = true;
        }
        $loader = new Twig_Loader_Filesystem(array(
            __DIR__ . '/../../app/views',
            $vendorTwigBridgeDir . '/Resources/views/Form',
        ));
        $this->twig = new Twig_Environment($loader, $twigVariables);

        $globalVariable = new AppVariable();
        $globalVariable->setEnvironment(APPLICATION_ENV);
        $globalVariable->setDebug(true);
        $requestStack = new RequestStack();
        $requestStack->push($kernel->getRequest());
        $globalVariable->setRequestStack($requestStack);

        if ($kernel->environment == 'development' || $kernel->environment == 'dev') {
            $this->twig->addExtension(new Twig_Extension_Debug());
        }
        $this->yamlExtension();
        $this->translationExtension($kernel);
        $this->formExtension($kernel);
        $this->assetExtension();
        $this->routingExtension($kernel);

        $this->twig->addGlobal('app', $globalVariable);

        $kernel->set('twig', $this->twig);
    }

    function yamlExtension()
    {
        $this->twig->addExtension(new YamlExtension());
    }

    /**
     * @param \Kernel $kernel
     */
    function translationExtension($kernel)
    {
        /** \Symfony\Component\Translation\Translator $translator */
        $translator = $kernel->get('translator');
        $this->twig->addExtension(new TranslationExtension($translator));
    }

    /**
     * @param \Kernel $kernel
     */
    function formExtension($kernel)
    {
        // CSRF
        $session = $kernel->get('session');

        $csrfGenerator = new UriSafeTokenGenerator();
        $csrfStorage = new SessionTokenStorage($session);
        $csrfManager = new CsrfTokenManager($csrfGenerator, $csrfStorage);

        // Validator
        $validator = Validation::createValidator();

        // Twig
        $defaultThemes = array();
        //$defaultThemes[] = 'form_layout.html.twig'; // the Twig file that holds all the default markup for rendering forms. This file comes with TwigBridge
        $defaultThemes[] = 'form_div_layout.html.twig'; // TODO: Use this (<---) one, so forms are displayed correctly
        //$defaultThemes[] = 'bootstrap_3_layout.html.twig';
        $defaultThemes[] = 'form_widget.html.twig';
        $formEngine = new TwigRendererEngine($defaultThemes, $this->twig);
        $this->twig->addRuntimeLoader(new \Twig_FactoryRuntimeLoader(array(
            TwigRenderer::class => function () use ($formEngine, $csrfManager) {
                return new TwigRenderer($formEngine, $csrfManager);
            },
        )));

        $this->twig->addExtension(new FormExtension(new TwigRenderer($formEngine, $csrfManager)));

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->addExtension(new CsrfExtension($csrfManager))
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();
        $kernel->set('form.factory', $formFactory);
    }

    function assetExtension()
    {
        $requestStack = new RequestStack();
        $assetContext = new RequestStackContext($requestStack);
        $assetPackage = new Packages(new PathPackage('', new EmptyVersionStrategy(), $assetContext));
        $this->twig->addExtension(new AssetExtension($assetPackage));
    }

    /**
     * @param \Kernel $kernel
     */
    function routingExtension($kernel)
    {
        $generator = new UrlGenerator($kernel->get('routing.routes'), $kernel->get('routing.context'));
        $this->twig->addExtension(new RoutingExtension($generator));
    }
}