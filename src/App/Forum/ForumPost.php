<?php

namespace App\Forum;

use Container\DatabaseContainer;
use PDO;

class ForumPost
{
    /**
     * @param int    $thread_id
     * @param int    $parent_post_id
     * @param int    $user_id
     * @param string $subject
     * @param string $message
     *
     * @return string
     * @throws \Exception
     */
    static function createPost($thread_id, $parent_post_id, $user_id, $subject, $message)
    {
        $database = DatabaseContainer::getDatabase();

        $subject = (string)$subject;
        $message = (string)$message; // TODO: This should bypass the BBCode parser

        $addPost = $database->prepare('INSERT INTO `forum_posts`(`thread_id`,`parent_post_id`,`user_id`,`subject`,`message`,`time`) VALUES (:thread_id,:parent_post_id,:user_id,:subject,:message,:time)');
        $addPost->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
        $addPost->bindValue(':parent_post_id', $parent_post_id, PDO::PARAM_INT);
        $addPost->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $addPost->bindValue(':subject', $subject, PDO::PARAM_STR);
        $addPost->bindValue(':message', $message, PDO::PARAM_STR);
        $addPost->bindValue(':time', time(), PDO::PARAM_INT);
        $addPost->execute();

        $iNewPostId       = $database->lastInsertId();
        $iThreadInBoardId = ForumThread::intent($thread_id)->getVar('board_id');

        ForumThread::updatePost($iThreadInBoardId, USER_ID, time());
        self::updateRepliesCountInThread($thread_id);
        self::updatePostsCountInForum($thread_id, $user_id, time());

        return $iNewPostId;
    }

    /**
     * @param int $post_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function postExists($post_id)
    {
        $database = DatabaseContainer::getDatabase();

        $postExists = $database->prepare('SELECT NULL FROM `forum_posts` WHERE `id`=:post_id');
        $postExists->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $postExists->execute();

        if ($postExists->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param int $thread_id
     */
    public static function updateRepliesCountInThread($thread_id)
    {
        $thread = new ForumThread($thread_id);

        $iActualRepliesCount = (int)$thread->getVar('replies');
        $iNewRepliesCount    = $iActualRepliesCount + 1;

        $thread->setReplies($iNewRepliesCount);
    }

    /**
     * @param int $thread_id
     * @param int $user_id
     * @param int $last_post_time
     */
    public static function updatePostsCountInForum($thread_id, $user_id, $last_post_time)
    {
        $thread   = new ForumThread($thread_id);
        $iBoardId = $thread->getVar('board_id');

        $boardsList = array();
        $iParentId  = (int)ForumBoard::intent($iBoardId)->getVar('parent_id');

        array_push($boardsList, $iBoardId);
        while ($iParentId != 0) {
            $iNext = (int)$iParentId;
            array_push($boardsList, $iNext);
            $iBoardId  = $iNext;
            $iParentId = (int)ForumBoard::intent($iBoardId)->getVar('parent_id');
        }

        foreach ($boardsList as $iBoard) {
            // Update last post
            $board             = new ForumBoard($iBoard);
            $iActualPostsCount = $board->getVar('posts');
            $iNewPostsCount    = $iActualPostsCount + 1;
            ForumBoard::intent($iBoard)->setPosts($iNewPostsCount);
            ForumThread::intent($thread_id)->setLastPostUserId($user_id);
            ForumThread::intent($thread_id)->setLastPostTime($last_post_time);
            ForumBoard::intent($iBoard)->setLastPostUserId($user_id);
            ForumBoard::intent($iBoard)->setLastPostTime($last_post_time);
        }
    }

    /*************************************************************************************************/

    private $postId;
    private $notFound = false;
    public  $postData;

    /**
     * ForumPost constructor.
     *
     * @param int $post_id
     *
     * @throws \Exception
     */
    public function __construct($post_id)
    {
        $this->boardId = (int)$post_id;
        $this->sync();
    }

    public static function intent($post_id)
    {
        $class = new self($post_id);

        return $class;
    }

    public function sync()
    {
        $database = DatabaseContainer::getDatabase();

        $dbSync = $database->prepare('SELECT * FROM `forum_posts` WHERE `id`=:post_id LIMIT 1');
        $dbSync->bindValue(':post_id', $this->postId, PDO::PARAM_INT);
        $sqlSuccess = $dbSync->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($dbSync->rowCount() > 0) {
                $data            = $dbSync->fetchAll(PDO::FETCH_ASSOC);
                $this->postData = $data[0];
            } else {
                $this->postData = null;
                $this->notFound  = true;
            }
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return !$this->notFound;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getVar($key)
    {
        if (!$this->exists()) {
            return null;
        }

        return $this->postData[$key];
    }


    /**
     * @param string $message
     *
     * @return $this|null
     */
    public function setMessage($message)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_posts` SET `message`=:value WHERE `id`=:post_id');
        $update->bindValue(':post_id', $this->postId, PDO::PARAM_INT);
        $update->bindValue(':value', $message, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }
}