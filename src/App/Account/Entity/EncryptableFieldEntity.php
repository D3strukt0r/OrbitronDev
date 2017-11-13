<?php

namespace App\Account\Entity;

class EncryptableFieldEntity
{
    /**
     * @var array
     */
    protected $hashOptions = array('cost' => 11);

    /**
     * @param string $value
     *
     * @return bool|string
     */
    protected function encryptField($value)
    {
        return password_hash($value, PASSWORD_BCRYPT, $this->hashOptions);
    }

    /**
     * @param string $encryptedValue
     * @param string $value
     *
     * @return bool
     */
    protected function verifyEncryptedFieldValue($encryptedValue, $value)
    {
        return password_verify($value, $encryptedValue);
    }
}
