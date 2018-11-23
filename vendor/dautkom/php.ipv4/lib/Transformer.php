<?php

namespace dautkom\ipv4\lib;


/**
 * Reflection class for converting IPv4 addresses and subnet masks between different formats.
 * All methods accept GMP resource in argument and return string as a result.
 * @package dautkom\ipv4\lib
 */
class Transformer
{

    /**
     * Convert IP in GMP resource to human-readable quad dotted decimal format AAA.BBB.CCC.DDD
     *
     * @param resource $res GMP resource of IP address or subnet mask
     * @return string
     */
    public function dec($res): string
    {
        return long2ip( gmp_intval($res) );
    }


    /**
     * Convert IP in GMP resource to hexademical format 0xAABBCCDD or into hex-dotted format
     * 0xAA.0xBB.0xCC.0xDD if second argument is set to TRUE
     *
     * @param  resource $res GMP resource of IP address or subnet mask
     * @param  bool     $dotted [optional] should output be dot separated or no
     * @return string
     */
    public function hex($res, $dotted = false): string
    {

        if( $dotted ) {

            $ip = explode( '.', $this->dec($res) );

            for( $i=0; $i<4; $i++ ) {
                $ip[$i] = '0x' . strtoupper( sprintf("%02x", $ip[$i]) );
            }

            return join('.', $ip);

        }
        else {
            return '0x'.strtoupper( str_pad(gmp_strval($res, 16), 8, '0', STR_PAD_LEFT) );
        }

    }


    /**
     * Convert IP in GMP resource to octal format aaaa.bbbb.cccc.dddd
     *
     * @param  resource $res GMP resource of IP address or subnet mask
     * @return string
     */
    public function oct($res): string
    {

        $ip = explode( '.', $this->dec($res) );

        for( $i=0; $i<4; $i++ ) {
            $ip[$i] = sprintf("%04o", $ip[$i]);
        }

        return join('.', $ip);

    }


    /**
     * Convert IP in GMP resource to integer (ip2long) format.
     * Result is always unsigned int. We say 'no' to negative integers after ip2long().
     *
     * @param  resource $res GMP resource of IP address or subnet mask
     * @return string
     */
    public function long($res): string
    {
        return gmp_strval($res, 10);
    }


    /**
     * Convert IP in GMP resource to binary format
     *
     * @param  resource $res GMP resource of IP address or subnet mask
     * @return string
     */
    public function bin($res): string
    {
        return str_pad( gmp_strval($res, 2), 32, '0', STR_PAD_LEFT);
    }


    /**
     * Convert subnet mask in GMP resource to CIDR format.
     * Performs additional check if argument is a valid subnet mask.
     * Ensure you didn't forget to add slash to the returned value if necessary.
     *
     * @param  resource $res GMP resource of IP subnet mask
     * @return string|null
     */
    public function cidr($res)
    {

        $mask = gmp_strval($res, 2);

        if( !preg_match('/(?=^[0-1]{32}$)^1+(0?)+$/i', $mask) ) {
            return null;
        }

        return strval( gmp_popcount($res) );

    }

}
