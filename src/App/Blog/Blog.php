<?php

namespace App\Blog;

use Container\DatabaseContainer;
use Exception;
use PDO;
use RuntimeException;

class Blog
{
    /**
     * Get a list of all existing blogs
     *
     * @return array
     * @throws \Exception
     */
    public static function getBlogList()
    {
        $database = DatabaseContainer::getDatabase();

        $getAllBlogs = $database->prepare('SELECT `name`,`url`,`owner_id` FROM `blogs`');
        $sqlSuccess = $getAllBlogs->execute();

        if (!$sqlSuccess) {
            throw new Exception('Cannot get list with all blogs');
        } else {
            return $getAllBlogs->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get all blogs which belong to the given User
     *
     * @param int $user_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getOwnerBlogList($user_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getAllBlogs = $database->prepare('SELECT * FROM `blogs` WHERE `owner_id`=:user_id');
        $getAllBlogs->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $sqlSuccess = $getAllBlogs->execute();

        if (!$sqlSuccess) {
            throw new Exception('Cannot get list with all blogs you own');
        } else {
            $blogList = array();
            $forumDataList = $getAllBlogs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($forumDataList as $currentForumData) {
                array_push($blogList, $currentForumData);
            }

            return $blogList;
        }
    }

    /**
     * Checks whether the given url exists, in other words, if the blog exists
     *
     * @param string $url
     *
     * @return bool
     * @throws \Exception
     */
    public static function urlExists($url)
    {
        $database = DatabaseContainer::getDatabase();

        $getUrl = $database->prepare('SELECT NULL FROM `blogs` WHERE `url`=:url');
        $getUrl->bindValue(':url', $url, PDO::PARAM_STR);
        $sqlSuccess = $getUrl->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('[DATABASE]: Could not execute sql');
        } else {
            if ($getUrl->rowCount()) {
                return true;
            }

            return false;
        }
    }

    /**
     * Checks whether the given blog exists
     *
     * @param int $blog_id
     *
     * @return bool
     * @throws \Exception
     */
    public static function blogExists($blog_id)
    {
        $database = DatabaseContainer::getDatabase();

        $blogExists = $database->prepare('SELECT NULL FROM `blogs` WHERE `id`=:blog_id LIMIT 1');
        $blogExists->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $sqlSuccess = $blogExists->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('[DATABASE]: Could not execute sql');
        } else {
            if ($blogExists->rowCount() > 0) {
                return true;
            }

            return false;
        }
    }

    /**
     * Converts the given URL to the existing id of the blog.
     * Hint: always use "urlExists()" before using this function
     *
     * @param string $blog_url
     *
     * @return mixed
     * @throws \Exception
     */
    public static function url2Id($blog_url)
    {
        $database = DatabaseContainer::getDatabase();

        $getBlogId = $database->prepare('SELECT `id` FROM `blogs` WHERE `url`=:blog_url LIMIT 1');
        $getBlogId->bindValue(':blog_url', $blog_url, PDO::PARAM_STR);
        $sqlSuccess = $getBlogId->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('[Database]: Could not execute sql');
        } else {
            $blogData = $getBlogId->fetchAll(PDO::FETCH_ASSOC);

            return $blogData[0]['id'];
        }
    }

    /******************************************************************************/

    private $blogId;
    public $blogData;

    /**
     * Blog constructor.
     *
     * @param $blog_id
     *
     * @throws \Exception
     */
    public function __construct($blog_id)
    {
        $this->blogId = $blog_id;

        $database = DatabaseContainer::getDatabase();

        $getData = $database->prepare('SELECT * FROM `blogs` WHERE `id`=:blog_id LIMIT 1');
        $getData->bindValue(':blog_id', $blog_id, PDO::PARAM_INT);
        $sqlSuccess = $getData->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('[Database]: '.'Could not execute sql');
        } else {
            if ($getData->rowCount() > 0) {
                $data = $getData->fetchAll(PDO::FETCH_ASSOC);
                $this->blogData = $data[0];
            } else {
                $this->blogData = null;
            }
        }
    }

    /**
     * Get information of current blog
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->blogData[$key];

        return $value;
    }

    /**
     * Set the new blog name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if ($this->blogData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `blogs` SET `name`=:value WHERE `id`=:blog_id');
        $update->bindValue(':blog_id', $this->blogId, PDO::PARAM_INT);
        $update->bindValue(':value', $name, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->blogData['name'] = $name;
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
        if ($this->blogData == null) {
            return null;
        }
        $database = DatabaseContainer::getDatabase();

        $update = $database->prepare('UPDATE `blogs` SET `url`=:value WHERE `id`=:blog_id');
        $update->bindValue(':blog_id', $this->blogId, PDO::PARAM_INT);
        $update->bindValue(':value', $url, PDO::PARAM_STR);
        $sqlSuccess = $update->execute();

        if (!$sqlSuccess) {
            throw new RuntimeException('Could not execute sql');
        } else {
            $this->blogData['url'] = $url;
        }

        return $this;
    }
}
