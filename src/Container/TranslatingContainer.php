<?php

namespace Container;

use Symfony\Component\Translation\Loader\CsvFileLoader;
use Symfony\Component\Translation\Loader\IcuDatFileLoader;
use Symfony\Component\Translation\Loader\IcuResFileLoader;
use Symfony\Component\Translation\Loader\IniFileLoader;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\QtFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

class TranslatingContainer
{
    /**
     * TODO: Should get locale if somewhere defined
     *
     */


    /** @var \Symfony\Component\Translation\Translator $translator */
    public static $translator = null;

    private $defaultLocale = 'en';

    /** @var \Kernel $kernel */
    private $kernel = null;

    /**
     * RoutingContainer constructor.
     *
     * @param \Kernel $kernel
     */
    function __construct($kernel)
    {
        $this->kernel = $kernel;

        $this->initLocaleListener(); // TODO: This is doing an error

        $translator = new Translator('en_US', new MessageSelector());
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addLoader('yml', new YamlFileLoader());
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addLoader('xliff', new XliffFileLoader());
        $translator->addLoader('po', new PoFileLoader());
        $translator->addLoader('mo', new MoFileLoader());
        $translator->addLoader('ts', new QtFileLoader());
        $translator->addLoader('csv', new CsvFileLoader());
        $translator->addLoader('res', new IcuResFileLoader());
        $translator->addLoader('dat', new IcuDatFileLoader());
        $translator->addLoader('ini', new IniFileLoader());
        $translator->addLoader('json', new JsonFileLoader());

        $directory = __DIR__ . '/../../app/translation';
        $files = scandir($directory);
        foreach ($files as $file) {
            if (!in_array($file, array('.', '..'))) {
                $fileParts = explode('.', $file); // "0" is the name, "1" is the locale, "2" is the type (php ect.)
                $translator->addResource($fileParts[2], $directory . '/' . $file, $fileParts[1], $fileParts[0]);
            }
        }
        $translator->setFallbackLocales(array('en_US'));

        TranslatingContainer::$translator = $translator;
        $kernel->set('translator', $translator);
    }

    function initLocaleListener()
    {
        $request = $this->kernel->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }
}
