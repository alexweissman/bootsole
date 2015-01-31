<?php

namespace Bootsole;

/**
 * Builds a collection of form components (FormComponentBuilder or FormComponentCollectionBuilder objects)
 */

class FormComponentCollectionBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;
    
    /**
     * @var mixed[] $_components      An array of FormComponentBuilder or FormComponentCollectionBuilder objects
     * @var string $_label_width      The width of form group labels, in columns (1-12), for horizontal forms
     * @var string $_layout           horizontal, inline, or vertical
     * @var string $_name             The name of the collection.  Will be used to populate the 'name' attribute for FormBuilder objects.
     * @var string[] $_validators     An array mapping field names to strings containing data-* FormValidation rules
     * @var string[] $_values         An array mapping field names to their initial values.
     */    
    
    protected $_components = [];    
    protected $_label_width = "4";  
    protected $_layout= "vertical";    
    protected $_name = "";     
    protected $_validators = [];     
    protected $_values = [];    

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default form template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, null, $options);              // No default template for this class.
                    
        // Check if @components has been specified
        if (isset($content['@components'])){
            foreach ($content['@components'] as $name => $component){
                $this->_components[$name] = $this->parseComponent($name, $component);
            }
        }
        
        // Set @name if specified
        if (isset($content['@name'])){
            $this->name($content['@name']);
        }
        
        // Check if @values has been specified
        if (isset($content['@values'])){
            $this->_values = $content['@values'];
        }
        
        // Set @layout if specified
        if (isset($content['@layout'])){
            $this->layout($content['@layout']);
        }
           
        // Set @label_width if specified
        if (isset($content['@label_width'])){
            $this->labelWidth($content['@label_width']);
        }

        // Set @validators if specified
        if (isset($content['@validators'])){
            $this->validators($content['@validators']);
        }        
        
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }        
    }

    // Clone components
    public function __clone() {
        foreach ($this->_components as $name => $component){
            // Clone array of elements or individual elements
            if (is_array($component)) {
                $this->_components[$name] = [];
                foreach($component as $sub_name => $subcomponent){
                    $this->_components[$name][$sub_name] = clone $subcomponent;
                }
            } else {
                $this->_components[$name] = clone $component;
            }
        }
    }
    
    
    public function layout($content){
        switch($content){
            case "horizontal":  
            case "inline":      
            case "vertical":    $this->_layout = $content; break;
            default:            throw new \Exception("layout must be 'horizontal', 'vertical', or 'inline'.");
        }
    }

    public function name($content){
        $this->_name = $content;
    }
    
    public function value($name, $value){
        if (!isset($this->_fields[$name]))
            throw new \Exception("There is no field with name '$name'!");
        $this->_values[$name] = $value;
    }
    
    public function labelWidth($content){
        $this->_label_width = $content;
    }

    public function validators($validators){
        $this->_validators = $validators;
    }
 
    // Add a new component
    public function addComponent($name, $content){    
        $component = $this->parseComponent($content);
        $this->_components[$name] = $component;
        return $component;        
    }
    
    public function getComponent($name){
        if (isset($this->_components[$name])){
            return $this->_components[$name];
        }
        else
            throw new \Exception("There is no component with name '$name'!");    
    }

    public function getName(){
        return $this->_name;
    }
    
    public function render(){
        $this->setContent("_css_classes", $this->renderCssClasses());
        $this->setContent("_data", $this->renderDataAttributes());
        $this->setContent("_name", $this->_name);
        
        switch($this->_layout){
            case "horizontal":  $this->setContent("_layout", "form-horizontal"); break;
            case "inline":      $this->setContent("_layout", "form-inline"); break;
            case "vertical":    $this->setContent("_layout", ""); break;
            default:            throw new \Exception("layout must be 'horizontal', 'vertical', or 'inline'.");
        }          
    
        // Set layout, label_width, validator, and value of each component
        foreach ($this->_components as $name => $component){

            if (is_array($component)) {
                foreach($component as $subcomponent){
                    $sub_name = $subcomponent->getName();
                    $this->cascadeProperties($sub_name, $subcomponent);
                }
            } else {
                $this->cascadeProperties($name, $component);
            }
 
            $this->setContent($name, $component);
        }
        return parent::render();
    }
    
    private function cascadeProperties($name, $component){
        // Set a value, if present
        if (isset($this->_values[$name])){
            $component->value($this->_values[$name]);
        }
        // Set a validator, if present
        if (isset($this->_validators[$name])){
            $component->validator($this->_validators[$name]);
        }        
        // set layout and label width for FormGroup and FormFieldCollections
        if (is_a($component, "Bootsole\FormGroupBuilder") || is_a($component, "Bootsole\FormComponentCollectionBuilder") ) {
            $component->layout($this->_layout);
            $component->labelWidth($this->_label_width);
        }
    }
    
    private function parseComponent($name, $content){
        if (is_array($content)){           
            // If the content specifies a "@type" field, create the corresponding FormFieldBuilder object and wrap it in the FormGroupBuilder object
            if (isset($content['@type'])){
                // Set '@name' directive, if not already set
                if (!isset($content["@name"]))
                    $content["@name"] = $name; 
                // Create a FormGroup, unless @group is set to false or we have a hidden field
                if ((isset($content['@group']) && $content['@group'] == "false") || $content['@type'] == 'hidden') {
                    $component = FormFieldBuilder::generate($content['@type'], $content);              
                } else {
                    $component = new FormGroupBuilder($content);
                    $component->layout($this->_layout);              // Pass on form layout to component.  Should be overridable?
                    $component->labelWidth($this->_label_width);    // Pass on form label width to component.  Should be overridable?
                }
                return $component;
            } else {
                // Attempt to parse each element as a FormComponentBuilder object.  They will be concatenated upon rendering.
                $result = [];
                foreach ($content as $id => $subcomponent){
                    $result[] = $this->parseComponent($name, $subcomponent);
                }
                return $result;
            }
        } else if (is_a($content, "Bootsole\FormComponentCollectionBuilder")){         
            // Set name if not set in content
            if (!$content->getName())
                $content->name($name);
            // Push down form properties
            $content->layout($this->_layout);               // Pass on form layout to component.  Should be overridable?
            $content->labelWidth($this->_label_width);     // Pass on form label width to component.  Should be overridable?
            return $content;                               // FormGroupBuilder passed in            
        } else if (is_a($content, "Bootsole\FormGroupBuilder")){
            // Set name if not set in content
            if (!$content->getName())
                $content->name($name);            
            // Push down form properties
            $content->layout($this->_layout);               // Pass on form layout to component.  Should be overridable?
            $content->labelWidth($this->_label_width);     // Pass on form label width to component.  Should be overridable?
            return $content;                               // FormGroupBuilder passed in
        } else if (is_a($content, "Bootsole\FormFieldBuilder")){
            // Set name if not set in content
            if (!$content->getName())
                $content->name($name);  
            return $content;
        } else if (is_a($content, "Bootsole\FormButtonBuilder")){
            // Set name if not set in content
            if (!$content->getName())
                $content->name($name);  
            return $content;
        } else
            throw new \Exception("Invalid component type for this form: " . $content);
    }
}


