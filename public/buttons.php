<?php

require_once("config-site.php");

use \Bootsole as BS;

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Button components",
    "description" => "Button components for Bootsole",
    "favicon_path" => BS\URI_PUBLIC_ROOT . "css/favicon.ico"
];

$content = [
    "@header" => $header_content,
    "@name" => "test",
    "heading_main" => "Buttons",
    "content" => [
        "@template" =>
            "<h2>Dropdown Button</h2>
            {{dropdown_button}}
            <h2>Combo button</h2>
            {{combo_button}}
            ",
        "@content" => [
            "combo_button" => new BS\ButtonGroupBuilder([
                "@items" => [
                    [
                        "@type" => "button",
                        "@label" => "Do it!",
                        "@css_classes" => ["btn-warning"],
                        "@name" => "action1",
                        "@display" => "disabled"
                    ],
                    [
                        "@type" => "button",
                        "@label" => "Do it again!",
                        "@css_classes" => ["btn-danger"],
                        "@name" => "action2"
                    ],                    
                    [
                        "@type" => "button",
                        "@css_classes" => ["btn-danger"],
                        "@name" => "dropdown_addon",
                        "@align" => "inherit",
                        "@items" => [
                            "algebra" => [
                                "@label" => "Algebra",
                                "@url" => BS\URI_PUBLIC_ROOT. "courses/algebra"
                            ],
                            "calculus" => [
                                "@label" => "Calculus",
                                "@url" => BS\URI_PUBLIC_ROOT. "courses/calculus",
                                "@display" => "disabled"
                                
                            ]
                        ]                
                    ]
                ]
            ])
        ]
    ]
];


// Build a dropdown:
$dropdown_content = [
    "@align" => "right",
    "@label" => "Press me!",
    "@css_classes" => ["btn-success"],
    "@items" => [
        "algebra" => [
            "@label" => "Algebra",
            "@url" => BS\URI_PUBLIC_ROOT. "courses/algebra"
        ],
        "calculus" => [
            "@label" => "Calculus",
            "@url" => BS\URI_PUBLIC_ROOT. "courses/calculus"
            
        ]
    ]
];

$dropdown = new BS\DropdownButtonBuilder($dropdown_content);
$pb = new BS\PageBuilder($content);

$pb->getContent("content")->setContent("dropdown_button", $dropdown);

//$pb->setContent("content", $dropdown);

echo $pb->render();

?>
