<?php

namespace PhpInet;

class InvalidInetException extends \Exception {}

class Inet {
  protected $family;
  protected $maskLength;
  protected $gmp;
  protected $alwaysDisplayMask = false;
  protected $invalidClass = InvalidInetException::class;

  public function __construct($input, $maskLength = null, $family = null) {
    if (is_string($input)) {
      $split = explode('/', $input, 2);
      $base = inet_pton($split[0]);

      if ($base === false) {
        throw new $this->invalidClass('Could not coerce ' . $split[0] . ' to address');
      }

      $this->gmp = gmp_import($base);
      $family = strlen($base) === 4 ? 4 : 6;

      if (isset($split[1])) {
        $maskLength = $split[1];
      }
    } else if ($input instanceof \GMP) {
      $this->gmp = $input;
    }

    if (!isset($this->gmp)) {
      throw new $this->invalidClass('Count not understand address');
    }

    $this->family = $family;
    if (empty($this->family)) {
      throw new $this->invalidClass('Could not construct object: could not determine address family');
    }

    if (empty($maskLength)) {
      $maskLength = $this->family === 4 ? 32 : 128; //TODO dedupe this code
    }
    $this->maskLength = $maskLength;
    if (empty($this->maskLength)) {
      throw new $this->invalidClass('Could not construct object: mask length not understood');
    }
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


