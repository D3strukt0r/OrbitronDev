<?php

namespace App\Forum;

use Container\DatabaseContainer;

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

        $iBoardId = (int)$board_id;
        $sThreadName = (string)$thread_name;
        $sMessage = (string)$message; // TODO: This should bypass the BBCode parser
        $iUserId = (int)$user_id;

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
        $aBoards = array();

        array_push($aBoards, $iBoardId);
        while ($iParentId = intval(ForumBoard::getVar($iBoardId, 'parent_id')) != 0) {
            $iNext = $iParentId;
            array_push($aBoards, $iNext);
            $iBoardId = $iNext;
        }

        foreach ($aBoards as $iBoard) {
            // Update threads count in boards
            $iThreads = ForumBoard::getVar($iBoard, 'threads');
            $iNewThreads = $iThreads + 1;
            ForumBoard::setVar($iBoard, 'threads', $iNewThreads);
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
        $iUserId = (int)$user_id;
        $iTime = (int)$time;
        $aBoards = array();

        array_push($aBoards, $iBoardId);
        while ($iParentId = intval(ForumBoard::getVar($iBoardId, 'parent_id')) != 0) {
            $iNext = $iParentId;
            array_push($aBoards, $iNext);
            $iBoardId = $iNext;
        }

        foreach ($aBoards as $iBoard) {
            // Update last post
            ForumBoard::setVar($iBoard, 'last_post_user_id', $iUserId);
            ForumBoard::setVar($iBoard, 'last_post_time', $iTime);
        }
    }

    /**
     * @param $thread_id
     */
    public static function addThreadView($thread_id)
    {
        $iThreadId = (int)$thread_id;

        $iViews = ForumThread::getVar($iThreadId, 'views');
        $iNewViews = $iViews + 1;
        ForumThread::setVar($iThreadId, 'views', $iNewViews);
    }

    /*****************************************************************************************/

    /**
     * @param $thread_id
     * @param $key
     *
     * @return string
     * @throws \Exception
     */
    public static function getVarStatic($thread_id, $key)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iThreadId = (int)$thread_id;
        $sKey = (string)$key;

        $oGetThreadInfo = $database->prepare('SELECT * FROM `forum_threads` WHERE `id`=:thread_id LIMIT 1');
        $oGetThreadInfo->execute(array(
            ':thread_id' => $iThreadId,
        ));

        if (@$oGetThreadInfo->rowCount() == 0) {
            return '';
        } else {
            $aThreadInfo = $oGetThreadInfo->fetchAll(\PDO::FETCH_ASSOC);
            return $aThreadInfo[0][$sKey];
        }
    }

    /**
     * @param int $thread_id
     * @param string $key
     * @param string $value
     *
     * @throws \Exception
     */
    public static function setVar($thread_id, $key, $value)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iThreadId = (int)$thread_id;
        $sKey = $key;
        $sValue = $value;

        $oSetBoardInfo = $database->prepare('UPDATE `forum_threads` SET :key=:value WHERE `id`=:thread_id');
        $oSetBoardInfo->execute(array(
            ':key'       => $sKey,
            ':value'     => $sValue,
            ':thread_id' => $iThreadId,
        ));
    }

    /******************************************************************************/

    private $threadId;
    public $threadData;

    /**
     * Forum constructor.
     *
     * @param $thread_id
     *
     * @throws \Exception
     */
    public function __construct($thread_id)
    {
        $this->threadId = (int)$thread_id;

        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $getData = $database->prepare('SELECT * FROM `forum_threads` WHERE `id`=:thread_id LIMIT 1');
        if (!$getData->execute(array(':thread_id' => $this->threadId))) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($getData->rowCount() > 0) {
                $data = $getData->fetchAll();
                $this->threadData = $data[0];
            } else {
                $this->threadData = null;
            }
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->threadData[$key];
        return $value;
    }
}