<?php

namespace App\Blog;

use App\Core\DatabaseConnection;

class BlogPost
{
    /**
     * @param $blog_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getPostList($blog_id)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }
        $fBlogId = (float)$blog_id;

        $oGetPostList = $database->prepare('SELECT * FROM `blog_posts` WHERE `blog_id`=:blog_id');
        if (!$oGetPostList->execute(array(':blog_id' => $fBlogId))) {
            throw new \Exception('Cannot get list with all posts');
        } else {
            $aPosts = array();
            $aPostData = $oGetPostList->fetchAll();
            foreach ($aPostData as $aBlogData) {
                array_push($aPosts, $aBlogData);
            }
            return $aPosts;
        }
    }

    /**
     * @param $post_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function postExists($post_id)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }
        $fPostId = (float)$post_id;

        $oPostExists = $database->prepare('SELECT null FROM `blog_posts` WHERE `post_id`=:post_id LIMIT 1');
        if (!$oPostExists->execute(array(':post_id' => $fPostId))) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        } else {
            if ($oPostExists->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }

    /******************************************************************************/

    private $iPostId;
    private $aPostData = array();

    /**
     * BlogPost constructor.
     *
     * @param $post_id
     *
     * @throws \Exception
     */
    public function __construct($post_id)
    {
        $this->iPostId = $post_id;

        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetPostData = $database->prepare('SELECT * FROM `blog_posts` WHERE `post_id`=:post_id LIMIT 1');
        if (!$oGetPostData->execute(array(':post_id' => $this->iPostId))) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        } else {
            $aPostData = $oGetPostData->fetchAll();
            if ($oGetPostData->rowCount() > 0) {
                $this->aPostData = $aPostData[0];
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
        $value = $this->aPostData[$key];
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
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oUpdateTable = $database->prepare('UPDATE `blog_posts` SET :key=:value WHERE `id`=:blog_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'     => $key,
            ':value'   => $value,
            ':blog_id' => $this->iPostId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        }
    }
}
