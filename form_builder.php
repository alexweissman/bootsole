<?php
/**********

bootsole, v0.1.3

Copyright 2014 by Alex Weissman

MIT License:

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.


**********/

require_once("template_functions.php");

class FormBuilder {

    /* Information about the fields to render.
     * Allowed field options:
     * 'label'
     * 'display': 'hidden', 'disabled', 'show'
     * 'placeholder'
     * 'icon'
     * 'icon_link'
     * 'addon_end'
     * 'type': 'hidden', 'text', 'password', 'toggle', 'select', 'select2', 'selectTime', 'switch', 'radioGroup'
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
            if ($type == "hidden"){
                $rendered_fields[$field_name] = $this->renderHiddenField($field_name);
            } else if ($type == "text"){
                $rendered_fields[$field_name] = $this->renderTextField($field_name);
            } else if ($type == "password") {
                $rendered_fields[$field_name] = $this->renderPasswordField($field_name);
            } else if ($type == "toggle") {
                $rendered_fields[$field_name] = $this->renderToggleField($field_name);
            } else if ($type == "select") {
                $rendered_fields[$field_name] = $this->renderSelectField($field_name);
            } else if ($type == "select2") {
                $rendered_fields[$field_name] = $this->renderSelect2Field($field_name);
            } else if ($type == "selectTime") {
                $rendered_fields[$field_name] = $this->renderSelectTimeField($field_name);
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
    
    private function renderHiddenField($field_name){
        $field_data = $this->generateFieldData($field_name);
        $result = "<input type='hidden' name='{{name}}' value='{{value}}' {{disabled}}>";
        return replaceKeyHooks($field_data, $result);    
    }
    
    // Renders a text field with the specified name.
    private function renderTextField($field_name){
        $field_data = $this->generateFieldData($field_name);
        
        $result = "
            <div class='form-group {{hidden}}'>";
        if ($field_data['label'] !== null){
            $result .= "<label>{{label}}</label>";
        }        
        $result .= "<div class='input-group'>
                    <span class='input-group-addon'>{{addon}}</span>
                    <input type='text' class='form-control' name='{{name}}' autocomplete='off' value='{{value}}' placeholder='{{placeholder}}' data-validate='{{validator_str}}' {{disabled}}>";
        if ($field_data['addon_end'])
            $result .= "<span class='input-group-addon'>{{addon_end}}</span>";
        $result .= "
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
                    <input type='password' class='form-control' name='{{name}}' autocomplete='off' value='{{value}}' placeholder='{{placeholder}}' data-validate='{{validator_str}}' {{disabled}}>";
        if ($field_data['addon_end'])
            $result .= "<span class='input-group-addon'>{{addon_end}}</span>";                    
        $result .= "
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
              <select class='form-control' name='{{name}}' data-validate='{{validator_str}}' {{disabled}}>";
        
        // Render choices (options)
        foreach ($choices as $choice => $choice_label){
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

    // Renders a select2 field with the specified name.
    private function renderSelect2Field($field_name){
        $field_data = $this->generateFieldData($field_name);
        $field = $this->_fields[$field_name];
        $field_data['data_placeholder'] = isset($field['placeholder']) ? "data-placeholder='{$field['placeholder']}'": "";
        $choices = isset($field['choices']) ? $field['choices'] : array();
        
        $result = "
        <div class='form-group {{hidden}}'>
            <label>{{label}}</label>
            <div class='input-group select2-bootstrap-prepend'>
              <span class='input-group-addon'>{{addon}}</span>
              <select class='form-control select2' name='{{name}}' {{data_placeholder}} data-validate='{{validator_str}}' {{disabled}}>";
        
        // Required empty option for placeholders
        if (isset($field['placeholder'])){
            $result .= "<option></option>";
        }
        
        // Render choices (options)
        foreach ($choices as $choice_value => $choice){
            $label = isset($choice['label']) ? $choice['label'] : $choice_value;
            $data = isset($choice['data']) ? $choice['data'] : array();
            // Render additional data fields, if any
            $data_str = "";
            foreach ($data as $name => $value){
                $data_str .= "data-$name='$value' ";
            }
            if ($field_data['value'] == $choice_value){ 
                $result .=  "<option value='$choice_value' $data_str selected>$label</option>";
            } else {
                $result .=  "<option value='$choice_value' $data_str>$label</option>";     
            }	
        }
        
        $result .= "
              </select>
            </div>
        </div>";
        
        return replaceKeyHooks($field_data, $result);
    }      

    // Dropdown for selecting times
    private function renderSelectTimeField($field_name){
        $field = $this->_fields[$field_name];
        $time_start = isset($field['time_start']) ? $field['time_start'] : "12:00 am";
        $time_end = isset($field['time_end']) ? $field['time_end'] : "11:30 pm";
        $time_increment = isset($field['time_increment']) ? $field['time_increment'] : 15;        
        // Build time choices
        $this->_fields[$field_name]['choices'] = array();
        $range = range(strtotime($time_start),strtotime($time_end),$time_increment*60);
        foreach($range as $time){
            $time_val = date("g:i a",$time);
            $this->_fields[$field_name]['choices'][$time_val] = [
                'label' => $time_val
                ];
        }
        return $this->renderSelect2Field($field_name);
        
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
            if (isset($choice['size'])){
                $size = $choice['size'];
            } else {
                $size = "xs";
            }
        
            if ($field_data['value'] == $choice_value){ 
                $result .=  "<button type='button' class='bootstrapradio' name='{{name}}' value='$choice_value' title='{$choice['label']}' {{disabled}} data-selected='true' data-size='$size'><i class='{$choice['icon']}'></i></button> ";
            } else {
                $result .=  "<button type='button' class='bootstrapradio' name='{{name}}' value='$choice_value' title='{$choice['label']}' {{disabled}} data-selected='false' data-size='$size'><i class='{$choice['icon']}'></i></button> ";
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
        $field_data['label'] = isset($field['label']) ? $field['label'] : null;
        $field_data['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : "";
        $field_data['addon_end'] = isset($field['addon_end']) ? $field['addon_end'] : null;
        
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