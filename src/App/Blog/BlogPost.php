<?php

namespace App\Blog;

use Container\DatabaseContainer;
use PDO;

class BlogPost
{
    /**
     * @param int $blog_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getPostList($blog_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getAllPosts = $database->prepare('SELECT * FROM `blog_posts` WHERE `blog_id`=:blog_id');
        $getAllPosts->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $sqlSuccess = $getAllPosts->execute();

        if (!$sqlSuccess) {
            throw new \Exception('Cannot get list with all posts');
        } else {
            $aPosts    = array();
            $aPostData = $getAllPosts->fetchAll();
            foreach ($aPostData as $aBlogData) {
                array_push($aPosts, $aBlogData);
            }

            return $aPosts;
        }
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

        $postExists = $database->prepare('SELECT NULL FROM `blog_posts` WHERE `post_id`=:post_id LIMIT 1');
        $postExists->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $sqlSuccess = $postExists->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('[Database]: '.'Could not execute sql');
        } else {
            if ($postExists->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    /******************************************************************************/

    private $postId;
    public  $postData;

    /**
     * BlogPost constructor.
     *
     * @param int $post_id
     *
     * @throws \Exception
     */
    public function __construct($post_id)
    {
        $this->postId = $post_id;

        $database = DatabaseContainer::getDatabase();

        $getData = $database->prepare('SELECT * FROM `blog_posts` WHERE `post_id`=:post_id LIMIT 1');
        $getData->bindValue(':post_id', $this->postId, PDO::PARAM_INT);
        $sqlSuccess = $getData->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('[Database]: '.'Could not execute sql');
        } else {
            if ($getData->rowCount() > 0) {
                $data           = $getData->fetchAll(PDO::FETCH_ASSOC);
                $this->postData = $data[0];
            } else {
                $this->postData = null;
            }
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->postData[$key];

        return $value;
    }

    /**
     * @param string $value
     *
     * @return $this|null
     */
    public function setStory($value)
    {
        if ($this->postData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `blog_posts` SET `story`=:value WHERE `post_id`=:post_id');
        $update->bindValue(':post_id', $this->postId, PDO::PARAM_INT);
        $update->bindValue(':value', $value, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('[Database]: '.'Could not execute sql');
        } else {
            $this->postData['story'] = $value;
        }

        return $this;
    }
}
