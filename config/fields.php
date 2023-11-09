<?php

// fields mapping: wordpress => contentful

$fields = [
    'post_type'         => 'blogPost',
    'post_title'        => 'title',
    'post_name'         => 'slug',
    'post_excerpt'      => 'description',
    'post_content'      => 'contentBlock1',
    'post_thumbnail'    => 'headerImage',
    'post_tags'         => 'keywords',
];