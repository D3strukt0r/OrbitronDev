<?php

namespace App\Forum;

use App\Core\DatabaseConnection;
use Container\DatabaseContainer;
use PDO;

class ForumBoard
{
    /**
     * @param $forum_id
     * @param $board_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function hasSubboards($forum_id, $board_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iForumId = (int)$forum_id;
        $iBoardId = (int)$board_id;

        $oGetSubBoardCount = $database->prepare('SELECT NULL FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=:parent_id');
        $oGetSubBoardCount->execute(array(
            ':forum_id'  => $iForumId,
            ':parent_id' => $iBoardId,
        ));
        if (@$oGetSubBoardCount->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $forum_id
     * @param $parent_board_id
     *
     * @return array
     * @throws \Exception
     */
    public static function scanForum($forum_id, $parent_board_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iForumId       = $forum_id;
        $iParentBoardId = $parent_board_id;

        $oGetBoards = $database->prepare('SELECT `id` FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=:parent_id');
        $oGetBoards->execute(array(
            ':forum_id'  => $iForumId,
            ':parent_id' => $iParentBoardId,
        ));
        if (@$oGetBoards->rowCount() == 0) {
            return array(); // Means that there is no subforum
        }
        $aBoardList = array();
        foreach ($oGetBoards->fetchAll() as $iListId => $aBoardData) {
            $aBoardList[] = $aBoardData['id'];
        }

        return $aBoardList;
    }

    /**
     * @param $forum_id
     * @param $board_id
     *
     * @return string
     */
    public static function listBoardsTree($forum_id, $board_id)
    {
        $iForumId = (float)$forum_id;
        $iBoardId = (float)$board_id;
        $aForums  = self::scanForum($iForumId, $iBoardId);

        if (is_null($aForums)) {
            return 'error:no_board';
        }

        foreach ($aForums as $iCurrentBoardId) {
            $text = '';
            $board = new ForumBoard($iCurrentBoardId);

            $text .= '
				<div class="media">
					<div class="media-left">
						<a href="#">
							<img class="media-object" src="//placehold.it/64x64" alt="" style="width:64px;height:64px;" />
						</a>
					</div>
					<div class="media-body">
						<h4 class="media-heading" id="media-heading">
                            '.$board->getVar('title').' (ID: '.$board->getVar('id').')
                            <a class="anchorjs-link" href="#media-heading"><span class="anchorjs-icon"></span></a>
						</h4>
						'.$board->getVar('description');

            if (self::hasSubboards($iForumId, $iCurrentBoardId)) {
                $text .= self::listBoardsTree($iForumId, $iCurrentBoardId);
            }

            $text .= '
					</div>
				</div>';

            return $text;
        }
    }

    /**
     * @param      $forum_id
     * @param      $board_id
     * @param int  $level
     * @param null $list
     *
     * @return array
     */
    public static function listBoardsFormSelect($forum_id, $board_id, $level = 1, &$list = null)
    {
        $iForumId = (int)$forum_id;
        $iBoardId = (int)$board_id;
        $aForums  = self::scanForum($iForumId, $iBoardId);
        if (is_null($aForums)) {
            return array('error:no_entry');
        }

        if(is_null($list)) {
            $list = array();
            $list['- Main (ID: 0)'] = 0;
        }

        foreach ($aForums as $iCurrentBoardId) {
            $sLine = '-';
            for ($i = strlen($sLine)-1; $i < $level; $i++) {
                $sLine .= '-';
            }

            $title = $sLine.' '.self::id2Board($iCurrentBoardId).' (ID: '.$iCurrentBoardId.')';
            $list[$title] = $iCurrentBoardId;

            if (self::hasSubboards($iForumId, $iCurrentBoardId)) {
                $iNextLevel = $level + 1;
                self::listBoardsFormSelect($iForumId, $iCurrentBoardId, $iNextLevel, $list);
            }
        }

        return $list;
    }

    /**
     * @param $board_id
     *
     * @return null
     * @throws \Exception
     */
    public static function id2Board($board_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iBoardId = $board_id;

        $oGetBoardTitle           = $database->prepare('SELECT `title` FROM `forum_boards` WHERE `id`=:board_id');
        $oGetBoardTitleSuccessful = $oGetBoardTitle->execute(array(
            ':board_id' => $iBoardId,
        ));
        if ($oGetBoardTitleSuccessful) {
            $aBoardData = $oGetBoardTitle->fetchAll();

            return $aBoardData[0]['title'];
        }

        return null;
    }

