<?php

namespace App\Forum;

use App\Account\Entity\User;
use App\Forum\Entity\Board;
use App\Forum\Entity\Forum;
use Decoda\Decoda;
use Decoda\Hook\EmoticonHook;

class ForumHelper
{
    const DEFAULT_SHOW_THREAD_COUNT = 10;

    /**
     * Get all forums which belong to the given User
     *
     * @param \App\Account\Entity\User $user
     *
     * @return \App\Forum\Entity\Forum[]
     * @throws \Exception
     */
    public static function getOwnerForumList(User $user)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Forum\Entity\Forum[] $list */
        $list = $em->getRepository(Forum::class)->findBy(array('owner' => $user->getId()));

        return $list;
    }

    /**
     * Checks whether the given url exists, in other words, if the forum exists
     *
     * @param string $url
     *
     * @return bool
     * @throws \Exception
     */
    public static function urlExists($url)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Forum\Entity\Forum[] $find */
        $find = $em->getRepository(Forum::class)->findBy(array('url' => $url));

        if (!is_null($find)) {
            if (count($find)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get a breadcrumb for the current tree
     *
     * @param \App\Forum\Entity\Board $board
     *
     * @return \App\Forum\Entity\Board[]
     */
    public static function getBreadcrumb($board)
    {
        $boardsList = array();
        $parentBoard = $board->getParentBoard();

        while (!is_null($parentBoard)) {
            $next = $parentBoard;
            array_unshift($boardsList, $next);
            $board = $next;
            $parentBoard = $board->getParentBoard();
        }

        return $boardsList;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function formatBbCode(string $string)
    {
        $bbParser = new Decoda($string);
        $bbParser->defaults();
        $bbParser->addHook(new EmoticonHook());
        return nl2br($bbParser->parse());
    }

    /**
     * @param \App\Forum\Entity\Forum      $forum
     * @param \App\Forum\Entity\Board|null $board
     * @param int                          $level
     * @param array                        $list
     *
     * @return array
     */
    public static function listBoardsFormSelect($forum, $board, $level = 1, &$list = array())
    {
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var \App\Forum\Entity\Board[] $boardList */
        $boardList = $em->getRepository(Board::class)->findBy(array('forum' => $forum, 'parent_board' => $board));

        if (empty($list)) {
            $list['- Main (ID: 0)'] = 0;
        }

        foreach ($boardList as $currentBoard) {
            $line = '-';
            for ($i = strlen($line) - 1; $i < $level; $i++) {
                $line .= '-';
            }

            $title = $line.' '.$currentBoard->getTitle().' (ID: '.$currentBoard->getId().')';
            $list[$title] = $currentBoard->getId();

            // Has sub-boards
            if (count($currentBoard->getBoards())) {
                $nextLevel = $level + 1;
                self::listBoardsFormSelect($forum, $currentBoard, $nextLevel, $list);
            }
        }

        return $list;
    }
}
