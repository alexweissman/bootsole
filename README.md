# Bootsole 0.0.1

### By Alex Weissman

Copyright (c) 2014

A PHP templating engine for rendering Bootstrap-themed, [Tablesorter](https://mottie.github.io/tablesorter/docs/) tables from arrays of raw data.

## Supports

- Custom sorting parameters
- Pagination
- Column filtering
- Row menus for performing actions on rows
- Alternative templates for empty table cells
- Iterative rendering of sub-array data

## Screenshot

![Tablebuilder](/screenshots/tablebuilder.png "Tablebuilder")


## Usage

````
$columns = [
    'col1' =>
        'label' => :Column label:,
        'sort' => :'asc'|'desc':,
        'sorter' => :'metatext'|'metanum':,
        'sort_field' => :field to sort on:,
        'template' => :data template:
        'empty_field' => :field to check if 'empty':,
        'empty_value' => :value that should be considered 'empty':,
        'empty_template' => :alternate template to use if empty:
    ],
    'col2' => ...

]

$rows = [
    'row1' => [
        'field1' => 'value1',
        'field2' => 'value2',
        'field3' => [
            'field3_1' => 'value3_1',
            'field3_2' => 'value3_2',
            ...
        ],
        ...
    ]
]

// The following are all optional parameters

$menu_items = [
    'item1' => [
        'template => :template for this menu item:
    ],
    ...
]

$menu_label = :column label for menu items:
$menu_state_field = :row field to use for the menu button state:
$menu_style_field = :row field to use for the menu button style:

$tb = new TableBuilder($columns, $rows, $actions, $menu_label, $menu_state_field, $menu_style_field);
echo $tb->render();

````

## Templates

Uses the double-handlebar notation:

````
<i>{{name}}</i>
````

The engine will replace `{{name}}` with the corresponding value of `$rows[$i]['name']` in each row.

Sub-array data:

````
[[names <span data-id={{id}}>{{name}}</span> ]]
````

The double-bracket notation indicates that a particular field is an array, rather than a single value.  Templates for this notation will be the field name, followed by a space, followed by the template, inside double brackets.
In this example, the engine will look for a sub-array in each row named "names".  Each element in `$rows[$i]['names']` will be rendered according to the template `"<span data-id={{id}}>{{name}}</span> "`.  The results for the entire sub-array will be concatenated.


## Dependencies

### PHP
- 5.4+

### Javascript/CSS (included in this repository)
- jQuery 1.10.2
- Bootstrap 3.0.2
- Tablesorter 2.17.7 with the pager and filter widgets
- FontAwesome 4.1

