<!-- Common header includes for all account pages -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Bootsole Table</title>
  
  <!-- Core CSS -->
  <link rel="stylesheet" href="css/bootstrap-3.0.2.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/tablesorter/theme.bootstrap.css">
  <link rel="stylesheet" href="css/tablesorter/jquery.tablesorter.pager.css">
  <link rel="stylesheet" href="css/bootstrap-custom.css">
  
</head>
<body>

    <div class='container' id='teachers'>
        <h1 class='text-center'><i class='fa fa-mortar-board'></i> Teachers</h1>
        <?php
        
        require_once('table_builder.php');
        require_once('form_builder.php');

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
                'user_name' => 'seamus',
                'display_name' => 'Seamus',
                'title' => 'Beanmaster',
                'email' => 'seamus@cardboardbox.com',
                'num_students' => '0',
                'students' => []
            ],    
            '3' => [
                'teacher_id' => '3',
                'user_name' => 'zizzo',
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
            'get_podcast' => [
                'template' => "<a href='#' data-id='{{teacher_id}}' class='btn-get-podcast'><i class='fa fa-headphones'></i> Get podcast</a>"
            ],
            'poison' => [
                'template' => "<a href='#' data-id='{{teacher_id}}' class='btn-poison'><i class='fa fa-flask'></i> Poison</a>"
            ],
            'change' => [
                'template' => "<a href='#' data-id='{{teacher_id}}' class='btn-give-change'><i class='fa fa-money'></i> Give change for the bus</a>"
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
        </form>";
        
        $fields = [
            'user_name' => [
                'type' => 'text',
                'label' => 'Username',
                'display' => 'disabled',
                'validator' => [
                    'minLength' => 1,
                    'maxLength' => 25,
                    'label' => 'Username'
                ],
                'placeholder' => 'Please enter the user name'
            ],
            'display_name' => [
                'type' => 'text',
                'label' => 'Display Name',
                'validator' => [
                    'minLength' => 1,
                    'maxLength' => 50,
                    'label' => 'Display name'
                ],
                'placeholder' => 'Please enter the display name'
            ],          
            'email' => [
                'type' => 'text',
                'label' => 'Email',
                'icon' => 'fa fa-envelope',
                'icon_link' => 'mailto: {{value}}',
                'validator' => [
                    'minLength' => 1,
                    'maxLength' => 150,
                    'email' => true,
                    'label' => 'Email'
                ],
                'placeholder' => 'Email goes here'
            ],
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'validator' => [
                    'minLength' => 1,
                    'maxLength' => 100,
                    'label' => 'Title'
                ],
                'default' => 'New User'
            ],
            'beard' => [
                'type' => 'toggle',
                'label' => 'Beard',
                'icon' => 'fa fa-trophy',
                'display' => 'disabled',
                'validator' => [
                    'selected' => true,
                    'label' => 'Beard'
                ],
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
                'validator' => [
                    'selected' => true,
                    'label' => 'Favorite Polyhedron'
                ],
                'choices' => [
                    'tetrahedron' => 'Tetrahedron',
                    'cube' => 'Cube',
                    'octahedron' => 'Octahedron',
                    'dodecahedron' => 'Dodecahedron',
                    'icosohedron' => 'Icosohedron'
                ]              
            ]
        ];

        $data = [
            'user_name' => "Bob",
            'email' => "bob@bob.com",
            'beard' => "scraggly"
        ];
                
        $fb = new FormBuilder($template, $fields, $data);
        echo $fb->render();
        
        ?>
    </div>

    <!-- Core JavaScript -->
    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="js/bootstrap-3.0.2.js"></script> 
    <script src="js/tablesorter/jquery.tablesorter.min.js"></script>
    <script src="js/tablesorter/tables.js"></script>
    <script src="js/tablesorter/jquery.tablesorter.pager.min.js"></script>
    <script src="js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
  
    <script>
        $(document).ready(function() {
            // define tablesorter pager options
            var pagerOptions = {
              // target the pager markup - see the HTML block below
              container: $('#teachers .pager'),
              // output string - default is '{page}/{totalPages}'; possible variables: {page}, {totalPages}, {startRow}, {endRow} and {totalRows}
              output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
              // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
              // table row set to a height to compensate; default is false
              fixedHeight: true,
              // remove rows from the table to speed up the sort of large tables.
              // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
              removeRows: false,
              // go to page selector - select dropdown that sets the current page
              cssGoto: '.gotoPage'
            };
            
            // Initialize the tablesorter
            $('#teachers .table').tablesorter({
                debug: false,
                theme: 'bootstrap',
                widthFixed: true,
                widgets: ['filter']
            }).tablesorterPager(pagerOptions);
        });
    </script>
  
</body>
</html>