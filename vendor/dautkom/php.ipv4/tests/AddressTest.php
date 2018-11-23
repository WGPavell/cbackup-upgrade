<?php

namespace tests;
use dautkom\ipv4\IPv4;


/**
 * Tests performed against valid and invalid examples.
 * @package tests
 */
class AddressTest extends \PHPUnit_Framework_TestCase
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
     * IPv4 address format recognition and handling
     */
    public function testFormatRecognition()
    {

        // Invalid examples
        $this->assertNull($this->net->address('62.205.205.2a')->getFormat());
        $this->assertNull($this->net->address('62.205.205.256')->getFormat());
        $this->assertNull($this->net->address('om.g.wt.f')->getFormat());

        // Valid decimal dotted addresses
        $this->assertEquals('dec', $this->net->address('62.205.205.2')->getFormat());
        $this->assertEquals('dec', $this->net->address('10.0.0.0')->getFormat());
        $this->assertEquals('dec', $this->net->address('192.168.10.1')->getFormat());
        $this->assertEquals('dec', $this->net->address('255.255.255.255')->getFormat());
        $this->assertEquals('dec', $this->net->address('0.0.0.0')->getFormat());

        // Binary addresses
        $this->assertEquals('bin', $this->net->address('00111110110011011100110100000010')->getFormat());
        $this->assertNull($this->net->address('0011111011001101110010100000010')->getFormat()); // one bit missing

        // Octal-dotted addresses
        $this->assertEquals('oct', $this->net->address('0076.0315.0315.0002')->getFormat());
        $this->assertNull($this->net->address('0076031503150002')->getFormat());
        $this->assertNull($this->net->address('0076.0315.0315.9000')->getFormat());
        $this->assertNull($this->net->address('0076.0315.0315.000')->getFormat());
        $this->assertNull($this->net->address('0076.315.0315.0000')->getFormat());

        // Hexademical addresses
        $this->assertEquals('hex', $this->net->address('0x3ECDCD02')->getFormat());
        $this->assertEquals('hex', $this->net->address('0x00000000')->getFormat());
        $this->assertEquals('hex', $this->net->address('0x3E.0xCD.0xCD.0x02')->getFormat());
        $this->assertNull($this->net->address('0x3ECDCD0')->getFormat());
        $this->assertNull($this->net->address('0x3ECDCD0z')->getFormat());
        $this->assertNull($this->net->address('0x3E.0xCD.0xCD.0x2')->getFormat());

        // Long addresses
        $this->assertEquals('long', $this->net->address('1053674754')->getFormat());
        $this->assertEquals('long', $this->net->address('23')->getFormat());
        $this->assertEquals('long', $this->net->address('0')->getFormat());
        $this->assertEquals('long', $this->net->address('-12345')->getFormat());
        $this->assertNull($this->net->address('-2147483649')->getFormat());
        $this->assertNull($this->net->address('-2147483650')->getFormat());
        $this->assertNull($this->net->address('4294967296')->getFormat());
        $this->assertNull($this->net->address('4294967300')->getFormat());

    }


    /**
     * IPv4 addresses validation
     */
    public function testValidation()
    {

        // Invalid addresses
        $this->assertFalse($this->net->address('62.205.205.2a')->isValid());
        $this->assertFalse($this->net->address('62.205.205.256')->isValid());
        $this->assertFalse($this->net->address('om.g.wt.f')->isValid());
        $this->assertFalse($this->net->address('0011111011001101110011010000000')->isValid());
        $this->assertFalse($this->net->address('0076031503150002')->isValid());
        $this->assertFalse($this->net->address('0076.0315.0315.9000')->isValid());
        $this->assertFalse($this->net->address('0076.0315.0315.000')->isValid());
        $this->assertFalse($this->net->address('0076.315.0315.0000')->isValid());
        $this->assertFalse($this->net->address('0x3ECDCD0')->isValid());
        $this->assertFalse($this->net->address('0x3ECDCD0z')->isValid());
        $this->assertFalse($this->net->address('0x3E.0xCD.0xCD.0x2')->isValid());
        $this->assertFalse($this->net->address('-2147483649')->isValid());
        $this->assertFalse($this->net->address('-2147483650')->isValid());
        $this->assertFalse($this->net->address('4294967296')->isValid());
        $this->assertFalse($this->net->address('4294967300')->isValid());

        // Valid addresses
        $this->assertTrue($this->net->address('62.205.205.2')->isValid());
        $this->assertTrue($this->net->address('10.0.0.0')->isValid());
        $this->assertTrue($this->net->address('192.168.10.1')->isValid());
        $this->assertTrue($this->net->address('255.255.255.255')->isValid());
        $this->assertTrue($this->net->address('0.0.0.0')->isValid());
        $this->assertTrue($this->net->address('0.0.0.1')->isValid());
        $this->assertTrue($this->net->address('00111110110011011100110100000010')->isValid());
        $this->assertTrue($this->net->address('0x3E.0xCD.0xCD.0x02')->isValid());
        $this->assertTrue($this->net->address('0076.0315.0315.0002')->isValid());
        $this->assertTrue($this->net->address('0x3ECDCD02')->isValid());
        $this->assertTrue($this->net->address('0x00000000')->isValid());
        $this->assertTrue($this->net->address('1053674754')->isValid());
        $this->assertTrue($this->net->address('23')->isValid());
        $this->assertTrue($this->net->address('0')->isValid());
        $this->assertTrue($this->net->address('-12345')->isValid());

    }


    /**
     * IPv4 addresses format converting
     */
    public function testFormatConverting()
    {

        // Invalid addresses
        $this->assertNull($this->net->address('62.205.205.2a')->convertTo('long'));
        $this->assertNull($this->net->address('0011111011001101110010100000010')->convertTo('dec'));
        $this->assertNull($this->net->address('om.g.wt.f')->convertTo('bin'));

        $this->assertEquals('3232235786', $this->net->address('192.168.1.10')->convertTo('long'));
        $this->assertEquals('192.168.1.10', $this->net->address('192.168.1.10')->convertTo('dec'));
        $this->assertEquals('192.168.1.10', $this->net->address('3232235786')->convertTo('dec'));
        $this->assertEquals('0xC0A8010A', $this->net->address('3232235786')->convertTo('hex'));
        $this->assertEquals('0xC0.0xA8.0x01.0x0A', $this->net->address('3232235786')->convertTo('hex', true));
        $this->assertEquals('0xC0.0xA8.0x01.0x0A', $this->net->address('0xC0A8010A')->convertTo('hex', true));
        $this->assertEquals('0xC0A8010A', $this->net->address('0xC0.0xA8.0x01.0x0A')->convertTo('hex'));
        $this->assertEquals('0300.0250.0001.0012', $this->net->address('3232235786')->convertTo('oct'));
        $this->assertEquals('0300.0250.0001.0012', $this->net->address('192.168.1.10')->convertTo('oct'));
        $this->assertEquals('0300.0250.0001.0012', $this->net->address('0xC0A8010A')->convertTo('oct'));
        $this->assertEquals('11000000101010000000000100001010', $this->net->address('0xC0A8010A')->convertTo('bin'));
        $this->assertEquals('11000000101010000000000100001010', $this->net->address('192.168.1.10')->convertTo('bin'));
        $this->assertEquals('11000000101010000000000100001010', $this->net->address('3232235786')->convertTo('bin'));

        // Signed and unsigned ints
        $this->assertEquals('200.117.248.17', $this->net->address('-931792879')->convertTo('dec'));
        $this->assertEquals('200.117.248.17', $this->net->address('3363174417')->convertTo('dec'));

    }

}