/* Builds a form from a template, using the following magic fields:
    @action
    @method
*/

class FormBuilder extends FormComponentCollectionBuilder {
    protected $_action = "";            // The url that this form should post/get
    protected $_method = "post";        // "post" or "get"
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default form template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, null, $options);          // No default form template
        
        // Set @action if specified
        if (isset($content['@action'])){
            $this->action($content['@action']);
        }

        // Set @method if specified
        if (isset($content['@method'])){
            $this->method($content['@method']);
        }
    }
    
    public function action($content){
        $this->_action = $content;           
    }
    
    public function method($content){
        switch($content){
            case "get":  
            case "post":      $this->_method = $content; break;
            default:          throw new \Exception("method must be 'get' or 'post'.");
        }            
    }
    
    public function render(){
        $this->setContent("_action", $this->_action);
        $this->setContent("_method", $this->_method);
        return parent::render();
    }
}

/* An abstract class representing a form component.  Uses the following magic fields:
   @name
   @value
   @default
   @validator
*/

abstract class FormComponentBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder;
    
    protected $_name;               // The name of the field (required).
    protected $_value;              // The value of the field (optional).
    protected $_default = "";       // The default value of the field, if none is specified (optional).
    protected $_validator = "";     // A string containing FormValidator validation rules, as HTML5 data* attributes

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

        // Set @validator if specified
        if (isset($content['@validator'])){
            $this->validator($content['@validator']);
        }        
        
        if (isset($content['@data'])){
            $this->dataAttributes($content['@data']);
        }

        if (isset($content['@css_classes'])){
            $this->cssClasses($content['@css_classes']);
        }        
    }
    
    public function name($content){
        $this->_name = $content;
    }
    
    public function getName(){
        return $this->_name;
    }
    
    public function value($content){
        $this->_value = $content;
    }

    public function default_value($content){
        $this->_default = $content;
    }    
    
    public function validator($validator) {
        $this->_validator = $validator; //htmlspecialchars($validator, ENT_QUOTES, false);
    }
    
    public function render(){
        $this->setContent("_css_classes", $this->renderCssClasses());
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
            <div class='form-group {{_hidden}} {{_css_classes}} {{_data}}'>
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
            $this->labelWidth($content['@label_width']);
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

    public function field($content){
        $this->_field = $this->parseField($content);
    }
      
    public function display($content){
        switch($content){
            case "show":        
            case "hidden":
            case "readonly":    
            case "disabled":    $this->_display = $content; break;
            default:            throw new \Exception("display must be 'show', 'hidden', 'readonly', or 'disabled'.");
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
            default:            throw new \Exception("layout must be 'horizontal', 'vertical', or 'inline'.");
        }
    }    

    public function labelWidth($content){
        $this->_label_width = $content;           
    }

    public function label($content){
        $this->_label = $content;           
    }
       
    public function getField(){
        return $this->_field;
    }
    
    public function render(){
        // Set label
        $this->setContent("_label", $this->_label);
        // Pass value on to field
        $this->_field->value($this->_value);
        // Pass validator on to field
        $this->_field->validator($this->_validator);
        
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
        if (is_a($content, "Bootsole\FormFieldBuilder")){
            return $content;                               // FormFieldBuilder passed in
        } else {
            // If the content specifies a "@type" field, create the corresponding subtype of FormFieldBuilder object
            if (isset($content['@type'])){
                $field = FormFieldBuilder::generate($content['@type'], $content);                 
                return $field;
            } else
                throw new \Exception("FormFieldBuilders must specify a '@type' field.");
        }  
    }
}

