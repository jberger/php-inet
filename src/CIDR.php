<?php

namespace PhpInet;

class InvalidCIDRException extends \Exception {}
class InvalidCIDRActionException extends \Exception {}

class CIDR extends Inet {
  protected $alwaysDisplayMask = true;

  function __construct(...$args) {
    parent::__construct(...$args);
    // cidr does not allow bits right of the mask
    if(gmp_popcount($this->gmp & ~$this->_getMask()) > 0) {
      throw new InvalidCIDRException('Invalid CIDR: address has bits beyond the mask');
    }
  }

  public static function fromRange ($start, $end) {
    $start = Inet::fromString($start);
    $end = Inet::fromString($end);

    $diff = $start->gmp ^ $end->gmp;
    $maskLength = $start->_addrLength() - gmp_popcount($diff);
    return new static($start->gmp, $maskLength, $start->family);
  }

  public function toRange() {
    $start = new Inet($this->gmp, $this->_addrLength(), $this->family);
    $end = new Inet($this->gmp | ~$this->_getMask(), $this->_addrLength(), $this->family);
    return [$start, $end];
  }

  function split($requested = null) {
    if ($requested === null) {
      $requested = $this->maskLength + 1;
    }
    $requested = ltrim($requested, '/');
    if ($requested < $this->maskLength) {
      throw new InvalidCIDRActionException('Cannot split a CIDR outside its mask length');
    }
    if ($requested > $this->_addrLength()) {
      throw new InvalidCIDRActionException('Cannot split a CIDR beyond its address length');
    }
    $count = pow(2, $requested - $this->maskLength);
    $delta = gmp_pow(2, $this->_addrLength() - $requested);
    $subnets = [];
    for ($i = 0; $i < $count; $i++) {
      $subnets[] = new static($this->gmp + $i * $delta, $requested, $this->family);
    }
    return $subnets;
  }
}


