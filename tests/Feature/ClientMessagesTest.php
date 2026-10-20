<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Support\Messages;
use App\Support\Statuses;
use Tests\TestCase;

class ClientMessagesTest extends TestCase
{
    private function shipment(): Shipment
    {
        return new Shipment(['direction' => 'import', 'mode' => 'road', 'goods' => '2 pallets of auto parts & tyres']);
    }

    public function test_the_telegram_update_names_the_tracking_number_and_what_the_shipment_is(): void
    {
        $about = Messages::about($this->shipment());
        $this->assertSame('Import · Road freight · 2 pallets of auto parts & tyres', $about);

        $text = Messages::telegramStatusUpdate('Tashi', 'TD-2610-K7QX4M', Statuses::info('declaration_lodged'), null, $about);
        $this->assertStringContainsString('Update on your tracking number TD-2610-K7QX4M', $text);
        $this->assertStringContainsString('Shipment: Import · Road freight · 2 pallets of auto parts &amp; tyres', $text);   // HTML-escaped for Telegram
        $this->assertStringContainsString('New stage: <b>Declaration lodged</b>', $text);
    }

    public function test_the_tracking_number_message_and_the_emails_say_it_too(): void
    {
        $about = Messages::about($this->shipment());
        $this->assertStringContainsString('Your tracking number: <code>TD-2610-K7QX4M</code>', Messages::telegramTrackingNumber('Tashi', 'TD-2610-K7QX4M', $about));
        $this->assertStringContainsString("Shipment: {$about}", Messages::trackingNumberEmail('Tashi', 'TD-2610-K7QX4M', $about)['text']);
        $this->assertStringContainsString("Update on your tracking number TD-2610-K7QX4M ({$about})", Messages::statusUpdateEmail('Tashi', 'TD-2610-K7QX4M', 'cleared', null, $about)['text']);
    }

    public function test_messages_still_work_without_the_shipment_line(): void
    {
        $this->assertStringNotContainsString('Shipment:', Messages::telegramStatusUpdate('Tashi', 'TD-2610-K7QX4M', Statuses::info('cleared'), null));
    }
}
