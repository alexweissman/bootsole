<?php

require_once("../bootsole/bootsole.php");

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Button components",
    "description" => "Button components for Bootsole",
    "favicon_path" => PUBLIC_ROOT . "css/favicon.ico"
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
            "combo_button" => new ButtonGroupBuilder([
                "@items" => [
                    [
                        "@type" => "button",
                        "@label" => "Oh hi",
                        "@css_classes" => ["btn-default"],
                        "@name" => "basic",
                        "@display" => "disabled"
                    ],
                    [
                        "@type" => "button",
                        "@css_classes" => ["btn-default"],
                        "@name" => "dropdown_addon",
                        "@align" => "inherit",
                        "@items" => [
                            "algebra" => [
                                "@label" => "Algebra",
                                "@url" => PUBLIC_ROOT. "courses/algebra"
                            ],
                            "calculus" => [
                                "@label" => "Calculus",
                                "@url" => PUBLIC_ROOT. "courses/calculus",
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
            "@url" => PUBLIC_ROOT. "courses/algebra"
        ],
        "calculus" => [
            "@label" => "Calculus",
            "@url" => PUBLIC_ROOT. "courses/calculus"
            
        ]
    ]
];

$dropdown = new DropdownButtonBuilder($dropdown_content);
$pb = new PageBuilder($content);

$pb->getContent("content")->setContent("dropdown_button", $dropdown);

echo $pb->render();

?>
