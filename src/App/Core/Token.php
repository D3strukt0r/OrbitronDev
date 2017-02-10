<?php

namespace App\Core;


use Container\DatabaseContainer;
use PDO;
use Symfony\Component\Config\Definition\Exception\Exception;

class Token
{
    private $isGenerated = false;
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
            $this->isGenerated = true;
            $this->token = $token;
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
     * @param string $job
     * @param null   $validUntil
     * @param array  $information
     *
     * @return string
     */
    public function generateToken($job, $validUntil = null, $information = array())
    {
        if (!$this->isGenerated) {
            $database = DatabaseContainer::$database;
            $token = rtrim(strtr(base64_encode($this->getRandomNumber()), '+/', '-_'), '=');

            $addToken = $database->prepare('INSERT INTO `app_token`(`token`, `job`, `expires`, `optional_info`) VALUES (:token, :job, :expires, :info)');
            $addToken->execute(array(
                ':token'   => $token,
                ':job'     => $job,
                ':expires' => !is_null($validUntil) ? $validUntil : 0,
                ':info'    => count($information) > 0 ? json_encode($information) : null,
            ));
            return $token;
        }
        return null;
    }

    /**
     * @return string
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

    public function getJob()
    {
        if ($this->isGenerated) {
            $database = DatabaseContainer::$database;

            $getJob = $database->prepare('SELECT * FROM `app_token` WHERE `token`=:token LIMIT 1');
            $getJob->execute(array(
                ':token' => $this->token,
            ));

            if ($getJob->rowCount() == 0) {
                return false;
            } else {
                $tokenData = $getJob->fetchAll(PDO::FETCH_ASSOC);

                if ($tokenData[0]['expires'] < time()) {
                    return 'expired';
                }

                return $tokenData[0]['job'];
            }
        }
        return null;
    }

    /**
     * @return bool|mixed|null|string
     */
    public function getInformation()
    {
        if ($this->isGenerated) {
            $database = DatabaseContainer::$database;

            $getJob = $database->prepare('SELECT * FROM `app_token` WHERE `token`=:token LIMIT 1');
            $getJob->execute(array(
                ':token' => $this->token,
            ));

            if ($getJob->rowCount() == 0) {
                return false;
            } else {
                $tokenData = $getJob->fetchAll(PDO::FETCH_ASSOC);

                if ($tokenData[0]['expires'] < time()) {
                    return 'expired';
                }

                return json_decode($tokenData[0]['optional_info'], true);
            }
        }
        return null;
    }

    /**
     *
     */
    public function remove()
    {
        if ($this->isGenerated) {
            $database = DatabaseContainer::$database;

            $getJob = $database->prepare('DELETE FROM `app_token` WHERE `token`=:token LIMIT 1');
            $getJob->execute(array(
                ':token' => $this->token,
            ));

            $this->isGenerated = false;
            $this->token = null;
        }
    }
}