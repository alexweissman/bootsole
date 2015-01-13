# Bootsole 0.2.0

### By Alex Weissman

Copyright (c) 2015

A nestable, object-oriented templating engine for generating beautiful Bootstrap-themed pages, forms, tables, and other components in PHP.

## Basic usage

Bootsole uses the `{{double handlebar}}` notation for representing placeholders in a template.

**Create a template:**

```
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
```

**Then, assign content to the placeholders:**

```
$content = [
    "img_src" => "http://avatars1.githubusercontent.com/u/5004534?v=3&s=400",
    "img_alt" => "Lord of the Fries",
    "heading" => "Alex Weissman",
    "body" => "Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors, as well as a backend site..."
];
```

**Construct a new `HtmlBuilder` object, set the template, and render:**

```
$hb = new HtmlBuilder($content);
$hb->setTemplate($template);
echo $hb->render();
```

**Output:**

![Basic Usage](/screenshots/bootsole-example-1.png "Basic usage")
```
<div class='media'>
  <a class='media-left' href='#'>
    <img src='http://avatars1.githubusercontent.com/u/5004534?v=3&s=400 alt='Lord of the Fries' width='64' height='64'>
  </a>
  <div class='media-body'>
    <h4 class='media-heading'>Alex Weissman</h4>
    Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors, as well as a backend site for managing tutor and client data and activity.
  </div>
</div>
```

Wow, amazing!  So far, this is just simple find-and-replace.  But we can also nest `HtmlBuilder` objects in the content of other `HtmlBuilder` objects:

## Nested template objects:

```
$jumbotron_template = "
    <div class='jumbotron'>
        <h1>{{heading}}</h1>
        {{body}}
    </div>";
    
$jumbotron_content = [
    "heading" => "Developers",
    "body" => $hb
];

$jumbotron = new HtmlBuilder($jumbotron_content);
$jumbotron->setTemplate($jumbotron_template);
echo $jumbotron->render();
```

**Output:**

![Nested Templates](/screenshots/bootsole-example-2.png "Nested templates")
```
<div class='jumbotron'>
    <h1>Developers</h1>        
    <div class='media'>
      <a class='media-left' href='#'>
        <img src='http://avatars1.githubusercontent.com/u/5004534?v=3&s=400 alt='Lord of the Fries' width='64' height='64'>
      </a>
      <div class='media-body'>
        <h4 class='media-heading'>Alex Weissman</h4>
        Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors, as well as a backend site...
      </div>
    </div>
</div>
```

> Alright, that's kind of cool.  But what if I need a whole list of developers?  Do I need a placeholder for each one?

**Of course not!**  You can also assign an array of `HtmlBuilder` objects to a placeholder.  They will automatically be concatenated on rendering:

## Arrays of nested template objects:

```
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
        $hb1,
        $hb2,
        $hb3
    ]
];

$jumbotron = new HtmlBuilder($jumbotron_content);
$jumbotron->setTemplate($jumbotron_template);
echo $jumbotron->render();
```

**Output:**

![Array Templates](/screenshots/bootsole-example-3.png "Array templates")
```
<div class='jumbotron'>
    <h1>Developers</h1>
      
    <div class='media'>
      <a class='media-left' href='#'>
        <img src='http://avatars1.githubusercontent.com/u/5004534?v=3&s=400' alt='Lord of the Fries' width='64' height='64'>
      </a>
      <div class='media-body'>
        <h4 class='media-heading'>Alex Weissman</h4>
        Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors...
      </div>
    </div>
    
    <div class='media'>
      <a class='media-left' href='#'>
        <img src='http://ww2.hdnux.com/photos/02/25/67/613833/3/gallery_thumb.jpg' alt='Rambo' width='64' height='64'>
      </a>
      <div class='media-body'>
        <h4 class='media-heading'>Sylvester Stallone</h4>
        Sylvester Gardenzio Stallone, nicknamed Sly Stallone, is an American actor, screenwriter and film director.  Stallone is well known for his Hollywood action roles...
      </div>
    </div>
    
    <div class='media'>
      <a class='media-left' href='#'>
        <img src='http://cdn.akamai.steamstatic.com/steamcommunity/public/images/avatars/d0/d0877f614b8bb52813a63915be4da611cfa0ac2e_medium.jpg' alt='John McClane' width='64' height='64'>
      </a>
      <div class='media-body'>
        <h4 class='media-heading'>Bruce Willis</h4>
        Walter Bruce Willis, better known as Bruce Willis, is an American actor, producer, and singer. His career began on the Off-Broadway stage and then in television in the 1980s, most notably as David Addison in Moonlighting...
      </div>
    </div>

</div>
```