    /**
     * @param $board_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function boardExists($board_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oForumExists = $database->prepare('SELECT NULL FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
        $oForumExists->execute(array(
            ':board_id' => $board_id,
        ));

        if (@$oForumExists->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /*****************************************************************************************/

    /**
     * @param $forum_id
     * @param $title
     * @param $description
     * @param $parent_id
     * @param $type
     *
     * @throws \Exception
     */
    public static function addBoard($forum_id, $title, $description, $parent_id, $type)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iForumId     = (int)$forum_id;
        $sParentId    = (int)$parent_id;
        $sTitle       = (string)$title;
        $sDescription = (string)$description;
        $iType        = (int)$type;

        $oAddBoard = $database->prepare('INSERT INTO `forum_boards`(`forum_id`,`parent_id`,`title`,`description`,`type`) VALUES (:forum_id,:parent_id,:title,:description,:type)');
        $oAddBoard->execute(array(
            ':forum_id'    => $iForumId,
            ':parent_id'   => $sParentId,
            ':title'       => $sTitle,
            ':description' => $sDescription,
            ':type'        => $iType,
        ));
    }

    /******************************************************************************/

    private $boardId;
    private $notFound = false;
    public  $boardData;

    /**
     * Forum constructor.
     *
     * @param int $board_id
     *
     * @throws \Exception
     */
    public function __construct($board_id)
    {
        $this->boardId = (int)$board_id;
        $this->sync();
    }

    public static function intent($board_id)
    {
        $class = new self($board_id);
        return $class;
    }

    public function sync()
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $dbSync = $database->prepare('SELECT * FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
        if (!$dbSync->execute(array(':board_id' => $this->boardId))) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($dbSync->rowCount() > 0) {
                $data            = $dbSync->fetchAll(PDO::FETCH_ASSOC);
                $this->boardData = $data[0];
            } else {
                $this->boardData = null;
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
        if ($this->exists()) {
            $value = $this->boardData[$key];

            return $value;
        } else {
            return null;
        }
    }

    /**
     * @param int    $board_id
     * @param string $key
     *
     * @return mixed|null
     *
     * @deprecated Moved to the function "getVarStatic"
     */
    public static function getVar2($board_id, $key) // TODO: Migrate function to "getVarStatic"
    {
        $board = new ForumBoard($board_id);

        return $board->getVar($key);
    }

    /**
     * @param int    $board_id
     * @param string $key
     *
     * @return mixed|null
     */
    public static function getVarStatic($board_id, $key) // TODO: Migrate function to "getVarStatic"
    {
        $board = new ForumBoard($board_id);

        return $board->getVar($key);
    }


    public function setLastPostUserId($value)
    {
        if ($this->exists()) {
            $database = DatabaseContainer::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

            $update = $database->prepare('UPDATE `forum_boards` SET `last_post_user_id`=:value WHERE `id`=:board_id');
            $update->bindValue(':value', $value, PDO::PARAM_INT);
            $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
            if ($update->execute()) {
                $this->sync();

                return true;
            }

            return false;
        } else {
            return null;
        }
    }

    public function setLastPostTime($value)
    {
        if ($this->exists()) {
            $database = DatabaseContainer::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

            $update = $database->prepare('UPDATE `forum_boards` SET `last_post_time`=:value WHERE `id`=:board_id');
            $update->bindValue(':value', $value, PDO::PARAM_INT);
            $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
            if ($update->execute()) {
                $this->sync();

                return true;
            }

            return false;
        } else {
            return null;
        }
    }

    public function setThreads($value)
    {
        if ($this->exists()) {
            $database = DatabaseContainer::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

            $update = $database->prepare('UPDATE `forum_boards` SET `threads`=:value WHERE `id`=:board_id');
            $update->bindValue(':value', $value, PDO::PARAM_INT);
            $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
            if ($update->execute()) {
                $this->sync();

                return true;
            }

            return false;
        } else {
            return null;
        }
    }

    public function setPosts($value)
    {
        if ($this->exists()) {
            $database = DatabaseContainer::$database;
            if (is_null($database)) {
                throw new \Exception('A database connection is required');
            }

            $update = $database->prepare('UPDATE `forum_boards` SET `posts`=:value WHERE `id`=:board_id');
            $update->bindValue(':value', $value, PDO::PARAM_INT);
            $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
            if ($update->execute()) {
                $this->sync();

                return true;
            }

            return false;
        } else {
            return null;
        }
    }
}