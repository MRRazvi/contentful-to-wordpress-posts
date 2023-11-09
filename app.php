<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../wp-load.php';

// load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// fields mapping
$fields = [
    'post_title'        => 'title',
    'post_name'         => 'slug',
    'post_excerpt'      => 'description',
    'post_content'      => 'contentBlock1',
    'post_thumbnail'    => 'headerImage',
    'post_tags'         => 'keywords',
    'post_status'       => 'publish',
    'post_type'         => 'post',
];

// get all the posts from contentful