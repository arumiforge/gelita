<?php

namespace App\Libraries;

use JsonSerializable;
use Stringable;

/**
 * Id acak satu request. Muncul sebagai string di JSON (request_id)
 * dan di log, sehingga guru cukup menyebut id ini saat melapor.
 */
final class RequestId implements JsonSerializable, Stringable
{
    private readonly string $value;

    public function __construct()
    {
        $this->value = bin2hex(random_bytes(6));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
