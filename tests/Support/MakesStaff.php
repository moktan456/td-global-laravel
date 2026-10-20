<?php

namespace Tests\Support;

use App\Models\Client;
use App\Models\Shipment;
use App\Models\User;

trait MakesStaff
{
    protected function staff(array $attrs = []): User
    {
        static $n = 0;
        $n++;

        return User::create($attrs + [
            'name' => "Staff {$n}", 'username' => "staff{$n}", 'email' => "staff{$n}@staff.td-global.invalid",
            'password' => 'a-long-password-1', 'role' => 'staff', 'active' => true, 'must_change_password' => false,
        ]);
    }

    protected function admin(array $attrs = []): User
    {
        return $this->staff($attrs + ['role' => 'admin']);
    }

    protected function client(array $attrs = []): Client
    {
        static $n = 0;
        $n++;

        return Client::create($attrs + [
            'full_name' => "Client {$n}", 'email' => "client{$n}@example.com", 'phone' => '+9751700'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
        ]);
    }

    protected function shipment(?Client $client = null, array $attrs = []): Shipment
    {
        return Shipment::create($attrs + [
            'client_id' => ($client ?? $this->client())->id, 'direction' => 'import', 'mode' => 'road', 'goods' => 'test goods',
        ]);
    }
}
