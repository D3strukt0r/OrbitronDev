<?php

use App\Forum\Entity\Board;
use App\Forum\Entity\Forum;
use App\Forum\Form\CreateBoardType;
use App\Forum\ForumAcp;
use App\Forum\ForumHelper;

ForumAcp::addGroup(array(
    'parent' => 'root',
    'id'     => 'boards',
    'title'  => 'Boards',
));

ForumAcp::addMenu(array(
    'parent' => 'boards',
    'id'     => 'list',
    'title'  => 'Manage Boards',
    'href'   => 'board-list',
    'screen' => 'acp_html_board_list',
));

ForumAcp::addMenu(array(
    'parent' => 'null',
    'id'     => 'new_board',
    'title'  => 'New Board',
    'href'   => 'new-board',
    'screen' => 'acp_html_new_board',
));

/**
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_board_list(Controller $controller)
{
    $em = $controller->getEntityManager();

    //////////// TEST IF FORUM EXISTS ////////////
    /** @var \App\Forum\Entity\Forum $forum */
    $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $controller->parameters['forum']));
    if (is_null($forum)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF FORUM EXISTS ////////////

    $boardList = $em->getRepository(Board::class)->findBy(array('forum' => $forum, 'parent_board' => null));

    return $controller->renderView('forum/theme_admin1/board-list.html.twig', array(
        'current_forum' => $forum,
        'board_list'    => $boardList,
    ));
}

/**
 * @param \Controller $controller
 *
 * @return string
 * @throws Exception
 */
function acp_html_new_board(Controller $controller)
{
    $em = $controller->getEntityManager();
    $request = $controller->getRequest();

    //////////// TEST IF FORUM EXISTS ////////////
    /** @var \App\Forum\Entity\Forum $forum */
    $forum = $em->getRepository(Forum::class)->findOneBy(array('url' => $controller->parameters['forum']));
    if (is_null($forum)) {
        return $controller->render('error/error404.html.twig');
    }
    //////////// END TEST IF FORUM EXISTS ////////////

    $specialBoardList = ForumHelper::listBoardsFormSelect($forum, null);

    $createBoardForm = $controller->createForm(CreateBoardType::class, null, array('board_list' => $specialBoardList));

    $createBoardForm->handleRequest($request);
    if ($createBoardForm->isSubmitted() && $createBoardForm->isValid()) {
        $formData = $createBoardForm->getData();

        /** @var \App\Forum\Entity\Board|null $parentBoard */
        $parentBoard = $em->getRepository(Board::class)->findOneBy(array('id' => $formData['parent']));

        $newBoard = new Board();
        $newBoard
            ->setForum($forum)
            ->setParentBoard($parentBoard)
            ->setTitle($formData['name'])
            ->setDescription($formData['description'])
            ->setType($formData['type']);

        $em->persist($newBoard);
        $em->flush();

        return $controller->renderView('forum/theme_admin1/new-board.html.twig', array(
            'create_board_form' => $createBoardForm->createView(),
            'current_forum'     => $forum,
            'board_added'       => true,
        ));
    }

    return $controller->renderView('forum/theme_admin1/new-board.html.twig', array(
        'create_board_form' => $createBoardForm->createView(),
        'current_forum'     => $forum,
    ));
}
