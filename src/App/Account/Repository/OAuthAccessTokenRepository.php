<?php

namespace App\Account\Repository;

use App\Account\Entity\OAuthAccessToken;
use App\Account\Entity\OAuthClient;
use App\Account\Entity\OAuthUser;
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
        $client = $this->_em->getRepository(OAuthClient::class)
            ->findOneBy(['client_identifier' => $clientIdentifier]);
        $user = $this->_em->getRepository(OAuthUser::class)
            ->findOneBy(['email' => $userEmail]);
        $token = OAuthAccessToken::fromArray([
            'token'     => $oauthToken,
            'client'    => $client,
            'user'      => $user,
            'expires'   => (new \DateTime())->setTimestamp($expires),
            'scope'     => $scope,
        ]);
        $this->_em->persist($token);
        $this->_em->flush();
    }
}
