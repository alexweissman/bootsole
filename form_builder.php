<?php

require_once("template_functions.php");

class FormBuilder {

    /* Information about the fields to render.
     * Allowed field options:
     * 'label'
     * 'display': 'hidden', 'disabled', 'show'
     * 'placeholder'
     * 'icon'
     * 'icon_link'
     * 'type': 'text', 'password', 'toggle', 'select'
     * 'preprocess' : a function to call to process the value before rendering.
     * 'default' : the default value to use if a value is not specified
     */
    protected $_fields = array();
    
    protected $_data = array();
    
    protected $_buttons = array();
    
    protected $_template = "";
    
    public function __construct($template, $fields = array(), $buttons = array(), $data = array()) {
        $this->_fields = $fields;
        $this->_buttons = $buttons;
        $this->_data = $data;
        $this->_template = $template;
    }
    
    public function render(){
        $result = $this->_template;
        $rendered_fields = array();        
        // Render fields
        foreach ($this->_fields as $field_name => $field){
            $type = isset($field['type']) ? $field['type'] : "text";
            if ($type == "text"){
                $rendered_fields[$field_name] = $this->renderTextField($field_name);
            } else if ($type == "password") {
                $rendered_fields[$field_name] = $this->renderPasswordField($field_name);
            } else if ($type == "toggle") {
                $rendered_fields[$field_name] = $this->renderToggleField($field_name);
            } else if ($type == "select") {
                $rendered_fields[$field_name] = $this->renderSelectField($field_name);
            } else if ($type == "switch") {
                $rendered_fields[$field_name] = $this->renderSwitchField($field_name);
            } else if ($type == "radioGroup") {
                $rendered_fields[$field_name] = $this->renderRadioGroupField($field_name);
            }
        }
        // Render buttons
        foreach ($this->_buttons as $button_name => $button){
            $rendered_fields[$button_name] = $this->renderButton($button_name);
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

    // Renders a password field with the specified name.
    private function renderPasswordField($field_name){
        $field_data = $this->generateFieldData($field_name);
        
        $result = "
            <div class='form-group {{hidden}}'>
                <label>{{label}}</label>
                <div class='input-group'>
                    <span class='input-group-addon'>{{addon}}</span>
                    <input type='password' class='form-control' name='{{name}}' autocomplete='off' value='{{value}}' placeholder='{{placeholder}}' data-validate='{{validator_str}}' {{disabled}}>
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

    private function renderSwitchField($field_name){
    
        $field_data = $this->generateFieldData($field_name);
        
        $field = $this->_fields[$field_name];
        $checked = $field_data['value'] ? "checked" : "";
        $icon = isset($field['icon']) ? $field['icon'] : null;
        $on = isset($field['on']) ? $field['on'] : "";
        $off = isset($field['off']) ? $field['off'] : "";
        
        if ($icon)
            $center_label = "data-label-text=\"{{addon}}\"";
        else 
            $center_label = "";
        
        $result = "
        <div class='form-group {{hidden}}'>
            <label class='label-switch'>{{label}}</label>
            <span class='pull-right'>
                <input class='form-control bootstrapswitch' type='checkbox' data-on-text='$on' data-off-text='$off' $center_label name='{{name}}' {{disabled}} $checked>
            </span>
        </div>";
        
        return replaceKeyHooks($field_data, $result);
    }    

    private function renderRadioGroupField($field_name){
    
        $field_data = $this->generateFieldData($field_name);
        
        $field = $this->_fields[$field_name];
        $choices = isset($field['choices']) ? $field['choices'] : array();
        
        $result = "
        <div class='form-group {{hidden}}'>
            <label>{{label}}</label>
            <div class='input-group'>";
        
        // Render choices (buttons)
        foreach ($choices as $choice_value => $choice){
            if ($field_data['value'] == $choice_value){ 
                $result .=  "<button type='button' class='bootstrapradio' name='{{name}}' value='$choice_value' title='{$choice['label']}' {{disabled}} data-selected='true'><i class='{$choice['icon']}'></i></button> ";
            } else {
                $result .=  "<button type='button' class='bootstrapradio' name='{{name}}' value='$choice_value' title='{$choice['label']}' {{disabled}} data-selected='false'><i class='{$choice['icon']}'></i></button> ";
            }	
        }
        
        $result .= "
            </div>
        </div>";
        
        return replaceKeyHooks($field_data, $result);
    }      
        
    private function renderButton($button_name){
        $button = $this->_buttons[$button_name];
        $display = isset($button['display']) ? $button['display'] : "show";
        if ($display == "hidden")
            return "";
        $button_data = array();
        $button_data['name'] = $button_name;
        $button_data['label'] = isset($button['label']) ? $button['label'] : $button_name;        
        $button_data['icon'] = isset($button['icon']) ? $button['icon'] : "";        
        $button_data['size'] = isset($button['size']) ? $button['size'] : "md";
        $button_data['style'] = isset($button['style']) ? $button['style'] : "primary";
        $data = isset($button['data']) ? $button['data'] : array();
        $button_data['data_disp'] = "";
        foreach ($data as $data_name => $data_val){
            $button_data['data_disp'] .= "data-$data_name='$data_val' ";
        }
        $button_data['disabled'] = ($display == "disabled") ? "disabled" : "";
        $type = isset($button['type']) ? $button['type'] : "button";
        
        if ($type == "submit"){
            $result = "
                <div class='vert-pad'>
                    <button name='{{name}}' type='submit' class='btn btn-block btn-{{size}} btn-{{style}}' {{data_disp}} data-loading-text='Please wait...' {{disabled}}><i class='{{icon}}'></i> {{label}}</button>
                </div>";                
        } else if ($type == "launch"){
            $result = "
                <div class='vert-pad'>
                    <button name='{{name}}' type='button' class='btn btn-block btn-{{size}} btn-{{style}}' {{data_disp}} {{disabled}} data-toggle='modal'><i class='{{icon}}'></i> {{label}}</button>
                </div>";                
        } else if ($type == "cancel"){
            $result = "
                <div class='vert-pad'>
                    <button name='{{name}}' type='button' class='btn btn-block btn-{{size}} btn-{{style}}' {{data_disp}} {{disabled}} data-dismiss='modal'><i class='{{icon}}'></i> {{label}}</button>
                </div>";                
        } else {
                $result = "
            <div class='vert-pad'>
                <button name='{{name}}' type='button' class='btn btn-block btn-{{size}} btn-{{style}}' {{data_disp}} {{disabled}}><i class='{{icon}}'></i> {{label}}</button>
            </div>";
        }
    
        return replaceKeyHooks($button_data, $result);
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