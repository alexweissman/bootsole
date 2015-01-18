<?php

/* Builds a form from a template, using the following magic fields:
    @layout
    @action
    @method
    @label_width
    @components
    @values

*/


class FormBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;

    protected $_components = [];        // An array of FormComponentBuilder objects
    protected $_values = [];            // An array of strings mapping field names to their values
    protected $_layout= "vertical";     // layout: horizontal, inline, or vertical
    protected $_action = "";            // The url that this form should post/get
    protected $_method = "post";        // "post" or "get"
    protected $_label_width = "4";      // The width of form group labels, for horizontal forms

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default form template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "forms/form-philosophers.html", $options);
            
        // Check if @components has been specified
        if (isset($content['@components'])){
            foreach ($content['@components'] as $name => $component){
                $this->_components[$name] = $this->parseComponent($name, $component);
            }
        }
        // Check if @values has been specified
        if (isset($content['@values'])){
            $this->_values = $content['@values'];
        }
        
        // Set @layout if specified
        if (isset($content['@layout'])){
            $this->layout($content['@layout']);
        }
        
        // Set @action if specified
        if (isset($content['@action'])){
            $this->action($content['@action']);
        }

        // Set @method if specified
        if (isset($content['@method'])){
            $this->method($content['@method']);
        }
        
        // Set @label_width if specified
        if (isset($content['@label_width'])){
            $this->label_width($content['@label_width']);
        } 
    }

    // Clone components
    public function __clone() {
        foreach ($this->_components as $name => $component){
            $this->_components[$name] = clone $component;
        }
    }
    
    
    public function layout($content){
        switch($content){
            case "horizontal":  
            case "inline":      
            case "vertical":    $this->_layout = $content; break;
            default:            throw new Exception("layout must be 'horizontal', 'vertical', or 'inline'.");
        }
        
        // Update layout of all FormGroupBuilder components
        foreach ($this->_components as $component){
            if (is_a($component, "FormGroupBuilder"))
                $component->layout($content);
        }
    }
    
    public function action($content){
        $this->_action = $content;           
    }
    
    public function method($content){
        switch($content){
            case "get":  
            case "post":      $this->_method = $content; break;
            default:          throw new Exception("method must be 'get' or 'post'.");
        }            
    }

    public function value($name, $value){
        if (!isset($this->_fields[$name]))
            throw new Exception("There is no field with name '$name'!");
        $this->_values[$name] = $value;
    }
    
    public function label_width($content){
        $this->_label_width = $content;
        
        // Update label width of all FormGroupBuilder components
        foreach ($this->_components as $component){
            if (is_a($component, "FormGroupBuilder"))
                $component->label_width($content);
        }
    }

    public function getComponent($name){
        if (isset($this->_components[$name])){
            return $this->_components[$name];
        }
        else
            throw new Exception("There is no component with name '$name'!");    
    }
    
    public function render(){
        $this->setContent("_action", $this->_action);
        $this->setContent("_method", $this->_method);
        
        switch($this->_layout){
            case "horizontal":  $this->setContent("_layout", "form-horizontal"); break;
            case "inline":      $this->setContent("_layout", "form-inline"); break;
            case "vertical":    $this->setContent("_layout", ""); break;
            default:            throw new Exception("layout must be 'horizontal', 'vertical', or 'inline'.");
        }          
    
        // Set value of each component
        foreach ($this->_components as $name => $component){
            if (isset($this->_values[$name])){
                $component->value($this->_values[$name]);
            }
            $this->setContent($name, $component);
        }
        return parent::render();
    }
    
    private function parseComponent($name, $content){
        if (is_array($content)){           
            // If the content specifies a "@type" field, create the corresponding FormFieldBuilder object and wrap it in the FormGroupBuilder object
            if (isset($content['@type'])){
                // Create a FormGroup, unless @group is set to false or we have a hidden field
                if ((isset($content['@group']) && $content['@group'] == "false") || $content['@type'] == 'hidden') {
                    $component = FormFieldBuilder::generate($content['@type'], $content);
                    $component->name($name);                 
                } else {
                    $component = new FormGroupBuilder($content);
                    $component->field($content);
                    $component->layout($this->_layout);              // Should be overridable?
                    $component->label_width($this->_label_width);    // Should be overridable?
                    $component->name($name);
                }
                return $component;
            } else
                throw new Exception("FormBuilder components defined as arrays must specify a '@type' field.");
        } else if (is_a($content, "FormGroupBuilder")){
            // Push down form properties
            $content->name($name);
            $content->layout($this->_layout);             // Should be overridable?
            $content->label_width($this->_label_width);    // Should be overridable?
            return $content;                               // FormGroupBuilder passed in
        } else if (is_a($content, "FormFieldBuilder")){
            $content->name($name);
            return $content;
        } else
            throw new Exception("Invalid component type for this form.");
    }
}

