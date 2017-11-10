<?php

namespace App\Account\Repository;

use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\UserCredentialsInterface;

class OAuthUserRepository extends EntityRepository implements UserCredentialsInterface
{
    public function checkUserCredentials($email, $password)
    {
        $user = $this->findOneBy(['email' => $email]);
        if ($user) {
            return $user->verifyPassword($password);
        }
        return false;
    }

    /**
     * @return
     * ARRAY the associated "user_id" and optional "scope" values
     * This function MUST return FALSE if the requested user does not exist or is
     * invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     * return array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($email)
    {
        $user = $this->findOneBy(['email' => $email]);
        if ($user) {
            $user = $user->toArray();
        }
        return $user;
    }
}
