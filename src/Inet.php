<?php

namespace PhpInet;

class Inet {
  protected $family;
  protected $maskLength;
  protected $gmp;
  protected $alwaysDisplayMask = false;

  public function __construct($gmp, $maskLength, $family) {
    $this->gmp = $gmp;
    $this->maskLength = $maskLength;
    $this->family = $family;
  }

  public static function fromString ($string) {
    $split = explode('/', $string, 2);
    $base = inet_pton($split[0]);
    $family = strlen($base) === 4 ? 4 : 6;
    $gmp = gmp_import($base);
    if (isset($split[1])) {
      $maskLength = $split[1];
    } else {
      $maskLength = $family === 4 ? 32 : 128; //TODO dedupe this code
    }
    return new static($gmp, $maskLength, $family);
  }

  public function __toString() {
    $mask = $this->alwaysDisplayMask || $this->maskLength < $this->_addrLength() ? '/' . $this->maskLength : '';
    return inet_ntop(gmp_export($this->gmp)) . $mask;
  }

  protected function _addrLength() { return $this->family === 4 ? 32 : 128; }

  protected function _getMask($length = null) {
    if ($length === null) { $length = $this->maskLength; }
    $mask = str_pad(str_repeat('1', $length), $this->_addrLength(), '0');
    return gmp_init($mask, 2);
  }
}


