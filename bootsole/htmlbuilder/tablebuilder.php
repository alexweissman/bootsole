<?php

namespace Bootsole;

/*
    Directives:
    @rows
    @columns

*/


class TableBuilder extends HtmlBuilder {

    protected $_columns = [];      // An array of TableColumnBuilder objects
    protected $_rows = [];      // An array of HtmlBuilder objects
    
    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default table template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else
            parent::__construct($content, "tables/table-tablesorter-default.html", $options);
            
        // Check if @columns has been specified
        if (isset($content['@columns'])){
            foreach ($content['@columns'] as $column){
                $this->_columns[] = $this->parseColumn($column);
            }
        }
            //throw new \Exception("The TableBuilder object must specify a '@columns' field.");        
        
        // Check if @rows has been specified
        if (isset($content['@rows'])){
            foreach ($content['@rows'] as $row){
                $this->_rows[] = $this->parseRow($row);
            }
        }
            //throw new \Exception("The TableBuilder object must specify a '@rows' field.");
    }    

    public function addColumn($content){
        $column = $this->parseColumn($content);
        $this->_columns[] = $column;
        return $column;
    }
    
    public function addRow($content){
        $row = $this->parseRow($content);
        $this->_rows[] = $row;
        return $row;
    }
    
    private function parseColumn($content){
        // If $column is already a 'TableColumnBuilder' object, then just return it.  Otherwise, construct it.
        if (is_a($content, "Bootsole\TableColumnBuilder")){
            return $content;
        } else {
            $column = new TableColumnBuilder($content);
            return $column;
        }
    }

    private function parseRow($content){
        // If $row is already a 'HtmlBuilder' object, then just return it.  Otherwise, construct it.
        if (is_a($content, "Bootsole\HtmlBuilder")){
            return $content;
        } else {
            $row = new HtmlBuilder($content);
            return $row;
        }
    }    
        
    public function render(){
        // Generate initial sort
        $initial_sort = "[";
        $i = 0;
        foreach($this->_columns as $column) {
            $sort = $column->getSortDirectionIndex();
            if ($sort != null)
                $initial_sort .= "[$i, $sort]";
            $i++;
        }
        $initial_sort .= "]";
        $this->setContent('_initial_sort', $initial_sort);
        
        $columns_visible = [];
        foreach ($this->_columns as $id => $column){
            // Skip columns that are hidden
            if ($column->getDisplay() == "hidden")
                continue;
            $columns_visible[$id] = $column;
        }
        
        // Then, build the table header by rendering the TableColumnBuilder objects in _columns_visible
        $header = [
            "@template" => "<tr>{{_column_headers}}</tr>",
            "@content" => [
                "_column_headers" => $columns_visible
            ]
        ];
        $this->setContent('_table_header', $header);
        
        // Next build the template for the rows
        foreach ($this->_rows as $id => $row){        
            $row_template = "";
            foreach ($this->_columns as $column){
                // Skip columns that are hidden
                if ($column->getContent('display') == "hidden")
                    continue;
                $row_template .= $column->getCellTemplate($row);
            }
            // Assign $row_template to each row
            $this->_rows[$id]->setTemplate("<tr>" . $row_template . "</tr>");
        }           
        
        $this->setContent('_table_body', $this->_rows);
        
        return parent::render();
    }

}

/*
    Magic fields:
    
    '@display' => :'show'|'hidden':
    '@initial_sort_direction' => :'asc'|'desc':,
    '@sorter' => :'metatext'|'metanum':,
    '@sort_field' => :field to sort on:,
    '@template' => :data template:
    '@empty_field' => :field to check if 'empty':,
    '@empty_value' => :value that should be considered 'empty':,
    '@empty_template' => :alternate template to use if empty:

    When rendered directly, TableColumnBuilder objects produce the column headers.  The getCellTemplate function is used to construct the rows of the parent TableBuilder object.    
*/



