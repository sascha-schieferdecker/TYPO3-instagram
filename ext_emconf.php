<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'instagram',
    'description' => 'Show images and posts from instagram profiles',
    'category' => 'plugin',
    'version' => '9.0.0',
    'author' => 'Sascha Schieferdecker',
    'author_email' => 'apps@sascha-schieferdecker.de',
    'author_company' => 'Sascha Schieferdecker',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'php' => '8.1.0-8.3.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
