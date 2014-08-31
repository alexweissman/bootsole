# Bootsole 0.1.1

### By Alex Weissman

Copyright (c) 2014

A PHP templating engine for rendering Bootstrap-themed, [Tablesorter](https://mottie.github.io/tablesorter/docs/) tables and forms from arrays of raw data.

## Tables

### Supports

- Custom sorting parameters
- Pagination
- Column filtering
- Row menus for performing actions on rows
- Alternative templates for empty table cells
- Iterative rendering of sub-array data

### Screenshot

![TableBuilder](/screenshots/tablebuilder.png "TableBuilder")

### Usage

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

];

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
];

// The following are all optional parameters

$menu_items = [
    'item1' => [
        'template => :template for this menu item:
    ],
    ...
];

$menu_label = :column label for menu items:
$menu_state_field = :row field to use for the menu button state:
$menu_style_field = :row field to use for the menu button style:

$tb = new TableBuilder($columns, $rows, $actions, $menu_label, $menu_state_field, $menu_style_field);
echo $tb->render();

````

### Templates

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

## Forms

### Supports

- text, password, toggle, and dropdown inputs
- pre-populate field with values from an array.  Useful for forms that "update" some information.
- fields can be hidden or disabled programmatically
- default field values
- field placeholders and icons
- preprocessor functions (PHP) for formatting data in certain fields
- field validation info
- customizable form buttons, with button types for submitting the form, launching and canceling modals

### Screenshot


![FormBuilder](/screenshots/formbuilder.png "FormBuilder")

### Usage

````

$template = :form template.  Use the double-handlebar notation to reference the names of the fields and buttons.:
// Example
$template = "<form method='post' class='col-md-6 col-md-offset-3'>
            <div class='row'>
                <div class='col-sm-6'>
                    {{field1}}
                </div>
                <div class='col-sm-6'>
                    {{field2}}
                </div>            
            </div>
            <div class='row'>
                <div class='col-xs-12 col-sm-6 hideable'>
                    {{btn_1}}
                </div>
                <div class='col-xs-12 col-sm-3 hideable'>
                    {{btn_2}}
                </div> 
        </form>";


$fields = [
    'field1' =>
        'name' => :field name attribute.  Default is array key (i.e. 'field1').     
        'label' => :Field label:,
        'type' => :'text'|'password'|'toggle'|'select':,
        'display' => :'show'|'hidden'|'disabled':,
        'icon' => :field add-on icon:,
        'icon_link' => :icon target link: (optional)
        'placeholder' => :field placeholder:
        'default' => :default value for field if empty:,
        'validator' => :validation array (UserFrosting only, will soon be replaced with the Bootstrapvalidator plugin):,
        'preprocess' => :PHP function to preprocess field values:,
        'choices' => :array of options.  'toggle' and 'select' types only.:
        'on' => :label for switches when they are turned on.  'switch' type only.:
        'off' => :label for switches when they are turned off.  'switch' type only.:
        ],
    'field2' => ...

];

$buttons = [
    'btn_1' => [
        'name' => :button name attribute.  Default is array key (i.e. 'btn_1'). 
        'type' => :'button'|'submit'|'launch'|'cancel',
        'display' => :'show'|'hidden'|'disabled':,
        'label' => :Button label:,
        'icon' => :Button icon:
        'size' => :'xs'|'sm'|'md'|'lg':,
        'style' => :'primary'|'success'|'warning'|'danger'|'info'|'default',
        'data' => :array of additional data attributes to add to this button.:
    ],
    'btn_2' => ...
];
    
$data = [
    'field1' => 'value1',
    'field2' => 'value2',
    ...

];

$fb = new FormBuilder($template, $fields, $buttons, $data);
echo $fb->render();

````

### Field types

#### <code>text</code>

Standard input field with label.  Wrapped in `input_group`, then in `form_group`.

#### <code>password</code>

Standard password field with label.  Wrapped in `input_group`, then in `form_group`.

#### <code>toggle</code>

Set of toggle buttons with label, overlays the `radio` input type.  Buttons are grouped in `btn-group`, then together with label wrapped in `input_group`, then in `form_group`.

#### <code>select</code>

Dropdown menu with label, overlays the `select` input type.  Wrapped in `input_group`, then in `form_group`.

#### <code>switch</code>

[Bootstrap Switch](http://www.bootstrap-switch.org/) switch, overlays the `checkbox` input type.  Wrapped in `form_group`.

#### <code>radioGroup</code>

Group of [Bootstrapradio](https://github.com/alexweissman/bootstrapradio) buttons, overlays the `button` input type.  Wrapped in `input_group`, then in `form_group`.


### Button types

#### <code>submit</code>

Creates a button that is used to submit the form.  Gets the HTML5 button attribute `type="submit"`.

#### <code>launch</code>

Creates a button that is used to launch a modal.  Useful for launching "edit" and "delete" dialogs.  Gets the HTML5 button attribute `type="button"` and the bootstrap `data-toggle="modal"` attribute.

#### <code>cancel</code>

Creates a button that is used to cancel a modal.  Useful for forms that are contained inside a modal dialog.  Gets the HTML5 button attribute `type="button"` and the bootstrap `data-dismiss="modal"` attribute.

#### <code>button</code>

Creates a button that is used for any other form action.  Gets the HTML5 button attribute `type="button"`.

## CSS

Use the <code>hideable</code> class to make Bootstrap columns that will collapse when empty.  This is useful for creating templates for forms with buttons that appear or disappear in different contexts.

## Dependencies

### PHP
- 5.4+

### Javascript/CSS (included in this repository)
- jQuery 1.10.2
- Bootstrap 3.0.2
- Tablesorter 2.17.7 with the pager and filter widgets
- FontAwesome 4.1
- Bootstrap Switch 3
- Bootstrapradio 0.1
