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

    // Load schema from a file
    public function __construct($file) {
        $this->_schema = json_decode(file_get_contents($file),true);
        if ($this->_schema === null)
            error_log(json_last_error());
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
                    $field_rules .= "$prefix=true ";
                    if (isset($validator['message']))
                        $field_rules .= "$prefix-message=\"{$validator['message']}\" ";                        
                }
                // String length validator
                if ($validator_name == "length"){
                    $prefix = "data-bv-stringlength";
                    $field_rules .= "$prefix=true ";
                    if (isset($validator['min']))
                        $field_rules .= "$prefix-min={$validator['min']} ";
                    if (isset($validator['max']))
                        $field_rules .= "$prefix-max={$validator['max']} ";
                    if (isset($validator['message']))
                        $field_rules .= "$prefix-message=\"{$validator['message']}\" ";                        
                }
                // Email validator
                if ($validator_name == "email"){
                    $prefix = "data-bv-emailaddress";
                    $field_rules .= "$prefix=true ";
                    if (isset($validator['message']))
                        $field_rules .= "$prefix-message=\"{$validator['message']}\" ";                        
                }            
                // Equals validator
                if ($validator_name == "equals"){
                    $prefix = "data-bv-identical";
                    $field_rules .= "$prefix=true ";
                    if (isset($validator['field'])){
                        $field_rules .= "$prefix-field={$validator['field']} ";
                    } else {
                        return null;    // TODO: throw exception
                    }
                    // Generates validator for matched field
                    $implicit_rules[$validator['field']] = "$prefix=true ";
                    $implicit_rules[$validator['field']] .= "$prefix-field=$field_name ";
                    if (isset($validator['message'])){
                        $field_rules .= "$prefix-message=\"{$validator['message']}\" ";
                        $implicit_rules[$validator['field']] .= "$prefix-message=\"{$validator['message']}\" ";
                    }
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
}
