<?php


require_once("config-site.php");

use Bootsole as BS;

// Declaring a menu item OOPishly
$home = new BS\MenuItemBuilder([
        "@label" => "Home",
        "@url" => BS\URI_PUBLIC_ROOT
    ]);

$nb = new BS\NavbarBuilder([
    "brand_label" => "Bootsole is Great!",
    "brand_url" => "http://github.com/alexweissman/bootsole",
    "@components" => [
        "btn1" => [
            "@type" => "button",
            "@css_classes" => ["btn-danger"],
            "@label" => "Self-Destruct!"
        ],
        "message" => [
            "@type" => "text",
            "@text" => "Whazzup!!!"
        ],
        "link" => [
            "@type" => "link",
            "@label" => "UserFrosting",
            "@url" => "https://www.userfrosting.com"
        ],
        "search-form" => [
            "@type" => "form",
            "@align" => "right",
            "@form" => "
                <form role='search'>
                    <div class='form-group'>
                      <input type='search' class='form-control' placeholder='Search'>
                    </div>*
                    <button type='submit' name='search' class='btn btn-default'>Submit</button>
                </form>"
        ],            
        // Declaring a nav group, mixed arrays and objects
        "main-menu" => [
            "@type" => "nav",
            "@align" => "right",
            "@items" => [
                "home" => $home,
                "about" => [
                    "@display" => "disabled",
                    "@label" => "About",
                    "@url" => BS\URI_PUBLIC_ROOT. "about"
                ],
                "courses" => [
                    "@active" => true,
                    "@label" => "Courses",
                    "@url" => BS\URI_PUBLIC_ROOT . "courses",
                    "@align" => "left",
                    "@items" => [
                        "algebra" => [
                            "@label" => "Algebra",
                            "@url" => BS\URI_PUBLIC_ROOT. "courses/algebra"
                        ],
                        "calculus" => [
                            "@label" => "Calculus",
                            "@url" => BS\URI_PUBLIC_ROOT. "courses/calculus"
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
            "@label" => "<i class='fa fa-paper-plane'></i>",
            "@url" => BS\URI_PUBLIC_ROOT.  "contact"
        ]
    ]
];

$nb->addComponent("side-menu", $new_component);

// ..and set an active item
//$nb->getComponent("main-menu")->setActiveItem("about");

$header_content = [
    "author" => "Alex Weissman",
    "site_title" => SITE_TITLE,
    "page_title" => "Simple, nested templating for rendering Bootstrap themed pages with PHP",
    "description" => "A sample page for Bootsole",
    "favicon_path" => BS\URI_PUBLIC_ROOT . "css/favicon.ico"
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

$pb = new BS\PageBuilder($content, "pages/page-jumbotron.html");
// ...or set it later!
//$pb->header($header);

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

$hb1 = new BS\HtmlBuilder($content);
$hb1->setTemplate($template);

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
//echo $jumbotron->render();


$pb->setContent("content", $jumbotron);

echo $pb->render();


?>
