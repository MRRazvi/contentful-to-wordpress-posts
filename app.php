<?php

require_once __DIR__ . '/vendor/autoload.php';

// load wordpress
define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';

// load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// load configuration
require_once __DIR__ . '/config/general.php';
require_once __DIR__ . '/config/fields.php';

// load helper functions
require_once __DIR__ . '/helpers/functions.php';

// get all the posts from contentful
$response = wp_remote_get(sprintf(
        'https://cdn.contentful.com/spaces/%s/entries?content_type=%s&limit=%s',
        $_ENV['CONTENTFUL_SPACE_ID'],
        $fields['post_type'],
        $page_size,
    ), [
        'headers' => [
            'Authorization' => sprintf('Bearer %s', $_ENV['CONTENTFUL_ACCESS_TOKEN'])
        ],
    ]);

$body = wp_remote_retrieve_body($response);
$data = json_decode($body, true);

// loop through each item
foreach($data['items'] as $item) {
    $post_data = [
        'post_type'     => 'post',
        'post_status'   => 'publish',
        'post_date'     => $item['sys']['createdAt'],
        'guid'          => $item['sys']['id'],
        'post_title'    => $item['fields'][$fields['post_title']],
        'post_name'     => $item['fields'][$fields['post_name']] ?? '',
        'post_excerpt'  => $item['fields'][$fields['post_excerpt']] ?? '',
        'post_content'  => contentful_rich_text_to_html($item['fields'][$fields['post_content']]['content']),
    ];

    dd($post_data);
}