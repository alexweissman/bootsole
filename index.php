<?php

require_once("bootsole/bootsole.php");

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

$content = [
    //"header" => $header,
    // ...or as an array...
    "@header" => $header_content,
    "@name" => "test",
    "main-nav" => $nb,
    "heading_main" => "Welcome to Bootsole",
    "content" => "Hey, I'm the content!",
    "@footer" => [
        "@source" => "pages/footers/footer-default.html",
        "@content" => []
    ]
];

$pb = new PageBuilder($content, "pages/page-jumbotron.html");
// ...or set it later!
//$pb->header($header);

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


$table_content = [
    "@columns" => [
        "info" =>  [
            "label" => "Teacher",
            "@sorter" => "metatext",
            "@sort_field" => "user_name",
            "@initial_sort_direction" => "asc",
            "@cell_template" => "
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
        "num_students" => [
            "label" => "Students",
            "@sorter" => "metanum",
            "@sort_field" => "num_students",
            "@cell_template" => "<a class='btn btn-success' href='students.php?teacher_id={{teacher_id}}'>{{num_students}}</a>",
            "@empty_field" => "num_students",
            "@empty_value" => "0",
            "@empty_template" => "Zero"
        ],
        "students" => [
            "label" => "Student List",
            "@sorter" => "metanum",
            "@sort_field" => "num_students",
            "@cell_template" => "{{students}}",
            "@empty_field" => "students",
            "@empty_value" => [],
            "@empty_template" => "<i>None</i>"  
        ]     
    ],
    "@rows" => [
        "1" => [
            "teacher_id" => "1",
            "user_name" => "socrizzle",
            "display_name" => "Socrates",
            "title" => "Big Cheese",
            "email" => "socrates@athens.gov",
            "num_students" => "2",
            "students" => [
                "@template" => "<a class='btn btn-success' href='student_details.php?student_id={{student_id}}'>{{display_name}}</a>",
                "@array" => [
                    "A" => [
                        "student_id" => "1",
                        "display_name" => "Xenophon"
                    ],
                    "B" => [
                        "student_id" => "2",
                        "display_name" => "Plato"
                    ]
                ]
            ]
        ],
        "2" => [
            "teacher_id" => "2",
            "user_name" => "seamus",
            "display_name" => "Seamus",
            "title" => "Beanmaster",
            "email" => "seamus@cardboardbox.com",
            "num_students" => "0",
            "students" => []
        ],    
        "3" => [
            "teacher_id" => "3",
            "user_name" => "plato",
            "display_name" => "Plato",
            "title" => "Idealist",
            "email" => "plato@athens.gov",
            "num_students" => "1"
        ]
    ]
];

$table = new TableBuilder($table_content);

$template = "
<div class='media'>
  <a class='media-left' href='#'>
    <img src='{{img_src}}' alt='{{img_alt}}' width='64' height='64'>
  </a>
  <div class='media-body'>
    <h4 class='media-heading'>{{heading}}</h4>
    {{body}}
  </div>
</div>";

$content = [
    "img_src" => "http://avatars1.githubusercontent.com/u/5004534?v=3&s=400",
    "img_alt" => "Lord of the Fries",
    "heading" => "Alex Weissman",
    "body" => "Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors..."
];

$hb1 = new HtmlBuilder($content);
$hb1->setTemplate($template);

$hb2 = new HtmlBuilder([
    "img_src" => "http://ww2.hdnux.com/photos/02/25/67/613833/3/gallery_thumb.jpg",
    "img_alt" => "Rambo",
    "heading" => "Sylvester Stallone",
    "body" => "Sylvester Gardenzio Stallone, nicknamed Sly Stallone, is an American actor, screenwriter and film director.  Stallone is well known for his Hollywood action roles..."
]);
$hb2->setTemplate($template);

$hb3 = new HtmlBuilder([
    "img_src" => "http://cdn.akamai.steamstatic.com/steamcommunity/public/images/avatars/d0/d0877f614b8bb52813a63915be4da611cfa0ac2e_medium.jpg",
    "img_alt" => "John McClane",
    "heading" => "Bruce Willis",
    "body" => "Walter Bruce Willis, better known as Bruce Willis, is an American actor, producer, and singer. His career began on the Off-Broadway stage and then in television in the 1980s, most notably as David Addison in Moonlighting..."
]);
$hb3->setTemplate($template);

$jumbotron_template = "
    <div class='jumbotron'>
        <h1>{{heading}}</h1>
        {{body}}
    </div>";
    
$jumbotron_content = [
    "heading" => "Developers",
    "body" => [
        "@template" => $template,
        "@array" => [
            [
                "img_src" => "http://avatars1.githubusercontent.com/u/5004534?v=3&s=400",
                "img_alt" => "Lord of the Fries",
                "heading" => "Alex Weissman",
                "body" => "Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors..."            
            ],
            [
                "img_src" => "http://ww2.hdnux.com/photos/02/25/67/613833/3/gallery_thumb.jpg",
                "img_alt" => "Rambo",
                "heading" => "Sylvester Stallone",
                "body" => "Sylvester Gardenzio Stallone, nicknamed Sly Stallone, is an American actor, screenwriter and film director.  Stallone is well known for his Hollywood action roles..."
            ],
            [
                "img_src" => "http://cdn.akamai.steamstatic.com/steamcommunity/public/images/avatars/d0/d0877f614b8bb52813a63915be4da611cfa0ac2e_medium.jpg",
                "img_alt" => "John McClane",
                "heading" => "Bruce Willis",
                "body" => "Walter Bruce Willis, better known as Bruce Willis, is an American actor, producer, and singer. His career began on the Off-Broadway stage and then in television in the 1980s, most notably as David Addison in Moonlighting..."
            ]
        ]
    ]
];

$jumbotron = new HtmlBuilder($jumbotron_content);
$jumbotron->setTemplate($jumbotron_template);
//echo $jumbotron->render();



$pb->setContent("content", $table);

echo $pb->render();


?>
