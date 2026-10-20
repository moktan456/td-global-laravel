<?php

// The nine services (ported from src/content/site.ts).

return [
    [
        'slug' => 'import-clearance',
        'title' => 'Import clearance',
        'summary' => 'Your inbound cargo, declared correctly and released on time.',
        'body' => [
            'We prepare and lodge your import declaration, calculate duties and taxes, and liaise with customs until your goods are released.',
            'Because we check your documents before your truck reaches the border or your flight lands, problems are fixed while there is still time — not while your goods sit at the dry port accruing storage.',
        ],
        'includes' => [
            'Pre-arrival document check',
            'Customs entry and duty calculation',
            'Liaison with customs, the dry port and your transporter',
            'Release and delivery coordination',
        ],
        'metaTitle' => 'Import Clearance in Gelephu, Bhutan',
        'metaDescription' => 'Import customs clearance in Gelephu and GMC: declaration, duty and tax calculation, document checks and release, handled by a local clearing agent.',
        'pageTitle' => 'Import clearance in Gelephu, GMC',
        'problems' => [
            [
                'problem' => 'Documents that don’t match',
                'fix' => 'We check your commercial invoice, packing list and transport document against each other before anything is lodged.',
            ],
            [
                'problem' => 'Goods held at the border or dry port',
                'fix' => 'We review your documents before your truck reaches the border or your flight lands, so problems are fixed while there is still time.',
            ],
            [
                'problem' => 'Storage charges building up',
                'fix' => 'Early lodging and clear follow-up with customs and your transporter help your goods leave the dry port sooner.',
            ],
            [
                'problem' => 'Paying the wrong duty',
                'fix' => 'We calculate duties and taxes and confirm the tariff code before you pay.',
            ],
        ],
        'image' => 'svcImport',
        'icon' => 'import',
    ],
    [
        'slug' => 'export-clearance',
        'title' => 'Export clearance',
        'summary' => 'Outbound shipments filed and cleared ahead of the cut-off.',
        'body' => [
            'We file export declarations, arrange the permits and certificates your destination requires, and make sure everything is lodged before the carrier\'s cut-off.',
        ],
        'includes' => [
            'Export declaration filing',
            'Permits and certificates of origin',
            'Cut-off monitoring',
            'Proof of export for your records',
        ],
        'metaTitle' => 'Export Clearance in Gelephu, Bhutan',
        'metaDescription' => 'Export customs clearance in Gelephu and GMC: export declarations, permits and certificates of origin, filed ahead of the carrier’s cut-off.',
        'pageTitle' => 'Export clearance in Gelephu, GMC',
        'problems' => [
            [
                'problem' => 'Missing the carrier’s cut-off',
                'fix' => 'We watch the cut-off and lodge your export declaration ahead of it.',
            ],
            [
                'problem' => 'Permits or certificates not ready',
                'fix' => 'We arrange the permits and certificates of origin your destination requires before the goods move.',
            ],
            [
                'problem' => 'No proof of export on file',
                'fix' => 'We keep proof of export for your records, ready for audits and buyers.',
            ],
        ],
        'image' => 'svcExport',
        'icon' => 'export',
    ],
    [
        'slug' => 'clearing-and-forwarding',
        'title' => 'Clearing & forwarding (CFA)',
        'summary' => 'Customs clearance and onward forwarding, handled together by one team.',
        'body' => [
            'A clearing and forwarding agent (CFA) clears your goods through customs and then forwards them to their destination. With us both steps are handled by one team, from the declaration to the delivery of your goods.',
            'That gives you one point of contact for your documents, the customs release and the onward movement, instead of several parties passing your cargo between them.',
        ],
        'includes' => [
            'Customs declaration and clearance',
            'Duty and tax calculation',
            'Onward forwarding to your destination',
            'Coordination with your transporter and the dry port',
            'Status updates until delivery',
        ],
        'metaTitle' => 'Clearing & Forwarding Agent (CFA) in Gelephu',
        'metaDescription' => 'Clearing and forwarding agent (CFA) in Gelephu and GMC: customs clearance and onward delivery handled together by one team.',
        'pageTitle' => 'Clearing & forwarding agent (CFA) in Gelephu',
        'problems' => [
            [
                'problem' => 'Several parties and no single owner',
                'fix' => 'One team follows the shipment from customs release to delivery, so you always know who to call.',
            ],
            [
                'problem' => 'A gap between clearance and delivery',
                'fix' => 'We plan the onward movement while your goods are being cleared, so transport is ready when they are released.',
            ],
            [
                'problem' => 'Paperwork that doesn’t follow the goods',
                'fix' => 'The same checked set of documents is used for clearance and for delivery.',
            ],
        ],
        'image' => 'heroRoad',
        'icon' => 'forwarding',
    ],
    [
        'slug' => 'transport-arrangement',
        'title' => 'Transport arrangement',
        'summary' => 'Transport arranged for your goods, before and after clearance.',
        'body' => [
            'We arrange the transport for your goods, from the border or dry port to your door, or from your premises to the border for export. You give us the pick-up and delivery details and we organise the rest.',
            'Because the same team handles the clearance, transport is booked to match the expected release instead of waiting on it.',
        ],
        'includes' => [
            'Transport booking for imports and exports',
            'Pick-up and delivery scheduling',
            'Coordination with the dry port and customs',
            'Delivery updates until the goods arrive',
        ],
        'metaTitle' => 'Transport Arrangement in Gelephu, Bhutan',
        'metaDescription' => 'Transport for imports and exports arranged from Gelephu and GMC: pick-up, delivery and coordination with customs and the dry port.',
        'pageTitle' => 'Transport arrangement in Gelephu',
        'problems' => [
            [
                'problem' => 'Goods cleared but no transport ready',
                'fix' => 'We book transport to match the expected release, so goods are not left waiting.',
            ],
            [
                'problem' => 'Unclear pick-up and delivery details',
                'fix' => 'We confirm addresses, contacts and timings with you before the transport is arranged.',
            ],
            [
                'problem' => 'Not knowing where your goods are',
                'fix' => 'We keep you updated from pick-up to delivery.',
            ],
        ],
        'image' => 'aboutTruck',
        'icon' => 'transport',
    ],
    [
        'slug' => 'warehousing-storage',
        'title' => 'Warehousing & storage',
        'summary' => 'Storage for your goods before or after clearance.',
        'body' => [
            'We store your goods before or after clearance, so you don’t have to rush delivery on the day they are released.',
            'Ask us about the space available, how long you can store your goods and the charges, and we will confirm what suits your consignment.',
        ],
        'includes' => [
            'Storage of goods before or after clearance',
            'Coordination with clearance and delivery',
            'Advice on space, duration and charges',
        ],
        'metaTitle' => 'Warehousing & Storage in Gelephu, Bhutan',
        'metaDescription' => 'Storage for your goods before or after customs clearance in Gelephu and GMC, coordinated with clearance and delivery by one team.',
        'pageTitle' => 'Warehousing & storage in Gelephu',
        'problems' => [
            [
                'problem' => 'Nowhere to hold goods between clearance and delivery',
                'fix' => 'We can store them until you are ready to receive them.',
            ],
            [
                'problem' => 'Storage charges that come as a surprise',
                'fix' => 'We confirm the space, duration and charges with you up front.',
            ],
        ],
        'image' => 'heroDock',
        'icon' => 'storage',
    ],
    [
        'slug' => 'documentation-compliance',
        'title' => 'Documentation & compliance',
        'summary' => 'Invoices, packing lists and permits that match — first time.',
        'body' => [
            'Most clearance delays start with paperwork. We review your commercial invoice, packing list, airway bill or truck consignment note, and permits so every figure and description agrees before submission.',
        ],
        'includes' => [
            'Document review and correction',
            'Permit and licence applications',
            'Record keeping for audits',
            'Regulatory change updates',
        ],
        'metaTitle' => 'Customs Documentation in Gelephu, Bhutan',
        'metaDescription' => 'Customs documentation and compliance in Gelephu and GMC: invoice and packing list checks, permit and licence applications, and record keeping.',
        'pageTitle' => 'Customs documentation & compliance in Gelephu',
        'problems' => [
            [
                'problem' => 'Figures that disagree between documents',
                'fix' => 'We compare quantities, weights and descriptions across your invoice, packing list and transport document and correct them before submission.',
            ],
            [
                'problem' => 'Permit or licence applied for too late',
                'fix' => 'We handle permit and licence applications so they are in place before your cargo arrives.',
            ],
            [
                'problem' => 'Records missing when an audit comes',
                'fix' => 'We keep a record of every document we submit for you.',
            ],
            [
                'problem' => 'Rules that change without notice',
                'fix' => 'We keep track of regulatory changes so your filings stay compliant.',
            ],
        ],
        'image' => 'svcDocs',
        'icon' => 'documents',
    ],
    [
        'slug' => 'tariff-classification',
        'title' => 'Tariff classification & duty advice',
        'summary' => 'The right HS code, so you pay the right duty.',
        'body' => [
            'We classify your goods under the correct tariff (HS) codes and explain the duty, tax and any trade-agreement preferences that apply — before you commit to a shipment.',
        ],
        'includes' => [
            'HS code classification',
            'Duty and tax estimates',
            'Trade-agreement preference checks',
            'Classification rulings support',
        ],
        'metaTitle' => 'HS Code Classification in Gelephu, Bhutan',
        'metaDescription' => 'HS code classification and duty advice in Gelephu and GMC: the right tariff code and a duty estimate before you commit to a shipment.',
        'pageTitle' => 'HS code classification & duty advice in Gelephu',
        'problems' => [
            [
                'problem' => 'The wrong HS code',
                'fix' => 'We classify every item under the correct tariff heading, so you pay the duty that actually applies.',
            ],
            [
                'problem' => 'Not knowing the duty until the goods arrive',
                'fix' => 'We give you a duty and tax estimate before you commit to a shipment.',
            ],
            [
                'problem' => 'Trade-agreement savings left unclaimed',
                'fix' => 'We check whether a trade-agreement preference applies to your goods.',
            ],
        ],
        'image' => 'svcTariff',
        'icon' => 'tariff',
    ],
    [
        'slug' => 'bafra-clearance',
        'title' => 'Food & agriculture (BAFRA) clearance',
        'summary' => 'Permits and clearance for food, plant and animal products.',
        'body' => [
            'Food, plants, animals and other agricultural goods are regulated by the Bhutan Agriculture and Food Regulatory Authority (BAFRA). We prepare the documents and applications and coordinate with BAFRA and customs, so your consignment can be cleared without being held at the border.',
            'Send us the product details and supplier documents before you order or ship, and we will tell you what is needed.',
        ],
        'includes' => [
            'Import permit applications',
            'Document preparation and checks',
            'Coordination with BAFRA and customs',
            'Coordination of inspections',
            'Release and delivery coordination',
        ],
        'metaTitle' => 'BAFRA & Food Import Clearance in Gelephu',
        'metaDescription' => 'BAFRA clearance for food, plant and animal imports in Gelephu and GMC: permit applications, documents and coordination with BAFRA and customs.',
        'pageTitle' => 'Food & agriculture (BAFRA) clearance in Gelephu',
        'problems' => [
            [
                'problem' => 'Not knowing what a product needs',
                'fix' => 'We check the requirements for your product before you ship.',
            ],
            [
                'problem' => 'Goods held for a missing permit',
                'fix' => 'We apply for the permits in advance and check your documents before the goods arrive.',
            ],
            [
                'problem' => 'Delays between BAFRA and customs',
                'fix' => 'We coordinate both, so one team follows the clearance through from start to release.',
            ],
            [
                'problem' => 'Perishable goods waiting at the border',
                'fix' => 'Early lodging and close follow-up help keep perishable goods moving.',
            ],
        ],
        'image' => 'docsReview',
        'icon' => 'food',
    ],
    [
        'slug' => 'third-country-transit',
        'title' => 'Third-country transit via India',
        'summary' => 'Documents and clearance for goods moving through India to or from other countries.',
        'body' => [
            'Cargo to or from countries other than India usually moves through Indian territory in transit. We prepare the customs documents for the transit leg and handle the clearance on the Bhutan side, so your goods keep moving from one border to the next.',
            'Tell us the origin or destination country and the route, and we will confirm the documents needed before your cargo leaves.',
        ],
        'includes' => [
            'Transit document preparation',
            'Bhutan-side import or export clearance',
            'Document checks before the cargo leaves',
            'Coordination with customs and your transporter',
            'Release and delivery coordination',
        ],
        'metaTitle' => 'Third-Country Transit via India, Bhutan',
        'metaDescription' => 'Clearance and documents for third-country transit cargo moving through India to or from Bhutan, handled from our office in Gelephu, GMC.',
        'pageTitle' => 'Third-country transit via India, from Gelephu',
        'problems' => [
            [
                'problem' => 'An unclear route or paperwork for the transit leg',
                'fix' => 'We confirm the route and the documents needed before your cargo leaves.',
            ],
            [
                'problem' => 'Documents that don’t agree from one stage to the next',
                'fix' => 'We check the invoice, packing list and transport documents against each other so they match at every stage.',
            ],
            [
                'problem' => 'Waiting at the border',
                'fix' => 'Early lodging and close liaison with customs and your transporter help keep the cargo moving.',
            ],
        ],
        'image' => 'aboutMountainRoad',
        'icon' => 'transit',
    ],
];
