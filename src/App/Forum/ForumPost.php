<?php

namespace App\Forum;

use Container\DatabaseContainer;

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
        $database = DatabaseContainer::getDatabase();

        $iThreadId     = (int)$thread_id;
        $iParentPostId = (int)$parent_post_id;
        $iUserId       = (int)$user_id;
        $sSubject      = (string)$subject;
        $sMessage      = (string)$message; // TODO: This should bypass the BBCode parser

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
        $thread = new ForumThread($iThreadId);
        $iThreadInBoardId = $thread->getVar('board_id');

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
        $database = DatabaseContainer::getDatabase();

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

        $thread = new ForumThread($iThreadId);

        $iActualRepliesCount = (int)$thread->getVar('replies');
        $iNewRepliesCount = $iActualRepliesCount + 1;

        $thread->setReplies($iNewRepliesCount);
    }

    /**
     * @param int $thread_id
     * @param int $user_id
     * @param int $last_post_time
     */
    public static function updatePostsCountInForum($thread_id, $user_id, $last_post_time)
    {
        $iThreadId = (int)$thread_id;
        $thread = new ForumThread($iThreadId);
        $iUserId = (int)$user_id;
        $iLastPostTime = (int)$last_post_time;
        $aBoards = array();
        $iBoardId = $thread->getVar('board_id');

        $iParentId = (int)ForumBoard::intent($iBoardId)->getVar('parent_id');

        array_push($aBoards, $iBoardId);
        while ($iParentId != 0) {
            $iNext = (int)$iParentId;
            array_push($aBoards, $iNext);
            $board_id = $iNext;
            $iParentId = (int)ForumBoard::intent($board_id)->getVar('parent_id');
        }

        foreach ($aBoards as $iBoard) {
            // Update last post
            $board = new ForumBoard($iBoard);
            $iActualPostsCount = $board->getVar('posts');
            $iNewPostsCount = $iActualPostsCount + 1;
            ForumBoard::intent($iBoard)->setPosts($iNewPostsCount);
            ForumThread::intent($iThreadId)->setLastPostUserId($iUserId);
            ForumThread::intent($iThreadId)->setLastPostTime($iLastPostTime);
            ForumBoard::intent($iBoard)->setLastPostUserId($iUserId);
            ForumBoard::intent($iBoard)->setLastPostTime($iLastPostTime);
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
        $database = DatabaseContainer::getDatabase();

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
        $database = DatabaseContainer::getDatabase();

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