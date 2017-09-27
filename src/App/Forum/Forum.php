<?php

namespace App\Forum;

use Container\DatabaseContainer;
use Exception;
use PDO;
use RuntimeException;

class Forum
{
    /**
     * Get a list of all existing forums
     *
     * @return array
     * @throws \Exception
     */
    public static function getForumList()
    {
        $database = DatabaseContainer::getDatabase();

        $getAllForums = $database->prepare('SELECT `name`,`url`,`owner_id` FROM `forums`');
        $sqlSuccess = $getAllForums->execute();

        if (!$sqlSuccess) {
            throw new Exception('Cannot get list with all forums');
        } else {
            return $getAllForums->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get all forums which belong to the given User
     *
     * @param int $user_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getOwnerForumList($user_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getAllForums = $database->prepare('SELECT * FROM `forums` WHERE `owner_id`=:user_id');
        $getAllForums->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $sqlSuccess = $getAllForums->execute();

        if (!$sqlSuccess) {
            throw new Exception('Cannot get list with all forums you own');
        } else {
            $forumList = array();
            $forumDataList = $getAllForums->fetchAll(PDO::FETCH_ASSOC);
            foreach ($forumDataList as $currentForumData) {
                array_push($forumList, $currentForumData);
            }

            return $forumList;
        }
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
        $database = DatabaseContainer::getDatabase();

        $getUrl = $database->prepare('SELECT NULL FROM `forums` WHERE `url`=:url');
        $getUrl->bindValue(':url', $url, PDO::PARAM_STR);
        $getUrl->execute();

        if ($getUrl->rowCount()) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether the given forum exists
     *
     * @param int $forum_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function forumExists($forum_id)
    {
        $database = DatabaseContainer::getDatabase();

        $forumExists = $database->prepare('SELECT NULL FROM `forums` WHERE `id`=:forum_id LIMIT 1');
        $forumExists->bindValue(':forum_id', $forum_id, PDO::PARAM_INT);
        $sqlSuccess = $forumExists->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            if ($forumExists->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    /**
     * Converts the given URL to the existing id of the forum.
     * Hint: always use "urlExists()" before using this function
     *
     * @param string $forum_url
     *
     * @return mixed
     * @throws \Exception
     */
    public static function url2Id($forum_url)
    {
        $database = DatabaseContainer::getDatabase();

        $getForumId = $database->prepare('SELECT `id` FROM `forums` WHERE `url`=:forum_url LIMIT 1');
        $getForumId->bindValue(':forum_url', $forum_url, PDO::PARAM_STR);
        $sqlSuccess = $getForumId->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $forumData = $getForumId->fetchAll(PDO::FETCH_ASSOC);

            return $forumData[0]['id'];
        }
    }

    /******************************************************************************/

    private $forumId;
    public $forumData;

    /**
     * Forum constructor.
     *
     * @param int $forum_id
     *
     * @throws \Exception
     */
    public function __construct($forum_id)
    {
        $this->forumId = $forum_id;

        $database = DatabaseContainer::getDatabase();

        $getData = $database->prepare('SELECT * FROM `forums` WHERE `id`=:forum_id LIMIT 1');
        $getData->bindValue(':forum_id', $this->forumId, PDO::PARAM_INT);
        $sqlSuccess = $getData->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            if ($getData->rowCount() > 0) {
                $data = $getData->fetchAll(PDO::FETCH_ASSOC);
                $this->forumData = $data[0];
            } else {
                $this->forumData = null;
            }
        }
    }

    /**
     * Get information of current forum
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->forumData[$key];

        return $value;
    }

    /**
     * Set the new forum name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if ($this->forumData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forums` SET `name`=:value WHERE `id`=:forum_id');
        $update->bindValue(':forum_id', $this->forumId, PDO::PARAM_INT);
        $update->bindValue(':value', $name, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->forumData['name'] = $name;
        }

        return $this;
    }

    /**
     * Set the new URL
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        if ($this->forumData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forums` SET `url`=:value WHERE `id`=:forum_id');
        $update->bindValue(':forum_id', $this->forumId, PDO::PARAM_INT);
        $update->bindValue(':value', $url, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->forumData['url'] = $url;
        }

        return $this;
    }

    /**
     * Set the given User to be the new Owner
     *
     * @param int $owner_id
     *
     * @return $this
     */
    public function setOwnerId($owner_id)
    {
        if ($this->forumData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forums` SET `owner_id`=:value WHERE `id`=:forum_id');
        $update->bindValue(':forum_id', $this->forumId, PDO::PARAM_INT);
        $update->bindValue(':value', $owner_id, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->forumData['owner_id'] = $owner_id;
        }

        return $this;
    }

    /**
     * Set Google Analytics ID
     *
     * @param string $ga_id
     *
     * @return $this
     */
    public function setGAID($ga_id)
    {
        if ($this->forumData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forums` SET `page_gaid`=:value WHERE `id`=:forum_id');
        $update->bindValue(':forum_id', $this->forumId, PDO::PARAM_INT);
        $update->bindValue(':value', $ga_id, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->forumData['page_gaid'] = $ga_id;
        }

        return $this;
    }


    /******************************************************************************/

    /**
     * Get a breadcrumb for the current tree
     *
     * @param int $board_id
     *
     * @return array
     */
    public static function getBreadcrumb($board_id)
    {
        $boardsList = array();
        $parentBoardId = (int)ForumBoard::intent($board_id)->getVar('parent_id');

        while ($parentBoardId != 0) {
            $next = (int)$parentBoardId;
            array_unshift($boardsList, $next);
            $board_id = $next;
            $parentBoardId = (int)ForumBoard::intent($board_id)->getVar('parent_id');
        }

        return $boardsList;
    }
}
