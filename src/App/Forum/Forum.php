<?php

namespace App\Forum;

use App\Core\DatabaseConnection;
use Container\DatabaseContainer;
use PDO;

class Forum
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getForumList()
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $getAllForums = $database->prepare('SELECT `name`,`url`,`owner_id` FROM `forums`');
        if (!$getAllForums->execute()) {
            throw new \Exception('Cannot get list with all forums');
        } else {
            return $getAllForums->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param $url
     *
     * @return bool
     * @throws \Exception
     */
    public static function urlExists($url)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $getUrl = $database->prepare('SELECT NULL FROM `forums` WHERE `url`=:url');
        $getUrl->execute(array(
            ':url' => $url
        ));
        if($getUrl->rowCount()) {
            return true;
        }
        return false;
    }

    /**
     * @param $user_id
     *
     * @return array
     * @throws \Exception
     */
    // TODO: Is this function required?
    public static function getOwnerForumList($user_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $fUserId = (float)$user_id;

        $oGetForumList = $database->prepare('SELECT * FROM `forums` WHERE `owner_id`=:user_id');
        if (!$oGetForumList->execute(array(':user_id' => $fUserId))) {
            throw new \Exception('Cannot get list with all forums you own');
        } else {
            $aForums = array();
            $aForumData = $oGetForumList->fetchAll();
            foreach ($aForumData as $aForumData) {
                array_push($aForums, $aForumData);
            }
            return $aForums;
        }
    }

    /**
     * @param $forum_url
     *
     * @return mixed
     * @throws \Exception
     */
    public static function url2Id($forum_url)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetForumId = $database->prepare('SELECT `id` FROM `forums` WHERE `url`=:forum_url LIMIT 1');
        if (!$oGetForumId->execute(array(':forum_url' => $forum_url))) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $aForumData = $oGetForumId->fetchAll();
            return $aForumData[0]['id'];
        }
    }

    /**
     * @param $forum_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function forumExists($forum_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oForumExists = $database->prepare('SELECT null FROM `forums` WHERE `id`=:forum_id LIMIT 1');
        if (!$oForumExists->execute(array(':forum_id' => $forum_id))) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($oForumExists->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }

    /******************************************************************************/

    private $fForumId;
    public $forumData;

    /**
     * Forum constructor.
     *
     * @param $forum_id
     *
     * @throws \Exception
     */
    public function __construct($forum_id)
    {
        $this->fForumId = (float)$forum_id;

        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $getData = $database->prepare('SELECT * FROM `forums` WHERE `id`=:forum_id LIMIT 1');
        if (!$getData->execute(array(':forum_id' => $forum_id))) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if($getData->rowCount() > 0) {
                $data = $getData->fetchAll();
                $this->forumData = $data[0];
            } else {
                $this->forumData = null;
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
        $value = $this->forumData[$key];
        return $value;
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public function setVar($key, $value)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oUpdateTable = $database->prepare('UPDATE `forums` SET :key=:value WHERE `id`=:forum_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'      => $key,
            ':value'    => $value,
            ':forum_id' => $this->fForumId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new \RuntimeException('Could not execute sql');
        }
    }

    /******************************************************************************/

    /**
     * @param $board_id
     *
     * @return array
     */
    public static function getBreadcrumb($board_id)
    {
        $aBoards = array();

        while ($iParentId = intval(ForumBoard::getVar2($board_id, 'parent_id')) != 0) {
            $iNext = (int)$iParentId;
            array_push($aBoards, $iNext);
            $board_id = $iNext;
        }

        return $aBoards;
    }
}