/* An abstract class representing a form component.  Uses the following magic fields:
   @name
   @value
   @default
*/

abstract class FormComponentBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;
    
    protected $_name;                // The name of the field.
    protected $_value;          // The value of the field (optional).
    protected $_default = "";        // The default value of the field, if none is specified (optional).

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default component template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, null, $options);
            
        // Set @name if specified
        if (isset($content['@name'])){
            $this->name($content['@name']);
        }        

        // Set @value if specified
        if (isset($content['@value'])){
            $this->value($content['@value']);
        }

        // Set @default if specified
        if (isset($content['@default'])){
            $this->default_value($content['@default']);
        }        
    }
    
    public function name($content){
        $this->_name = $content;
    }
    
    public function value($content){
        $this->_value = $content;
    }

    public function default_value($content){
        $this->_default = $content;
    }    
    
    public function render(){
        $this->setContent("_classes", $this->renderCssClasses());
        $this->setContent("_data", $this->renderDataAttributes());
        return parent::render();
    }
}


/* Builds a form group.  Magic fields:
   @type
   @label
   @display
   @layout
   @label_width
*/

class FormGroupBuilder extends FormComponentBuilder {
    protected $_field = "";             // The field in this group.  Must be a FormFieldBuilder object.
    protected $_label = "";             // Label for this group.
    protected $_display = "";           // Display mode for this group.  Should be "show", "hidden", "readonly", or "disabled".
    protected $_layout = "vertical";    // Layout for this group.  Should be "horizontal", "inline", or "vertical".
    protected $_label_width = "4";      // The width of the form group label, for horizontal forms
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default FormGroup template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <div class='form-group {{_hidden}} {{_classes}} {{_data}}'>
                <label class='control-label {{_sr_only}} {{_col_label}}'>{{_label}}</label>
                {{_field}}
            </div>");
        }
        
        // Use the content to directly construct the field
        $this->field($content);
        
        // Set @layout if specified
        if (isset($content['@layout'])){
            $this->layout($content['@layout']);
        }
        
        // Set @display if specified
        if (isset($content['@display'])){
            $this->display($content['@display']);
        }
        
        // Set @label_width if specified
        if (isset($content['@label_width'])){
            $this->label_width($content['@label_width']);
        }

        // Set @label if specified
        if (isset($content['@label'])){
            $this->label($content['@label']);
        }        
    }
    
    // Clone field, if it exists
    public function __clone(){
        if (is_object($this->_field)) {
            $this->_field = clone $this->_field;
        }
    }
    
    public function display($content){
        switch($content){
            case "show":        
            case "hidden":
            case "readonly":    
            case "disabled":    $this->_display = $content; break;
            default:            throw new Exception("display must be 'show', 'hidden', 'readonly', or 'disabled'.");
        }
        
        // Pass display mode on to field
        if (isset($this->_field))
            $this->_field->display($content);    
        
    }

    public function layout($content){
    
        switch($content){
            case "horizontal":
            case "vertical":
            case "inline":      $this->_layout = $content; break; 
            default:            throw new Exception("layout must be 'horizontal', 'vertical', or 'inline'.");
        }
    }    

    public function label_width($content){
        $this->_label_width = $content;           
    }

    public function label($content){
        $this->_label = $content;           
    }
    
    public function field($content){
        $this->_field = $this->parseField($content);
    }
   
    // Set the group's field's name, rather than the group itself
    public function name($name){
        if (is_a($this->_field, "FormFieldBuilder"))
            $this->_field->name($name);
        else
            throw new Exception("Field must be initialized before setting its name.");
    }

    // Set the group's field's value, rather than the group itself
    public function value($content){
        $this->_field->value($content);
    }      

    public function getField(){
        return $this->_field;
    }
    
    public function render(){
        // Set label
        $this->setContent("_label", $this->_label);
        
        // For 'hidden' fields, disable their inputs so they won't be submitted
        if ($this->_display == "hidden") {
            $this->setContent("_hidden", "hidden");
            $this->_field->display("disabled");
        }
        else
            $this->setContent("_hidden", "");
            
        switch($this->_layout){
            case "horizontal":
                $this->setContent("_sr_only", "");
                $this->setContent("_col_label", "col-sm-{$this->_label_width}");
                $field_width = 12 - (int)$this->_label_width;
                $this->setContent("_field", "<div class='col-sm-$field_width'>{$this->_field->render()}</div>");
                break;
                
            case "vertical":
                $this->setContent("_sr_only", "");
                $this->setContent("_col_label", "");
                $this->setContent("_field", $this->_field);
                break;
            
            case "inline":
                $this->setContent("_sr_only", "sr-only");
                $this->setContent("_col_label", "");
                $this->setContent("_field", $this->_field); 
                break; 
        }  
        
        return parent::render();
    }
    
    private function parseField($content){
        if (is_a($content, "FormFieldBuilder")){
            return $content;                               // FormFieldBuilder passed in
        } else {
            // If the content specifies a "@type" field, create the corresponding subtype of FormFieldBuilder object
            if (isset($content['@type'])){
                $field = FormFieldBuilder::generate($content['@type'], $content);
                return $field;
            } else
                throw new Exception("Must specify a '@type' field.");
        }  
    }
}

