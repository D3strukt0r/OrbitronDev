<?php

namespace App\Blog;

use App\Account\Entity\User;
use App\Blog\Entity\Blog;
use Kernel;

class BlogHelper
{
    /**
     * Get all blogs which belong to the given User
     *
     * @param \App\Account\Entity\User $user
     *
     * @return \App\Blog\Entity\Blog[]|null
     */
    public static function getOwnerBlogList(User $user)
    {
        $em = Kernel::getIntent()->getEntityManager();
        /** @var null|\App\Blog\Entity\Blog[] $blogList */
        $blogList = $em->getRepository(Blog::class)->findBy(array('owner' => $user->getId()));

        return $blogList;
    }

    /**
     * Checks whether the given url exists, in other words, if the blog exists
     *
     * @param string $url
     *
     * @return bool
     */
    public static function urlExists($url)
    {
        $em = Kernel::getIntent()->getEntityManager();
        /** @var null|\App\Blog\Entity\Blog[] $find */
        $find = $em->getRepository(Blog::class)->findBy(array('url' => $url));

        if (!is_null($find)) {
            if (count($find)) {
                return true;
            }
        }
        return false;
    }
}
