<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** CSV downloads for the staff lists: streamed (large lists are fine), UTF-8 with a BOM so Excel shows names correctly. */
class Csv
{
    /**
     * A spreadsheet runs a cell that starts with = + - @ as a formula, so text that does is prefixed with an apostrophe
     * (what a client types in a name or the goods must never become a formula on a staff member's computer). A plain
     * phone number such as +97517975757 has nothing to run and stays as it is.
     */
    public static function cell(mixed $value): string
    {
        $text = (string) ($value ?? '');
        if ($text !== '' && preg_match('/^[=+\-@\t\r]/', $text) && ! preg_match('/^\+?\d+$/', $text)) {
            return "'".$text;
        }

        return $text;
    }

    /**
     * @param  list<string>  $headings
     * @param  callable(callable(list<mixed>): void): void  $rows  receives a function that writes one row
     */
    public static function download(string $name, array $headings, callable $rows): StreamedResponse
    {
        $file = $name.'-'.now('Asia/Thimphu')->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = fn (array $row) => fputcsv($out, array_map(self::cell(...), $row), ',', '"', '');
            $write($headings);
            $rows($write);
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
