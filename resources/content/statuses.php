<?php

// Clearance stages, in order (ported from src/lib/portal/statuses.ts). Keys are stored in shipments.status.

return [
    'statuses' => [
        ['key' => 'received', 'label' => 'Request received', 'clientText' => "We've received your request."],
        ['key' => 'documents_pending', 'label' => 'Documents pending', 'clientText' => "We're waiting for documents from you."],
        ['key' => 'documents_checked', 'label' => 'Documents checked', 'clientText' => 'Your documents have been checked.'],
        ['key' => 'declaration_lodged', 'label' => 'Declaration lodged', 'clientText' => 'Your declaration has been lodged with customs.'],
        ['key' => 'duties_due', 'label' => 'Duties to pay', 'clientText' => 'Duties and taxes are ready to be paid.'],
        ['key' => 'cleared', 'label' => 'Cleared', 'clientText' => 'Your goods have been cleared by customs.'],
        ['key' => 'delivered', 'label' => 'Delivered', 'clientText' => 'Delivered — this clearance is complete.'],
        ['key' => 'on_hold', 'label' => 'On hold', 'clientText' => 'On hold — we need something from you. Please contact us.'],
    ],
    'directions' => [
        ['value' => 'import', 'label' => 'Import'],
        ['value' => 'export', 'label' => 'Export'],
    ],
    'modes' => [
        ['value' => 'air', 'label' => 'Air freight'],
        ['value' => 'road', 'label' => 'Road freight'],
        ['value' => 'rail', 'label' => 'Rail freight'],
    ],
];
