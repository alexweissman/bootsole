<!-- Common header includes for all account pages -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Bootsole Demo</title>
  
  <!-- Core CSS -->
  <link rel="stylesheet" href="css/bootstrap-3.0.2.css">
  <link rel="stylesheet" href="css/bootstrap-switch.min.css">
  <link rel="stylesheet" href="css/bootstrapradio.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/select2/select2.css">
  <link rel="stylesheet" href="css/select2/select2-bootstrap.css">
  <link rel="stylesheet" href="css/tablesorter/theme.bootstrap.css">
  <link rel="stylesheet" href="css/tablesorter/jquery.tablesorter.pager.css">
  <link rel="stylesheet" href="css/bootstrap-custom.css">
  <link rel="stylesheet" href="css/bootstrapValidator.min.css">

</head>
<body>

    <div class='container' id='teachers'>
        <h1 class='text-center'><i class='fa fa-mortar-board'></i> Teachers</h1>
        <?php
        
        require_once('table_builder.php');
        require_once('form_builder.php');
        require_once('validation.php');
        
        // Table Demo
        $columns = [
            'info' =>  [
                'label' => 'Teacher',
                'sorter' => 'metatext',
                'sort_field' => 'user_name',
                'sort' => 'asc',
                'template' => "
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
                'sorter' => 'metanum',
                'sort_field' => 'num_students',
                'template' => "<a class='btn btn-success' href='students.php?teacher_id={{teacher_id}}'>{{num_students}}</a>",
                'empty_field' => 'num_students',
                'empty_value' => '0',
                'empty_template' => "Zero"
            ],
            'students' => [
                'label' => 'Student List',
                'sorter' => 'metanum',
                'sort_field' => 'num_students',
                'template' => "[[students <a class='btn btn-success' href='student_details.php?student_id={{student_id}}'>{{display_name}}</a> ]]",
                'empty_field' => 'students',
                'empty_value' => [],
                'empty_template' => "<i>None</i>"  
            ]
        ];
        
        $rows = [
            '1' => [
                'teacher_id' => '1',
                'user_name' => 'socrizzle',
                'display_name' => 'Socrates',
                'title' => 'Big Cheese',
                'email' => 'socrates@athens.gov',
                'num_students' => '2',
                'students' => [
                    '1' => [
                        'student_id' => '1',
                        'display_name' => 'Xenophon'
                    ],
                    '2' => [
                        'student_id' => '2',
                        'display_name' => 'Plato'
                    ]
                ]
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
                'num_students' => '1',
                'students' => [
                    '1' => [
                        'student_id' => '3',
                        'display_name' => 'Aristotle'
                    ]
                ]
            ]
        ];
        
        $menu_items = [
            'star' => [
                'type' => "addon",
                'template' => "<button type='button' data-id='{{teacher_id}}' class='btn btn-{{row_style}}'><i class='fa fa-star'></i></button>",
                'show_field' => 'num_students',
                'show_field_values' => [
                    2
                ]                
            ],
            'get_podcast' => [
                'template' => "<a href='#' data-id='{{teacher_id}}' class='btn-get-podcast'><i class='fa fa-headphones'></i> Get podcast</a>"
            ],
            'divider_1' => [
                'type' => 'divider'
            ],
            'poison' => [
                'template' => "<a href='#' data-id='{{teacher_id}}' class='btn-poison'><i class='fa fa-flask'></i> Poison</a>",
            ],
            'change' => [
                'template' => "<a href='#' data-id='{{teacher_id}}' class='btn-give-change'><i class='fa fa-money'></i> Give change for the bus</a>",
                'show_field' => "num_students",
                'show_field_values' => [
                    0,
                    1
                ]
            ]
        ];
        
        $tb = new TableBuilder($columns, $rows, $menu_items, "Do Stuff");
        echo $tb->render();

        
        // Form Demo
        
        $template = "
        <form method='post' class='col-md-6 col-md-offset-3'>
            <div class='row'>
                <div class='col-sm-6'>
                    {{user_name}}
                </div>
                <div class='col-sm-6'>
                    {{display_name}}
                </div>            
            </div>
            <div class='row'>
                <div class='col-sm-6'>
                    {{email}}
                </div>
                <div class='col-sm-6'>
                    {{title}}
                </div>            
            </div>
            <div class='row'>
                <div class='col-sm-6'>
                    {{beard}}
                </div>
                <div class='col-sm-6'>
                    {{fav_polyhedron}}
                </div>
            </div>
            <div class='row'>
                <div class='col-sm-6'>
                    {{school}}
                </div>
                <div class='col-sm-6'>
                    {{wakeup}}
                </div>
            </div>
            <div class='row'>
                <div class='col-sm-6'>
                    {{shopping_bags}}
                </div>
                <div class='col-sm-6'>
                    {{tenure}}
                </div>
            </div>
            <div class='row'>
                <div class='col-xs-12 col-sm-6 hideable'>
                    {{btn_submit}}
                </div>
                <div class='col-xs-12 col-sm-3 hideable'>
                    {{btn_poison}}
                </div>
                <div class='col-xs-12 col-sm-3 hideable'>
                    {{btn_cancel}}
                </div> 
        </form>";
        
        $fields = [
            'user_name' => [
                'type' => 'text',
                'label' => 'Username',
                'display' => 'disabled',
                'placeholder' => 'Please enter the user name'
            ],
            'display_name' => [
                'type' => 'text',
                'label' => 'Display Name',
                'placeholder' => 'Please enter the display name'
            ],          
            'email' => [
                'type' => 'text',
                'label' => 'Email',
                'icon' => 'fa fa-envelope',
                'icon_link' => 'mailto: {{value}}',
                'placeholder' => 'Email goes here'
            ],
            'wakeup' => [
                'type' => 'selectTime',
                'label' => 'Wakeup Call',
                'icon' => 'fa fa-clock-o',
                'time_start' => '5:00 am',
                'time_end' => '12:00 pm',
                'time_increment' => 30
            ],    
            'title' => [
                'type' => 'select2',
                'label' => 'Title',
                'choices' => [
                    'ta' => [
                        'label' => 'Teaching Assistant'
                        ],
                    'street_lord' => [
                        'label' => 'Street Lord'
                        ],
                    'adjunct' => [
                        'label' => 'Adjunct Instructor'
                        ],
                    'assistant' => [
                        'label' => 'Assistant Professor'
                        ],
                    'associate' => [
                        'label' => 'Associate Professor'
                        ],
                    'professor' => [
                        'label' => 'Professor'
                        ],
                    'emeritus' => [
                        'label' => 'Professor Emeritus'
                        ]
                ],
                'default' => 'assistant'
            ],
            'beard' => [
                'type' => 'toggle',
                'label' => 'Beard',
                'icon' => 'fa fa-trophy',
                'display' => 'disabled',
                'choices' => [
                    'fluffy' => 'Fluffy',
                    'scraggly' => 'Scraggly',
                    'pointy' => 'Pointy'
                ],
                'default' => 'fluffy'
            ],
            'fav_polyhedron' => [
                'type' => 'select',
                'label' => 'Favorite Polyhedron',
                'icon' => 'fa fa-cubes',
                'choices' => [
                    'tetrahedron' => 'Tetrahedron',
                    'cube' => 'Cube',
                    'octahedron' => 'Octahedron',
                    'dodecahedron' => 'Dodecahedron',
                    'icosohedron' => 'Icosohedron'
                ]              
            ],
            'password' => [
                'type' => 'password',
                'label' => 'Password',
                'icon' => 'fa fa-key'
            ],
            'tenure' => [
                'type' => 'switch',
                'label' => 'Has tenure?',
                'on' => 'Yes',
                'off' => 'No'
            ],
            'shopping_bags' => [
                'type' => 'switch',
                'label' => 'Shopping bags',
                'on' => 'Paper',
                'off' => 'Plastic',
                'icon' => 'fa fa-briefcase'
            ],
            'school' => [
                'type' => 'radioGroup',
                'label' => 'School',
                'choices' => [
                    'epicurist' => [
                        'label' => 'Epicurist.  Relax and enjoy life.',
                        'icon' => 'fa fa-cutlery'
                    ],
                    'futurist' => [
                        'label' => 'Futurist.  Cyborgs unite!',
                        'icon' => 'fa fa-space-shuttle'
                    ],
                    'stoic' => [
                        'label' => 'Stoic.  Grin and bear it.',
                        'icon' => 'fa fa-tree'
                    ]
                ]                  
            ]
        ];

        $data = [
            'user_name' => "Bob",
            'email' => "bob@bob.com",
            'beard' => "scraggly",
            'tenure' => true,
            'shopping_bags' => false,
            'school' => 'futurist'
        ];
        
        $buttons = [
            'btn_poison' => [
                'label' => 'Poison',
                'type' => 'button',
                'style' => 'danger',
                'size' => 'lg',
                'display' => 'disabled',
                'icon' => 'fa fa-flask'
            ],
            'btn_submit' => [
                'label' => 'Create Philosopher',
                'type' => 'submit',
                'size' => 'lg',
                'style' => 'success'
            ],
            'btn_cancel' => [
                'label' => 'Cancel',
                'type' => 'cancel',
                'size' => 'lg',
                'style' => 'link'
            ]       
        ];
            
        // Load validation schema
        $vs = new ValidationSchema("validators/philosophers.json");
        
        $fb = new FormBuilder($template, $fields, $buttons, $data, $vs->clientRules());
        echo $fb->render();
        
        ?>
    </div>

    <!-- Core JavaScript -->
    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="js/bootstrap-3.0.2.js"></script> 
    <script src="js/bootstrap-switch.min.js"></script>
    <script src="js/bootstrapradio.js"></script>
    <script src="js/select2/select2.min.js"></script>
    <script src="js/tablesorter/jquery.tablesorter.min.js"></script>
    <script src="js/tablesorter/tables.js"></script>
    <script src="js/tablesorter/jquery.tablesorter.pager.min.js"></script>
    <script src="js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
    <script src="js/bootstrapValidator.min.js"></script>
    <script src="js/bootsole.js"></script>
  
</body>
</html>