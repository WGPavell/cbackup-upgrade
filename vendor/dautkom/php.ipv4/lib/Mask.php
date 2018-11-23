<?php

namespace dautkom\ipv4\lib;


/**
 * Class with IPv4 subnet mask methods
 * @package dautkom\ipv4\lib
 */
class Mask extends Address
{

    /** @noinspection PhpMissingParentConstructorInspection
     *  @param string $subnet ipv4 subnet mask
     *  @ignore
     */
    public function __construct(string $subnet)
    {
        self::$subnetResource = null;
        self::$subnetRaw      = $subnet;
        $this->formats        = array_merge($this->formats, ['cidr' => '%^(/?[1-9]$|/?[1-2][0-9]|/?3[0-2])$%i']);
        $this->registerResource();
    }


    /**
     * Retrieve subnet address format.
     * This method DOES NOT check if mask is valid - it only determines format compliance.
     *
     * Return values:
     * - dec
     * - hex
     * - oct
     * - long
     * - bin
     * - cidr
     * 
     * @return string|null
     */
    public function getFormat()
    {

        // Tricky way to determine CIDR notation
        // by putting 'cidr' regexp above 'long'
        ksort($this->formats);

        foreach ( $this->formats as $format=>$pattern ) {

            // For regular expressions
            if( !is_array($pattern) ) {
                if ( preg_match( $pattern, self::$subnetRaw ) ) {
                    return $format;
                }
            }

            // Check for Long and CIDR formats
            elseif( count($pattern) == 2 && array_key_exists(0, $pattern) && array_key_exists(1, $pattern) ) {
                if( is_numeric(self::$subnetRaw) && self::$subnetRaw > $pattern[0] && self::$subnetRaw < $pattern[1] ) {
                    return $format;
                }
            }

        }

        return null;

    }


