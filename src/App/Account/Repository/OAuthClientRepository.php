<?php

namespace App\Account\Repository;

use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\ClientCredentialsInterface;

class OAuthClientRepository extends EntityRepository implements ClientCredentialsInterface
{
    public function getClientDetails($clientIdentifier)
    {
        $client = $this->findOneBy(['client_identifier' => $clientIdentifier]);
        if ($client) {
            $client = $client->toArray();
        }
        return $client;
    }

    public function checkClientCredentials($clientIdentifier, $clientSecret = NULL)
    {
        $client = $this->findOneBy(['client_identifier' => $clientIdentifier]);
        if ($client) {
            return $client->verifyClientSecret($clientSecret);
        }
        return false;
    }

    public function checkRestrictedGrantType($clientId, $grantType)
    {
        // we do not support different grant types per client in this example
        return true;
    }

    public function isPublicClient($clientId)
    {
        return false;
    }

    public function getClientScope($clientId)
    {
        return null;
    }
}
