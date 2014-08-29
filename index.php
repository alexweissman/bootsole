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
        
        $columns = [
            'info' =>  [
                'label' => 'Teacher',
                'sorter' => 'metatext',
                'meta_field' => 'user_name',
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
                'sort' => 'desc',
                'sorter' => 'metanum',
                'meta_field' => 'count_students',
                'template' => "<a class='btn btn-success' href='students.php?teacher_id={{teacher_id}}'>{{num_students}}</a>",
                'empty_field' => 'num_students',
                'empty_value' => '0',
                'empty_template' => "Zero"
            ],
            'students' => [
                'label' => 'Student List',
                'sorter' => 'metanum',
                'meta_field' => 'count_students',
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
                'title' => 'Smelly Dude',
                'email' => 'seamus@cardboardbox.com',
                'num_students' => '0',
                'students' => []
            ],    
            '3' => [
                'teacher_id' => '3',
                'user_name' => 'zizzo',
                'display_name' => 'Plato',
                'title' => 'Perfectionist',
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
        
        $actions = array();
        
        $tb = new TableBuilder($columns, $rows, $actions);
        echo $tb->render();
 
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