/* An abstract class representing a form field.
*/   

abstract class FormFieldBuilder extends FormComponentBuilder {
    protected $_placeholder = "";    // The placeholder for the field (optional).
    protected $_validator = "";      // A string containing formvalidator validation rules.
    protected $_display = "show";    // Can be "disabled", "readonly", or "show".
    
    public static function generate($type, $content){
        // Pass along any @template or @source templates
        $source = null;
        if (isset($content['@source']))
            $source = $content['@source'];
        
        switch ($type){
            case "text":        $field = new FormTextFieldBuilder($content, $source);       break;
            case "password":    $field = new FormPasswordFieldBuilder($content, $source);   break;
            case "number":      $field = new FormNumberFieldBuilder($content, $source);     break;
            case "email":       $field = new FormEmailFieldBuilder($content, $source);      break;
            case "url":         $field = new FormUrlFieldBuilder($content, $source);        break;
            case "color":       $field = new FormColorFieldBuilder($content, $source);      break;
            case "search":      $field = new FormSearchFieldBuilder($content, $source);     break;
            case "select":      $field = new FormSelectFieldBuilder($content, $source);     break;
            case "select2":     $field = new FormSelect2FieldBuilder($content, $source);    break;
            case "selecttime":  $field = new FormSelectTimeFieldBuilder($content, $source); break;
            case "hidden":      $field = new FormHiddenFieldBuilder($content, $source);     break;            
            case "textarea":    $field = new FormTextAreaFieldBuilder($content, $source);   break;
            default:            throw new Exception("Unknown form field type '$type'.");
        }
        // Set a template, if specified
        if (isset($content['@template'])) {
            $field->setTemplate($content['@template']);
        }
        return $field;
    }
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, null, $options);
            
        // Set @placeholder if specified
        if (isset($content['@placeholder'])){
            $this->placeholder($content['@placeholder']);
        } 

        // Set @validator if specified
        if (isset($content['@validator'])){
            $this->validator($content['@validator']);
        }         
    
        // Set @display if specified
        if (isset($content['@display'])){
            $this->display($content['@display']);
        }

        // Set @prepend if specified
        if (isset($content['@prepend'])){
            if (method_exists($this, 'prepend'))
                $this->prepend($content['@prepend']);
            else {
                $class = get_class($this);
                throw new Exception("Form fields of type $class do not support addons.");
            }
        }
        
        // Set @append if specified
        if (isset($content['@append'])){
            if (method_exists($this, 'append'))
                $this->append($content['@append']);
            else {
                $class = get_class($this);
                throw new Exception("Form fields of type $class do not support addons.");
            }
        }        
    }
    
    public function placeholder($content){
        $this->_placeholder = htmlspecialchars($content, ENT_QUOTES, false);
    }    

    public function validator($content){
        $this->_validator = htmlspecialchars($content, ENT_QUOTES, false);
    }

    public function display($content){
        $this->_display = $content;
    }
    
    public function render(){
        $this->_content['_name'] =          $this->_name;
        if ($this->_value)
            $this->_content['_value'] =     $this->_value;
        else
            $this->_content['_value'] =     $this->_default;
        $this->_content['_placeholder'] =   $this->_placeholder;
        $this->_content['_validator'] =     $this->_validator;
        $this->_content['_name'] =          $this->_name;
        if ($this->_display == 'readonly' || $this->_display == 'disabled')
            $this->_content['_display'] = $this->_display;
        else
            $this->_content['_display'] = "";
            
        return parent::render();
    }

}

