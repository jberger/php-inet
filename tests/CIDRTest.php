<?php

namespace PhpInet\Test;

use PhpInet\{Inet, CIDR};

use PHPUnit\Framework\TestCase;
use Jchook\AssertThrows\AssertThrows;

class CIDRTest extends TestCase {
  use AssertThrows;

  public function testFromRange() {
    $this->assertEquals(new CIDR('10.0.0.0/8'), CIDR::fromRange('10.0.0.0', '10.255.255.255'));
    $this->assertEquals(new CIDR('192.168.0.0/24'), CIDR::fromRange('192.168.0.0', '192.168.0.255'));
  }

  public function testToRange() {
    $this->assertEquals([new Inet('10.0.0.0'), new Inet('10.255.255.255')], (new CIDR('10.0.0.0/8'))->toRange());
    $this->assertEquals([new Inet('192.168.0.0'), new Inet('192.168.0.255')], (new CIDR('192.168.0.0/24'))->toRange());
  }

  public function testSplit() {
    $cidr = new CIDR('10.20.0.0/22');
    $this->assertEquals([new CIDR('10.20.0.0/23'), new CIDR('10.20.2.0/23')], $cidr->split());
    $this->assertEquals([new CIDR('10.20.0.0/24'), new CIDR('10.20.1.0/24'), new CIDR('10.20.2.0/24'), new CIDR('10.20.3.0/24')], $cidr->split('/24'));
    $this->assertThrows(
      \PhpInet\InvalidCIDRActionException::class,
      function () use ($cidr) { $cidr->split('/33'); },
      function ($e) { $this->assertEquals('Cannot split a CIDR beyond its address length', $e->getMessage()); },
    );
    $this->assertThrows(
      \PhpInet\InvalidCIDRActionException::class,
      function () use ($cidr) { $cidr->split('/21'); },
      function ($e) { $this->assertEquals('Cannot split a CIDR outside its mask length', $e->getMessage()); },
    );
  }

  public function testValidateCIDR() {
    $this->assertThrows(
      \PhpInet\InvalidCIDRException::class,
      function () { new CIDR('10.1.0.0/8'); },
      function ($e) { $this->assertEquals('Invalid CIDR: address has bits beyond the mask', $e->getMessage()); }
    );
  }
}
