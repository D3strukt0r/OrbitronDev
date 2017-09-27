<?php

namespace App\Forum;

use Container\DatabaseContainer;
use PDO;
use RuntimeException;

class ForumThread
{
    const DefaultShowThreadAmount = 10;

    /**
     * @param int    $board_id
     * @param string $thread_name
     * @param string $message
     * @param int    $user_id
     *
     * @return float
     * @throws \Exception
     */
    static function createThread($board_id, $thread_name, $message, $user_id)
    {
        $database = DatabaseContainer::getDatabase();

        $timeAdded = time();

        $addThread = $database->prepare('INSERT INTO `forum_threads`(`user_id`,`board_id`,`topic`,`time`,`last_post_user_id`,`last_post_time`) VALUES (:user_id,:board_id,:topic,:time,:user_id,:time)');
        $addThread->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $addThread->bindValue(':board_id', $board_id, PDO::PARAM_INT);
        $addThread->bindValue(':topic', $thread_name, PDO::PARAM_STR);
        $addThread->bindValue(':time', $timeAdded, PDO::PARAM_INT);
        $addThread->execute();

        $iNewThreadId = (int)$database->lastInsertId();

        // Add Post for the new thread
        $addPost = $database->prepare('INSERT INTO `forum_posts`(`thread_id`,`parent_post_id`,`user_id`,`subject`,`message`,`time`) VALUES (:thread_id,0,:user_id,:subject,:message,:time)');
        $addPost->bindValue(':thread_id', $iNewThreadId, PDO::PARAM_INT);
        $addPost->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $addPost->bindValue(':subject', $thread_name, PDO::PARAM_STR);
        $addPost->bindValue(':message', $message, PDO::PARAM_STR);
        $addPost->bindValue(':time', $timeAdded, PDO::PARAM_INT);
        $addPost->execute();

        self::addThreadCount($board_id);
        self::updatePost($board_id, $user_id, $timeAdded);

        return $iNewThreadId;
    }

    /**
     * @param int $thread_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function threadExists($thread_id)
    {
        $database = DatabaseContainer::getDatabase();

        $threadExists = $database->prepare('SELECT NULL FROM `forum_threads` WHERE `id`=:thread_id');
        $threadExists->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
        $threadExists->execute();

        if ($threadExists->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param int $board_id
     */
    private static function addThreadCount($board_id)
    {
        $aBoards = array();
        $iParentId = (int)ForumBoard::intent($board_id)->getVar('parent_id');

        array_push($aBoards, $board_id);
        while ($iParentId != 0) {
            $iNext = (int)$iParentId;
            array_push($aBoards, $iNext);
            $board_id = $iNext;
            $iParentId = (int)ForumBoard::intent($board_id)->getVar('parent_id');
        }

        foreach ($aBoards as $iBoard) {
            // Update threads count in boards
            $board = new ForumBoard($iBoard);
            $iThreads = $board->getVar('threads');
            $iNewThreads = $iThreads + 1;
            $board->setThreads($iNewThreads);
        }
    }

    /**
     * @param int $board_id
     * @param int $user_id
     * @param int $time
     */
    public static function updatePost($board_id, $user_id, $time)
    {
        $aBoards = array();
        $iParentId = (int)ForumBoard::intent($board_id)->getVar('parent_id');

        array_push($aBoards, $board_id);
        while ($iParentId != 0) {
            $iNext = (int)$iParentId;
            array_push($aBoards, $iNext);
            $board_id = $iNext;
            $iParentId = (int)ForumBoard::intent($board_id)->getVar('parent_id');
        }

        foreach ($aBoards as $iBoard) {
            // Update last post
            $board = new ForumBoard($iBoard);
            $board->setLastPostUserId($user_id);
            $board->setLastPostUsername($user_id);
            $board->setLastPostTime($time);
        }
    }

    /******************************************************************************/

    private $threadId;
    private $notFound = false;
    public $threadData;

    /**
     * Forum constructor.
     *
     * @param int $thread_id
     *
     * @throws \Exception
     */
    public function __construct($thread_id)
    {
        $this->threadId = (int)$thread_id;
        $this->sync();
    }

    /**
     * @param int $thread_id
     *
     * @return \App\Forum\ForumThread
     */
    public static function intent($thread_id)
    {
        $class = new self($thread_id);

        return $class;
    }

    public function sync()
    {
        $database = DatabaseContainer::getDatabase();

        $dbSync = $database->prepare('SELECT * FROM `forum_threads` WHERE `id`=:thread_id LIMIT 1');
        $dbSync->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
        if (!$dbSync->execute()) {
            throw new RuntimeException('Could not execute sql');
        } else {
            if ($dbSync->rowCount() > 0) {
                $data = $dbSync->fetchAll(PDO::FETCH_ASSOC);
                $this->threadData = $data[0];
            } else {
                $this->threadData = null;
                $this->notFound = true;
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
     * @return string
     */
    public function getVar($key)
    {
        if ($this->exists()) {
            $value = $this->threadData[$key];

            return $value;
        } else {
            return null;
        }
    }

    /**
     * @throws \Exception
     */
    public function addView()
    {
        if (!$this->exists()) {
            return null;
        }

        $currentViews = (int)$this->getVar('views');
        $newViews = $currentViews + 1;

        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_threads` SET `views`=:views WHERE `id`=:thread_id');
        $update->bindValue(':views', $newViews, PDO::PARAM_INT);
        $update->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this|null
     */
    public function setReplies($value)
    {
        if (!$this->exists()) {
            return null;
        }

        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_threads` SET `replies`=:value WHERE `id`=:thread_id');
        $update->bindValue(':value', $value, PDO::PARAM_INT);
        $update->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this|null
     */
    public function setLastPostUserId($value)
    {
        if (!$this->exists()) {
            return null;
        }

        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_threads` SET `last_post_user_id`=:value WHERE `id`=:thread_id');
        $update->bindValue(':value', $value, PDO::PARAM_INT);
        $update->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $value
     *
     * @return $this|null
     */
    public function setLastPostTime($value)
    {
        if (!$this->exists()) {
            return null;
        }

        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_threads` SET `last_post_time`=:value WHERE `id`=:thread_id');
        $update->bindValue(':value', $value, PDO::PARAM_INT);
        $update->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }
}
