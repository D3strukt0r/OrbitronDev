<?php

namespace App\Account;

class UserSubscription
{
    private $oUser = null;

    /**
     * UserSubscription constructor.
     *
     * @param $fUserId
     */
    public function __construct($fUserId)
    {
        $this->oUser = new UserInfo($fUserId);
    }

    /**
     * @return float|int
     */
    public function getSubscriptionDays()
    {
        if ((float)$this->oUser->getFromSubscription('timestamp_expire') == -1) {
            return -1;
        }

        $fTimeDifference = (float)$this->oUser->getFromSubscription('timestamp_expire') - time();
        if ($fTimeDifference <= 0) {
            return 0;
        }

        return ceil($fTimeDifference / 86400);
    }

    /**
     * @return bool
     */
    public function hasSubscription()
    {
        if ($this->getSubscriptionDays() == -1 || $this->getSubscriptionDays() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getSubscription()
    {
        return $this->oUser->getFromSubscription('subscription_id');
    }

    /*
    static function subscruptionInfo($iSubscriptionID)
    {
        global $DBroot;

        $sQuerySubscriptionInfo = 'SELECT * FROM user_ranks WHERE id = "' . $iSubscriptionID . '" LIMIT 1';
        $oResultSubscriptionInfo = $DBroot->query($sQuerySubscriptionInfo);

        if($oResultSubscriptionInfo)
            return $oResultSubscriptionInfo->fetch_assoc();
        return null;
    }
    */
    /**
     * @return bool
     */
    public function isNormal()
    {
        if ($this->oUser->isNormal()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isPremium()
    {
        if ($this->oUser->isPremium()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEnterprise()
    {
        if ($this->oUser->isEnterprise()) {
            return true;
        }
        return false;
    }
}
