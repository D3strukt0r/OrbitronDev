<?php

namespace App\Store;

use App\Account\Entity\User;
use App\Store\Entity\Store;

class StoreHelper
{
    /**
     * Get all stores which belong to the given User
     *
     * @param \App\Account\Entity\User $user
     *
     * @return \App\Store\Entity\Store[]
     * @throws \Exception
     */
    public static function getOwnerStoreList(User $user)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Store\Entity\Store[] $list */
        $list = $em->getRepository(Store::class)->findBy(array('owner' => $user->getId()));

        return $list;
    }

    /**
     * Checks whether the given url exists, in other words, if the store exists
     *
     * @param string $url
     *
     * @return bool
     * @throws \Exception
     */
    public static function urlExists($url)
    {
        $em = \Kernel::getIntent()->getEntityManager();
        /** @var \App\Store\Entity\Store[] $find */
        $find = $em->getRepository(Store::class)->findBy(array('url' => $url));

        if (!is_null($find)) {
            if (count($find)) {
                return true;
            }
        }
        return false;
    }
}