/* An abstract class representing a form field.
*/   

abstract class FormFieldBuilder extends FormComponentBuilder {
    protected $_placeholder = "";    // The placeholder for the field (optional).
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
            case "checkbox":    $field = new FormCheckboxFieldBuilder($content, $source);   break;
            case "radio":       $field = new FormRadioFieldBuilder($content, $source);      break;
            case "switch":      $field = new FormSwitchFieldBuilder($content, $source);     break;
            case "toggle":      $field = new FormToggleFieldBuilder($content, $source);     break;
            case "bootstrapradio": $field = new FormBootstrapRadioBuilder($content, $source);   break;
            default:            throw new \Exception("Unknown form field type '$type'.");
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
                throw new \Exception("Form fields of type $class do not support addons.");
            }
        }
        
        // Set @append if specified
        if (isset($content['@append'])){
            if (method_exists($this, 'append'))
                $this->append($content['@append']);
            else {
                $class = get_class($this);
                throw new \Exception("Form fields of type $class do not support addons.");
            }
        }        
    }
    
    public function placeholder($content){
        $this->_placeholder = htmlspecialchars($content, ENT_QUOTES, false);
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

    In general, this class should be used for fields that involve user-supplied text.
   
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
            <input type='{{_text_type}}' class='form-control {{_css_classes}}' name='{{_name}}' autocomplete='off' value='{{_value}}' placeholder='{{_placeholder}}' {{_validator}} {{_data}} {{_display}}>");
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
            <input type='hidden' class='form-control {{_css_classes}}' name='{{_name}}' value='{{_value}}' {{_data}} {{_display}}>");
        }

    }            
}