/* Represents a simple text field.  Derived classes include:

   * text
   * password
   * number (integer)
   * email
   * url
   * color
   * search

    In general, this class should be used for fields that involve user-supplied text
   
*/

class FormTextFieldBuilder extends FormFieldBuilder {
    
    use FormFieldAddonable;    // All FormTextFieldBuilder objects can use input group addons
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <input type='{{_text_type}}' class='form-control {{_classes}}' name='{{_name}}' autocomplete='off' value='{{_value}}' placeholder='{{_placeholder}}' {{_validator}} {{_data}} {{_display}}>");
            $this->setContent("_text_type", "text");
        }

    }            

}

/* Represents a password field.
*/

class FormPasswordFieldBuilder extends FormTextFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setContent("_text_type", "password");
        }
    }           
}

/* Represents an HTML5 number (integer) field.
*/

class FormNumberFieldBuilder extends FormTextFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setContent("_text_type", "number");
        }
    }           
}

/* Represents an email field.
*/

class FormEmailFieldBuilder extends FormTextFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setContent("_text_type", "email");
        }
    }           
}

/* Represents an HTML5 url field.
*/

class FormUrlFieldBuilder extends FormTextFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setContent("_text_type", "url");
        }
    }           
}

/* Represents an HTML5 color field.
*/

class FormColorFieldBuilder extends FormTextFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setContent("_text_type", "color");
        }
    }           
}

/* Represents an HTML5 search field.
*/

class FormSearchFieldBuilder extends FormTextFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setContent("_text_type", "search");
        }
    }           
}

/* Represents a select field.

   * select
   * select2
   * selecttime (select2 with time increments)
   * toggle
*/

class FormSelectFieldBuilder extends FormFieldBuilder {
    protected $_multiple = "";   // Set to "multiple" if it is possible to have multiple selected items.
    
