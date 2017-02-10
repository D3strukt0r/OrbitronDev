<?php

namespace App\Blog;


use Container\DatabaseContainer;

class Blog
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getBlogList()
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetBlogList = $database->prepare('SELECT `url` FROM `blogs`');
        if (!$oGetBlogList->execute()) {
            throw new \Exception('Cannot get list with all blogs');
        } else {
            $aBlogs = array();
            $aBlogData = $oGetBlogList->fetchAll();
            foreach ($aBlogData as $aBlogData) {
                array_push($aBlogs, $aBlogData['url']);
            }
            return $aBlogs;
        }
    }

    /**
     * @param $user_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getOwnerBlogList($user_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }
        $fUserId = (float)$user_id;

        $oGetBlogList = $database->prepare('SELECT * FROM `blogs` WHERE `owner_id`=:user_id');
        if (!$oGetBlogList->execute(array(':user_id' => $fUserId))) {
            throw new \Exception('Cannot get list with all blogs you own');
        } else {
            $aBlogs = array();
            $aBlogData = $oGetBlogList->fetchAll();
            foreach ($aBlogData as $aBlogData) {
                array_push($aBlogs, $aBlogData);
            }
            return $aBlogs;
        }
    }

    /**
     * @param $blog_url
     *
     * @return mixed
     * @throws \Exception
     */
    public static function url2Id($blog_url)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetBlogId = $database->prepare('SELECT `id` FROM `blogs` WHERE `url`=:blog_url LIMIT 1');
        $bGetBlogIdQuerySuccessful = $oGetBlogId->execute(array(
            ':blog_url' => $blog_url,
        ));
        if (!$bGetBlogIdQuerySuccessful) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        } else {
            $aBlogData = $oGetBlogId->fetchAll();
            $blog_id = $aBlogData[0]['id'];
            return $blog_id;
        }
    }

    /**
     * @param $blog_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function blogExists($blog_id)
    {
        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oBlogExists = $database->prepare('SELECT NULL FROM `blogs` WHERE `id`=:blog_id LIMIT 1');
        if (!$oBlogExists->execute(array(':blog_id' => $blog_id))) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        } else {
            if ($oBlogExists->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }

    /******************************************************************************/

    private $iBlogId;
    private $aBlogData;

    /**
     * Blog constructor.
     *
     * @param $blog_id
     *
     * @throws \Exception
     */
    public function __construct($blog_id)
    {
        $this->iBlogId = $blog_id;

        $database = DatabaseContainer::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetBlogData = $database->prepare('SELECT * FROM `blogs` WHERE `id`=:blog_id LIMIT 1');
        if (!$oGetBlogData->execute(array(':blog_id' => $this->iBlogId))) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        } else {
            $aBlogData = $oGetBlogData->fetchAll();
            $this->aBlogData = $aBlogData[0];
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->aBlogData[$key];
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

        $oUpdateTable = $database->prepare('UPDATE `blogs` SET :key=:value WHERE `id`=:blog_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'     => $key,
            ':value'   => $value,
            ':blog_id' => $this->iBlogId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new \RuntimeException('[Database]: ' . 'Could not execute sql');
        }
    }
}