<?php

namespace Tests\Unit;

use App\Support\TrackingNumber;
use PHPUnit\Framework\TestCase;

class TrackingNumberTest extends TestCase
{
    public function test_normalizes_and_validates_old_and_new_numbers(): void
    {
        $this->assertSame('TD-2610-K7QX4M', TrackingNumber::normalize('td2610k7qx4m'));
        $this->assertSame('TD-2610-UPZ4', TrackingNumber::normalize(' td-2610-upz4 '));
        $this->assertTrue(TrackingNumber::isValid('TD-2610-UPZ4'));       // issued before numbers got 6 characters
        $this->assertTrue(TrackingNumber::isValid('td2610k7qx4m'));
        $this->assertFalse(TrackingNumber::isValid('TD-2610-K7QX4'));     // 5 characters
        $this->assertFalse(TrackingNumber::isValid('TD-2610-K7OX4M'));    // O is not in the alphabet
        $this->assertFalse(TrackingNumber::isValid('hello'));
    }

    public function test_normalizes_bhutan_phone_numbers(): void
    {
        $this->assertSame('+97517975757', TrackingNumber::normalizePhone('17975757'));
        $this->assertSame('+97517975757', TrackingNumber::normalizePhone('+975 17975757'));
        $this->assertSame('+97517975757', TrackingNumber::normalizePhone('975-17975757'));
        $this->assertSame('', TrackingNumber::normalizePhone(''));
    }
}
