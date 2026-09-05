<?php

return [
    'basketball-brick' => [
        'title' => 'Basketball on brick',
        'document' => [
            'schemaVersion' => 1,
            'catalogueVersion' => 'workshop-1',
            'instances' => [
                ['id' => 'ball', 'partKey' => 'basketball', 'xMm' => 0, 'yMm' => 3000, 'rotationMilliDegrees' => 0],
                ['id' => 'platform', 'partKey' => 'platform-brick', 'xMm' => 0, 'yMm' => 0, 'rotationMilliDegrees' => 0],
            ],
            'lockedInstanceIds' => [],
            'inventory' => [],
            'goal' => null,
        ],
    ],
    'starter' => [
        'title' => 'Workshop sandbox',
        'document' => [
            'schemaVersion' => 1,
            'catalogueVersion' => 'workshop-1',
            'instances' => [],
            'lockedInstanceIds' => [],
            'inventory' => [],
            'goal' => null,
        ],
    ],
];
