<?php

use Contentful\Delivery\Query;
use Contentful\RichText\Renderer;

// set php time limit
set_time_limit(0);
ini_set('max_input_time', -1);
ini_set('max_execution_time', 0);

// load composer autoload
require_once __DIR__ . '/vendor/autoload.php';

// load wordpress
define('WP_USE_THEMES', false);
require_once __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/../wp-admin/includes/media.php';
require_once __DIR__ . '/../wp-admin/includes/file.php';
require_once __DIR__ . '/../wp-admin/includes/image.php';

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

// get all contentful assets urls
$assets = contentful_get_assets_urls($data['includes']['Asset']);

$renderer = new Renderer();

// loop through each item
foreach($data['items'] as $item) {
    // exist post slug
    if (get_page_by_path($item['fields'][$fields['post_name']], OBJECT, 'post')) {
        continue;
    }

    $client = new \Contentful\Delivery\Client($_ENV['CONTENTFUL_ACCESS_TOKEN'], $_ENV['CONTENTFUL_SPACE_ID']);

    $entry = $client->getEntry($item['sys']['id']);
    $content = $entry->contentBlock1;
    
    $post_data = [
        'post_type'     => 'post',
        'post_status'   => 'publish',
        'post_date'     => $item['sys']['createdAt'],
        'guid'          => $item['sys']['id'],
        'post_title'    => $item['fields'][$fields['post_title']],
        'post_name'     => $item['fields'][$fields['post_name']] ?? '',
        'post_excerpt'  => $item['fields'][$fields['post_excerpt']] ?? '',
        'post_content'  => $renderer->render($content),
    ];

    $post_id = wp_insert_post($post_data);

    // set post tags
    $tags = $item['fields'][$fields['post_tags']] ?? '';
    if (isset($tags) && !empty($tags)) {
        wp_set_post_tags($post_id, explode(',', $tags));
    }
    
    // set post thumbnail
    $thumnail = $assets[$item['fields'][$fields['post_thumbnail']]['sys']['id']] ?? '';
    if (isset($thumnail) && !empty($thumnail)) {
        $image_id = media_sideload_image($thumnail, $post_id, $post_data['post_title'], 'id');

        if (!is_wp_error($image_id)) {
            set_post_thumbnail($post_id, $image_id);
        }
    }
}