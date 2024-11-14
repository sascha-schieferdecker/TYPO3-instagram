<?php
declare(strict_types=1);

return [
    'instagram:importfeed' => [
        'class' => \SaschaSchieferdecker\Instagram\Command\ImportFeedCommand::class,
        'schedulable' => true
    ],
];
