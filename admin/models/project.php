<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * This model provides functionality for managing user project.
 * 
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelProject extends JModelAdmin {
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'ItpTransifexTable', $config = array()){
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to get the record form.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true){
        
        // Get the form.
        $form = $this->loadForm($this->option.'.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)){
            return false;
        }
        
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData(){
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.project.data', array());
        
        if(empty($data)){
            $data = $this->getItem();
        }
        
        return $data;
    }
    
    /**
     * Save data into the DB
     * 
     * @param $data   The data about item
     * 
     * @return     Item ID
     */
    public function save($data){
        
        $id         = JArrayHelper::getValue($data, "id");
        $name       = JArrayHelper::getValue($data, "name");
        $alias      = JArrayHelper::getValue($data, "alias");
        $filename   = JArrayHelper::getValue($data, "filename");
        $desc       = JArrayHelper::getValue($data, "description");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);
        
        $row->set("name",           $name);
        $row->set("alias",          $alias);
        $row->set("filename",       $filename);
        $row->set("description",    $desc);
        
        $this->prepareTable($row);
        
        $row->store(true);
        
        return $row->id;
    }
    
    /**
     * Prepare and sanitise the table prior to saving.
     * @since	1.6
     */
    protected function prepareTable(&$table) {
         
        // Fix magic qutoes
        if( get_magic_quotes_gpc() ) {
            $table->name            = stripcslashes($table->name);
            $table->description     = stripcslashes($table->description);
        }
        
        if(empty($table->filename)) {
            $table->filename = null;
        }
        
        if(empty($table->description)) {
            $table->description = null;
        }
        
    }
    
}