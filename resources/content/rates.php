<?php

// Sample rates for the cost calculator (ported from src/content/rates.ts).

return [
    'base' => [
        'standard' => 2000,
        'complex' => 4000,
    ],
    'includedLines' => 3,
    'perExtraLine' => 200,
    'mode' => [
        'air' => 600,
        'road' => 400,
        'rail' => 800,
    ],
    'exportDiscount' => 0.1,
];
