<?php

require_once("bootsole.php");

use \Bootsole as BS;

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Simple, nested templating for rendering Bootstrap themed pages with PHP",
    "description" => "A sample page for Bootsole",
    "favicon_path" => PUBLIC_ROOT . "css/favicon.ico"
];

$content = [
    "@header" => $header_content,
    "@name" => "test",
    "heading_main" => "Welcome to Bootsole",
    "content" => "Hey, I'm the content!"
];

$pb = new BS\PageBuilder($content);
echo $pb->render();

?>