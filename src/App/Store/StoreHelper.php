<?php

namespace App\Store;

use App\Store\Entity\Store;

class StoreHelper
{
    /**
     * Get a list of all existing stores
     *
     * @return \App\Store\Entity\Store[]
     * @throws \Exception
     */
    public static function getStoreList()
    {
        $em = \Kernel::getIntent()->getEntityManager();

        /** @var \App\Store\Entity\Store[] $stores */
        $stores = $em->getRepository(Store::class)->findAll();

        if (is_null($stores)) {
            throw new \Exception('Cannot get list with all stores');
        } else {
            return $stores;
        }
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
