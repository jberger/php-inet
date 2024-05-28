<?php

namespace PhpInet\Test;

use PhpInet\Inet;

use PHPUnit\Framework\TestCase;

class InetTest extends TestCase {

  public function testConstructor() {
    $inet = new Inet('192.168.0.1/32');
    $this->assertEquals('192.168.0.1', $inet);
  }
}
