<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Authentication;

use Icinga\Exception\AuthenticationException;

/**
 * Helper for password hashing to improve compatibility to PHP < 5.5
 */
class PasswordHelper
{
    /**
     * The PHP version that introduced support for functions:
     *
     * password_hash()
     * password_verify()
     *
     * @var string
     */
    const PHP_VERSION_COMPAT = '5.5.0';

    /**
     * Algo to force fallback to compat algorithm
     */
    const PASSWORD_ALGO_FALLBACK = 999;

    /**
     * The length of the salt to use when hashing a password with SHA method
     *
     * 16 is the required character count
     *
     * @var int
     */
    const COMPAT_SALT_LENGTH = 16;

    /**
     * Hash type to use as compat method: SHA-512
     *
     * @var string
     */
    const COMPAT_HASH = '$6$rounds=5000$';

    /**
     * Check if we have a modern PHP based on version
     *
     * @return bool
     */
    public static function supportsModernAPI()
    {
        return version_compare(phpversion(), self::PHP_VERSION_COMPAT, '>=');
    }

    /**
     * Hash a password with password_hash() or crypt()
     *
     * @param string $password
     * @param int    $algo
     *
     * @return string
     * @throws AuthenticationException
     */
    public static function hash($password, $algo = null)
    {
        if (static::supportsModernAPI() and $algo !== self::PASSWORD_ALGO_FALLBACK) {
            if ($algo === null) {
                $algo = PASSWORD_DEFAULT;
            }
            $p = password_hash($password, $algo);
            if ($p === false) {
                throw new AuthenticationException('Could not hash password, password_hash() returned false!');
            }
        } else {
            $p = crypt($password, self::COMPAT_HASH . static::generateSalt());
            if (strlen($p) < 13) {
                throw new AuthenticationException('Hash generated by crypt() seems too small, this suggests an error!');
            }
        }

        return $p;
    }

    /**
     * Verify a password with either password_verify() or crypt()
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public static function verify($password, $hash)
    {
        if (static::supportsModernAPI()) {
            return password_verify($password, $hash);
        } else {
            return crypt($password, $hash) === $hash;
        }
    }

    /**
     * Shorthand to generate a salt to use with crypt()
     *
     * @return string
     */
    public static function generateSalt()
    {
        return bin2hex(openssl_random_pseudo_bytes(self::COMPAT_SALT_LENGTH / 2));
    }
}
