<?php

namespace Geekbrains\Application1\Domain\Models;

class Phone
{
    private string $phone;

    public function __construct()
    {
        $this->phone = '+71122111111';
    }

    public function getPhone() {
        return $this->phone;
    }
}