<?php

/***********
bootsole, v0.1.4

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

class ValidationSchema {

    protected $_schema = array();
    protected $_locale = "";

    // Load schema from a file
    public function __construct($file, $locale = "en_US") {
        $this->_schema = json_decode(file_get_contents($file),true);
        if ($this->_schema === null) {
            error_log(json_last_error());
            // Throw error
        }
        $this->_locale = $locale;
    }

    public function clientRules(){
        $client_rules = array();
        $implicit_rules = array();
        foreach ($this->_schema as $field_name => $field){
            $field_rules = "";
            $validators = $field['validators'];
            foreach ($validators as $validator_name => $validator){
                // Required validator
                if ($validator_name == "required"){
                    $prefix = "data-bv-notempty";
                    $field_rules = $this->html5Attributes($validator, $prefix);
                }
                // String length validator
                if ($validator_name == "length"){
                    $prefix = "data-bv-stringlength";
                    $field_rules = $this->html5Attributes($validator, $prefix);
                    if (isset($validator['min']))
                        $field_rules .= "$prefix-min={$validator['min']} ";
                    if (isset($validator['max']))
                        $field_rules .= "$prefix-max={$validator['max']} ";
                }
                // Integer validator
                if ($validator_name == "integer"){
                    $prefix = "data-bv-integer";
                    $field_rules = $this->html5Attributes($validator, $prefix);   
                }                  
                // Choice validator
                if ($validator_name == "choice"){
                    $prefix = "data-bv-choice";
                    $field_rules = $this->html5Attributes($validator, $prefix);
                    if (isset($validator['min']))
                        $field_rules .= "$prefix-min={$validator['min']} ";
                    if (isset($validator['max']))
                        $field_rules .= "$prefix-max={$validator['max']} ";                    
                }
                // Email validator
                if ($validator_name == "email"){
                    $prefix = "data-bv-emailaddress";
                    $field_rules = $this->html5Attributes($validator, $prefix); 
                }            
                // Equals validator
                if ($validator_name == "equals"){
                    $prefix = "data-bv-identical";
                    if (isset($validator['field'])){
                        $field_rules .= "$prefix-field={$validator['field']} ";
                    } else {
                        return null;    // TODO: throw exception
                    }
                    
                    $field_rules = $this->html5Attributes($validator, $prefix);
                    // Generates validator for matched field
                    $implicit_rules[$validator['field']] = $field_rules;
                    $implicit_rules[$validator['field']] .= "$prefix-field=$field_name ";
                }
            }

            $client_rules[$field_name] = $field_rules;
        }
        
        // Merge in any implicit rules       
        foreach ($implicit_rules as $field_name => $field){
            $client_rules[$field_name] .= $field;
        }
        
        return $client_rules;    
    }
    
    public function html5Attributes($validator, $prefix){
        $attr = "$prefix=true ";
        if (isset($validator['messages'])){
            $msg = "";
            if (isset($validator['messages'][$this->_locale])){
                $msg = $validator['messages'][$this->_locale];
            } else if (isset($validator['messages']["default"])){
                $msg = $validator['messages']["default"];
            } else {
                return $attr;
            }
            $attr .= "$prefix-message=\"$msg\" ";    
        }
        return $attr;
    }
}
