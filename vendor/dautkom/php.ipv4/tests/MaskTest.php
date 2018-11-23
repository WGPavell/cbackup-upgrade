<?php

namespace tests;
use dautkom\ipv4\IPv4;


/**
 * Tests performed against valid and invalid examples.
 * @package tests
 */
class MaskTest extends \PHPUnit_Framework_TestCase
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
     * IPv4 subnet mask format recognition and handling
     */
    public function testFormatRecognition()
    {

        // Invalid examples
        $this->assertNull($this->net->mask('62.205.205.2a')->getFormat());
        $this->assertNull($this->net->mask('255.255.255.256')->getFormat());
        $this->assertNull($this->net->mask('a.s.d.f')->getFormat());
        $this->assertNull($this->net->mask('/33')->getFormat());
        $this->assertNull($this->net->mask('/42')->getFormat());
        $this->assertNull($this->net->mask('/666')->getFormat());
        $this->assertNull($this->net->mask(33)->getFormat());
        $this->assertNull($this->net->mask('34')->getFormat());

        // Valid examples
        $this->assertEquals('dec', $this->net->mask('255.255.0.0')->getFormat());
        $this->assertEquals('bin', $this->net->mask('11111111111111111111111100000000')->getFormat());
        $this->assertEquals('oct', $this->net->mask('0377.0377.0377.0000')->getFormat());
        $this->assertEquals('hex', $this->net->mask('0xFFFF0000')->getFormat());
        $this->assertEquals('long', $this->net->mask('4294901760')->getFormat());
        $this->assertEquals('long', $this->net->mask(4294901760)->getFormat());
        $this->assertEquals('cidr', $this->net->mask('/1')->getFormat());
        $this->assertEquals('cidr', $this->net->mask('/24')->getFormat());
        $this->assertEquals('cidr', $this->net->mask('24')->getFormat());
        $this->assertEquals('cidr', $this->net->mask(24)->getFormat());

        // More invalid examples
        $this->assertNull($this->net->mask('255.255.10.0')->getFormat());
        $this->assertNull($this->net->mask('11111111111111111111111100000010')->getFormat());
        $this->assertNull($this->net->mask('0377.0377.0000.0377')->getFormat());
        $this->assertNull($this->net->mask('0xFFFF0F00')->getFormat());
        $this->assertNull( $this->net->mask('1053674754')->getFormat());

    }


    /**
     * IPv4 subnet mask validation
     */
    public function testValidation()
    {

        // Valid masks
        $this->assertTrue($this->net->mask('255.255.255.0')->isValid());
        $this->assertTrue($this->net->mask('255.255.255.255')->isValid());
        $this->assertTrue($this->net->mask('255.0.0.0')->isValid());
        $this->assertTrue($this->net->mask('128.0.0.0')->isValid());
        $this->assertTrue($this->net->mask('0xFF000000')->isValid());
        $this->assertTrue($this->net->mask('0377.0377.0377.0377')->isValid());
        $this->assertTrue($this->net->mask('11111111111111111111111100000000')->isValid());
        $this->assertTrue($this->net->mask('11111111111111111111111110000000')->isValid());
        $this->assertTrue($this->net->mask('24')->isValid());
        $this->assertTrue($this->net->mask(24)->isValid());
        $this->assertTrue($this->net->mask('/24')->isValid());
        $this->assertTrue($this->net->mask('/8')->isValid());
        $this->assertTrue($this->net->mask('/1')->isValid());
        $this->assertTrue($this->net->mask('4294901760')->isValid());
        $this->assertTrue($this->net->mask('128.0.0.0')->isValid());

        // Invalid masks
        $this->assertFalse($this->net->address('62.205.205.128')->mask('/23')->isValid(true));
        $this->assertFalse($this->net->address('0076.0315.0315.0200')->mask('23')->isValid(true));
        $this->assertFalse($this->net->address('0x3ECDCD80')->mask(23)->isValid(true));
        $this->assertFalse($this->net->mask('11111111111111111111111101000000')->isValid());
        $this->assertFalse($this->net->mask('255.255.0.1')->isValid());
        $this->assertFalse($this->net->mask('255.255.0.128')->isValid());
        $this->assertFalse($this->net->mask('255.255.0.fff')->isValid());
        $this->assertFalse($this->net->mask('0.0.0.0')->isValid());
        $this->assertFalse($this->net->mask('0377.0377.0000.0377')->isValid());
        $this->assertFalse($this->net->mask('/33')->isValid());
        $this->assertFalse($this->net->mask(34)->isValid());
        $this->assertFalse($this->net->mask(35)->isValid());
        $this->assertFalse($this->net->mask('/42')->isValid());
        $this->assertFalse($this->net->mask('64.0.0.0')->isValid());

        // Valid ip-mask combinations
        $this->assertTrue($this->net->address('62.205.205.2')->mask('/24')->isValid());
        $this->assertTrue($this->net->address('62.205.205.2')->mask(24)->isValid());
        $this->assertTrue($this->net->address('62.205.205.2')->mask('24')->isValid());
        $this->assertTrue($this->net->address('62.205.205.2')->mask('255.255.255.0')->isValid());
        $this->assertTrue($this->net->address('62.205.205.2')->mask('11111111111111111111111110000000')->isValid());
        $this->assertTrue($this->net->address('10.0.0.0')->mask('4294901760')->isValid());

        // Invalid ip-mask combinations
        $this->assertFalse($this->net->address('62.205.205.2')->mask('11111111111111111111111110000001')->isValid());
        $this->assertFalse($this->net->address('62.205.205.2')->mask('255.255.255.12')->isValid());

        // Even if subnet address is invalid - method doesn't care and it shouldn't
        $this->assertTrue($this->net->address('a.s.d.f')->mask('255.255.255.0')->isValid());
        // unless it's a strict check
        $this->assertFalse($this->net->address('a.s.d.f')->mask('255.255.255.0')->isValid(true));

        // Invalid subnets, strict check
        $this->assertFalse($this->net->address('62.205.205.2')->mask('255.255.255.0')->isValid(true));
        $this->assertFalse($this->net->address('62.205.205.0')->mask('0xFFFF0000')->isValid(true));
        $this->assertFalse($this->net->address('62.205.205.128')->mask('255.255.255.0')->isValid(true));
        $this->assertFalse($this->net->address('62.205.205.128')->mask('/23')->isValid(true));
        $this->assertFalse($this->net->address('62.205.205.2')->mask('/30')->isValid(true));

        // Valid subnets, strict check
        $this->assertTrue($this->net->address('10.0.0.0')->mask('4294901760')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.0')->mask('255.255.255.0')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.0')->mask('0xFFFFFF80')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.128')->mask('0xFFFFFF80')->isValid(true));
        // this is a single host, it's perfectly valid
        $this->assertTrue($this->net->address('62.205.205.2')->mask('/32')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.2')->mask('32')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.2')->mask(32)->isValid(true));

        // cidr /31
        $this->assertTrue($this->net->address('62.205.205.0')->mask('/31')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.2')->mask('/31')->isValid(true));
        $this->assertFalse($this->net->address('62.205.205.1')->mask('/31')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.0')->mask('31')->isValid(true));
        $this->assertTrue($this->net->address('62.205.205.2')->mask(31)->isValid(true));
        $this->assertFalse($this->net->address('62.205.205.1')->mask(31)->isValid(true));

    }


    /**
     * IPv4 subnet masks format converting
     */
    public function testFormatConverting()
    {

        // Invalid masks
        $this->assertNull($this->net->mask('255.255.255.0a')->convertTo('long'));
        $this->assertNull($this->net->mask('1111111111111111111111100000010')->convertTo('dec'));
        $this->assertNull($this->net->mask('a.s.d.f')->convertTo('bin'));
        $this->assertNull($this->net->mask('0377.0377.0000.0377')->convertTo('bin'));
        $this->assertNull($this->net->mask('/33')->convertTo('bin'));
        $this->assertNull($this->net->mask('/42')->convertTo('bin'));
        $this->assertNull($this->net->mask('42')->convertTo('bin'));
        $this->assertNull($this->net->mask(42)->convertTo('bin'));

        // Valid masks
        $this->assertEquals('255.255.255.0', $this->net->mask('/24')->convertTo('dec'));
        $this->assertEquals('24', $this->net->mask('255.255.255.0')->convertTo('cidr'));
        $this->assertEquals('11111111111111111111111100000000', $this->net->mask('/24')->convertTo('bin'));
        $this->assertEquals('11111111111111111111111100000000', $this->net->mask('255.255.255.0')->convertTo('bin'));
        $this->assertEquals('0377.0377.0000.0000', $this->net->mask('255.255.0.0')->convertTo('oct'));
        $this->assertEquals('0xFFFF0000', $this->net->mask('255.255.0.0')->convertTo('hex'));
        $this->assertEquals('0xFF.0xFF.0x00.0x00', $this->net->mask('255.255.0.0')->convertTo('hex', true));

        // Only mask will be converted
        $this->assertEquals('255.255.255.0', $this->net->address('62.205.205.0')->mask('/24')->convertTo('dec'));
        // Even if subnet address is invalid - method doesn't care and it shouldn't
        $this->assertEquals('255.255.255.0', $this->net->address('62.205.205.2')->mask('/24')->convertTo('dec'));

    }


    /**
     * Get subnet base address from IP address and subnet mask
     */
    public function testGetSubnetAddress()
    {

        // Valid examples
        $this->assertEquals('62.205.205.0', $this->net->address('62.205.205.2')->mask('255.255.255.0')->getSubnetAddress());
        $this->assertEquals('62.205.205.0', $this->net->address('62.205.205.2')->mask('/24')->getSubnetAddress());
        $this->assertEquals('62.205.205.0', $this->net->address('62.205.205.2')->mask('11111111111111111111111100000000')->getSubnetAddress());
        $this->assertEquals('62.205.205.0', $this->net->address('62.205.205.2')->mask('255.255.255.192')->getSubnetAddress());

        // Invalid examples
        $this->assertNull($this->net->address('62.205.205.2')->mask('asdf')->getSubnetAddress());
        $this->assertNull($this->net->address('62.205.205.2')->mask('255.0.0.12')->getSubnetAddress());
        $this->assertNull($this->net->address('62.205.205.2')->mask('0.0.0.0')->getSubnetAddress());

    }


    /**
     * Get range of addresses within the given subnet
     */
    public function testGetSubnetRange()
    {

        // Invalid examples
        $this->assertNull($this->net->address('62.205.205.2')->mask('asdf')->getRange());

        // Valid examples
        $this->assertEquals(
            [0 => '62.205.205.0', 1 => '62.205.205.127'],
            $this->net->address('62.205.205.0')->mask('/25')->getRange()
        );

    }


    /**
     * Count available hosts in the given subnet. The broadcast address is already excluded, boolean argument
     * flags should the gateway also be excluded or not.
     */
    public function testCountHosts()
    {

        // Invalid examples
        $this->assertNull($this->net->address('62.205.205.2')->mask('/34')->countHosts());
        $this->assertNull($this->net->address('62.205.205.2')->mask('/42')->countHosts(true));
        $this->assertNull($this->net->address('62.205.205.2')->mask('0xFFFFFF00')->countHosts());

        // Invalid examples
        $this->assertNull($this->net->address('a.s.d.f')->mask('/24')->countHosts());

        // Valid examples with and without gateway
        // The broadcast address is already excluded
        $this->assertEquals('254', $this->net->address('62.205.205.0')->mask('0xFFFFFF00')->countHosts());
        $this->assertEquals('253', $this->net->address('62.205.205.0')->mask('0xFFFFFF00')->countHosts(true));

    }


    /**
     * Determine if given IPv4 address is a broadcast address
     */
    public function testBroadcast()
    {

        // Valid
        $this->assertTrue($this->net->address('62.205.205.255')->mask('255.255.255.0')->isBroadcast());
        $this->assertTrue($this->net->address('62.205.205.255')->mask('/32')->isBroadcast());
        $this->assertEquals('62.205.205.255', $this->net->address('62.205.205.2')->mask('0xFFFFFF00')->getBroadcast());

        // Invalid
        $this->assertFalse($this->net->address('62.205.205.2')->mask('255.255.255.0')->isBroadcast());
        $this->assertFalse($this->net->address('62.205.205.255')->mask('asdf')->isBroadcast());
        $this->assertNull($this->net->address('62.205.205.3')->mask('asdf')->getBroadcast());
        $this->assertNull($this->net->address('a.s.d.f')->mask('0xFFFF0000')->getBroadcast());

    }


    /**
     * Determine if given address and mask is a valid subnet
     */
    public function testIsSubnet()
    {

        $this->assertTrue($this->net->address('192.168.0.0')->mask('/24')->isSubnet());
        $this->assertTrue($this->net->address('192.168.0.1')->mask('255.255.255.255')->isSubnet());
        $this->assertTrue($this->net->address('10.0.0.128')->mask('255.255.255.192')->isSubnet());

        $this->assertFalse($this->net->address('192.168.0.1')->mask('255.255.255.0')->isSubnet());
        $this->assertFalse($this->net->address('192.168.0.1')->mask('/23')->isSubnet());
        $this->assertFalse($this->net->address('192.168.250.0')->mask('/20')->isSubnet());
        $this->assertFalse($this->net->address('62.205.205.3')->mask('asdf')->isSubnet());
        $this->assertFalse($this->net->address('a.s.d.f')->mask('255.255.255.0')->isSubnet());

    }


    /**
     * Check if an IP-address belongs to the given subnet.
     * Test is performed against Mask::has(string $ip) method
     */
    public function testIpAddressBelonging()
    {

        $this->assertTrue($this->net->address('192.168.0.0')->mask('/24')->has('192.168.0.12'));
        $this->assertTrue($this->net->address('192.168.0.0')->mask('0xFFFFFF00')->has('192.168.0.1'));
        $this->assertTrue($this->net->address('192.168.0.0')->mask('255.255.255.0')->has('192.168.0.255'));
        $this->assertTrue($this->net->address('192.168.0.10')->mask('255.255.255.255')->has('192.168.0.10'));

        $this->assertFalse($this->net->address('10.1.2.0')->mask('255.255.255.128')->has('192.168.0.255'));
        $this->assertFalse($this->net->address('10.1.2.0')->mask('255.255.255.128')->has('10.1.2.200'));

        $this->assertFalse($this->net->address('a.s.d.f')->mask('255.255.255.128')->has('10.1.2.200'));
        $this->assertFalse($this->net->address('10.1.2.0')->mask('a.s.d.f')->has('10.1.2.200'));

    }

}
