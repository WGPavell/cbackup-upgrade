<?php

namespace dautkom\ipv4\lib;


/**
 * Reflection class for registering IPv4::$ipResource and IPv4::$subnetResource properties.
 * Performs necessary translations and returns GMP resource. 
 * @package dautkom\ipv4\lib
 */
class Registrator
{

    /**
     * @param  string $src
     * @return resource
     */
    public function dec($src)
    {
        $src = sprintf("%u", floatval(ip2long($src)));
        return gmp_init($src);
    }

    /**
     * @param  string $src
     * @return resource
     */
    public function bin($src)
    {
        $src = gmp_init( $src, 2 );
        $src = gmp_strval( $src, 10 );
        return gmp_init($src);
    }

    /**
     * @param  string $src
     * @return resource
     */
    public function oct($src)
    {
        $src = explode('.', $src);
        $src = array_map(function($value) { return octdec($value); }, $src);
        $src = join('.', $src);
        return $this->dec($src);
    }


    /**
     * @param  string $src
     * @return resource
     */
    public function hex($src)
    {
        $src = str_replace(['.', '0x'], '', $src);
        $src = hexdec($src);
        return gmp_init($src);
    }


    /**
     * @param  string $src
     * @return resource
     */
    public function long($src)
    {
        return gmp_init($src);
    }


    /**
     * @param  string $src
     * @return resource
     */
    public function cidr($src)
    {
        $src = intval( preg_replace('/[^\d]/', '', $src) );
        $src = sprintf("%u", floatval(ip2long(long2ip(-1 << (32 - $src)))));
        return gmp_init($src);
    }

}