    use FormFieldSelectable, FormFieldAddonable {
        render as renderInputGroup;        // Can select options with this one
    }
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("<select class='form-control {{_classes}}' name='{{_name}}' autocomplete='off' {{_multiple}} placeholder='{{_placeholder}}' {{_validator}} {{_data}} {{_display}}>{{_items}}</select>");
        }
        
        if (isset($content['@items']))
            $this->items($content['@items']);

        if (isset($content['@multiple']))
            $this->_multiple = $content['@multiple'];            
    }
    
    // Select elements do not have a value attribute.  So, we override and select one of the child options. 
    public function value($content){
        // If an array, automatically override the 'multiple' attribute
        if (is_array($content))
            $this->_multiple = "multiple";

        $this->unselectItems();
        $this->selectItems($content);        // Calling from trait FormFieldSelectable's
    }
    
    public function render(){
        $items = "";
    
        $this->setContent("_multiple", $this->_multiple);
    
        // Select default item(s), if nothing is selected
        if (count($this->getSelectedItems()) <= 0){
            $this->selectItem($this->_default);     // Will only work if name = value for the option
        }
    
        // Insert empty dummy item if placeholder is set
        if ($this->_placeholder)
            $items .= "<option></option>";
    
        // Set the items
        $items .= $this->renderItems();
        
        $this->setContent("_items", $items);
    
        return $this->renderInputGroup();       // Call trait FormFieldAddonable's render() function instead of parent's function
    }
    
}

/* Represents a select2 field.
*/
class FormSelect2FieldBuilder extends FormSelectFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->cssClass("select2");
        }
    }
}

/*  Represents a select2 field, prepopulated with times at preset intervals.  Uses the following magic fields:
    @time_start
    @time_end
    @time_increment
    
*/

class FormSelectTimeFieldBuilder extends FormSelectFieldBuilder {
    
    protected $_time_start = "12:00 am";
    protected $_time_end = "11:30 pm";
    protected $_time_increment = "15";
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->cssClass("select2");
        }
        
        if (isset($content['@time_start']))
            $this->time_start($content['@time_start']);    
    
        if (isset($content['@time_end']))
            $this->time_end($content['@time_end']);
    
        if (isset($content['@time_increment']))
            $this->time_increment($content['@time_increment']);
            
        $this->generateTimes();
    }
    
    public function time_start($content){
        $this->_time_start = $content;
    }

    public function time_end($content){
        $this->_time_end = $content;
    }
    
    public function time_increment($content){
        $this->_time_increment = $content;
    }
    
    // This will (re)generate the time choices
    public function generateTimes(){
        // Compute time range
        $range = range(strtotime($this->_time_start),strtotime($this->_time_end),$this->_time_increment*60);
        // Populate items
        $this->unselectItems();
        $items = [];
        foreach($range as $time){
            $time_val = date("g:i a",$time);
            $content = [
                "@value" => $time_val,
                "@label" => $time_val
            ];
            $items[$time_val] = new FormSelectItemBuilder($content, null);
        }
        $this->items($items);    
    }
}


/*
  Represents an item in a 'select' field
*/

class FormSelectItemBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;

    protected $_value;          // The value of this item (required).  Not to be confused with the *selected* value.
    protected $_label;          // The label to display for this item (set to value by default)
    protected $_selected = "";  // Whether or not this option is selected (set to 'selected')
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("<option class='{{_classes}}' value='{{_value}}' {{_data}} {{_selected}}>{{_label}}</option>");
        }
        
        if (isset($content['@value'])){
            $this->value($content['@value']);
        }
        
        if (isset($content['@label'])){
            $this->label($content['@label']);
        }
        
        if (isset($content['@selected'])){
            $this->selected($content['@selected']);
        }
        
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }
    }    

    public function value($content){
        $this->_value = $content;
    }
    
    public function label($content){
        $this->_label = $content;
    }

    public function selected($content){
        switch($content){
            case "selected" :
            case "" : $this->_selected = $content; break;
            default: throw new Exception("'selected' must be either 'selected' or ''.");
        }
    }
    
    public function getValue(){
        return $this->_value;
    }
    
    public function getSelected(){
        return $this->_selected;
    }
    
    public function render(){
        if (!$this->_value)
            throw new Exception("'value' not set in " . get_class($this));
        else
            $this->setContent('_value', $this->_value);
        if (!$this->_label)
            $this->_label = $this->_value;
        $this->setContent('_label', $this->_label);
        $this->setContent('_selected', $this->_selected);
        $this->setContent('_data', $this->renderDataAttributes());
        return parent::render();
    }
}