/* Represents a field consisting of one or more options that can be selected.  Derived field types include:

   * select
   * select2
   * selecttime (select2 with time increments)
   * toggle
*/

class FormSelectFieldBuilder extends FormFieldBuilder {
    protected $_multiple = false;   // Set to true if it is possible to have multiple selected items.
    
    use FormFieldSelectable, FormFieldAddonable {
        render as renderInputGroup;        // Can select options with this one
    }
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("<select class='form-control {{_css_classes}}' name='{{_name}}' autocomplete='off' {{_multiple}} placeholder='{{_placeholder}}' {{_validator}} {{_data}} {{_display}}>{{_items}}</select>");
        }
        
        if (isset($content['@items']))
            $this->items($content['@items']);

        if (isset($content['@multiple']))
            $this->multiple($content['@multiple']);

        if (isset($content['@item_classes']))
            $this->itemClasses($content['@item_classes']);            
            
    }
    
    public function multiple($multiple){
        if (is_bool($multiple)) {
            $this->_multiple = $multiple;
        }
        else {
            switch(strtolower($multiple)){
                case "true":
                case "1": $this->_multiple = true;  break;     
                case "false":
                case "0": $this->_multiple = false; break;            
                default: throw new \Exception("'multiple' must be a boolean value.");
            }
        }
    }
    
    // Select elements do not have a value attribute.  So, we override and select one of the child options. 
    public function value($content){
        // If an array, automatically override the 'multiple' attribute
        if (is_array($content))
            $this->multiple(true);

        $this->unselectItems();
        $this->selectItems($content);        // Calling from trait FormFieldSelectable's
    }
    
    public function render(){
        $items = "";
    
        $this->setContent("_multiple", $this->_multiple ? "multiple" : "");
    
        // Select default item(s), if nothing is selected
        if (count($this->getSelectedItems()) <= 0){
            $this->selectItem($this->_default);     // Will only work if name = value for the option
        }
    
        // Insert empty dummy item if placeholder is set
        if ($this->_placeholder)
            $items .= "<option></option>";
    
        // Render the items as select options
        $items .= $this->renderItems("select");
        
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
    protected $_time_end = "11:45 pm";
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
            $this->timeStart($content['@time_start']);    
    
        if (isset($content['@time_end']))
            $this->timeEnd($content['@time_end']);
    
        if (isset($content['@time_increment']))
            $this->timeIncrement($content['@time_increment']);
            
        $this->generateTimes();
    }
    
    public function timeStart($content){
        $this->_time_start = $content;
    }

    public function timeEnd($content){
        $this->_time_end = $content;
    }
    
    public function timeIncrement($content){
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
            $items[$time_val] = new FormFieldOptionBuilder($content, null);
        }
        $this->items($items);    
    }
}



/* Represents a toggle group (checkbox or radio group) */

class FormToggleFieldBuilder extends FormSelectFieldBuilder {
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
                <div class='btn-group' data-toggle='buttons'>
                {{_items}}
                </div>");
        }        
    }
    
    public function render(){
        // Pass on _name and _display to children
        foreach ($this->_items as $item){
            $item->setContent("_name", $this->_name);
            $item->setContent("_display", in_array($this->_display, ["disabled", "readonly"]) ? $this->_display : "" );
        }
    
        // Select default item(s), if nothing is selected
        if (count($this->getSelectedItems()) <= 0){
            $this->selectItem($this->_default);     // Will only work if name = value for the option
        }

        // Render the items as radios or checkboxes, depending on value of _multiple
        if ($this->_multiple)
            $items = $this->renderItems("togglecheckbox");
        else
            $items = $this->renderItems("toggleradio");
        
        $this->setContent("_items", $items);
    
        return $this->renderInputGroup();       // Call trait FormFieldAddonable's render() function instead of parent's function
    }
    
}

