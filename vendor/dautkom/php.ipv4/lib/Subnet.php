<?php

namespace dautkom\ipv4\lib;
use dautkom\ipv4\IPv4;


class Subnet
{
    
    /** @var string Subnet address */
    private $address;
    
    /** @var string CIDR mask */
    private $mask;

    /** @var IPv4 */
    private $net;
    

    /**
     * @ignore
     * @param string $subnet
     */
    public function __construct(string $subnet)
    {

        $subnet = explode('/', $subnet);

        if( is_array($subnet) && count($subnet) == 2 ) {
            $this->address = strval($subnet[0]);
            $this->mask    = strval($subnet[1]);
            $this->net     = new IPv4();
        }

    }


    /**
     * Check if given subnet is valid and given in correct notation (decimal-dotted + cidr)
     *
     * @return bool
     */
    public function isValid(): bool
    {

        if( isset($this->address) && isset($this->mask) ) {

            if(
                !$this->net->address($this->address)->isValid()
                || strcasecmp('dec', $this->net->address($this->address)->getFormat()) !== 0
                || strcasecmp('cidr', $this->net->mask($this->mask)->getFormat()) !== 0
            ) {
                return false;
            }

            return $this->net->address($this->address)->mask($this->mask)->isValid(true);
        }

        return false;

    }


    /**
     * Retrieve subnet address in decimal-dotted format
     * 
     * @return string|null
     */
    public function getSubnetAddress()
    {
        return $this->isValid() ? $this->address : null;
    }


    /**
     * @param  bool $slash should slash be added to the result
     * @return string|null
     */
    public function getMask(bool $slash = false)
    {

        if( $this->isValid() ) {
            return ($slash) ? "/{$this->mask}" : $this->mask;
        }
        
        return null;
        
    }


    /**
     * Get avaliable range of IP-addresses in given subnet
     *
     * @return array|null
     */
    public function getRange()
    {

        if( $this->isValid() ) {
            return $this->net->address($this->address)->mask($this->mask)->getRange();
        }

        return null;

    }


    /**
     * Return broadcast address.
     *
     * @return string|null
     */
    public function getBroadcast()
    {

        if( $this->isValid() ) {
            return $this->net->address($this->address)->mask($this->mask)->getBroadcast();
        }

        return null;

    }


    /**
     * Return amount of avaliable IP-addresses in subnet without broadcast address.
     * 0 is returned if there're no avaliable addresses (e.g. for mask /31)
     *
     * @param  bool $exclude [optional] exclude gateway
     * @return int|null
     */
    public function countHosts($exclude = false)
    {
        
        if( $this->isValid() ) {
            return $this->net->address($this->address)->mask($this->mask)->countHosts($exclude);
        }
        
        return null;
        
    }


    /**
     * Check if $ip belongs to a specified subnet.
     *
     * @param  string $ip
     * @return bool|null
     */
    public function has(string $ip)
    {

        if( $this->isValid() ) {
            return $this->net->address($this->address)->mask($this->mask)->has($ip);
        }

        return null;

    }

}
