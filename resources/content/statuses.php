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
    // Transport stages (optional per shipment), in the order of the journey. Keys are stored in shipments.transport_status
    // and shipment_events.status (with track = 'transport'). They must stay different from the customs keys above.
    'transport' => [
        ['key' => 'order_placed', 'label' => 'Order Placed', 'clientText' => 'Your order is registered.'],
        ['key' => 'processing', 'label' => 'Processing', 'clientText' => "We're preparing your shipment."],
        ['key' => 'ready_for_pickup', 'label' => 'Ready for Pickup', 'clientText' => 'Your goods are packed and waiting for the carrier.'],
        ['key' => 'in_transit', 'label' => 'In Transit', 'clientText' => 'Your shipment is on its way.'],
        ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'clientText' => 'Your shipment is out for delivery.'],
        ['key' => 'delivered', 'label' => 'Delivered', 'clientText' => 'Your shipment has been delivered.'],
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
