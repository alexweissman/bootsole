<?php

require_once("config-site.php");

use \Bootsole as BS;

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Sample Tablesorter table",
    "description" => "A sample page for Bootsole",
    "favicon_path" => BS\URI_PUBLIC_ROOT . "css/favicon.ico"
];

$content = [
    "@header" => $header_content,
    "@name" => "test",
    "heading_main" => "A Tablesorter Table",
    "@footer" => [
        "@source" => "pages/footers/footer-default.html",
        "@content" => []
    ]
];

$pb = new BS\PageBuilder($content);

// Generate teachers
$teachers = [
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
];

// Build custom menus
foreach ($teachers as $teacher_id => $teacher){  
    if ($teacher['num_students'] == 0)
        $show_change = "show";
    else
        $show_change = "hidden";
    
    if ($teacher['display_name'] == "Socrates")
        $show_poison = "show";
    else
        $show_poison = "disabled";        
    
    $menu = new BS\DropdownButtonBuilder([
        "@type" => "button",
        "@label" => "Actions",
        "@css_classes" => ["btn-primary"],
        "@items" => [
            'get_podcast' => [
                '@label' => "<i class='fa fa-headphones'></i> Get podcast",
                '@css_classes' => ['btn-get-podcast'],
                '@data' => [
                    "id" => $teacher_id
                ]
            ],
            'poison' => [
                '@label' => "<i class='fa fa-flask'></i> Poison",
                '@css_classes' => ['btn-poison'],
                '@data' => [
                    "id" => $teacher_id
                ],
                '@display' => $show_poison    
            ],
            'change' => [
                '@label' => "<i class='fa fa-money'></i> Give change for the bus",
                '@css_classes' => ['btn-give-change'],
                '@data' => [
                    "id" => $teacher_id
                ],
                '@display' => $show_change
            ]
        ]
    ]);
    
    $teachers[$teacher_id]['menu'] = $menu;
    
}

$table_content = [
    "@columns" => [
        "info" =>  [
            "@label" => "Teacher",
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
            "@label" => "Students",
            "@sorter" => "metanum",
            "@sort_field" => "num_students",
            "@cell_template" => "<a class='btn btn-success' href='students.php?teacher_id={{teacher_id}}'>{{num_students}}</a>",
            "@empty_field" => "num_students",
            "@empty_value" => "0",
            "@empty_template" => "Zero"
        ],
        "students" => [
            "@label" => "Student List",
            "@sorter" => "metanum",
            "@sort_field" => "num_students",
            "@cell_template" => "{{students}}",
            "@empty_field" => "students",
            "@empty_value" => [],
            "@empty_template" => "<i>None</i>"  
        ],
        "actions" => [
            "@label" => "Actions",
            "@cell_template" => "{{menu}}"
        ]
    ],
    "@rows" => $teachers
 
];

$table = new BS\TableBuilder($table_content);

$pb->setContent("content", $table);

echo $pb->render();


?>
