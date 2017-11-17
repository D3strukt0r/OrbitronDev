<?php

namespace App\Core;

use App\Core\Entity\Token as TokenEntity;
use Kernel;
use Exception;

class Token
{
    /**
     * @var bool
     */
    private $isGenerated = false;

    /**
     * @var \App\Core\Entity\Token
     */
    private $token = null;

    /**
     * @var bool
     */
    private $useOpenSsl;

    /**
     * TokenGenerator constructor.
     *
     * @param null $token
     */
    public function __construct($token = null)
    {
        if (!is_null($token) && is_string($token)) {
            $em = Kernel::getIntent()->getEntityManager();

            /** @var \App\Core\Entity\Token $token */
            $token = $em->getRepository(TokenEntity::class)->findOneBy(array('token' => $token));

            if (!is_null($token)) {
                $this->isGenerated = true;
                $this->token = $token;
            }
        }
        // determine whether to use OpenSSL
        if (defined('PHP_WINDOWS_VERSION_BUILD') && version_compare(PHP_VERSION, '5.3.4', '<')) {
            $this->useOpenSsl = false;
        } elseif (!function_exists('openssl_random_pseudo_bytes')) {
            $this->useOpenSsl = false;
        } else {
            $this->useOpenSsl = true;
        }
    }

    /**
     * @param string         $job
     * @param null|\DateTime $validUntil
     * @param null|array     $information
     *
     * @return string
     */
    public function generateToken($job, $validUntil = null, array $information = null)
    {
        if (!$this->isGenerated) {
            $token = rtrim(strtr(base64_encode($this->getRandomNumber()), '+/', '-_'), '=');

            $newToken = new TokenEntity();
            $newToken
                ->setToken($token)
                ->setJob($job)
                ->setExpires($validUntil)
                ->setOptionalInfo($information);

            $em = Kernel::getIntent()->getEntityManager();
            $em->persist($newToken);
            $em->flush();

            $this->token = $newToken;

            return $token;
        }

        return null;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getRandomNumber()
    {
        $nbBytes = 32;
        // try OpenSSL
        if ($this->useOpenSsl) {
            $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
            if (false !== $bytes && true === $strong) {
                return $bytes;
            }
            throw new Exception('OpenSSL did not produce a secure random number.');
        }

        return hash('sha256', uniqid(mt_rand(), true), true);
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    public static function createRandomToken($options = array())
    {
        $defaultOptions = array(
            'use_openssl' => false,
        );
        $options = array_merge($defaultOptions, $options);

        if ($options['use_openssl']) {
            $nbBytes = 32;
            $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
            if (false !== $bytes && true === $strong) {
                return $bytes;
            }
            throw new Exception('OpenSSL did not produce a secure random number.');
        } else {
            return hash('sha256', uniqid(mt_rand(), true));
        }
    }

    /**
     * @return bool|null|string
     */
    public function getJob()
    {
        if ($this->isGenerated) {
            if (is_null($this->token)) {
                return false;
            }
            if ($this->token->getExpires()->getTimestamp() < time()) {
                return 'expired';
            }

            return $this->token->getJob();
        }

        return null;
    }

    /**
     * @return array|bool|null|string
     */
    public function getInformation()
    {
        if ($this->isGenerated) {
            if (is_null($this->token)) {
                return false;
            }
            if ($this->token->getExpires()->getTimestamp() < time()) {
                return 'expired';
            }

            return $this->token->getOptionalInfo();
        }

        return null;
    }

    public function remove()
    {
        if ($this->isGenerated) {
            $em = Kernel::getIntent()->getEntityManager();

            if (is_null($this->token)) {
                return false;
            }

            $em->remove($this->token);
            $em->flush();

            $this->isGenerated = false;
        }

        return null;
    }
}