> Ok, but can I load templates from files?

Of course, this is actually the preferred way.  The path to your template (relative to the root directory, `TEMPLATES_PATH`) is the optional second argument when you construct an HtmlBuilder object:

```
$hb = new HtmlBuilder($content, "path/to/template.html");
```

> Alright, I can see how this is useful. But I'm not really an object-oriented guy/gal/unicorn.  Do I really have to create a separate object for every single component of my web page?

Well, it's not a bad idea, and it'll help you stay organized.  But if you really want, you can define child components directly in your content:

## Implicitly defined child components:

```
$jumbotron_template = "
    <div class='jumbotron'>
        <h1>{{heading}}</h1>
        {{body}}
    </div>";
    
$jumbotron_content = [
    "heading" => "Developers",
    "body" => [
        "@template" => $template,
        "@content" => [
            "img_src" => "http://avatars1.githubusercontent.com/u/5004534?v=3&s=400",
            "img_alt" => "Lord of the Fries",
            "heading" => "Alex Weissman",
            "body" => "Alex has many years of experience in software development, including web development using MySQL, PHP, and Javascript frameworks including jQuery and Twitter Bootstrap. Alex maintains the frontend website for Bloomington Tutors..."            
        ]
    ]
];

$jumbotron = new HtmlBuilder($jumbotron_content);
$jumbotron->setTemplate($jumbotron_template);
echo $jumbotron->render();
```

You'll notice that we've used two special **directives**, `@template` and `@content`, to directly define a child component in the main "jumbotron" component.  When the parent `HtmlBuilder` is constructed, it will use the template supplied in `@template` and the content supplied in `@content` to automatically construct a child `HtmlBuilder` object.  You can also use the `@source` directive to pass in a path to a template file, instead of the template itself.

You can create arrays of content for a given template using the `@array` directive:

```
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
echo $jumbotron->render();
```

By using `@array` instead of `@content`, you're telling `HtmlBuilder` that the template should be applied to each subarray in the array assigned to `@array`.  The rendered content is then concatenated at render time.

> Speaking of **directives**, what are they?

## Directives

Well, we've already seen the `@template`, `@content`, `@source`, and `@array` directives.  So, you probably already figured it out.  But just in case, **directives** are members of a content array that are not rendered as literal placeholders in the template.  Instead, you use them to convey something special to `HtmlBuilder`.  For example, `@template` tells `HtmlBuilder` that we are passing in a template that should be applied to the contents of a corresponding `@content` or `@array` directive.

There are other directives for the special-purpose templating classes that come with Bootsole.  They allow you to access specific types of content such as the items in a `NavbarBuilder` or `NavDropdownBuilder`, or they signal special behavior such as the `@display` directive for `TableColumnBuilder` objects.

> What are these special-purpose classes, anyway?

Glad you asked - read on!

## Components

Bootsole comes with many classes, called **components**, that extend the basic `HtmlBuilder` class for additional functionality.

- PageBuilder
- NavbarBuilder
- NavComponentBuilder
- NavBuilder
- NavItemBuilder
- NavDropdownBuilder
- NavFormBuilder
- NavTextBuilder
- NavButtonBuilder
- NavLinkBuilder
- TableBuilder
- TableColumnBuilder


### PageBuilder


### NavbarBuilder


### TableBuilder

