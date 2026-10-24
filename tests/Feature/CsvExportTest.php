<?php

namespace Tests\Feature;

use App\Support\Csv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    /** @return list<list<string>> */
    private function rows(string $url): array
    {
        $response = $this->actingAs($this->staff())->get($url)->assertOk();
        $body = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body, 'UTF-8 byte order mark so Excel reads names correctly');

        return array_map(fn ($line) => str_getcsv($line, ',', '"', ''), array_values(array_filter(preg_split('/\r?\n/', substr($body, 3)))));
    }

    public function test_exports_need_a_staff_sign_in(): void
    {
        $this->get('/staff/export/shipments.csv')->assertRedirect('/staff/login');
        $this->get('/staff/export/clients.csv')->assertRedirect('/staff/login');
    }

    public function test_the_download_has_the_right_headers_and_a_dated_file_name(): void
    {
        $response = $this->actingAs($this->staff())->get('/staff/export/shipments.csv')->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('/attachment; filename=shipments-\d{4}-\d{2}-\d{2}\.csv/', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->actingAs($this->staff())->get('/staff/export/clients.csv')->assertHeader('Content-Disposition');
    }

    public function test_shipments_csv_has_every_matching_row_not_just_one_page(): void
    {
        $client = $this->client(['full_name' => 'Tashi Wangmo', 'company' => 'Druk Motors', 'email' => 'tashi@example.com', 'phone' => '+97517975757']);
        for ($i = 1; $i <= 20; $i++) {
            $this->shipment($client, ['goods' => sprintf('Load %02d', $i)]);
        }
        $rows = $this->rows('/staff/export/shipments.csv');

        $this->assertSame(['Tracking number', 'Client', 'Company', 'Email', 'Phone', 'Direction', 'Method', 'Goods', 'Stage', 'Requested by', 'Created', 'Last updated', 'Notes on the request', 'Assigned to'], $rows[0]);
        $this->assertCount(21, $rows, '20 shipments although the list shows 15 a page');
        $this->assertSame('Load 20', $rows[1][7], 'newest first, like the list');
        $this->assertSame(['Tashi Wangmo', 'Druk Motors', 'tashi@example.com', '+97517975757', 'Import', 'Road freight'], array_slice($rows[1], 1, 6));
        $this->assertSame('Declaration Submitted', $rows[1][8]);
        $this->assertMatchesRegularExpression('/^TD-\d{4}-[A-Z0-9]{6}$/', $rows[1][0]);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $rows[1][10]);
    }

    public function test_the_export_follows_the_search_and_the_stage_of_the_list(): void
    {
        $tashi = $this->client(['full_name' => 'Tashi Wangmo']);
        $pema = $this->client(['full_name' => 'Pema Choden']);
        $this->shipment($tashi, ['goods' => 'Tashi open']);
        $this->shipment($tashi, ['goods' => 'Tashi done', 'status' => 'released']);
        $this->shipment($pema, ['goods' => 'Pema open']);

        $this->assertSame(['Tashi done', 'Tashi open'], array_column(array_slice($this->rows('/staff/export/shipments.csv?q=tashi'), 1), 7));
        $this->assertSame(['Tashi done'], array_column(array_slice($this->rows('/staff/export/shipments.csv?q=tashi&status=released'), 1), 7));
        $this->assertCount(1, $this->rows('/staff/export/shipments.csv?q=nothing-matches'), 'only the heading row');
        $this->assertCount(4, $this->rows('/staff/export/shipments.csv?status[]=x&q[]=y'), 'junk is ignored: everything');
    }

    public function test_text_that_looks_like_a_formula_is_defused_but_phone_numbers_are_left_alone(): void
    {
        $this->assertSame("'=HYPERLINK(\"http://x\")", Csv::cell('=HYPERLINK("http://x")'));
        $this->assertSame("'@SUM(A1)", Csv::cell('@SUM(A1)'));
        $this->assertSame("'-2+3", Csv::cell('-2+3'));
        $this->assertSame("'+cmd|' /C calc'!A0", Csv::cell("+cmd|' /C calc'!A0"));
        $this->assertSame('+97517975757', Csv::cell('+97517975757'));
        $this->assertSame('Plain text', Csv::cell('Plain text'));
        $this->assertSame('', Csv::cell(null));

        $client = $this->client(['full_name' => '=1+1', 'company' => '@evil', 'phone' => '+97517975757']);
        $this->shipment($client, ['goods' => '=cmd|calc']);
        $row = $this->rows('/staff/export/shipments.csv')[1];
        $this->assertSame("'=1+1", $row[1]);
        $this->assertSame("'@evil", $row[2]);
        $this->assertSame('+97517975757', $row[4]);
        $this->assertSame("'=cmd|calc", $row[7]);
    }

    public function test_commas_quotes_and_accents_survive(): void
    {
        $this->shipment($this->client(['full_name' => 'Tsheringma Dorji']), ['goods' => 'Rice, "Basmati" 25kg — བྱ་']);
        $this->assertSame('Rice, "Basmati" 25kg — བྱ་', $this->rows('/staff/export/shipments.csv')[1][7]);
    }

    public function test_clients_csv_lists_status_counts_and_no_internal_ids(): void
    {
        $linked = $this->client(['full_name' => 'Linked One', 'company' => 'Druk Motors', 'telegram_chat_id' => 987654321]);
        $off = $this->client(['full_name' => 'Off One', 'telegram_chat_id' => 123456789, 'notify_telegram' => false, 'notify_email' => false]);
        $none = $this->client(['full_name' => 'None One']);
        $this->shipment($linked);
        $this->shipment($linked, ['status' => 'released']);

        $body = $this->actingAs($this->staff())->get('/staff/export/clients.csv')->streamedContent();
        $this->assertStringNotContainsString('987654321', $body);
        $this->assertStringNotContainsString('123456789', $body);

        $rows = $this->rows('/staff/export/clients.csv');
        $this->assertSame(['Name', 'Company', 'Email', 'Phone', 'Telegram', 'Email updates', 'Registered', 'Registered by', 'Requests', 'In progress'], $rows[0]);
        $byName = collect(array_slice($rows, 1))->keyBy(0);
        $this->assertSame(['Druk Motors', $linked->email, $linked->phone, 'Connected', 'On'], array_slice($byName['Linked One'], 1, 5));
        $this->assertSame(['2', '1'], array_slice($byName['Linked One'], 8, 2));
        $this->assertSame(['Connected, off', 'Off'], array_slice($byName['Off One'], 4, 2));
        $this->assertSame(['Not connected', 'On', 'Staff', '0', '0'], [$byName['None One'][4], $byName['None One'][5], $byName['None One'][7], $byName['None One'][8], $byName['None One'][9]]);
    }

    public function test_the_clients_export_follows_the_search_and_the_telegram_filter(): void
    {
        $this->client(['full_name' => 'Linked Tashi', 'telegram_chat_id' => 555]);
        $this->client(['full_name' => 'Plain Tashi']);
        $this->client(['full_name' => 'Pema Choden']);

        $this->assertSame(['Plain Tashi', 'Linked Tashi'], array_column(array_slice($this->rows('/staff/export/clients.csv?q=tashi'), 1), 0));
        $this->assertSame(['Linked Tashi'], array_column(array_slice($this->rows('/staff/export/clients.csv?q=tashi&telegram=connected'), 1), 0));
        $this->assertSame(['Plain Tashi'], array_column(array_slice($this->rows('/staff/export/clients.csv?q=tashi&telegram=not'), 1), 0));
    }

    public function test_the_lists_offer_the_export_with_the_current_filter_and_hide_it_when_empty(): void
    {
        $this->shipment($this->client(['full_name' => 'Tashi Wangmo']));
        $this->actingAs($this->staff());

        $html = html_entity_decode($this->get('/staff?q=tashi&status=declaration_submitted')->assertOk()->assertSee('Export CSV')->getContent());
        $this->assertStringContainsString('/staff/export/shipments.csv?q=tashi&status=declaration_submitted', $html);
        $this->get('/staff?q=nomatch')->assertDontSee('Export CSV');

        $html = html_entity_decode($this->get('/staff/clients?q=tashi')->assertOk()->assertSee('Export CSV')->getContent());
        $this->assertStringContainsString('/staff/export/clients.csv?q=tashi', $html);
        $this->get('/staff/clients?q=nomatch')->assertDontSee('Export CSV');
    }

    public function test_regular_staff_can_export_too(): void
    {
        $this->shipment();
        $this->actingAs($this->staff())->get('/staff/export/shipments.csv')->assertOk();
    }
}
