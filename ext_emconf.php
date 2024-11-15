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
            'typo3' => '11.5.0-12.4.99',
            'php' => '7.4.0-8.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
