## Magic fields

Some templating classes have "magic" fields, which should not be directly set by the developer.  Rather, they are created automatically by the template class during rendering.  Magic fields are denoted by an underscore `_` in front of the field name.  The NavBuilder class, for example, has the `_items` field.  The NavBuilder class will automatically construct the HTML for this field based on the items specified in the constructor, along with other information such as the currently selected item.

## Field scope

Content fields, by default, have global scope - they will be applied to fields directly in the template, as well as in any child templates.  The exception to this is object child templates, which are considered "self-contained".  Fields in a parent template can be overridden in child templates by passing the new value directly into the child's content.

## Directives

Some templating classes have special directives that can be passed in along with their content.  These are metafields that are not directly rendered.  Instead, they represent data that helps the class construct or render the object.  The root class, `HtmlBuilder`, has the following directives:

- `@source`: A path to a template file to be loaded for a child template.
- `@template`: A template, as a string, to be loaded for a child template.
- `@content`: An array containing the contents of a child template.
- `@array`: An array of subarrays, each containing contents that should be rendered with the child template.  The results will then be concatenated.

The `TableBuilder` class also has the following directives:

- `@columns`
- `@rows`

The `TableColumnBuilder` class has the directives:

- `@cell_template`
- `@empty_field`
- `@empty_value`
- `@empty_template`
