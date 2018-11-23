<?php

namespace tests;
use dautkom\ipv4\IPv4;


/**
 * Tests performed against valid and invalid examples.
 * @package tests
 */
class SubnetTest extends \PHPUnit_Framework_TestCase
{

    /** @var IPv4 */
    private $net;

    /**
     * @ignore
     */
    public function __construct()
    {
        parent::__construct();
        $this->net = new IPv4();
    }


    /**
     * IPv4 subnet validation
     */
    public function testValidation()
    {

        $this->assertTrue($this->net->subnet('62.205.205.0/24')->isValid());
        $this->assertTrue($this->net->subnet('10.0.0.0/30')->isValid());
        $this->assertTrue($this->net->subnet('10.0.0.1/32')->isValid());
        $this->assertTrue($this->net->subnet('10.0.0.4/30')->isValid());

        $this->assertFalse($this->net->subnet('10.3.2.1/31')->isValid());
        
        $this->assertFalse($this->net->subnet('10.0.0.0/42')->isValid());
        $this->assertFalse($this->net->subnet('62.205.205.1/24')->isValid());
        $this->assertFalse($this->net->subnet('62.205.205.1')->isValid());
        $this->assertFalse($this->net->subnet('')->isValid());
        $this->assertFalse($this->net->subnet('a.s.d.f')->isValid());
        $this->assertFalse($this->net->subnet('0/24')->isValid());
        $this->assertFalse($this->net->subnet('0xFF00F0FF/24')->isValid());

    }


    /**
     * Subnet base address retrieving
     */
    public function testGetSubnetAddress()
    {

        $this->assertEquals('62.205.205.0', $this->net->subnet('62.205.205.0/24')->getSubnetAddress());
        $this->assertEquals('10.0.0.0', $this->net->subnet('10.0.0.0/30')->getSubnetAddress());
        $this->assertEquals('10.0.0.1', $this->net->subnet('10.0.0.1/32')->getSubnetAddress());
        $this->assertEquals('10.0.0.4', $this->net->subnet('10.0.0.4/30')->getSubnetAddress());

        $this->assertNull($this->net->subnet('10.3.2.1/31')->getSubnetAddress());
        $this->assertNull($this->net->subnet('10.0.0.0/42')->getSubnetAddress());
        $this->assertNull($this->net->subnet('62.205.205.0')->getSubnetAddress());
        $this->assertNull($this->net->subnet('asdf')->getSubnetAddress());

    }


    /**
     * Subnet base address retrieving
     */
    public function testGetMask()
    {

        $this->assertEquals('24', $this->net->subnet('62.205.205.0/24')->getMask());
        $this->assertEquals('/24', $this->net->subnet('62.205.205.0/24')->getMask(true));
        $this->assertEquals('/24', $this->net->subnet('62.205.205.0/24')->getMask(1));
        $this->assertEquals('30', $this->net->subnet('10.0.0.0/30')->getMask());
        $this->assertEquals('32', $this->net->subnet('10.0.0.1/32')->getMask());
        $this->assertEquals('30', $this->net->subnet('10.0.0.4/30')->getMask());

        $this->assertNull($this->net->subnet('10.3.2.1/31')->getMask());
        $this->assertNull($this->net->subnet('10.0.0.0/42')->getMask());
        $this->assertNull($this->net->subnet('62.205.205.0')->getMask());
        $this->assertNull($this->net->subnet('asdf')->getMask());

    }


    /**
     * Get range of addresses within the given subnet
     */
    public function testGetSubnetRange()
    {

        // Invalid examples
        $this->assertNull($this->net->subnet('')->getRange());
        $this->assertNull($this->net->subnet('/23')->getRange());
        $this->assertNull($this->net->subnet('62.205.205.0/asdf')->getRange());
        $this->assertNull($this->net->subnet('62.205.205.0/255.255.255.0')->getRange());

        // Valid examples
        $this->assertEquals([0 => '62.205.205.0', 1 => '62.205.205.127'], $this->net->subnet('62.205.205.0/25')->getRange());
        $this->assertEquals([0 => '192.168.10.0', 1 => '192.168.10.255'], $this->net->subnet('192.168.10.0/24')->getRange());

    }


    /**
     * Determine if given IPv4 address is a broadcast address
     */
    public function testBroadcast()
    {

        // Valid
        $this->assertEquals('62.205.205.255', $this->net->subnet('62.205.205.0/24')->getBroadcast());
        $this->assertEquals('62.205.205.2', $this->net->subnet('62.205.205.2/32')->getBroadcast());
        $this->assertEquals('62.205.205.1', $this->net->subnet('62.205.205.0/31')->getBroadcast());
        $this->assertEquals('192.168.0.127', $this->net->subnet('192.168.0.0/25')->getBroadcast());

        // Invalid
        $this->assertNull($this->net->subnet('62.205.205.3')->getBroadcast());
        $this->assertNull($this->net->subnet('a.s.d.f')->getBroadcast());
        $this->assertNull($this->net->subnet('0/24')->getBroadcast());
        $this->assertNull($this->net->subnet('0/20')->getBroadcast());

    }


    /**
     * Count available hosts in the given subnet. The broadcast address is already excluded, boolean argument
     * flags should the gateway also be excluded or not.
     */
    public function testCountHosts()
    {

        // Invalid examples
        $this->assertNull($this->net->subnet('a.s.d.f/24')->countHosts());
        $this->assertNull($this->net->subnet('62.205.205.0/34')->countHosts());
        $this->assertNull($this->net->subnet('62.205.205.0/42')->countHosts(true));
        $this->assertNull($this->net->subnet('62.205.205.0/0xFFFFFF00')->countHosts());
        $this->assertNull($this->net->subnet('0/24')->countHosts());
        $this->assertNull($this->net->subnet('/24')->countHosts());
        $this->assertNull($this->net->subnet('24')->countHosts());
        $this->assertNull($this->net->subnet('')->countHosts());

        // Valid examples with and without gateway
        // The broadcast address is already excluded
        $this->assertEquals('254', $this->net->subnet('62.205.205.0/24')->countHosts());
        $this->assertEquals('253', $this->net->subnet('62.205.205.0/24')->countHosts(true));

    }


    /**
     * Check if an IP-address belongs to the given subnet.
     * Test is performed against Subnet::has(string $ip) method
     */
    public function testIpAddressBelonging()
    {

        $this->assertTrue($this->net->subnet('192.168.0.0/24')->has('192.168.0.12'));
        $this->assertTrue($this->net->subnet('192.168.0.0/24')->has('192.168.0.1'));
        $this->assertTrue($this->net->subnet('192.168.0.0/24')->has('192.168.0.255'));
        $this->assertTrue($this->net->subnet('192.168.0.10/32')->has('192.168.0.10'));

        $this->assertFalse($this->net->subnet('10.1.2.0/25')->has('192.168.0.255'));
        $this->assertFalse($this->net->subnet('10.1.2.0/25')->has('10.1.2.200'));

        $this->assertNull($this->net->subnet('a.s.d.f/25')->has('10.1.2.200'));
        $this->assertNull($this->net->subnet('10.1.2.0/a.s.d.f')->has('10.1.2.200'));

    }

}
