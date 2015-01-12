<?php

require_once("resources/config.php");

// Declaring a menu item OOPishly
$home = new NavItemBuilder([
        "label" => "Home",
        "url" => PUBLIC_ROOT
    ]);

$nb = new NavbarBuilder([
        "brand_label" => "Bootsole is Great!",
        "brand_url" => "http://github.com/alexweissman/bootsole",
        "@components" => [
            "btn1" => [
                "@type" => "button",
                "styles" => "btn-danger",
                "label" => "Self-Destruct!"
            ],
            "search-form" => [
                "@type" => "form",
                "@align" => "right",
                "form" => '<form role="search">
                    <div class="form-group">
                      <input type="text" class="form-control" placeholder="Search">
                    </div>*
                    <button type="submit" class="btn btn-default">Submit</button>
                </form>'
            ],            
            // Declaring a nav group, mixed arrays and objects
            "main-menu" => [
                "@type" => "nav",
                "@align" => "right",
                "@items" => [
                    "home" => $home,
                    "about" => [
                        "label" => "About",
                        "url" => PUBLIC_ROOT. "about"
                    ],
                    "courses" => [
                        "active" => "active",
                        "label" => "Courses",
                        "url" => PUBLIC_ROOT . "courses",
                        "@position" => "right",
                        "@items" => [
                            "algebra" => [
                                "label" => "Algebra",
                                "url" => PUBLIC_ROOT. "courses/algebra"
                            ],
                            "calculus" => [
                                "label" => "Calculus",
                                "url" => PUBLIC_ROOT. "courses/calculus"
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]);

// You can add a component like this, too

$new_component = [
    "@type" => "nav",
    "@align" => "right",
    "@items" => [ 
        "contact" => [
            "label" => "<i class='fa fa-paper-plane'></i>",
            "url" => PUBLIC_ROOT.  "contact"
        ]
    ]
];

$nb->addComponent("side-menu", $new_component);

// ..and set an active item
$nb->getComponent("main-menu")->setActiveItem("about");

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Simple, nested templating for rendering Bootstrap themed pages with PHP",
    "description" => "A sample page for Bootsole",
    "favicon_path" => PUBLIC_ROOT . "css/favicon.ico"
];

// Can specify 'header' as an object...
$header = new HtmlBuilder($header_content, "pages/headers/header-default.html");

$content = [
    //"header" => $header,
    // ...or as an array...
    /*
    "header" => [
        "@source" => "pages/headers/header-default.html",
        "@content" => $header_content
    ],
    */
    "main-nav" => $nb,
    "heading_main" => "Welcome to Bootsole",
    "content" => "Hey, I'm the content!",
    "footer" => [
        "@source" => "pages/footers/footer-default.html",
        "@content" => []
    ]
];

$pb = new PageBuilder("test", $content, "pages/page-jumbotron.html");
// ...or set it later!
//$pb->header($header);

// Yup, you can use an array here too...
$pb->header([
        "@source" => "pages/headers/header-default.html",
        "@content" => $header_content
    ]);


// Build a dropdown:
$dropdown_content = [
    "@items" => [
        "algebra" => [
            "label" => "Algebra",
            "url" => PUBLIC_ROOT. "courses/algebra"
        ],
        "calculus" => [
            "label" => "Calculus",
            "url" => PUBLIC_ROOT. "courses/calculus"
        ]
    ]
];

$dropdown = new DropdownBuilder($dropdown_content);


// Build a table:
$table_content = [
    '@columns' => [
        'info' =>  [
            'label' => 'Teacher',
            '@sorter' => 'metatext',
            '@sort_field' => 'user_name',
            '@initial_sort_direction' => 'asc',
            '@cell_template' => "
                    <div class='h4'>
                        <a href='user_details.php?id={{teacher_id}}'>{{display_name}} ({{user_name}})</a>
                    </div>
                    <div>
                        <i>{{title}}</i>
                    </div>
                    <div>
                        <i class='fa fa-envelope'></i> <a href='mailto:{{email}}'>{{email}}</a>
                    </div>"
        ],
        'num_students' => [
            'label' => 'Students',
            '@sorter' => 'metanum',
            '@sort_field' => 'num_students',
            '@cell_template' => "<a class='btn btn-success' href='students.php?teacher_id={{teacher_id}}'>{{num_students}}</a>",
            '@empty_field' => 'num_students',
            '@empty_value' => '0',
            '@empty_template' => "Zero"
        ],
        'students' => [
            'label' => 'Student List',
            '@sorter' => 'metanum',
            '@sort_field' => 'num_students',
            '@cell_template' => '{{students}}',
            '@empty_field' => 'students',
            '@empty_value' => [],
            '@empty_template' => "<i>None</i>"  
        ],
        'actions' => [
            'label' => 'Actions',
            '@cell_template' => '{{actions}}'
        ]       
    ],
    '@rows' => [
        '1' => [
            'teacher_id' => '1',
            'user_name' => 'socrizzle',
            'display_name' => 'Socrates',
            'title' => 'Big Cheese',
            'email' => 'socrates@athens.gov',
            'num_students' => '2',
            'students' => [
                '@template' => "<a class='btn btn-success' href='student_details.php?student_id={{student_id}}'>{{display_name}}</a>",
                '@array' => [
                    'A' => [
                        'student_id' => '1',
                        'display_name' => 'Xenophon'
                    ],
                    'B' => [
                        'student_id' => '2',
                        'display_name' => 'Plato'
                    ]
                ]
            ],
            'actions' => $dropdown
        ],
        '2' => [
            'teacher_id' => '2',
            'user_name' => 'zizzo',
            'display_name' => 'Randy',
            'title' => 'Beanmaster',
            'email' => 'seamus@cardboardbox.com',
            'num_students' => '0',
            'students' => []
        ],    
        '3' => [
            'teacher_id' => '3',
            'user_name' => 'plato',
            'display_name' => 'Plato',
            'title' => 'Idealist',
            'email' => 'plato@athens.gov',
            'num_students' => '1'
        ]
    ]
];

$table = new TableBuilder($table_content);
$pb->setContent("content", $table);

echo $pb->render();


?>
