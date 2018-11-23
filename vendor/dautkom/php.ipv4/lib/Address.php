<?php

namespace dautkom\ipv4\lib;
use dautkom\ipv4\{
    IPv4
};


/**
 * Class with IPv4 addresses methods
 * @package dautkom\ipv4\lib
 */
class Address extends IPv4
{

    /**
     * @param  string $ip ipv4 address
     * @ignore
     */
    public function __construct(string $ip)
    {
        self::$ipRaw      = $ip;
        self::$ipResource = null;
        $this->registerResource();
    }


    /**
     * Retrieve IP-address format.
     *
     * Return values:
     * - dec
     * - hex
     * - oct
     * - long
     * - bin
     *
     * @return string|null
     */
    public function getFormat()
    {

        foreach( $this->formats as $format=>$pattern ) {

            // For regular expressions
            if( !is_array($pattern) ) {
                if ( preg_match( $pattern, self::$ipRaw ) ) {
                    return $format;
                }
            }

            // Check for Long format
            elseif( count($pattern) == 2 && array_key_exists(0, $pattern) && array_key_exists(1, $pattern) ) {
                if( is_numeric(self::$ipRaw) && self::$ipRaw > $pattern[0] && self::$ipRaw < $pattern[1] && preg_match('/^\-?[0-9]+$/', self::$ipRaw) ) {
                    return $format;
                }
            }

        }

        return null;

    }


    /**
     * Check if IP-address is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !is_null(self::$ipResource);
    }


    /**
     * Convert IP-address to a certain format
     *
     * Supported arguments:
     * - dec
     * - hex
     * - oct
     * - long
     * - bin
     *
     * @param  string $format target notation literal representation
     * @param  bool   $flag   additional flag for 'hex' do return as 'hex-dotted'
     * @return string|null
     */
    public function convertTo(string $format, bool $flag = false)
    {

        if( $this->isValid() ) {
            $reflection = new \ReflectionMethod('dautkom\ipv4\lib\Transformer', $format);
            return $reflection->invokeArgs(new Transformer(), [self::$ipResource, $flag]);
        }

        return null;

    }


    /**
     * Transform IP-address into GMP decimal resource. Source IP-address could be declarated in several formats.
     * 
     * @return bool
     */
    private function registerResource(): bool
    {

        $format = $this->getFormat();

        if( !is_null($format) ) {
            $reflection       = new \ReflectionMethod('dautkom\ipv4\lib\Registrator', $format);
            self::$ipResource = $reflection->invoke(new Registrator(), self::$ipRaw);
            return true;
        }

        self::$ipRaw      = null;
        self::$ipResource = null;
        return false;

    }

}
