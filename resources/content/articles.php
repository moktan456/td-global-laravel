<?php

// Blog articles (ported from src/content/articles.ts).

return [
    [
        'slug' => 'invoice-and-packing-list-must-match',
        'title' => 'Why your commercial invoice and packing list must match',
        'category' => 'Documentation',
        'excerpt' => 'Mismatched quantities, weights or descriptions are among the most common reasons a shipment is held for inspection.',
        'date' => '2026-09-01',
        'author' => '[Author name]',
        'image' => 'svcDocs',
        'body' => [
            [
                'paragraphs' => [
                    'Customs officers compare your documents against each other before they ever look at the goods. When the commercial invoice says one thing and the packing list says another, the shipment is likely to be queried, held or physically inspected — and storage charges keep running while it waits.',
                ],
            ],
            [
                'heading' => 'What to check before you ship',
                'paragraphs' => [
                    'Go through both documents line by line and confirm that these agree:',
                ],
                'list' => [
                    'Number of packages and the type of packaging',
                    'Gross and net weights',
                    'Item descriptions — specific enough to classify, not just “parts” or “samples”',
                    'Consignee, shipper and invoice numbers',
                    'Currency and Incoterms',
                ],
            ],
            [
                'heading' => 'Fixing a mismatch',
                'paragraphs' => [
                    'If you spot a discrepancy after the goods have left, tell your broker straight away. It is usually far quicker to correct a document before lodgement than to explain it once customs has raised a query.',
                ],
            ],
        ],
    ],
    [
        'slug' => 'hs-codes-explained',
        'title' => 'HS codes explained: getting tariff classification right',
        'category' => 'Compliance',
        'excerpt' => 'The Harmonized System code on your declaration decides the duty you pay — and whether any permits apply.',
        'date' => '2026-08-18',
        'author' => '[Author name]',
        'image' => 'svcImport',
        'body' => [
            [
                'paragraphs' => [
                    'The Harmonized System (HS) is an international product nomenclature maintained by the World Customs Organization. The first six digits are shared by participating countries; each country then adds its own digits for national tariff and statistical purposes.',
                ],
            ],
            [
                'heading' => 'Why the code matters',
                'paragraphs' => [
                    'The code determines the duty rate, whether import or export permits are needed, and whether a trade-agreement preference can apply. An incorrect code can mean paying too much duty — or too little, which can lead to penalties later.',
                ],
            ],
            [
                'heading' => 'Tips for classifying correctly',
                'paragraphs' => [
                    'Classification follows legal interpretation rules, not just product names. In practice:',
                ],
                'list' => [
                    'Describe what the product is made of, what it does and how it is used',
                    'Keep specification sheets and photos on file',
                    'Use the same code consistently for the same product',
                    'Ask your broker, or your customs authority, for a ruling when a product is unusual',
                ],
            ],
        ],
    ],
    [
        'slug' => 'avoid-storage-charges',
        'title' => 'Five ways to avoid dry port storage charges',
        'category' => 'Planning',
        'excerpt' => 'Most storage and detention costs come from delays that start with paperwork. Here is how to keep cargo moving.',
        'date' => '2026-08-04',
        'author' => '[Author name]',
        'image' => 'heroDock',
        'body' => [
            [
                'paragraphs' => [
                    'Dry ports and border warehouses typically allow a limited free-storage period, after which daily charges apply. Transporters may also charge detention when a truck is kept waiting beyond an agreed time. The good news: most of these costs are avoidable with a little preparation.',
                ],
                'list' => [
                    'Send your documents to your broker before the truck reaches the border or the flight arrives',
                    'Confirm permits and licences are in place well ahead of shipping',
                    'Pay duties and taxes promptly once they are assessed',
                    'Book collection or onward transport before release',
                    'Keep contact details for the consignee up to date so queries are answered fast',
                ],
            ],
        ],
    ],
];
