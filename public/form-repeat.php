<?php

require_once("config-site.php");

use \Bootsole as BS;

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Repeated Groups of Fields",
    "description" => "A sample page for Bootsole",
    "favicon_path" => BS\URI_PUBLIC_ROOT . "css/favicon.ico"
];


$ids = [
    3 => "San Juan",
    2 => "Baltimore",
    8 => "Montreal",
    4 => "Texarkana",
    1 => "Hanoi"
];

$form_groups = [];

foreach ($ids as $id => $location){
    $fb = new BS\FormComponentCollectionBuilder([
        "@components" => [
            'location' => [
                '@type' => 'text',
                '@name' => "location[$id]",
                '@label' => 'Location',
                '@placeholder' => 'Please enter the location'
            ]
        ],
        "@values" => [
            'location' => $location
        ]
    ]);
    $fb->setTemplate("<fieldset>{{location}}</fieldset>");
    $form_groups[] = $fb;
}



$form_content = [
    "@layout" => "horizontal",
    "@label_width" => 2,
    "@name" => "locations",
    "@components" => [
        "groups" => $form_groups
    ]
];

/*
echo "<pre>";
print_r($form_content);
echo "</pre>";
*/

$form = new BS\FormBuilder($form_content, "forms/form-groups.html");

$page_content = [
    "@header" => $header_content,
    "@name" => "form-repeat",
    "heading_main" => "A form generated from an array of fieldsets",
    "content" => $form 
];


$pb = new BS\PageBuilder($page_content);

echo $pb->render();

?>