<?php

namespace App\Account\Repository;

use App\Account\Entity\OAuthAccessToken;
use App\Account\Entity\OAuthClient;
use App\Account\Entity\User;
use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\AccessTokenInterface;

class OAuthAccessTokenRepository extends EntityRepository implements AccessTokenInterface
{
    public function getAccessToken($oauthToken)
    {
        $token = $this->findOneBy(array('token' => $oauthToken));
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }

        return $token;
    }

    public function setAccessToken($oauthToken, $clientIdentifier, $userEmail, $expires, $scope = null)
    {
        /** @var \App\Account\Entity\OAuthClient $client */
        $client = $this->_em->getRepository(OAuthClient::class)->findOneBy(array('client_identifier' => $clientIdentifier));
        /** @var \App\Account\Entity\User $user */
        $user = $this->_em->getRepository(User::class)->findOneBy(array('email' => $userEmail));
        $token = OAuthAccessToken::fromArray(array(
            'token'   => $oauthToken,
            'client'  => $client,
            'user'    => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope'   => $scope,
        ));
        $this->_em->persist($token);
        $this->_em->flush();
    }
}
