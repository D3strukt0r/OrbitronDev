<?php

use App\Forum\Forum;
use App\Forum\ForumAcp;
use App\Forum\ForumBoard;
use Container\DatabaseContainer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

ForumAcp::addGroup(array(
	'parent' => 'root',
	'id'     => 'boards',
	'title'  => _('Boards'),
));

ForumAcp::addMenu(array(
	'parent' => 'boards',
	'id'     => 'list',
	'title'  => _('Manage Boards'),
	'href'   => 'board-list',
	'screen' => 'acp_html_board_list',
));

ForumAcp::addMenu(array(
	'parent' => 'null',
	'id'     => 'new_board',
	'title'  => _('New Board'),
	'href'   => 'new-board',
	'screen' => 'acp_html_new_board',
));

/**
 * @param \Twig_Environment $twig
 *
 * @param \Controller\ForumController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_board_list($twig, $controller)
{
    $database = DatabaseContainer::$database;
    if (is_null($database)) {
        throw new Exception('A database connection is required');
    }

    $forumId = Forum::url2Id($controller->parameters['forum']);
    $forum = new Forum($forumId);

    $boardList = ForumBoard::listBoardsTree($forum->getVar('id'), 0);

    return $twig->render('forum/theme_admin1/board-list.html.twig', array(
        'current_forum' => $forum->forumData,
        'board_list'    => $boardList,
    ));
}

/**
 * @param \Twig_Environment $twig
 *
 * @param \Controller\ForumController $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_new_board($twig, $controller)
{
    $database = DatabaseContainer::$database;
    if (is_null($database)) {
        throw new \Exception('A database connection is required');
    }

    $forumId = Forum::url2Id($controller->parameters['forum']);
    $forum = new Forum($forumId);

    $createBoardForm = $controller->createFormBuilder()
        ->add('name', TextType::class, array(
            'label'    => 'Board name',
            'required' => true,
        ))
        ->add('description', TextType::class, array(
            'label'    => 'Description',
            'required' => false,
        ))
        ->add('parent', ChoiceType::class, array(
            'label'    => 'Parent',
            'required' => true,
            'choices'  => ForumBoard::listBoardsFormSelect($forum->getVar('id'), 0),
            'expanded' => false, // select tag
            'multiple' => false,
        ))
        ->add('type', ChoiceType::class, array(
            'label'       => 'Type',
            'required'    => false,
            'choices'  => array(
                'Board'    => 1,
                'Category' => 2,
            ),
            'placeholder' => false,
            'expanded' => true, // radio buttons
            'multiple' => false,

        ))
        ->add('send', SubmitType::class, array(
            'label' => 'Submit',
        ))
        ->getForm();

    $request = $controller->getRequest();
    $createBoardForm->handleRequest($request);

    if ($createBoardForm->isSubmitted() && $createBoardForm->isValid()) {
        $formData = $createBoardForm->getData();

        ForumBoard::addBoard($forum->getVar('id'), $formData['name'], $formData['description'], $formData['parent'], $formData['type']);
        $boardAdded = true;

        return $twig->render('forum/theme_admin1/new-board.html.twig', array(
            'create_board_form' => $createBoardForm->createView(),
            'current_forum'     => $forum->forumData,
            'board_added'       => $boardAdded,
        ));
    }

    return $twig->render('forum/theme_admin1/new-board.html.twig', array(
        'create_board_form' => $createBoardForm->createView(),
        'current_forum'     => $forum->forumData,
    ));
}
