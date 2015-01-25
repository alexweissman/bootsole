# Bootsole 0.2.0

### By Alex Weissman

Copyright (c) 2015

A nestable, object-oriented templating engine for generating beautiful Bootstrap-themed pages, forms, tables, and other components in PHP.

## Installation

It is possible to install Bootsole via Composer, or as a standalone library.

### To install with composer:

1. If you haven't already, get [Composer](http://getcomposer.org/) and [install it](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) - preferably, globally.
2. Require Bootsole, either by running `php composer.phar require alexweissman/bootsole`, or by creating a `composer.json` file:

```
{
    "require": {
        "php": ">=5.4.0",
        "alexweissman/bootsole": "0.2.0"
    }
}
```

and running `composer install`.

3. Include the `vendor/autoload.php` file in your project.  For an example of how this can be done, see `public/site-config.php`.

Note: The core Bootsole library is contained entirely in `vendor/alexweissman/bootsole/bootsole/`.  You don't need the `public` directory - it just contains examples for how Bootsole can be used in a PHP project.

### To install manually:

Copy the `bootsole/` subdirectory to wherever you usually keep your site's non-public resources.  If you don't know what "non-public resources" means, see [Organize Your Next PHP Project the Right Way](http://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873).

If you want to run the premade examples, you can copy the contents of `public` to your site's public directory.

Without Composer, you will need to manually include Bootsole's source files.  The `public/config-site.php` file will do this automatically for you - feel free to move that code to your project's main config file.

## Configuration

Bootsole relies on a number of predefined constants to properly find and render templates, JS and CSS includes, etc.  You can find these in the `bootsole/config-bootsole.php` file.  Most of the default values should work out of the box, except for the following:

**`PATH_PUBLIC_ROOT`**

This is the local (file) path to the public directory of your site.  It is recommended that you declare it relative to the location of your `config-bootsole.php` file.  For example, if your directory structure looks like this:

```
- public_html/               // This is where we want PATH_PUBLIC_ROOT to point
  - js/
  - css/
  - <public-facing content>
- resources/
  - bootsole/
    - config-bootsole.php    // This is where PATH_PUBLIC_ROOT is defined
    - ...
  - <other libraries>
```

you could set `PATH_PUBLIC_ROOT` as:
`define ("Bootsole\PATH_PUBLIC_ROOT", realpath(dirname(__FILE__) . "/../../public_html") . "/");`

**`URI_PUBLIC_ROOT`**

As you should know, file paths != URL paths (though there is often a strong relationship between them, especially if you aren't using a URL routing system).  So, Bootsole needs to know what the public URL will be for your site.

For a development environment, this might be something like:
`http://localhost/myproject/`.

For a production environment, this might look like:
`https://mysite.com/`.

## Basic usage

If you have autoloaded the library with Composer, all you should need is:

```
<?php

require_once "path/to/autoload.php";
use \Bootsole as BS;

...
```  

Otherwise, you will need include the files manually, in the correct order.  See `public/config-site.php` for an example of how this is done.

Then, you can start defining and deploying templates:

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

use \Bootsole as BS;

$hb = new BS\HtmlBuilder($content);
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

$jumbotron = new BS\HtmlBuilder($jumbotron_content);
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
$hb2 = new BS\HtmlBuilder([
    "img_src" => "http://ww2.hdnux.com/photos/02/25/67/613833/3/gallery_thumb.jpg",
    "img_alt" => "Rambo",
    "heading" => "Sylvester Stallone",
    "body" => "Sylvester Gardenzio Stallone, nicknamed Sly Stallone, is an American actor, screenwriter and film director.  Stallone is well known for his Hollywood action roles..."
]);
$hb2->setTemplate($template);

$hb3 = new BS\HtmlBuilder([
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

$jumbotron = new BS\HtmlBuilder($jumbotron_content);
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

Of course, this is actually the preferred way.  The path to your template (relative to the root directory, `PATH_TEMPLATES`) is the optional second argument when you construct an HtmlBuilder object:

```
$hb = new BS\HtmlBuilder($content, "path/to/template.html");
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

$jumbotron = new BS\HtmlBuilder($jumbotron_content);
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

$jumbotron = new BS\HtmlBuilder($jumbotron_content);
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

- [HtmlAttributesBuilder](#htmlattributesbuilder)
- [PageBuilder](#pagebuilder)
- PageHeaderBuilder
- PageFooterBuilder
- [NavbarBuilder](#navbarbuilder)
- NavComponentBuilder
- [NavBuilder](#navbuilder)
- [NavItemBuilder](#navitembuilder-and-navdropdownbuilder)
- [NavDropdownBuilder](#navitembuilder-and-navdropdownbuilder)
- NavFormBuilder
- NavTextBuilder
- NavButtonBuilder
- NavLinkBuilder
- [TableBuilder](#tablebuilder)
- TableColumnBuilder

### HtmlAttributesBuilder

Many components have a top-level HTML tag, such as `table`, `form`, `button`, etc.  You can add CSS classes and `data-*` attributes to these components using the following directives:

- `@css_classes`: An array of CSS class names to be applied to the component (usually the top-level tag of the component, but may be applied to other components that are more typically styled.  See source code for the component.)
- `@data`: A hash of `data-*` attributes, mapping `<name> => <value>`.  For example, `[ "id" => "1" ]` will create a data attribute `data-id=1` for the component.

### PageBuilder

The `PageBuilder` class builds a fully renderable HTML document.  This will usually be your "top-level" templating object when developing websites and web applications.  A `PageBuilder` object, in addition to the user-defined placeholders, accepts four directives: `@name`, `@schema`, `@header`, and `@footer`.

#### `@name`
Every page must have a unique name.  This is used to reference the page, for example, when using the `PageSchema` class to automatically include relevant CSS and JS files.

#### `@schema`
The `@schema` directive takes a path to a JSON schema file.  The schema file for pages consists of groups of pages, called **manifests**, and a corresponding list of paths to javascript and css files (relative to the `PATH_JS_ROOT` and `PATH_CSS_ROOT` directories):

```
{
    "terran" : {
        "pages": [
            "marine",
            "firebat",
            "ghost"
        ],
        "css": [
            "bootstrap-3.3.1.css",
            "font-awesome.min.css",
            "terran.css"
        ],
        "js": [
            "jquery-1.10.2.min.js",
            "bootstrap-3.3.1.js"
            ],
        "min_css": "min/terran.min.css",
        "min_js": "min/terran.min.js"    
    },
    "zerg" : {
        "pages": [
            "zergling",
            "hydralisk",
            "ultralisk"
        ],
        "css": [
            "bootstrap-3.3.1.css",
            "font-awesome.min.css",
            "zerg.css"
        ],
        "js": [
            "jquery-1.10.2.min.js",
            "bootstrap-3.3.1.js"
            ],
        "min_css": "min/zerg.min.css",
        "min_js": "min/zerg.min.js"    
    },    
    "default" : {
        "pages": [
            "about",
            "contact"
        ],
        "css": [
            "bootstrap-3.3.1.css",
            "font-awesome.min.css"
        ],
        "js": [
            "jquery-1.10.2.min.js",
            "bootstrap-3.3.1.js"     
            ],
        "min_css": "min/default.min.css",
        "min_js": "min/default.min.js"
    }
}

```

When a `PageBuilder` object is rendered, the schema file will be searched for a manifest group that matches the page's `name`.  If found, it will automatically include the specified CSS and JS files into the `<head>` and `<footer>` elements, respectively.  If a manifest is not found, the `default` manifest will be used.  The default schema file is in `schema/pages/pages.json`.

Schemas can also be used to quickly toggle minified/merged Javascript and CSS for production environments.  When the global constant `JS_DEV` in `bootsole/config-bootsole.php` is set to `false`, the single javascript file specified in `min_js` will be used instead of the files specified in `js`.  Likewise, the CSS file specified in `min_css` will be used if `CSS_DEV` is set to false.  To automatically generate the minified CSS and JS for each manifest group, use the script provided in `build/build.php`.  **The build script should only be used on the development server**, immediately before pushing to the production server.  This script requires write access to the directories in which the minified JS and CSS files are to be written; consult your server manual for more information on setting directory permissions for PHP.

For more information about minified Javascript and CSS, see [Minification](https://en.wikipedia.org/wiki/Minification_%28programming%29#Web_development).

#### `@header`

Objects of type `PageHeaderBuilder` are used to represent the `<head>` block of a page.  `PageBuilder` will look for a special placeholder in the page template, `_header`, to insert the rendered header.  **Therefore, any custom templates you write for `PageBuilder` should have a `_header` placeholder!**

`PageHeaderBuilder` objects themselves have one directive, `@css_includes`, which represent the manifest group to be used in rendering the CSS.  Typically there is no need to specify this directive explicitly - `PageBuilder` will do it automatically for you when it constructs the page! 

#### `@footer`

Objects of type `PageFooterBuilder` are used to represent the `<footer>` block of a page.  `PageBuilder` will look for a special placeholder in the page template, `_footer`, to insert the rendered footer.

They have one directive, `@js_includes`, which represent the manifest group to be used in rendering the JS.  Typically there is no need to specify this directive explicitly - `PageBuilder` will do it automatically for you when it constructs the page! 

#### Example

**pages/page-default.html**
```
<!DOCTYPE html>
<html lang="en">
    {{_header}}
    <body>
        <div class="container" role="main">
            <h1>{{heading_main}}</h1>
            <p>{{content}}</p>
        </div>
        {{_footer}}
    </body>    
</html>
```
...

```
require_once("config-site.php");

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Simple, nested templating for rendering Bootstrap themed pages with PHP",
    "description" => "A sample page for Bootsole",
    "favicon_path" => BS\URI_PUBLIC_ROOT . "css/favicon.ico"
];

$content = [
    "@header" => $header_content,
    "@name" => "test",
    "heading_main" => "Welcome to Bootsole",
    "content" => "Hey, I'm the content!"
];

$pb = new BS\PageBuilder($content);
echo $pb->render();
```

**Output:**

![PageBuilder](/screenshots/bootsole-page-1.png "PageBuilder")

```
<!DOCTYPE html>
<html lang="en">
    <!-- Common header includes for all account pages -->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A sample page for Bootsole">
        <meta name="author" content="Alex Weissman">
        
        <title>Bootsole | Simple, nested templating for rendering Bootstrap themed pages with PHP</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="/bootsole/css/favicon.ico" />
        
        <link href='/bootsole/css/bootstrap-3.3.1.css' rel='stylesheet'>
        <link href='/bootsole/css/bootstrap-custom.css' rel='stylesheet'>
        <link href='/bootsole/css/font-awesome.min.css' rel='stylesheet'>
        <link href='/bootsole/css/tablesorter/theme.bootstrap.css' rel='stylesheet'>
        <link href='/bootsole/css/tablesorter/jquery.tablesorter.pager.css' rel='stylesheet'>
        <!-- Conditional formatting -->
    </head>
    <body> 
        <div class="container" role="main">
            <h1>Welcome to Bootsole</h1>
            <p>Hey, I'm the content!</p>
        </div>
        <footer>
            <div class="container">
              <p>
                  <a href="https://github.com/alexweissman/bootsole">Bootsole</a>, 2015
              </p>
            </div>
        </footer>
        <script src='/bootsole/js/jquery-1.10.2.min.js'></script>
        <script src='/bootsole/js/bootstrap-3.3.1.js'></script>
        <script src='/bootsole/js/bootsole.js'></script>
        <script src='/bootsole/js/tablesorter/jquery.tablesorter.min.js'></script>
        <script src='/bootsole/js/tablesorter/tables.js'></script>
        <script src='/bootsole/js/tablesorter/jquery.tablesorter.pager.min.js'></script>
        <script src='/bootsole/js/tablesorter/jquery.tablesorter.widgets.min.js'></script>
    </body>    
</html>
```

### NavbarBuilder

The `NavbarBuilder` class constructs a Bootstrap [navbar](http://getbootstrap.com/components/#navbar) for a page.  A navbar contains a list of components, specified by the `@components` directive:

```
$nb = new BS\NavbarBuilder([
    "brand_label" => "Bootsole is Great!",
    "brand_url" => "http://github.com/alexweissman/bootsole",
    "@components" => [
        "destroy" => [
            "@type" => "button",
            "@css_classes" => ["btn-danger"],
            "@label" => "Self-Destruct!"
        ],          
        // Implicitly declaring a NavBuilder object
        "main-menu" => [
            "@type" => "nav",
            "@align" => "right",
            "@items" => [
                "home" =>  [
                    "@active" => "active",
                    "@label" => "Home",
                    "@url" => BS\URI_PUBLIC_ROOT
                ],
                "about" => [
                    "@label" => "About",
                    "@url" => BS\URI_PUBLIC_ROOT. "about"
                ],
                "contact" => [
                    "@label" => "Contact",
                    "@url" => BS\URI_PUBLIC_ROOT. "contact"
                ]
            ]
        ]
    ]
]);
```
**Outputs:**

![NavbarBuilder](/screenshots/bootsole-navbar-1.png "NavbarBuilder")

```
<!-- Fixed navbar -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav-default" aria-expanded="false" aria-controls="main-nav-default">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
         <a class='navbar-brand' href='http://github.com/alexweissman/bootsole'>Bootsole is Great!</a>
        </div>
        <div id="main-nav-default" class="navbar-collapse collapse">
            <button type='button' class='btn btn-danger navbar-btn '>Self-Destruct!</button>
            <ul class='nav navbar-nav navbar-right'>
                <li class='active'><a href='/bootsole/'>Home</a></li>
                <li class=''><a href='/bootsole/about'>About</a></li>
                <li class=''><a href='/bootsole/contact'>Contact</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
```

The default navbar is a [fixed top navbar](http://getbootstrap.com/components/#navbar-fixed-top) specified in `templates/navs/main-nav-default`, but you may adapt this template to your own design needs.

####`@components`

`NavbarBuilder` currently supports the following types of Bootstrap components:

- [Navs](http://getbootstrap.com/components/#nav) via the `NavBuilder` class.
- [Forms](http://getbootstrap.com/components/#navbar-forms) via the `NavFormBuilder` class.
- [Buttons](http://getbootstrap.com/components/#navbar-buttons) via the `NavButtonBuilder` class
- [Text](http://getbootstrap.com/components/#navbar-text) via the `NavTextBuilder` class
- [Links](http://getbootstrap.com/components/#navbar-links) via the `NavLinkBuilder` class

Each component is built off of the abstract `NavComponentBuilder` class.  To declare a component implicitly, rather than explicitly defining `NavBuilder`, `NavFormBuilder`, etc objects, use an array containing the `@type` directive:

| @type  | object created   |
| ------ | ---------------- |
| nav    | NavBuilder       |
| form   | NavFormBuilder   |
| button | NavButtonBuilder |
| text   | NavTextBuilder   |
| link   | NavLinkBuilder   |

All `NavComponentBuilder` objects support the `@align` directive, which describes how the component will be aligned within the navbar.  Permitted values include `left`, `right`, and `inherit`.  All templates for classes derived from `NavComponentBuilder` should use a `_align` placeholder, where the corresponding component will try to place the CSS classes for alignment.

All `NavComponentBuilder` objects support the `@css_classes` and `@data` directives.

##### NavBuilder

`NavBuilder` builds a collection of navigation items, which will be structured as an unordered list.  The items are an array of `NavItemBuilder` objects, specified using the `@items` directive.  Each `NavItemBuilder` object may be declared explicitly, or you can implicitly define them in nested subarrays:

```
$navb = new BS\NavBuilder([
    "@align" => "right",
    "@items" => [
        "home" =>  [
            "@active" => "active",
            "@label" => "Home",
            "@url" => BS\URI_PUBLIC_ROOT
        ],
        "about" => [
            "@label" => "About",
            "@url" => BS\URI_PUBLIC_ROOT. "about"
        ],
        "contact" => [
            "@label" => "Contact",
            "@url" => BS\URI_PUBLIC_ROOT. "contact"
        ]
    ]
]);
```

**Output:**

```
<ul class='nav navbar-nav navbar-right'>
    <li class='active'><a href='/bootsole/'>Home</a></li>
    <li class=''><a href='/bootsole/about'>About</a></li>
    <li class=''><a href='/bootsole/contact'>Contact</a></li>
</ul>

```

Every item in a `NavBuilder` has a name.  This is automatically drawn from the keys in the `@items` array, or specified when you add the item via the `addItem($name, $content)` function.

Call the function `setActiveItem($name)` on a NavBuilder object to set the a nav item as active.  All other items in the nav will automatically become inactive, so that only one menu item will be active at any time.

Use the function `getItem($name)` to get an item by name. 

###### NavItemBuilder and NavDropdownBuilder

The items in a `NavBuilder` are represented using the `NavItemBuilder` and `NavDropdownBuilder` classes.

###### Directives

- `@active`: Setting `@active` to `active` will make the specified item be highlighted as active in the nav.  Optional.
- `@display`: Setting `@display` to `disabled` will make the specified item be disabled, while setting it to `hidden` will make it not show up at all.  Optional.

A `NavDropdownBuilder` builds a dropdown with submenu items.  It is called in the same way as `NavBuilder`, with an `@items` directive specifying the child `NavItemBuilder` objects.

##### NavFormBuilder

`NavFormBuilder` wraps a form in a simple `div` with the `navbar-form` class.  Bootstrap will automatically render the content as an inline form:

```
$navform = new BS\NavFormBuilder([
    "@align" => "right",
    "@form" => '<form role="search">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>*
        <button type="submit" class="btn btn-default">Submit</button>
    </form>'    
]);
```

**Output:**

```
<div class='navbar-form navbar-right'>
    <form role="search">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>*
        <button type="submit" class="btn btn-default">Submit</button>
    </form>
</div>
```

###### Directives

- `@form`: The form, as a string or a `FormBuilder` object.

##### NavButtonBuilder

`NavButtonBuilder` renders a button with the `navbar-btn` CSS class.  Bootstrap will automatically align the button in the navbar:

```
$navbtn = new BS\NavButtonBuilder([
    "@css_classes" => ["btn-danger"],
    "@label" => "Self-Destruct!"
]);
```

**Output:**

```
<button type='button' class='btn navbar-btn btn-danger '>Self-Destruct!</button>
```

###### Directives

- `@label`: The text for this button.  Required.

##### NavTextBuilder

`NavTextBuilder` wraps the specified text in a `<p>` tag with the `navbar-text` CSS class.  This formats it for display in the navbar:

```
$navtext = new BS\NavTextBuilder([
    "@text" => "Whazzup!!!"
]);
```

**Output:**

```
<p class='navbar-text '>Whazzup!!!</p>
```

###### Directives

- `@text`: The text for this item.  Required.

##### NavLinkBuilder

`NavLinkBuilder` creates a non-nav link in your navbar, using the `navbar-link` CSS class:

$navlink = new BS\NavLinkBuilder([
    "@label" => "UserFrosting",
    "@url" => "https://www.userfrosting.com"
]);

**Output:**

```
<p class='navbar-text '><a href='https://www.userfrosting.com' class='navbar-link'>UserFrosting</a></p>
```

####### Directives

- `@label`: The text for this link.  Required.
- `@url`  : The url for this link. Required.

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

$column = new BS\TableColumnBuilder($column_content);
$column->setTemplate($column_template);

```

Thus when `$column->render()` is called, the template `<h4>{{_label}}</h4>` will be rendered with the supplied value of the `@label` directive.  To set a template for the actual cell content of this column, use the `@cell_template` directive.  When the parent `TableBuilder` object is rendered, it will attempt to replace the placeholders in `@cell_template` with the corresponding values in each row as specified by the `@rows` directive.  In this way, `TableBuilder` can construct the entire table.

You can also specify an alternative template to use when a certain field in a row matches a certain value.  This is useful when you want a cell to render differently when a value is 0 or empty.  This is done using the `@empty_template`, `@empty_field`, and `@empty_value` directives.  When a row has a field, as specified by `@empty_field`, whose value is equal to `@empty_value`, the `@empty_template` will be used instead of the usual `@cell_template` template.

For tablesorter tables, you can also specify sorting rules, using the `@sorter` and `@sort_field` directives.  The field specified in `@sort_field` specifies which row field to sort on, and the `@sorter` specifies the sorting rule to be used.  Use `metatext` for sorting alphabetically, and `metanum` to sort numerically.  One easy way to sort by time is to use the `metanum` sorter in conjunction with a UTC timestamp as the `@sort_field`.  Note that the field specified in `@sort_field` does not actually need to be rendered by the templates.

To set an initial sort direction, set the `@initial_sort_direction` directive to `asc` or `desc`.  This will cause the table to be automatically sorted on the column in the specified direction upon page load.

You can of course define `@columns` and `@rows` directly in the table content array, without explicitly creating `TableColumnBuilder` and `HtmlBuilder` objects:

```

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

$table = new BS\TableBuilder($table_content);

```

**Output:**

![TableBuilder](/screenshots/bootsole-example-4.png "TableBuilder")

### FormBuilder

Coming soon!

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
- FormValidation v0.6.1
- FontAwesome 4.1
- Bootstrap Switch 3
- Select2 3.5.1
- Bootstrapradio 0.1