/*
Other field types:
   * textarea
   * switch (bootstrap-switch)
   * radio
   * checkbox
   * bootstrapradio
*/



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
            <textarea class='form-control {{_css_classes}}' name='{{_name}}' autocomplete='off' placeholder='{{_placeholder}}' {{_validator}} {{_data}} {{_display}} rows={{_rows}}>{{_value}}</textarea>");
        }

        if (isset($content['@rows']))
            $this->rows($content['@rows']);
    }
    
    public function rows($content){
        if (ctype_digit(strval($content)))
            $this->_rows = $content;
        else
            throw new \Exception("'rows' must be an integer.");
        
        return $this;
    }
 
    public function render(){
        $this->setContent("_rows", $this->_rows);
        return parent::render();
    }
}

/* A checkbox */

class FormCheckboxFieldBuilder extends FormFieldBuilder {
     
    use FormFieldSelectableItem {
        render as renderSelectableItem;
    }
    
    protected $_text = "";
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <div class='checkbox {{_display}}'>
                <label>
                    <input type='checkbox' class='{{_css_classes}}' name='{{_name}}' value='{{_item_value}}' title='{{_title}}' {{_data}} {{_selected}} {{_display}}> {{_text}}
                </label>
            </div>");
        }
        
        if (isset($content['@item_value'])){
            $this->itemValue($content['@item_value']);
        }
        
        if (isset($content['@text'])){
            $this->text($content['@text']);
        }
        
        if (isset($content['@title'])){
            $this->title($content['@title']);
        }
        
        if (isset($content['@selected'])){
            $this->selected($content['@selected']);
        }
        
    }
    
    // If set to the item_value, automatically select the checkbox
    public function value($value){
        if ($value == $this->_item_value)
            $this->selected(true);
        else
            $this->selected(false);
        return parent::value($value);
    }
    
    public function text($text){
        $this->_text = $text;
    }
    
    public function render(){
        $this->setContent('_selected', ($this->_selected ? "checked" : ""));
        $this->setContent('_text', $this->_text);
        return $this->renderSelectableItem();
    }

}

/* A radio button */

class FormRadioFieldBuilder extends FormCheckboxFieldBuilder {    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <div class='radio {{_display}}'>
                <label>
                    <input type='radio' class='{{_css_classes}}' name='{{_name}}' value='{{_item_value}}' title='{{_title}}' {{_data}} {{_selected}} {{_display}}> {{_text}}
                </label>
            </div>");
        }
    }
}

class FormSwitchFieldBuilder  extends FormCheckboxFieldBuilder {    
    protected $_text_on;
    protected $_text_off;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <div>
                <input type='checkbox' class='form-control bootstrapswitch {{_css_classes}}' name='{{_name}}' value='{{_item_value}}' title='{{_title}}' {{_data}} {{_selected}} {{_display}} data-on-text='{{_text_on}}' data-off-text='{{_text_off}}'> {{_text}}
            </div>
            ");
        }
        
        if (isset($content['@text_on'])){
            $this->textOn($content['@text_on']);
        }        

        if (isset($content['@text_off'])){
            $this->textOff($content['@text_off']);
        } 
    }

    public function textOn($text){
        $this->_text_on = $text;
    }

    public function textOff($text){
        $this->_text_off = $text;
    }
    
    public function render(){
        $this->setContent('_text_on', $this->_text_on);
        $this->setContent('_text_off', $this->_text_off);
        return parent::render();
    }
}

/* A Bootstrap Radio group */

class FormBootstrapRadioBuilder extends FormFieldBuilder {
    protected $_multiple = false;   // Set to true if it is possible to have multiple selected items.
    protected $_size = "xs";
    
