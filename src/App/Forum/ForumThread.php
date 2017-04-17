<?php

namespace App\Forum;

use Container\DatabaseContainer;
use PDO;

class ForumThread
{
    const DefaultShowThreadAmount = 10;

    /**
     * @param $board_id
     * @param $thread_name
     * @param $message
     * @param $user_id
     *
     * @return float
     * @throws \Exception
     */
    static function createThread($board_id, $thread_name, $message, $user_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iBoardId    = (int)$board_id;
        $sThreadName = (string)$thread_name;
        $sMessage    = (string)$message; // TODO: This should bypass the BBCode parser
        $iUserId     = (int)$user_id;

        $oAddThread = $database->prepare('INSERT INTO `forum_threads`(`user_id`,`board_id`,`topic`,`time`,`last_post_user_id`,`last_post_time`) VALUES (:user_id,:board_id,:topic,:time,:user_id,:time)');
        $oAddThread->execute(array(
            ':user_id'  => $iUserId,
            ':board_id' => $iBoardId,
            ':topic'    => $sThreadName,
            ':time'     => time(),
        ));

        $iNewThreadId = $database->lastInsertId();

        $oAddPostToNewThread = $database->prepare('INSERT INTO `forum_posts`(`thread_id`,`parent_post_id`,`user_id`,`subject`,`message`,`time`) VALUES (:thread_id,0,:user_id,:subject,:message,:time)');
        $oAddPostToNewThread->execute(array(
            ':thread_id' => $iNewThreadId,
            ':user_id'   => $iUserId,
            ':subject'   => $sThreadName,
            ':message'   => $sMessage,
            ':time'      => time(),
        ));

        self::addThreadCount($iBoardId);
        self::updatePost($iBoardId, $iUserId, time());

        $new_thread_id = (float)$iNewThreadId;

        return $new_thread_id;
    }

    /**
     * @param $thread_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function threadExists($thread_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iThreadId = (int)$thread_id;

        $oThreadExists = $database->prepare('SELECT NULL FROM `forum_threads` WHERE `id`=:thread_id');
        $oThreadExists->execute(array(
            ':thread_id' => $iThreadId,
        ));

        if ($oThreadExists->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param int $board_id
     */
    private static function addThreadCount($board_id)
    {
        $iBoardId = (int)$board_id;
        $aBoards  = array();

        array_push($aBoards, $iBoardId);
        while ($iParentId = intval(ForumBoard::getVarStatic($iBoardId, 'parent_id')) != 0) {
            $iNext = $iParentId;
            array_push($aBoards, $iNext);
            $iBoardId = $iNext;
        }

        foreach ($aBoards as $iBoard) {
            // Update threads count in boards
            $board       = new ForumBoard($iBoard);
            $iThreads    = $board->getVar('threads');
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
        $iBoardId = (int)$board_id;
        $iUserId  = (int)$user_id;
        $iTime    = (int)$time;
        $aBoards  = array();

        array_push($aBoards, $iBoardId);
        while ($iParentId = intval(ForumBoard::getVarStatic($iBoardId, 'parent_id')) != 0) {
            $iNext = $iParentId;
            array_push($aBoards, $iNext);
            $iBoardId = $iNext;
        }

        foreach ($aBoards as $iBoard) {
            // Update last post
            $board = new ForumBoard($iBoard);
            $board->setLastPostUserId($iUserId);
            $board->setLastPostTime($iTime);
        }
    }

    /******************************************************************************/

    private $threadId;
    private $notFound = false;
    public  $threadData;

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

    public function sync()
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $dbSync = $database->prepare('SELECT * FROM `forum_threads` WHERE `id`=:thread_id LIMIT 1');
        $dbSync->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
        if (!$dbSync->execute()) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($dbSync->rowCount() > 0) {
                $data             = $dbSync->fetchAll(PDO::FETCH_ASSOC);
                $this->threadData = $data[0];
            } else {
                $this->threadData = null;
                $this->notFound   = true;
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
     * @param $key
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

    public static function getVarStatic($thread_id, $key)
    {
        $thread = new ForumThread($thread_id);

        return $thread->getVar($key);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool|null
     * @throws \Exception
     *
     * TODO: Variable Key is not possible to be set in PDO sql
     */
    //public function setVar($key, $value)
    private function setVar($key, $value)
    {
        if ($this->exists()) {
            $database = DatabaseContainer::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

            $update = $database->prepare('UPDATE `forum_threads` SET :key=:value WHERE `id`=:thread_id');
            $update->bindValue(':key', $key, PDO::PARAM_STR);
            $update->bindValue(':value', $value, PDO::PARAM_STR);
            $update->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);

            if ($update->execute()) {
                $this->threadData[$key] = $value;
                $this->sync();

                return true;
            }

            return false;
        } else {
            return null;
        }
    }

    /**
     * @throws \Exception
     */
    public function addView()
    {
        if ($this->exists()) {
            $currentViews = (int)$this->getVar('views');
            $newViews     = $currentViews + 1;

            $database = DatabaseContainer::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }
            $update = $database->prepare('UPDATE `forum_threads` SET `views`=:views WHERE `id`=:thread_id');
            $update->bindValue(':views', $newViews, PDO::PARAM_INT);
            $update->bindValue(':thread_id', $this->threadId, PDO::PARAM_INT);
            if ($update->execute()) {
                $this->sync();
            } else {
                // TODO: Send a message to an admin that views are not being updated
            }
        }
    }
}
