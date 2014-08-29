<?php

require_once("template_functions.php");

class FormBuilder {

    /* Information about the fields to render.
     * Allowed field options:
     * 'display': 'hidden', 'readonly', 'show'
     * 'preprocess' : a function to call to process the value before rendering.
     * 'default' : the default value to use if a value is not specified
     */
    protected $_fields = array();
    
    protected $_data = array();
    
    protected $_buttons = array();
    
    protected $_template = "";
    
    public function __construct($template, $fields = array(), $data = array(), $buttons = array()) {
        $this->_fields = $fields;
        $this->_buttons = $buttons;
        $this->_data = $data;
        $this->_template = $template;
    }
    
    public function render(){
        $result = $this->_template;
        $rendered_fields = array();        
        foreach ($this->_fields as $field_name => $field){
            $type = isset($field['type']) ? $field['type'] : "text";
            if ($type == "text"){
                $rendered_fields[$field_name] = $this->renderTextField($field_name);
            } else if ($type == "toggle") {
                $rendered_fields[$field_name] = $this->renderToggleField($field_name);
            } else if ($type == "select") {
                $rendered_fields[$field_name] = $this->renderSelectField($field_name);
            }
        }
            
        return replaceKeyHooks($rendered_fields, $result);
    }
    
    // Renders a text field with the specified name.
    private function renderTextField($field_name){
        $field_data = $this->generateFieldData($field_name);
        
        $result = "
            <div class='form-group {{hidden}}'>
                <label>{{label}}</label>
                <div class='input-group'>
                    <span class='input-group-addon'>{{addon}}</span>
                    <input type='text' class='form-control' name='{{name}}' autocomplete='off' value='{{value}}' placeholder='{{placeholder}}' data-validate='{{validator_str}}' {{disabled}}>
                </div>
            </div>";
        
        return replaceKeyHooks($field_data, $result);
    }
    
    // Renders a toggle button toggle group.
    private function renderToggleField($field_name){
        $field_data = $this->generateFieldData($field_name);
        
        $field = $this->_fields[$field_name];
        $choices = isset($field['choices']) ? $field['choices'] : array();
        
        $result = "
        <div class='form-group {{hidden}}'>
            <label>{{label}}</label>
            <div class='input-group'>
              <span class='input-group-addon'>{{addon}}</span>
              <div class='btn-group' data-toggle='buttons'>";
        
        // Render choices (toggles)
        foreach ($choices as $choice => $choice_label){
            // Special trick for making readonly radio buttons: make one checked and the rest disabled
            if ($field_data['value'] == $choice){ 
                $result .=  "<label class='btn btn-primary active'>
                  <input class='form-control' type='radio' name='{{name}}' value='$choice' data-validate='{{validator_str}}' checked> $choice_label
                  </label>";
            } else {
                $result .=  "<label class='btn btn-primary' {{disabled}}>
                  <input class='form-control' type='radio' name='{{name}}' value='$choice' data-validate='{{validator_str}}' {{disabled}}> $choice_label
                  </label>";     
            }	
        }
        
        $result .= "
              </div>
            </div>
        </div>";
        
        return replaceKeyHooks($field_data, $result);
    }
    
    private function renderSelectField($field_name){
    
        $field_data = $this->generateFieldData($field_name);
        
        $field = $this->_fields[$field_name];
        $choices = isset($field['choices']) ? $field['choices'] : array();
        
        $result = "
        <div class='form-group {{hidden}}'>
            <label>{{label}}</label>
            <div class='input-group'>
              <span class='input-group-addon'>{{addon}}</span>
              <select class='form-control' name='{{name}}' {{disabled}}>";
        
        // Render choices (toggles)
        foreach ($choices as $choice => $choice_label){
            // Special trick for making readonly radio buttons: make one checked and the rest disabled
            if ($field_data['value'] == $choice){ 
                $result .=  "<option value='$choice' selected>$choice_label</option>";
            } else {
                $result .=  "<option value='$choice'>$choice_label</option>";     
            }	
        }
        
        $result .= "
              </select>
            </div>
        </div>";
        
        return replaceKeyHooks($field_data, $result);
    }

    private function generateFieldData($field_name){
        $field = $this->_fields[$field_name];
        
        $field_data = array();
        
        $field_data['name'] = $field_name;
        $field_data['label'] = isset($field['label']) ? $field['label'] : $field_name;
        $field_data['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : "";
        
        $icon = isset($field['icon']) ? $field['icon'] : "fa fa-edit";
        $icon_link = isset($field['icon_link']) ? $field['icon_link'] : null;
        if ($icon_link)
            $field_data['addon'] = "<a href='$icon_link'><i class='$icon'></i></a>";
        else
            $field_data['addon'] = "<i class='$icon'></i>";
        
        $display = isset($field['display']) ? $field['display'] : "show";
        if ($display == "hidden"){
            $field_data['hidden'] = "hidden";
            $field_data['disabled'] = "disabled";
        } else if ($display == "disabled"){
            $field_data['hidden'] = "";
            $field_data['disabled'] = "disabled";
        } else {
            $field_data['hidden'] = "";
            $field_data['disabled'] = "";            
        }

        $validator = isset($field['validator']) ? $field['validator'] : array();
        $field_data['validator_str'] = json_encode($validator, JSON_FORCE_OBJECT);
        
        if (isset($this->_data[$field_name]))
            $field_data['value'] = $this->_data[$field_name];
        else {
            // Set default value
            if (isset($field['default'])){
                $field_data['value'] = $field['default'];
            } else {
                $field_data['value'] = "";
            }
        }
        
        // Preprocess value
        if (isset($field['preprocess'])){
            $method = new ReflectionFunction($field['preprocess']);
            if ($method){
                $field_data['value'] = $method->invokeArgs(array($field_data['value']));
            }
        }
        
        return $field_data;
    }
    
}
?>