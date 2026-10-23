<?php

// Clearance stages, in the order of the customs process. Keys are stored in shipments.status and shipment_events.status.
// (The first one is where a new shipment starts. Reorder here to change the order of the dropdown and the Track page example.)

return [
    'statuses' => [
        ['key' => 'declaration_submitted', 'label' => 'Declaration Submitted', 'clientText' => 'Your declaration has been submitted to customs.'],
        ['key' => 'pending_valuation', 'label' => 'Pending Valuation', 'clientText' => 'Customs is valuing your goods.'],
        ['key' => 'pending_risk', 'label' => 'Pending Risk', 'clientText' => 'Your declaration is with customs for risk assessment.'],
        ['key' => 'pending_physical_examination', 'label' => 'Pending Physical Examination', 'clientText' => 'Your goods are waiting for a physical examination by customs.'],
        ['key' => 'pending_payment', 'label' => 'Pending Payment', 'clientText' => 'Your duties and taxes are ready to be paid.'],
        ['key' => 'pending_release', 'label' => 'Pending Release', 'clientText' => "We're waiting for customs to release your goods."],
        ['key' => 'released', 'label' => 'Released', 'clientText' => 'Customs has released your goods.'],
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
