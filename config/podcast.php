<?php

return [
    'author' => env('PODCAST_AUTHOR', 'Jeffrey & Cassie Davidson'),
    'owner_name' => env('PODCAST_OWNER_NAME', 'Jeffrey Davidson'),
    'owner_email' => env('PODCAST_OWNER_EMAIL', env('MAIL_FROM_ADDRESS')),
];
