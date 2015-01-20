<?php

require_once("../bootsole/bootsole.php");

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
    "content" => [
        "@template" =>
            "<h2>Horizontal Form</h2>
            {{horizontal}}
             <h2>Vertical Form</h2>
            {{vertical}}",
        "@content" => []
    ]
];


$fb = new FormBuilder([
    "@layout" => "horizontal",
    "@label_width" => 2,
    "@components" => [
        'user_name' => [
            '@type' => 'text',
            '@display' => 'disabled',            
            '@label' => 'Username',
            '@placeholder' => 'Please enter the user name'
        ],
        'title' => [
            '@type' => 'select2',
            '@label' => 'Title',
            '@items' => [
                'ta' => [
                    '@label' => 'Teaching Assistant'
                    ],
                'street_lord' => [
                    '@label' => 'Street Lord'
                    ],
                'adjunct' => [
                    '@label' => 'Adjunct Instructor'
                    ],
                'assistant' => [
                    '@label' => 'Assistant Professor'
                    ],
                'associate' => [
                    '@label' => 'Associate Professor'
                    ],
                'professor' => [
                    '@label' => 'Professor'
                    ],
                'emeritus' => [
                    '@label' => 'Professor Emeritus'
                    ]
            ],
            '@multiple' => "multiple",
            '@default' => 'emeritus',
            '@prepend' => "<span class='input-group-addon'><i class='fa fa-fw fa-mortar-board'></i></span>",            
        ],
        'email' => [
            '@type' => 'email',
            '@label' => 'Email',
            '@prepend' => "<span class='input-group-addon'><a href='mailto: blah@blah.com'><i class='fa fa-fw fa-envelope'></i></a></span>",
            '@placeholder' => 'Email goes here'
        ],
        'password' => [
            '@type' => 'password',
            '@label' => 'Password',
            '@placeholder' => 'Pick a good one',
            '@default' => 'dumb'
        ],
        'bio' => [
            '@type' => 'textarea',
            '@label' => 'Bio',
            '@placeholder' => "What's your deal?",
            '@rows' => '10'        
        ],
        'color' => [
            '@template' => "
                <div class='row'>
                    <div class='col-sm-8'>
                        <input type='number' class='form-control' name='{{_name}}' autocomplete='off' value='{{_value}}' placeholder='{{_placeholder}}' {{_validator}} {{_display}}>
                    </div>
                    <div class='col-sm-4'>
                        {{stuff}}
                    </div>
                </div>",
            '@type' => 'number',
            '@label' => 'Bunnies',
            '@placeholder' => 'So many...',
            'stuff' => "bunnies left to pet"
        ],
        'beard' => [
            '@type' => 'toggle',
            '@label' => 'Beard',
            '@multiple' => "multiple",
            '@prepend' => "<span class='input-group-addon'><i class='fa fa-fw fa-trophy'></i></span>",
            '@item_classes' => [
                'btn-primary'
            ],
            '@items' => [
                'fluffy' => [
                    '@label' => 'Fluffy'
                ],
                'scraggly' => [
                    '@label' => 'Scraggly'
                ],
                'pointy' => [
                    '@label' => 'Pointy'
                ]
            ],
            '@display' => 'disabled'
        ],        
        'wakeup' => [
            '@type' => 'selecttime',
            '@label' => 'Wakeup Call',
            '@prepend' => "<span class='input-group-addon'><i class='fa fa-fw fa-clock-o'></i></span>",
            '@time_start' => '5:00 am',
            '@time_end' => '12:00 pm',
            '@time_increment' => 30,
            '@placeholder' => 'When?',
            '@default' => '10:30 am'
        ],
        'school' => [
            '@type' => 'bootstrapradio',
            '@label' => 'School',
            '@items' => [
                'epicurist' => [
                    '@title' => 'Epicurist.  Relax and enjoy life.',
                    '@label' => "<i class='fa fa-cutlery'></i>"
                ],
                'futurist' => [
                    '@title' => 'Futurist.  Cyborgs unite!',
                    '@label' => "<i class='fa fa-space-shuttle'></i>"
                ],
                'stoic' => [
                    '@title' => 'Stoic.  Grin and bear it.',
                    '@label' => "<i class='fa fa-tree'></i>"
                ]
            ]                  
        ],
        'tos' => [
            '@type' => 'switch',
            '@label' => "TOS",
            '@text' => "I agree to the Terms and Conditions",
            '@text_on' => "Yes",
            '@text_off' => "No",
            '@item_value' => "yessir"
        ],
        'special_offers' => [
            '@type' => 'checkbox',
            '@label' => "Offers",
            '@display' => "disabled",
            '@text' => "Send me special offers",
            '@item_value' => "yessir"
        ]
    ],
    "@values" => [
        'user_name' => "Bob",
        'email' => "bob@bob.com",
        //'wakeup' => "11:00 am",
        'title' => "adjunct",
        'beard' => 'pointy',
        'password' => "yo",
        'school' => 'epicurist',
        'tos' => "yessir"
        
    ]
]);    

//$field = $fb->getComponent("user_name")->display("readonly");

//$fb->print_r();

$fb2 = clone $fb;
$fb2->layout("vertical");
$fb2->getComponent("user_name")->display("show");

$pb = new PageBuilder($content);

$pb->getContent("content")->setContent("horizontal", $fb);
$pb->getContent("content")->setContent("vertical", $fb2);

echo $pb->render();

?>