    use FormFieldSelectable;
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("<div>{{_items}}</div>");
        }
        
        if (isset($content['@items']))
            $this->items($content['@items']);

        if (isset($content['@multiple']))
            $this->multiple($content['@multiple']);

        if (isset($content['@size']))
            $this->size($content['@size']);            
    
        if (isset($content['@item_classes']))
            $this->itemClasses($content['@item_classes']);            
            
    }
    
    public function multiple($multiple){
        if (is_bool($multiple)) {
            $this->_multiple = $multiple;
        }
        else {
            switch(strtolower($multiple)){
                case "true":
                case "1": $this->_multiple = true;  break;     
                case "false":
                case "0": $this->_multiple = false; break;            
                default: throw new \Exception("'multiple' must be a boolean value.");
            }
        }
    }  
    
    public function size($size){
        switch($size){
            case "xs":
            case "sm": 
            case "md":
            case "lg":
            case "block": $this->_size = $size; break;            
            default: throw new \Exception("'size' must be 'xs', 'sm', 'md', 'lg', or 'block'.");
        }
    }    
    
    // Select elements do not have a value attribute.  So, we override and select one of the child options. 
    public function value($content){
        // If an array, automatically override the 'multiple' attribute
        if (is_array($content))
            $this->_multiple = true;

        $this->unselectItems();
        $this->selectItems($content);        // Calling from trait FormFieldSelectable's
    }
    
    public function render(){
        // Pass on _name, _display, and _size to children
        foreach ($this->_items as $item){
            $item->setContent("_name", $this->_name);
            $item->setContent("_size", $this->_size);
            $item->setContent("_display", in_array($this->_display, ["disabled", "readonly"]) ? $this->_display : "" );
        }
        
        $this->setContent("_multiple", $this->_multiple ? "multiple" : "");
        
        // Select default item(s), if nothing is selected
        if (count($this->getSelectedItems()) <= 0){
            $this->selectItem($this->_default);     // Will only work if name = value for the option
        }
    
        // Render the items as bootstrapradio options
        $items = "";
        $items .= $this->renderItems("bootstrapradio");
        
        $this->setContent("_items", $items);
    
        return parent::render();
    }
    
}

/*
  Represents an item in a 'select', 'toggleradio', 'togglecheckbox', or 'bootstrapradio' field
*/

class FormFieldOptionBuilder extends HtmlBuilder {
    use HtmlAttributesBuilder, FormFieldSelectableItem;

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default navbar template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("<option class='{{_css_classes}}' value='{{_item_value}}' {{_data}} {{_selected}}>{{_label}}</option>");
        }
        
        if (isset($content['@item_value'])){
            $this->itemValue($content['@item_value']);
        }
        
        if (isset($content['@label'])){
            $this->label($content['@label']);
        }

        if (isset($content['@title'])){
            $this->title($content['@title']);
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
}

// Represents a form button
class FormButtonBuilder extends FormComponentBuilder {
    protected $_button;     // A ButtonBuilder object
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default FormGroup template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("
            <div>
                {{_button}}
            </div>");
        }
    
        $this->button($content);
    }
    
    
    public function button($button){
        $this->_button = $this->parseButton($button);
    }
    
    public function parseButton($button){
        if (is_a($button, "Bootsole\ButtonBuilder") || is_a($button, "Bootsole\ButtonGroupBuilder"))
            return $button;
        else if (is_array($button)){
            $result = new ButtonBuilder($button);
            return $result;
        } else {
            throw new \Exception("'button' must be a ButtonBuilder, ButtonGroupBuilder, or array.");
        }
    }
    
    public function render(){
        // Pass name on to button
        $this->_button->name($this->_name);
        
        $this->setContent("_button", $this->_button);
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
    protected $_items = [];         // An array of FormFieldOptionBuilder objects
    protected $_item_classes = [];  // An array of CSS classes to be applied to the children
    
    // Set classes for items
    public function itemClasses($classes){
        $this->_item_classes = $classes;
    }
    
    // Set the items from an array
    public function items($items){
        foreach($items as $name => $item){
            $this->_items[$name] = $this->parseItem($name, $item);
        }
    }
     
    // Set a given item as 'selected'   
    public function selectItem($name){
        if (isset($this->_items[$name]))
            $this->_items[$name]->selected(true);
    }

    // Select the item(s) corresponding to the given value(s)
    public function selectItems($content){
        foreach($this->_items as $name => $item){
            if (is_array($content)){
                if (in_array($item->getItemValue(), $content))
                    $item->selected(true);
            } else {
                if ($item->getItemValue() == $content)
                    $item->selected(true);             
            }
        }
    }
    
    /* Returns an array of selected items (FormFieldOptionBuilder objects) */
    public function getSelectedItems(){
        $results = [];
        foreach($this->_items as $item){
            if ($item->getSelected())
                $results[] = $item;
        }
        return $results;     
    }

    // Set a given item as not 'selected'   
    public function unselectItem($name){
        if (isset($this->_items[$name]))
            $this->_items[$name]->selected(false);
    }
    
    // Unselect all items
    public function unselectItems(){
        foreach($this->_items as $item)
            $item->selected(false);
    }    
    
    public function renderItems($type = null){
        $result = "";
        foreach($this->_items as $name => $item){
            // Set CSS classes for item
            $item->cssClasses($this->_item_classes);
            $result .= $item->render($type) . PHP_EOL;
        }
        return $result;
    }

    private function parseItem($name, $item){
        if (is_a($item, "Bootsole\FormFieldOptionBuilder"))
            $result = $item;
        else
            $result = new FormFieldOptionBuilder($item);
        
        // Set item value to name of item, if not otherwise specified
        if (!$result->getItemValue())
            $result->itemValue($name);
    
        return $result;
    }
    
}

