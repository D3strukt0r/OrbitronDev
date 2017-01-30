<?php

namespace App\Forum;

use App\Core\DatabaseConnection;
use Container\DatabaseContainer;

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

        $oGetSubBoardCount = $database->prepare('SELECT null FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=:parent_id');
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

        $iForumId = $forum_id;
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
     */
    public static function listBoards($forum_id, $board_id)
    {
        $iForumId = (float)$forum_id;
        $iBoardId = (float)$board_id;
        $aForums = self::scanForum($iForumId, $iBoardId);

        if (is_null($aForums)) {
            echo _('There is no board');
            return;
        }

        foreach ($aForums as $iCurrentBoardId) {
            echo '
				<div class="media">
					<div class="media-left">
						<a href="#">
							<img class="media-object" src="//placehold.it/64x64" alt="" style="width:64px;height:64px;" />
						</a>
					</div>
					<div class="media-body">
						<h4 class="media-heading" id="media-heading">' . self::id2Board($iCurrentBoardId) . ' (ID: ' . $iCurrentBoardId . ')<a class="anchorjs-link" href="#media-heading"><span class="anchorjs-icon"></span></a></h4>
						{{BoardDescription}}';

            if (self::hasSubboards($iForumId, $iCurrentBoardId)) {
                self::listBoards($iForumId, $iCurrentBoardId);
            }

            echo '
					</div>
				</div>';
        }
    }

    /**
     * @param     $forum_id
     * @param     $board_id
     * @param int $level
     */
    public static function listBoards2($forum_id, $board_id, $level = 1)
    {
        $iForumId = (int)$forum_id;
        $iBoardId = (int)$board_id;
        $aForums = self::scanForum($iForumId, $iBoardId);
        if (is_null($aForums)) {
            echo 'IS NULL';
            return;
        }
        foreach ($aForums as $iCurrentBoardId) {
            $sLine = '—';
            for ($i = strlen($sLine); $i < $level; $i++) {
                $sLine .= ' —';
            }
            echo '<option value="' . $iCurrentBoardId . '" selected>' . $sLine . ' ' . self::id2Board($iCurrentBoardId) . ' (ID: ' . $iCurrentBoardId . ')</option>' . PHP_EOL;

            if (self::hasSubboards($iForumId, $iCurrentBoardId)) {
                $iNextLevel = $level + 1;
                self::listBoards2($iForumId, $iCurrentBoardId, $iNextLevel);
            }
        }
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

        $oGetBoardTitle = $database->prepare('SELECT `title` FROM `forum_boards` WHERE `id`=:board_id');
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

        $oForumExists = $database->prepare('SELECT null FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
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

        $iForumId = (int)$forum_id;
        $sParentId = (int)$parent_id;
        $sTitle = (string)$title;
        $sDescription = (string)$description;
        $iType = (int)$type;

        $oAddBoard = $database->prepare('INSERT INTO `forum_boards`(`forum_id`,`parent_id`,`title`,`description`,`type`) VALUES (:forum_id,:parent_id,:title,:description,:type)');
        $oAddBoard->execute(array(
            ':forum_id'    => $iForumId,
            ':parent_id'   => $sParentId,
            ':title'       => $sTitle,
            ':description' => $sDescription,
            ':type'        => $iType,
        ));
    }

    /**
     * @param $board_id
     * @param $key
     *
     * @return string
     * @throws \Exception
     */
    public static function getVar2($board_id, $key)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iBoardId = (int)$board_id;
        $sKey = (string)$key;

        $oGetBoardInfo = $database->prepare('SELECT * FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
        $oGetBoardInfo->execute(array(
            ':board_id' => $iBoardId,
        ));

        if (@$oGetBoardInfo->rowCount() == 0) {
            return '';
        } else {
            $aBoardInfo = $oGetBoardInfo->fetchAll(\PDO::FETCH_ASSOC);
            return $aBoardInfo[0][$sKey];
        }
    }

    /**
     * @param $board_id
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public static function setVar($board_id, $key, $value)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $iBoardId = (int)$board_id;
        $sKey = $key;
        $sValue = $value;

        $oSetBoardInfo = $database->prepare('UPDATE `forum_boards` SET :key=:value WHERE `id`=:board_id');
        $oSetBoardInfo->execute(array(
            ':key'      => $sKey,
            ':value'    => $sValue,
            ':board_id' => $iBoardId,
        ));
    }

    /******************************************************************************/

    private $boardId;
    public $boardData;

    /**
     * Forum constructor.
     *
     * @param $board_id
     *
     * @throws \Exception
     */
    public function __construct($board_id)
    {
        $this->boardId = (int)$board_id;

        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $getData = $database->prepare('SELECT * FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
        if (!$getData->execute(array(':board_id' => $this->boardId))) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if($getData->rowCount() > 0) {
                $data = $getData->fetchAll();
                $this->boardData = $data[0];
            } else {
                $this->boardData = null;
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
        $value = $this->boardData[$key];
        return $value;
    }
}