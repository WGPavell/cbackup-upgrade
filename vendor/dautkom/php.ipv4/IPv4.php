<?php

namespace dautkom\ipv4;
use dautkom\ipv4\lib\{
    Address, Mask, Subnet
};


/**
 * PHP IPv4 library core class 
 * @package dautkom\ipv4
 */
class IPv4
{

    /**
     * Raw IP-address
     * @var string
     */
    protected static $ipRaw = null;

    /**
     * Subnet mask property
     * @var string
     */
    protected static $subnetRaw = null;

    /**
     * IP-address as a GMP resource
     * @var resource
     */
    protected static $ipResource = null;

    /**
     * Subnet mask as a GMP resource
     * @var resource
     */
    protected static $subnetResource = null;

    /**
     * Supported IP-address formats.
     *
     * @var array
     */
    protected $formats = [
        // 172.16.128.22
        'dec'  => '/^((0|1[0-9]{0,2}|2[0-9]{0,1}|2[0-4][0-9]|25[0-5]|[3-9][0-9]{0,1})\.){3}(0|1[0-9]{0,2}|2[0-9]{0,1}|2[0-4][0-9]|25[0-5]|[3-9][0-9]{0,1})$/',
        // 0xc0.0xA8.0xFF.0xFF ; 0xa412bf11
        'hex'  => '/(?=^[0-9a-fx.]{10,19}$)^(0x(?:(?:0x)?[0-9a-f]{2}\.?){4}$)/i',
        // 0300.0250.0377.0155
        'oct'  => '/(?=^[0-8.]{19}$)^([0-8.?]+)/',
        // 10101010111100111011010001110011
        'bin'  => '/(?=^.*1.*$)^([0-1]{32})$/',
        // ip2long formats
        'long' => [-2147483649, 4294967296],
    ];
    
    
    /**
     * @param string $ip
     * @return Address
     */
    public function address(string $ip)
    {
        return new Address($ip);
    }


    /**
     * @param string $subnet
     * @return Mask
     */
    public function mask(string $subnet)
    {
        return new Mask($subnet);
    }


    /**
     * @param string $subnet
     * @return Subnet
     */
    public function subnet(string $subnet)
    {
        return new Subnet($subnet);
    }

}
