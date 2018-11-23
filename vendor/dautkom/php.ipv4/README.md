# IPv4 PHP library

Library supports ip addresses and subnet masks in the following standard formats:

* Dotted Decimal (e.g. '192.168.1.10')
* Dotted Octal (e.g. '0300.0250.0001.0012')
* Undotted Hexadecimal (e.g. '0xC0A8010A')
* Dotted Hexadecimal (e.g. '0xC0.0xA8.0x01.0x0A')
* Long (e.g. '3232235786')
* Binary (e.g. '11000000101010000000000100001010')

For subnets CIDR notation is also supported:

* Decimal (e.g. '24')
* Slashed decimal string (e.g. '/24')

## Requirements

* PHP 7.0+
* GMP extension

## License

Copyright (c) 2013-2016 Oļegs Čapligins and respective contributors under the MIT License.

# Usage

Examples below cover general usage. Consider including necessary classes and dependencies.
Also a lot of usage examples are available in unit tests.

## Address handling

    <?php
    
    $net = new \dautkom\ipv4\IPv4(); 
    
    // Format handling
    $s = $net->address('62.205.205.2')->getFormat();
    $s = $net->address('00111110110011011100110100000010')->getFormat();
    $s = $net->address('0076.0315.0315.0002')->getFormat();
    $s = $net->address('0x3ECDCD02')->getFormat();
    $s = $net->address('0x3E.0xCD.0xCD.0x02')->getFormat();
    $s = $net->address('1053674754')->getFormat();
    $s = $net->address('-12345')->getFormat();
    
    // Or getFormat() can be replaced with isValid() for validation
    $t = $net->address('62.205.205.2')->isValid();
    $t = $net->address('0x3ECDCD02')->isValid();
    
    // Convert address to another format
    $s = $net->address('192.168.1.10')->convertTo('long');
    $s = $net->address('0xC0A8010A')->convertTo('bin');
    $s = $net->address('3363174417')->convertTo('dec');
    $s = $net->address('-931792879')->convertTo('dec');
    $s = $net->address('3232235786')->convertTo('oct');
    $s = $net->address('0300.0250.0001.0012')->convertTo('hex', true);
    $s = $net->address('0300.0250.0001.0012')->convertTo('hex');

## Subnet handling

There're several ways to work with subnets and subnet masks, combining with address or separately. In examples below return types are shortcoded in variables: $s for string, $a for array, $n for null, $t and $f for boolean true and false respectively.

### CIDR notation

    <?php
    
    $net = new \dautkom\ipv4\IPv4();
    
    // Valid notation
    $t = $net->subnet('62.205.205.0/24')->isValid();
    $t = $net->subnet('10.0.0.1/32')->isValid();
        
    // Invalid examples
    $f = $net->subnet('62.205.205.1/24')->isValid(); # wrong subnet address
    $f = $net->subnet('62.205.205.1')->isValid();    # missing cidr mask
    $f = $net->subnet('0x3ECDCD00/24')->isValid();   # wrong format
    
    // Retrieve subnet address
    $s = $net->subnet('62.205.205.0/24')->getSubnetAddress();
    
    // Retrieve mask in CIDR format without and with slash
    $s = $net->subnet('62.205.205.0/24')->getMask();     # 24
    $s = $net->subnet('62.205.205.0/24')->getMask(true); # /24
    
    // Retrieve subnet range
    $a = $net->subnet('62.205.205.0/25')->getRange();
    
    // Retrieve subnet broadcast address
    $s = $net->subnet('62.205.205.0/24')->getBroadcast();
    
    // Count hosts in subnet without broadcast address
    // 1 host (e.g. for gateway) can be excluding by optional argument
    $s = $net->subnet('62.205.205.0/24')->countHosts());     # 254
    $s = $net->subnet('62.205.205.0/24')->countHosts(true)); # 253
    
    // Check if IP address belongs to given subnet
    $t = $net->subnet('192.168.0.0/24')->has('192.168.0.2'); # yes
    $f = $net->subnet('10.1.2.0/25')->has('10.1.2.200');     # no

### Handling subnet mask in multiple formats

    <?php
    
    $net = new \dautkom\ipv4\IPv4();
    
    // Format handling
    $s = $net->mask('255.255.0.0')->getFormat();
    $s = $net->mask('11111111111111111111111100000000')->getFormat();
    $s = $net->mask('0377.0377.0377.0000')->getFormat();
    $s = $net->mask('/23')->getFormat();
    $s = $net->mask('24')->getFormat();
    $s = $net->mask(25)->getFormat();
    
    // Or getFormat() can be replaced with isValid() for validation
    $t = $net->mask('0xFF000000')->isValid();
    
    // Convert to different notation
    $s = $net->mask('/24')->convertTo('dec');
    $s = $net->mask('24')->convertTo('long');
    $s = $net->mask(24)->convertTo('hex');
    $s = $net->mask('255.255.255.0')->convertTo('bin');
    $s = $net->mask('0xFFFF0000')->convertTo('hex', true);
    $s = $net->mask('11111111111111111111111100000000')->convertTo('cidr');
    
    // Valid ip-mask combinations
    $t = $net->address('62.205.205.2')->mask('/24')->isValid();
    $t = $net->address('0x3ECDCD00')->mask('255.255.255.0')->isValid();
    
    // Strict check (only valid subnet passes)
    // Valid
    $t = $net->address('62.205.205.0')->mask(24)->isValid(true);
    // Invalid, too wide mask for given subnet address
    $f = $net->address('62.205.205.128')->mask('/23')->isValid(true);
    // Valid, it's a single host
    $t = $net->address('62.205.205.2')->mask('32')->isValid(true);
    // Invalid, not a valid subnet address
    $f = $net->address('62.205.205.2')->mask('/30')->isValid(true);
    // Valid, now it works
    $t = $net->address('62.205.205.4')->mask('/30')->isValid(true)
    
    // Retrieve subnet address from IP address and mask
    $s = $net->address('62.205.205.2')->mask('255.255.255.0')->getSubnetAddress();
    
    // Retrieve subnet range from given network
    $a = $net->address('62.205.205.0')->mask('/25')->getRange();
    
    // Count hosts in subnet without broadcast address
    // 1 host (e.g. for gateway) can be excluding by optional argument
    $s = $net->address('62.205.205.0')->mask('0xFFFFFF00')->countHosts();     # 254
    $s = $net->address('62.205.205.0')->mask('0xFFFFFF00')->countHosts(true); # 253
    
    // Retrieve subnet broadcast address
    $s = $net->address('62.205.205.2')->mask('0xFFFFFF00')->getBroadcast();
    
    // Check if given arguments represent a valid subnet
    $t = $net->address('192.168.0.0')->mask('/24')->isSubnet();
    $t = $net->address('192.168.0.1')->mask('255.255.255.255')->isSubnet();
    $f = $net->address('192.168.0.1')->mask('255.255.255.0')->isSubnet();
    $f = $net->address('192.168.250.0')->mask('/20')->isSubnet(); # too wide mask
    
    // Check if IP address belongs to given subnet
    $t = $net->address('192.168.0.0')->mask('24')->has('192.168.0.12');
    $f = $net->address('10.1.2.0')->mask('255.255.255.128')->has('192.168.0.255');
