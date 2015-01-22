<?php

require_once("../bootsole/bootsole.php");

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Dropdown components",
    "description" => "Dropdown components for Bootsole",
    "favicon_path" => PUBLIC_ROOT . "css/favicon.ico"
];

$content = [
    "@header" => $header_content,
    "@name" => "test",
    "heading_main" => "Buttons",
    "content" => [
        "@template" =>
            "<h2>Basic button</h2>
            {{basic_button}}
            <h2>Dropdown Button</h2>
            {{dropdown_button}}
            ",
        "@content" => [
            "basic_button" => new ButtonGroupBuilder([
                "@items" => [
                    [
                        "@type" => "button",
                        "@label" => "Oh hi",
                        "@css_classes" => ["btn-default"],
                        "@name" => "basic",                        
                    ],

                    [
                        "@type" => "button",
                        //"@label" => "Press me!",
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

$dropdown = new DropdownBuilder($dropdown_content);
$pb = new PageBuilder($content);

$pb->getContent("content")->setContent("basic_dropdown", $dropdown);
$pb->getContent("content")->setContent("dropdown_button",
    [ '@template' => '
<div class="btn-group" role="group" aria-label="...">
  <button type="button" class="btn btn-default">Default</button>
  
  <div class="btn-group" role="group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
      <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
      <li><a href="#">Dropdown link</a></li>
      <li><a href="#">Dropdown link</a></li>
    </ul>
  </div>
</div>
<!-- Split button -->
<div class="btn-group">
  <button type="button" class="btn btn-default">Default</button>
  
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
    <span class="caret"></span>
    <span class="sr-only">Toggle Dropdown</span>
  </button>
  {{dropdown}}
</div>',
    '@content' => [
        'dropdown' => $dropdown
    ]
    ]);

echo $pb->render();

?>
