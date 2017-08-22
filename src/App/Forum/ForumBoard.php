<?php

namespace App\Forum;

use App\Account\UserInfo;
use Container\DatabaseContainer;
use PDO;

class ForumBoard
{
    /**
     * @param int $forum_id
     * @param int $board_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function hasSubBoards($forum_id, $board_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getSubBoardCount = $database->prepare('SELECT NULL FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=:parent_id');
        $getSubBoardCount->bindValue(':forum_id', $forum_id, PDO::PARAM_INT);
        $getSubBoardCount->bindValue(':parent_id', $board_id, PDO::PARAM_INT);
        $sqlSuccess = $getSubBoardCount->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute SQL');
        } else {
            if ($getSubBoardCount->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    /**
     * @param int $forum_id
     * @param int $parent_board_id
     *
     * @return array
     * @throws \Exception
     */
    public static function scanForum($forum_id, $parent_board_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getBoards = $database->prepare('SELECT `id` FROM `forum_boards` WHERE `forum_id`=:forum_id AND `parent_id`=:parent_id');
        $getBoards->bindValue(':forum_id', $forum_id, PDO::PARAM_INT);
        $getBoards->bindValue(':parent_id', $parent_board_id, PDO::PARAM_INT);
        $sqlSuccess = $getBoards->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute SQL');
        } else {
            if ($getBoards->rowCount() == 0) {
                return array(); // Means that there is no sub-forum
            }
            $boardList = array();
            foreach ($getBoards->fetchAll() as $currentBoardData) {
                $boardList[] = $currentBoardData['id'];
            }

            return $boardList;
        }
    }

    /**
     * @param int $forum_id
     * @param int $board_id
     *
     * @return string
     *
     * TODO: This should return an array, so we can define the style in twig (see "App/Forum/addons/10-forums.php", and "app/views/forum/theme_admin1/board-list.html.twig")
     */
    public static function listBoardsTree($forum_id, $board_id)
    {
        $boardList = self::scanForum($forum_id, $board_id);

        $text = '';
        foreach ($boardList as $currentBoardId) {
            $board = new ForumBoard($currentBoardId);

            $text
                .= '
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

            if (self::hasSubBoards($forum_id, $currentBoardId)) {
                $text .= self::listBoardsTree($forum_id, $currentBoardId);
            }

            $text .= '</div></div>';
        }

        return $text;
    }

    /**
     * @param int   $forum_id
     * @param int   $board_id
     * @param int   $level
     * @param array $list
     *
     * @return array
     */
    public static function listBoardsFormSelect($forum_id, $board_id, $level = 1, &$list = array())
    {
        $boardList = self::scanForum($forum_id, $board_id);

        if (empty($list)) {
            $list['- Main (ID: 0)'] = 0;
        }

        foreach ($boardList as $currentBoardId) {
            $line = '-';
            for ($i = strlen($line) - 1; $i < $level; $i++) {
                $line .= '-';
            }

            $title        = $line.' '.self::id2Board($currentBoardId).' (ID: '.$currentBoardId.')';
            $list[$title] = $currentBoardId;

            if (self::hasSubBoards($forum_id, $currentBoardId)) {
                $nextLevel = $level + 1;
                self::listBoardsFormSelect($forum_id, $currentBoardId, $nextLevel, $list);
            }
        }

        return $list;
    }

    /**
     * @param int $board_id
     *
     * @return null
     * @throws \Exception
     */
    public static function id2Board($board_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getBoardTitle = $database->prepare('SELECT `title` FROM `forum_boards` WHERE `id`=:board_id');
        $getBoardTitle->bindValue(':board_id', $board_id, PDO::PARAM_INT);
        $sqlSuccess = $getBoardTitle->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $boardData = $getBoardTitle->fetchAll();

            return $boardData[0]['title'];
        }
    }

    /**
     * @param int $board_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function boardExists($board_id)
    {
        $database = DatabaseContainer::getDatabase();

        $forumExists = $database->prepare('SELECT NULL FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
        $forumExists->bindValue(':board_id', $board_id, PDO::PARAM_INT);
        $sqlSuccess = $forumExists->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($forumExists->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    /*****************************************************************************************/

    /**
     * Create a new board under the given parent
     *
     * @param int    $forum_id
     * @param string $title
     * @param string $description
     * @param int    $parent_id
     * @param int    $type
     *
     * @throws \Exception
     */
    public static function addBoard($forum_id, $title, $description, $parent_id, $type)
    {
        $database = DatabaseContainer::getDatabase();

        $addBoard = $database->prepare('INSERT INTO `forum_boards`(`forum_id`,`parent_id`,`title`,`description`,`type`) VALUES (:forum_id,:parent_id,:title,:description,:type)');
        $addBoard->bindValue(':forum_id', $forum_id, PDO::PARAM_INT);
        $addBoard->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
        $addBoard->bindValue(':title', $title, PDO::PARAM_STR);
        $addBoard->bindValue(':description', $description, PDO::PARAM_STR);
        $addBoard->bindValue(':type', $type, PDO::PARAM_INT);
        $addBoard->execute();
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
        $database = DatabaseContainer::getDatabase();

        $dbSync = $database->prepare('SELECT * FROM `forum_boards` WHERE `id`=:board_id LIMIT 1');
        $dbSync->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
        $sqlSuccess = $dbSync->execute();

        if (!$sqlSuccess) {
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
        if (!$this->exists()) {
            return null;
        }

        return $this->boardData[$key];
    }

    /**
     * @param int $user_id
     *
     * @return $this|null
     */
    public function setLastPostUserId($user_id)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_boards` SET `last_post_user_id`=:value WHERE `id`=:board_id');
        $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
        $update->bindValue(':value', $user_id, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $user_id
     *
     * @return $this|null
     */
    public function setLastPostUsername($user_id)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_boards` SET `last_post_username`=:value WHERE `id`=:board_id');
        $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);

        $user = new UserInfo($user_id);
        $update->bindValue(':value', $user->getFromUser('username'), PDO::PARAM_STR);

        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $time
     *
     * @return $this|null
     */
    public function setLastPostTime($time)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_boards` SET `last_post_time`=:value WHERE `id`=:board_id');
        $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
        $update->bindValue(':value', $time, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $threads
     *
     * @return $this|null
     */
    public function setThreads($threads)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_boards` SET `threads`=:value WHERE `id`=:board_id');
        $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
        $update->bindValue(':value', $threads, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }

    /**
     * @param int $posts
     *
     * @return $this|null
     */
    public function setPosts($posts)
    {
        if (!$this->exists()) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `forum_boards` SET `posts`=:value WHERE `id`=:board_id');
        $update->bindValue(':board_id', $this->boardId, PDO::PARAM_INT);
        $update->bindValue(':value', $posts, PDO::PARAM_INT);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $this->sync();
        }

        return $this;
    }
}