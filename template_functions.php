<?php

function replaceKeyHooks($data, $template){
    foreach ($data as $key => $value){
        if (gettype($value) != "array" && gettype($value) != "object") {
            $find = '{{' . $key . '}}';
            $template = str_replace($find, $value, $template);
        }
    }
    return $template;
}

?>
