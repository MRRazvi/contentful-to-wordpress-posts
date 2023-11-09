<?php

// convert contentful rich text editor to raw html
function contentful_rich_text_to_html($contentArray) {
    $html = '';

    foreach ($contentArray as $item) {
        if ($item["nodeType"] === "heading-1") {
            $html .= '<h1>';
            foreach ($item["content"] as $content) {
                if ($content["nodeType"] === "text") {
                    $html .= $content["value"];
                }
            }
            $html .= '</h1>';
        } elseif ($item["nodeType"] === "unordered-list" && isset($item["content"])) {
            $html .= '<ul>';
            foreach ($item["content"] as $listItem) {
                if ($listItem["nodeType"] === "list-item" && isset($listItem["content"][0]) && $listItem["content"][0]["nodeType"] === "paragraph") {
                    $html .= '<li>';
                    $html .= $listItem["content"][0]["content"][0]["value"];
                    $html .= '</li>';
                }
            }
            $html .= '</ul>';
        } elseif ($item["nodeType"] === "paragraph") {
            $html .= '<p>';
            $paragraphContent = '';
            foreach ($item["content"] as $content) {
                if ($content["nodeType"] === "text") {
                    $bold = in_array("bold", array_column($content["marks"], "type"));
                    $italic = in_array("italic", array_column($content["marks"], "type"));
                    $underline = in_array("underline", array_column($content["marks"], "type"));

                    if ($bold) {
                        $paragraphContent .= '<b>';
                    }
                    if ($italic) {
                        $paragraphContent .= '<i>';
                    }
                    if ($underline) {
                        $paragraphContent .= '<u>';
                    }

                    $paragraphContent .= str_replace("\n", '<br>', $content["value"]);

                    if ($underline) {
                        $paragraphContent .= '</u>';
                    }
                    if ($italic) {
                        $paragraphContent .= '</i>';
                    }
                    if ($bold) {
                        $paragraphContent .= '</b>';
                    }
                } elseif ($content["nodeType"] === "hyperlink" && isset($content["data"]["uri"])) {
                    $paragraphContent .= '<a href="' . $content["data"]["uri"] . '">';
                    foreach ($content["content"] as $linkContent) {
                        if ($linkContent["nodeType"] === "text") {
                            $paragraphContent .= $linkContent["value"];
                        }
                    }
                    $paragraphContent .= '</a>';
                }
            }
            $html .= $paragraphContent;
            $html .= '</p>';
        }
    }

    return $html;
}

function contentful_get_assets_urls($assets) {
    $data = [];

    foreach($assets as $asset) {
        $data[$asset['sys']['id']] = sprintf('https:%s', $asset['fields']['file']['url']) ?? '';
    }

    return $data;
}