class TableColumnBuilder extends HtmlBuilder {
    protected $_cell_template;        // The template for the cells rendered by this column
    protected $_sorter;     // The sorter for this column.  "metatext" or "metanum".
    protected $_sort_field; // The name of the field to be sorted on for this column
    protected $_initial_sort_direction; // "asc" or "desc".
    protected $_display = "show";     // The display mode for this column.  "hidden" or "show".
    protected $_label = "";
    protected $_empty_template;
    protected $_empty_field;
    protected $_empty_value;

    public function __construct($content = [], $template_file = null, $options = []){
        // Load the specified template, or the default cell template
        if ($template_file)
            parent::__construct($content, $template_file, $options);
        else {
            parent::__construct($content, null, $options);
            $this->setTemplate("<th class='{{_sorter}}'>{{_label}} <i class='fa fa-sort'></i></th>");   // Default is hardcoded for now
        }
        
        // Set the cell template
        if (isset($content['@cell_template']))
            $this->_cell_template = $content['@cell_template'];
        else
            throw new \Exception("Each column in a TableBuilder must specify a '@cell_template' field.");
            
        // Set the column sorter
        if (isset($content['@sorter'])){
            $this->_sorter = $content['@sorter'];
        }
        else
            $this->_sorter = null;

        // Set the column sort field
        if (isset($content['@sort_field']))
            $this->_sort_field = $content['@sort_field'];
        else
            $this->_sort_field = null;            

        // Set the initial sort direction
        if (isset($content['@initial_sort_direction']))
            $this->_initial_sort_direction = $content['@initial_sort_direction'];
        else
            $this->_initial_sort_direction = null;                    
        
        // Set the display mode
        if (isset($content['@display']))
            $this->_display = $content['@display'];
        else
            $this->_display = null;            

        if (isset($content['@label'])){
            $this->label($content['@label']);
        }
        
        // Set the empty template
        if (isset($content['@empty_template']))
            $this->_empty_template = $content['@empty_template'];
        else
            $this->_empty_template = null;
            
        // Set the empty field
        if (isset($content['@empty_field']))
            $this->_empty_field = $content['@empty_field'];
        else
            $this->_empty_field = null;            
        
        // Set the empty value
        if (isset($content['@empty_value']))
            $this->_empty_value = $content['@empty_value'];
        else
            $this->_empty_value = null;
    }
    
    public function label($label){
        $this->_label = $label;
    }    
    
    // Return the appropriate cell template for this column, optionally based on the data supplied
    public function getCellTemplate($content = null){
        $td = "<td>";       // Default will be empty
        // If a sorter is set, construct the appropriate metadata td
        if ($this->_sorter){
            if (!$this->_sort_field)
                throw new \Exception("'sorter' cannot be defined without 'sort_field'.");
                
            $sort_field = $this->_sort_field;
 
            if ($this->_sorter == "metanum"){
                $td = "<td data-num='{{" . $sort_field . "}}'>";
            } else if ($this->_sorter == "metatext"){
                $td = "<td data-text='{{" . $sort_field . "}}'>";
            }
        }
        
        // If an empty_field name was specified, and its value matches the "empty value", render the empty template 
        if ($content && ($this->_empty_field != null) && ($content->getContent($this->_empty_field) == $this->_empty_value)){
            return $td . $this->_empty_template . "</td>";
        } else {
            return $td . $this->_cell_template . "</td>";
        }
    }
    
    public function getSortDirectionIndex(){
        if ($this->_initial_sort_direction == "asc")
            return "0";
        else if ($this->_initial_sort_direction == "desc")
            return "1";
        else
            return null;
    }
    
    public function getDisplay(){
        return $this->_display;
    }
    
    public function render(){
        if ($this->_sorter)
            $this->setContent('_sorter', "sorter-{$this->_sorter}");
        else
            $this->setContent('_sorter', "");
        $this->setContent('_label', $this->_label);
        return parent::render();
    }
    
}

?>