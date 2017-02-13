<?php

namespace App\Forum;

use App\Core\DatabaseConnection;

class ForumPost
{
    /**
     * @param $thread_id
     * @param $parent_post_id
     * @param $user_id
     * @param $subject
     * @param $message
     *
     * @return string
     * @throws \Exception
     */
    static function createPost($thread_id, $parent_post_id, $user_id, $subject, $message)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iThreadId = (int)$thread_id;
        $iParentPostId = (int)$parent_post_id;
        $iUserId = (int)$user_id;
        $sSubject = (string)$subject;
        $sMessage = (string)$message; // TODO: This should bypass the BBCode parser

        $oAddPost = $database->prepare('INSERT INTO `forum_posts`(`thread_id`,`parent_post_id`,`user_id`,`subject`,`message`,`time`) VALUES (:thread_id,:parent_post_id,:user_id,:subject,:message,:time)');
        $oAddPost->execute(array(
            ':thread_id'      => $iThreadId,
            ':parent_post_id' => $iParentPostId,
            ':user_id'        => $iUserId,
            ':subject'        => $sSubject,
            ':message'        => $sMessage,
            ':time'           => time(),
        ));

        $iNewPostId = $database->lastInsertId();
        $iThreadInBoardId = ForumThread::getVar($iThreadId, 'board_id');

        ForumThread::updatePost($iThreadInBoardId, USER_ID, time());
        self::updateRepliesCountInThread($iThreadId);
        self::updatePostsCountInForum($iThreadId, $iUserId, time());

        return $iNewPostId;
    }

    /**
     * @param $post_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function postExists($post_id)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iPostId = (int)$post_id;

        $oPostExists = $database->prepare('SELECT NULL FROM `forum_posts` WHERE `id`=:post_id');
        $oPostExists->execute(array(
            ':post_id' => $iPostId,
        ));

        if ($oPostExists->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param int $thread_id
     */
    public static function updateRepliesCountInThread($thread_id)
    {
        $iThreadId = (int)$thread_id;

        $iActualRepliesCount = ForumThread::getVar($iThreadId, 'replies');
        $iNewRepliesCount = $iActualRepliesCount + 1;
        ForumThread::setVar($iThreadId, 'replies', $iNewRepliesCount);
    }

    /**
     * @param int $thread_id
     * @param int $user_id
     * @param int $last_post_time
     */
    public static function updatePostsCountInForum($thread_id, $user_id, $last_post_time)
    {
        $iThreadId = (int)$thread_id;
        $iUserId = (int)$user_id;
        $iLastPostTime = (int)$last_post_time;
        $aBoards = array();
        $iBoardId = ForumThread::getVar($iThreadId, 'board_id');

        array_push($aBoards, $iBoardId);
        while ($iParentId = intval(ForumBoard::getVar($iBoardId, 'parent_id')) != 0) {
            $iNext = $iParentId;
            array_push($aBoards, $iNext);
            $iBoardId = $iNext;
        }

        foreach ($aBoards as $iBoard) {
            // Update last post
            $iActualPostsCount = ForumBoard::getVar($iBoard, 'posts');
            $iNewPostsCount = $iActualPostsCount + 1;
            ForumBoard::setVar($iBoard, 'posts', $iNewPostsCount);
            ForumThread::setVar($iThreadId, 'last_post_user_id', $iUserId);
            ForumThread::setVar($iThreadId, 'last_post_time', $iLastPostTime);
            ForumBoard::setVar($iBoard, 'last_post_user_id', $iUserId);
            ForumBoard::setVar($iBoard, 'last_post_time', $iLastPostTime);
        }
    }

    /*************************************************************************************************/

    /**
     * @param $post_id
     * @param $key
     *
     * @return string
     * @throws \Exception
     */
    public static function getVar($post_id, $key)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iPostId = (int)$post_id;
        $sKey = (string)$key;

        $oGetPostInfo = $database->prepare('SELECT * FROM `forum_posts` WHERE `id`=:post_id LIMIT 1');
        $oGetPostInfo->execute(array(
            ':post_id' => $iPostId,
        ));

        if (@$oGetPostInfo->rowCount() == 0) {
            return '';
        } else {
            $aPostInfo = $oGetPostInfo->fetchAll(\PDO::FETCH_ASSOC);
            return $aPostInfo[0][$sKey];
        }
    }

    /**
     * @param $post_id
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public static function setVar($post_id, $key, $value)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iPostId = (int)$post_id;
        $sKey = $key;
        $sValue = $value;

        $oSetPostInfo = $database->prepare('UPDATE `forum_posts` SET :key=:value WHERE `id`=:post_id');
        $oSetPostInfo->execute(array(
            ':key'     => $sKey,
            ':value'   => $sValue,
            ':post_id' => $iPostId,
        ));
    }
}