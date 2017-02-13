<?php

namespace Controller;

use Controller;
use Form\RecaptchaType;
use Kernel;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DefaultController extends Controller
{
    public function redirToIndexAction()
    {
        return $this->redirectToRoute('homepage');
    }

    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    public function aboutAction()
    {
        return $this->render('default/about.html.twig');
    }

    public function aboutTeamAction()
    {
        return $this->render('default/about-team.html.twig');
    }

    public function contactAction()
    {
        //    $requestStack = new RequestStack();
        //    $requestStack->push(new Request());
        $contactForm = $this->createFormBuilder()
            ->add('name', TextType::class, array(
                'label'       => 'Name',
                'attr'        => array(
                    'pattern' => '.{1,}', //minlength
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your name')),
                    new Length(array('min' => 4, 'max' => 255)),
                ),
            ))
            ->add('email', EmailType::class, array(
                'label'       => 'E-mail',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your email address')),
                    new Email(array('message' => 'Please enter a VALID email address')),
                    new Length(array('max' => 255)),
                ),
            ))
            ->add('subject', TextType::class, array(
                'label'       => 'Subject',
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your subject')),
                    new Length(array('min' => 10, 'max' => 255)),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'label'       => 'Message',
                'attr'        => array(
                    'rows' => 6,
                ),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please enter your message')),
                    new Length(array('min' => 10, 'max' => 255)),
                ),
            ))
            ->add('recaptcha', RecaptchaType::class, array(
                'private_key'    => '6Ldec_4SAAAAAMqZOBRgHo0KRYptXwsfCw-3Pxll',
                'public_key'     => '6Ldec_4SAAAAAJ_TnvICnltNqgNaBPCbXp-wN48B',
                'recaptcha_ajax' => false,
                'attr'           => array(
                    'options' => array(
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal',
                        'defer' => true,
                        'async' => true,
                    ),
                ),
                /*
                'constraints' => array(
                    new RecaptchaConstraint(null, array(
                        'enabled' => true,
                        'privateKey' => '6LcFPwcUAAAAAP2vo5xPbUoVRAyq9VmyLEfXmazU',
                        'requestStack' => $requestStack,
                        'httpProxy' => array(
                            'host' => null,
                            'port' => null,
                            'auth' => null,
                        )
                    )),
                ),
                */
                'mapped'         => false,
            ))
            ->add('send_to_own', CheckboxType::class, array(
                'label'    => 'Sende eine Kopie an meine Email Addresse',
                'required' => false,
                'attr'     => array(
                    'options' => array(
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal',
                        'defer' => true,
                        'async' => true,
                    ),
                ),
                'mapped'   => false,
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Send message',
            ))
            ->getForm();
        /*
                if (isset($_POST[$form->getName()])) {
                    $form->submit($_POST[$form->getName()]);
                    if ($form->isValid()) {
                        //$request->getSession()->getFlashBag()->add('success', 'Your email has been sent! Thanks!');
                        var_dump('VALID', $form->getData());
                        die;
                    }
                }
        */

        $request = Kernel::$kernel->getRequest();

        if ($request->isMethod('POST')) {
            $contactForm->handleRequest($request);

            if ($contactForm->isSubmitted() && $contactForm->isValid()) {
                $formData = $contactForm->getData();

                if ($contactForm->get('send_to_own')->getData()) {
                    $message = Swift_Message::newInstance()
                        ->setSubject(trim($formData['subject']))
                        ->setFrom(array(trim($formData['email']) => trim($formData['name'])))
                        ->setTo(array('info@orbitrondev.org'))
                        ->setCc(array(trim($formData['email']) => trim($formData['name'])))
                        ->setBody($this->renderView('default/mail/contact.html.twig', array(
                            'ip'      => $request->getClientIp(),
                            'name'    => $formData['name'],
                            'message' => $formData['message'],
                        )), 'text/html');
                } else {
                    $message = Swift_Message::newInstance()
                        ->setSubject(trim($formData['subject']))
                        ->setFrom(array(trim($formData['email']) => trim($formData['name'])))
                        ->setTo(array('info@orbitrondev.org'))
                        ->setBody($this->renderView('mail/contact.html.twig', array(
                            'ip'      => $request->getClientIp(),
                            'name'    => $formData['name'],
                            'message' => $formData['message'],
                        )), 'text/html');
                }
                $this->get('mailer')->send($message);

                // TODO: Send message to UI as soon as email is sent
                //$request->getSession()->getFlashBag()->add('success', 'Your email has been sent! Thanks!');

                return $this->redirectToRoute('app_default_contact', array('success'));
            }
        }

        return $this->render('default/contact.html.twig', array(
            'contact_form' => $contactForm->createView(),
        ));
    }

    public function termsAction()
    {
        return $this->render('default/terms.html.twig');
    }

    public function privacyAction()
    {
        return $this->render('default/privacy.html.twig');
    }

    public function faqAction()
    {
        return $this->render('default/faq.html.twig');
    }

    public function searchAction()
    {
        $response = $this->searchHandler();
        if ($response !== null) {
            return $response;
        }

        // TODO: Handle form submission or use Google search
        return $this->render('default/search.html.twig');
    }

    private function searchHandler()
    {
        $searchForm = $this->createFormBuilder()
            ->setMethod('GET')
            ->add('search', TextType::class, array(
                'label'    => 'Search',
                'required' => true,
                'attr'     => array(
                    'pattern'     => '.{1,}',
                    'placeholder' => 'Search',
                ),
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Go',
            ))
            ->getForm();

        $request = Kernel::$kernel->getRequest();
        if ($request->isMethod('POST')) {
            $searchForm->handleRequest($request);

            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                return $this->redirect($this->generateUrl('app_default_search',
                    array('q' => $searchForm->get('search')->getData())));
            }
        }
        return null;
    }
}