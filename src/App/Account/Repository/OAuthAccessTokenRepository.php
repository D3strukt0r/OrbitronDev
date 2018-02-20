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
        $token = $this->findOneBy(['token' => $oauthToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }

        return $token;
    }

    public function setAccessToken($oauthToken, $clientIdentifier, $userEmail, $expires, $scope = null)
    {
        /** @var \App\Account\Entity\OAuthClient $client */
        $client = $this->_em->getRepository(OAuthClient::class)->findOneBy(['client_identifier' => $clientIdentifier]);

        /** @var \App\Account\Entity\User $user */
        $user = $this->_em->getRepository(User::class)->findOneBy(['id' => $userEmail]);

        $token = OAuthAccessToken::fromArray([
            'token'   => $oauthToken,
            'client'  => $client,
            'user'    => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope'   => $scope,
        ]);
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function unsetAccessToken($access_token)
    {
        $accessToken = $this->findOneBy(['token' => $access_token]);
        if (is_null($accessToken)) {
            return false;
        }

        $this->_em->remove($accessToken);
        $this->_em->flush();

        return true;
    }
}