The `TableBuilder` class allows you to set templates for each column of a table, via the `@columns` directive.  The content of the table is then supplied with an array of `HtmlBuilder` objects via the `@rows` directive.  The column templates will then be used to render each data row.

The default table template, `templates/tables/table-tablesorter-default`, uses the [tablesorter](https://mottie.github.io/tablesorter/docs/) jQuery library to render dynamic, sortable, filterable, paginated tables.

A `TableBuilder` object is constructed as follows:

```
$content = [
    "@columns" => [...]
    "@rows" => [...]
];

```

Each member of the `@columns` array is a `TableColumnBuilder` object.  A `TableColumnBuilder` object is a special templating class that renders the column header, but also maintains information about their corresponding cells in each row.  It can be declared in the same way as any other `HtmlBuilder` object:

```
$column_content = [
    'label' => 'Students',
    '@sorter' => 'metanum',
    '@sort_field' => 'num_students',
    '@intial_sort_direction' => 'asc',
    '@cell_template' => "<a class='btn btn-success' href='students.php?teacher_id={{teacher_id}}'>{{num_students}}</a>",
    '@empty_field' => 'num_students',
    '@empty_value' => '0',
    '@empty_template' => "Zero"
];

$column_template = "<h4>{{label}}</h4>";

$column = new TableColumnBuilder($column_content);
$column->setTemplate($column_template);

```

Thus when `$column->render()` is called, the template `<h4>{{label}}</h4>` will be rendered with the supplied value of `label`.  To set a template for the actual cell content of this column, use the `@cell_template` directive.  When the parent `TableBuilder` object is rendered, it will attempt to replace the placeholders in `@cell_template` with the corresponding values in each row as specified by the `@rows` directive.  In this way, `TableBuilder` can construct the entire table.

You can also specify an alternative template to use when a certain field in a row matches a certain value.  This is useful when you want a cell to render differently when a value is 0 or empty.  This is done using the `@empty_template`, `@empty_field`, and `@empty_value` directives.  When a row has a field, as specified by `@empty_field`, whose value is equal to `@empty_value`, the `@empty_template` will be used instead of the usual `@cell_template` template.

For tablesorter tables, you can also specify sorting rules, using the `@sorter` and `@sort_field` directives.  The field specified in `@sort_field` specifies which row field to sort on, and the `@sorter` specifies the sorting rule to be used.  Use `metatext` for sorting alphabetically, and `metanum` to sort numerically.  One easy way to sort by time is to use the `metanum` sorter in conjunction with a UTC timestamp as the `@sort_field`.  Note that the field specified in `@sort_field` does not actually need to be rendered by the templates.

To set an initial sort direction, set the `@initial_sort_direction` directive to `asc` or `desc`.  This will cause the table to be automatically sorted on the column in the specified direction upon page load.

You can of course define `@columns` and `@rows` directly in the table content array, without explicitly creating `TableColumnBuilder` and `HtmlBuilder` objects:

```

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

```

**Output:**

![TableBuilder](/screenshots/bootsole-example-4.png "TableBuilder")


## Changelog

### 0.2.0

- Brand spanking new system based on base `HtmlBuilder` class.  New `PageBuilder`, `NavbarBuilder`, and a whole mess of other special-purpose templating classes!

### 0.1.4

- Switched over to BootstrapValidator and began implementing validation schema

### 0.1.3

- Added the 'selectTime' and 'hidden' input types.
- Added placeholders, data-* fields for options in select2 fields.

### 0.1.2

- Added the 'select2' input type.
- Added table menu item options 'type', 'show_field', and 'show_field_values'.
- Added 'addon_end' for input and password fields.

## Dependencies

### PHP
- 5.4+

### Javascript/CSS (included in this repository)
- jQuery 1.10.2
- Bootstrap 3.3.1
- Tablesorter 2.17.7 with the pager and filter widgets
- BootstrapValidator v0.5.1
- FontAwesome 4.1
- Bootstrap Switch 3
- Select2 3.5.1
- Bootstrapradio 0.1
