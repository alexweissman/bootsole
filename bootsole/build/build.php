<?php

    require_once("../bootsole.php"); 
    
    // Builds the minified CSS and JS files for the site.

    /*
     * In XAMPP and Mac OSX, by default Apache is run under the user 'daemon'.  To grant proper write permissions, run the following shell command:
     * `sudo chown -R daemon:daemon <write_path>`
     * This will grant ownership of the path to the web server.
     */
    echo "Running as user: ";
    echo `whoami`;
    echo "<br><br>";    
        
    // Get the manifest for the site
    $manifest_file = PAGE_INCLUDES_SCHEMA_PATH;
    
    echo "Loading manifest from $manifest_file...<br>";
    
    // Load the include manifest
    $manifest = json_decode(file_get_contents($manifest_file, FILE_USE_INCLUDE_PATH),true);
    if ($manifest === null){
        error_log(json_last_error());
        echo "Could not load manifest.  Please see the PHP error log.";
        exit();
    }
    
    echo "Manifest loaded.<br>";
    // For each manifest group, build the corresponding minified, concatenated JS and CSS files
    
    foreach ($manifest as $name => $manifest_group){
        echo "Building manifest '$name'...<br>";
 
        /***** JS *****/
        $output_js = $manifest_group['min_js'];
        echo "--Creating bundled, minified JS file '$output_js'...<br>";
        
        // Each file will be minified, and the result appended to the output array
        $output_arr = array();
        foreach($manifest_group['js'] as $js_file){
            echo "----Added file '$js_file'.<br>";
            $js_file_local = LOCAL_ROOT . $js_file;
            exec("export DYLD_LIBRARY_PATH=''; java -jar yuicompressor-2.4.8.jar $js_file_local", $output_arr); 
        }
        
        // Write it all to the output file
        file_put_contents (LOCAL_ROOT.$output_js, implode("\n", $output_arr));
            
        /***** CSS *****/
        $output_css = $manifest_group['min_css'];
        echo "--Creating bundled, minified CSS file '$output_css'...<br>";    

        // Each file will be minified, and the result appended to the output array
        $output_arr = array();
        foreach($manifest_group['css'] as $css_file){
            echo "----Added file '$css_file'.<br>";
            $css_file_local = LOCAL_ROOT.$css_file;
            exec("export DYLD_LIBRARY_PATH=''; java -jar yuicompressor-2.4.8.jar $css_file_local", $output_arr); 
        }
        
        // Write it all to the output file
        file_put_contents (LOCAL_ROOT.$output_css, implode("\n", $output_arr));  
    }
    

?>
