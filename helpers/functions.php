<?php

function contentful_get_assets_urls($assets) {
    $data = [];

    foreach($assets as $asset) {
        $data[$asset['sys']['id']] = sprintf('https:%s', $asset['fields']['file']['url']) ?? '';
    }

    return $data;
}