trait FormFieldSelectableItem {
    protected $_item_value;         // The value of this item (required).  Not to be confused with the actual *selected* value.
    protected $_label;              // The label to display for this item (set to value by default)
    protected $_title = "";
    protected $_selected = false;   // Whether or not this option is selected

    public function itemValue($content){
        $this->_item_value = $content;
    }
    
    public function label($content){
        $this->_label = $content;
    }

    public function title($content){
        $this->_title = $content;
    }
    
    public function selected($content){
        if (is_bool($content)) {
            $this->_selected = $content;
        }
        else {
            switch(strtolower($content)){
                case "true":
                case "1": $this->_selected = true;  break;     
                case "false":
                case "0": $this->_selected = false; break;            
                default: throw new \Exception("'selected' must be a boolean value.");
            }
        }
    }
    
    public function getItemValue(){
        return $this->_item_value;
    }
    
    public function getSelected(){
        return $this->_selected;
    }
    
    /* Render field as a select, toggleradio, togglecheckbox, or bootstrapradio option. */
    public function render($type = null){
        /* If 'type' is specified, override the base template */
        if ($type){
            switch($type){
                case 'select':          $this->setTemplate("<option class='{{_css_classes}}' value='{{_item_value}}' {{_data}} {{_selected}}>{{_label}}</option>");
                                        $this->setContent('_selected', $this->_selected ? "selected" : "");
                                        break;
                case 'toggleradio':     $this->setContent("_type", "radio");
                                        $this->setInputTemplate($this->_selected);
                                        break;
                case 'togglecheckbox':  $this->setContent("_type", "checkbox");
                                        $this->setInputTemplate($this->_selected);
                                        break;
                case 'bootstrapradio':  $this->setTemplate("<button type='button' class='bootstrapradio {{_css_classes}}' name='{{_name}}' value='{{_item_value}}' title='{{_title}}' {{_display}} data-selected='{{_selected}}' data-size='{{_size}}'>{{_label}}</button> ");
                                        $this->setContent('_selected', ($this->_selected ? "true" : "false"));
                                        break;
                
                default:   throw new \Exception("'type' must be 'select', 'toggle', or 'bootstrapradio.");
            }
        }
        
        if (!$this->_item_value)
            throw new \Exception("'item_value' not set in " . get_class($this));
        else
            $this->setContent('_item_value', $this->_item_value);
        if (!$this->_label)
            $this->_label = $this->_item_value;
        $this->setContent('_label', $this->_label);
        $this->setContent('_title', $this->_title);
        $this->setContent('_data', $this->renderDataAttributes());
        $this->setContent("_css_classes", $this->renderCssClasses());
        return parent::render();
    }
    
    private function setInputTemplate($selected){
        if ($selected)
            $this->setTemplate("
                <label class='btn {{_css_classes}} active {{_display}}'>
                    <input class='form-control' type='{{_type}}' name='{{_name}}' value='{{_item_value}}' {{_data}} {{validator}} {{_display}} checked> {{_label}}
                </label>");
        else
            $this->setTemplate("
                <label class='btn {{_css_classes}} {{_display}}'>
                    <input class='form-control' type='{{_type}}' name='{{_name}}' value='{{_item_value}}' {{_data}} {{validator}} {{_display}}> {{_label}}
                </label>");  
    }
}
?>
