<?php

namespace App\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DateFormat {
    public function __construct(public string $format) {}
}