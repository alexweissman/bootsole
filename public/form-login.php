<?php

require_once("config-site.php");

use \Bootsole as BS;

// Load form validation schema
$vs = new BS\ValidationSchema("forms/form-login.json", "en_US");

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Simple login form",
    "description" => "A sample page for Bootsole",
    "favicon_path" => BS\URI_PUBLIC_ROOT . "css/favicon.ico"
];

$content = [
    "@header" => $header_content,
    "heading_main" => "Please Log In",
    "content" =>
        new BS\FormBuilder([
            "@layout" => "horizontal",
            "@label_width" => 2,
            "@components" => [
                'user_name' => [
                    '@type' => 'text',
                    '@label' => 'Username',
                    '@placeholder' => 'Please enter the user name'
                ],
                'password' => [
                    '@type' => 'password',
                    '@label' => 'Password',
                    '@placeholder' => 'Please enter your password'
                ],
                'submit' => new BS\FormButtonBuilder([
                    "@type" => "submit",
                    "@label" => "Log In",
                    "@css_classes" => ["btn-primary", "btn-lg"]
                ])
            ],
            "@validators" => $vs->clientRules()
        ], "forms/form-login.html")  
];

$pb = new BS\PageBuilder($content);

echo $pb->render();

?>