/*
Permitted field types:
   
   (No input groups)
   * hidden
   * textarea
   * switch (bootstrap-switch)
   * radio
   * checkbox
   * bootstrapradiogroup
*/

/* Represents a hidden field.
*/

class FormHiddenFieldBuilder extends FormFieldBuilder {
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <input type='hidden' class='form-control {{_classes}}' name='{{_name}}' value='{{_value}}' {{_data}} {{_display}}>");
        }

    }            
}

/* Represents a textarea field.
*/

class FormTextAreaFieldBuilder extends FormFieldBuilder {
    protected $_rows = "";
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <textarea class='form-control {{_classes}}' name='{{_name}}' autocomplete='off' {{_multiple}} placeholder='{{_placeholder}}' {{_validator}} {{_data}} {{_display}} rows={{_rows}}>{{_value}}</textarea>");
        }

        if (isset($content['@rows']))
            $this->rows($content['@rows']);
    }
    
    public function rows($content){
        if (ctype_digit(strval($content)))
            $this->_rows = $content;
        else
            throw new Exception("'rows' must be an integer.");
        
        return $this;
    }
 
    public function render(){
        $this->setContent("_rows", $this->_rows);
        return parent::render();
    }
}

// Gives FormFieldBuilder objects the ability to be wrapped in input groups, with pre/append items
trait FormFieldAddonable {

    protected $_prepend;
    protected $_append;
        
    public function prepend($content){
        $this->_prepend = $content;
    }

    public function append($content){
        $this->_append = $content;
    }
    
    // Override normal field rendering, inserting the appropriate pre/append addons
    public function render(){
        if ($this->_prepend || $this->_append) {
            $result = "<div class='input-group'>";
            $result .= $this->_prepend;
            $result .= parent::render();
            $result .= $this->_append;
            $result .= "</div>";
            return $result;
        } else {
            return parent::render();
        }
    }    
}

// Gives FormFieldBuilder objects the ability to have selections
trait FormFieldSelectable {
    protected $_items = [];     // An array of FormSelectItemBuilder objects
    
    // Set the items from an array
    public function items($items){
        foreach($items as $name => $item){
            $this->_items[$name] = $this->parseItem($name, $item);
        }
    }
     
    // Set a given item as 'selected'   
    public function selectItem($name){
        if (isset($this->_items[$name]))
            $this->_items[$name]->selected("selected");
    }

    // Set a given item as not 'selected'   
    public function unselectItem($name){
        if (isset($this->_items[$name]))
            $this->_items[$name]->selected("");
    }

    // Select the item(s) corresponding to the given value(s)
    public function selectItems($content){
        foreach($this->_items as $name => $item){
            if (is_array($content)){
                if (in_array($item->getValue(), $content))
                    $item->selected("selected");
            } else {
                if ($item->getValue() == $content)
                    $item->selected("selected");             
            }
        }
    }
    
    public function getSelectedItems(){
        $results = [];
        foreach($this->_items as $item){
            if ($item->getSelected() == "selected")
                $results[] = $item;
        }
        return $results;     
    }
    
    // Unselect all items
    public function unselectItems(){
        foreach($this->_items as $item)
            $item->selected("");
    }    
    
    public function renderItems(){
        $result = "";
        foreach($this->_items as $name => $item){
            $result .= $item->render() . PHP_EOL;
        }
        return $result;
    }

    private function parseItem($name, $item){
        if (is_a($item, "FormSelectItemBuilder"))
            $result = $item;
        else
            $result = new FormSelectItemBuilder($item);
        
        if (!$result->getValue())
            $result->value($name);
    
        return $result;
    }
    
}


?>