    /**
     * Check if subnet mask is valid
     * 
     * @param  bool $strict check if mask+subnet combination is a valid network
     * @return bool
     */
    public function isValid(bool $strict = false): bool
    {

        // Cannot proceed if IP-address was set and if it is not valid
        if( isset(self::$ipRaw) && is_null(self::$ipResource) ) {
            return false;
        }

        // Strict check for subnet.
        // In this case ip address and mask must be a valid subnet address combination, otherwise 'false' will be returned
        if( $strict == true ) {

            if( is_null(self::$subnetResource) || is_null(self::$ipResource) ) {
                return false;
            }

            $subnet = long2ip(gmp_intval(self::$ipResource));
            $mask   = gmp_popcount(self::$subnetResource);
            $range  = long2ip((ip2long($subnet)) & ((-1 << (32 - (int)$mask))));
            if ($range!=$subnet) return false;

        }

        return !is_null(self::$subnetResource);

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
     * - cidr
     *
     * @param  string $format target notation literal representation  
     * @param  bool   $flag   additional flag for 'hex' do return as 'hex-dotted'
     * @return string|null
     */
    public function convertTo(string $format, bool $flag = false)
    {

        if( $this->isValid() ) {
            $reflection = new \ReflectionMethod('dautkom\ipv4\lib\Transformer', $format);
            return $reflection->invokeArgs(new Transformer(), [self::$subnetResource, $flag]);
        }

        return null;

    }


    /**
     * Calculate subnet address based on IP and subnet mask
     * 
     * @return string|null
     */
    public function getSubnetAddress()
    {

        if( parent::isValid() && $this->isValid() ) {
            return long2ip( gmp_intval( gmp_and(self::$ipResource, self::$subnetResource) ) );
        }

        return null;

    }


    /**
     * Get avaliable range of IP-addresses in subnet
     * 
     * @return array|null
     */
    public function getRange()
    {

        if( !parent::isValid() || !$this->isValid() ) {
            return null;
        }

        $subnet   = parent::convertTo('dec');
        $mask     = $this->convertTo('cidr');
        $range[0] = long2ip((ip2long($subnet)) & ((-1 << (32 - (int)$mask))));
        $range[1] = long2ip((ip2long($subnet)) + pow(2, (32 - (int)$mask)) - 1);

        return ($range[0]!=$subnet) ? null : $range;

    }


    /**
     * Return amount of avaliable IP-addresses in subnet without broadcast address.
     * 0 is returned if there're no avaliable addresses (e.g. mask 255.255.255.254)
     * 
     * @param  bool $exclude [optional] exclude gateway
     * @return int|null
     */
    public function countHosts(bool $exclude = false)
    {

        if ( !$this->isValid(true) ) {
            return null;
        }

        $result = gmp_strval( gmp_xor(self::$subnetResource, gmp_init('0xffffffff', 16)) ) - 1;

        // Is it supposed to exclude gateway?
        return ($exclude) ? (($result < 0) ? abs($result) : ($result-1) ) : abs($result);

    }


    /**
     * Return broadcast address.
     * In ->address($arg) there could be passed regular ip-address or subnet address
     *
     * @return string|null
     */
    public function getBroadcast()
    {
        
        if ( !$this->isValid() || !parent::isValid() ) {
            return null;
        }
        
        $mask  = ip2long( $this->convertTo('dec') );
        $bcast = (ip2long( parent::convertTo('dec') ) & $mask) | (~$mask);
        
        return long2ip($bcast);
        
    }


    /**
     * Check if an IP argument is a broadcast address
     *
     * @return bool
     */
    public function isBroadcast(): bool
    {
        return ( parent::isValid() && $this->isValid() && $this->getBroadcast() == parent::convertTo('dec') ) ? true : false;
    }


    /**
     * Check if an IP argument is a subnet address
     *
     * @return bool
     */
    public function isSubnet(): bool
    {
        
        if( !$this->isValid() || !parent::isValid() ) {
            return false;
        }
        
        return ( $this->getSubnetAddress() == parent::convertTo('dec') ) ? true : false;
        
    }


    /**
     * Check if $ip belongs to a specified subnet.
     * 
     * @param  string $ip
     * @return bool
     */
    public function has(string $ip): bool
    {

        if ( !$this->isValid() || !parent::isValid() ) {
            return false;
        }

        if( !$this->address(parent::convertTo('dec'))->mask(self::$subnetRaw)->isValid(true) ) {
            return false;
        }

        $subnet = ip2long($this->getSubnetAddress());
        $mask   = $this->convertTo('cidr');
        $ip     = ip2long($ip);

        return ($subnet <= $ip) && ($ip <= ($subnet + pow(2, (32 - (int)$mask)) - 1));

    }
    

    /**
     * Transform subnet mask address into GMP decimal resource.
     * Source subnet mask address could be declarated in several formats.
     * 
     * Why  $net->mask('4294967299')->getFormat() does not count mask as long?
     * This method after first initialization in  $net->mask('4294967299') and
     * before ->getFormat(), performs $this->validateMask(). And 4294967299 is
     * not a correct mask, that's why static self::$subnetRaw becomes NULL and
     * getFormat() returns NULL.
     *
     * @return bool
     */
    private function registerResource(): bool
    {

        $format = $this->getFormat();
        self::$subnetResource = null;

        if( !is_null($format) ) {

            $reflection = new \ReflectionMethod('dautkom\ipv4\lib\Registrator', $format);
            $mask       = $reflection->invoke(new Registrator(), self::$subnetRaw);

            if( $this->validateMask($mask) ) {
                self::$subnetResource = $mask;
                return true;
            }
            else {
                self::$subnetRaw = null;
            }

        }

        return false;

    }


    /**
     * Internal subnet mask validator. Perform binary check if subnet mask address indeed represents
     * valid mask. E.g. 11111111111111111111111100000000 is valid for /24 and 11111111111111111111111100001000 is not
     *
     * @param  resource $mask
     * @return bool
     */
    private function validateMask($mask): bool
    {

        if( is_object($mask) && $mask instanceof \GMP) {

            // Check if binary mask doesn't have gaps in 1's
            // e.g. 11100... is valid and 11101... is not
            if( preg_match('/(?=^[0-1]{32}$)^1+(0?)+$/i', gmp_strval($mask, 2)) ) {
                return true;
            }

        }

        return false;

    }

}
