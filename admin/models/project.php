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
    

    /**
     * This mothod loads a data about Project from Transifex.
     *
     * @param array Projects IDs
     * @param array Options for connection to Transifex ( username, password, URL, etc. )
     */
    public function synchronize($ids, $options) {
    
        $username = JArrayHelper::getValue($options, "username");
        $password = JArrayHelper::getValue($options, "password");
        $url      = JArrayHelper::getValue($options, "url");
    
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("a.id, a.alias")
            ->from($db->quoteName("#__itptfx_projects", "a"))
            ->where("a.id IN (".implode(",", $ids).")");
    
        $db->setQuery($query);
        $projects = $db->loadObjectList("alias");
    
        $data = array();
        $info = array();
    
        if(!empty($projects)) {
            foreach($projects as $project) {
    
                $projectUrl = $url."/".$project->alias."/";
    
                $ch         = curl_init();
    
                $headers = array();
                $headers[] = 'Content-type: application/json';
                $headers[] = 'X-HTTP-Method-Override: GET';
    
                curl_setopt($ch, CURLOPT_URL, $projectUrl);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch, CURLOPT_TIMEOUT, 400);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
                // Get the data
                $data[$project->alias] = curl_exec($ch);
    
                // Close the request
                curl_close($ch);
    
            }
        }
    
        if(!empty($data)) {
    
            $this->prepareProjectsData($projects, $data);
            $this->updateProjectsData($projects);
            $this->updateProjectsResources($projects, $options);
    
        }
    
    }
    
    /**
     * Prepare some data, that comes from Transifex. Encode JSON strings, etc.
     *
     * @param array $projects
     * @param array $data
     */
    public function prepareProjectsData(&$projects, $data) {
    
        foreach($data as $key => $value) {
    
            $item     = json_decode($value, true);
    
            $projects[$key]->description = JArrayHelper::getValue($item, "description");
            $projects[$key]->source_language_code = JArrayHelper::getValue($item, "source_language_code");
    
        }
    }
    
    /**
     * Store the data that comes from Transifex.
     *
     * @param array$projects
     */
    public function updateProjectsData($projects) {
    
        $db    = $this->getDbo();
    
        foreach($projects as $project) {
    
            $query = $db->getQuery(true);
            $query
                ->update("#__itptfx_projects")
                ->set($db->quoteName('description') . "=". $db->quote($project->description))
                ->set($db->quoteName('source_language_code') . "=". $db->quote($project->source_language_code))
                ->where($db->quoteName('id') ."=". $db->quote($project->id));
    
            $db->setQuery($query);
            $db->execute();
    
        }
    
    }
    
    /**
     * Load information about resources from transifex.
     *
     * @param array $data
     * @param array $options
     *
     * @todo Add functionality for deleting resources, if they are removed on Transifex.
     */
    public function updateProjectsResources($data, $options) {
    
        $username = JArrayHelper::getValue($options, "username");
        $password = JArrayHelper::getValue($options, "password");
        $url      = JArrayHelper::getValue($options, "url");
    
        $resources = array();
    
        // Get the resources from Transifex.
        foreach($data as $alias => $itemData) {
    
            $projectUrl = $url."/".$alias."/resources/";
    
            $ch         = curl_init();
    
            $headers = array();
            $headers[] = 'Content-type: application/json';
            $headers[] = 'X-HTTP-Method-Override: GET';
    
            curl_setopt($ch, CURLOPT_URL, $projectUrl);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
            // Get the data
            $resources[$itemData->id] = curl_exec($ch);
    
            // Close the request
            curl_close($ch);
    
        }
    
        // Store the data about the resources.
        if(!empty($resources)) {
    
            $db    = $this->getDbo();
            $query = $db->getQuery(true);
    
            $query
                ->select("a.id, a.alias, a.project_id")
                ->from($db->quoteName("#__itptfx_resources", "a"));
    
            $db->setQuery($query);
            $currentResources_ = $db->loadObjectList();
    
            // Set project ID as index
            $currendtResources = array();
            foreach($currentResources_ as $value) {
                $currentResources[$value->project_id][] = $value;
            }
            unset($currentResources_);
    
            // Prepare the resources for inserting, updating and deleting.
            $resourcesData = $this->prepareResources($resources, $currentResources);
    
            // Update resources
            if(!empty($resourcesData["update"])) {
                $this->updateResources($resourcesData["update"]);
            }
    
            // Insert resources
            if(!empty($resourcesData["insert"])) {
                $this->insertResources($resourcesData["insert"]);
            }
    
            // Delete resources
            if(!empty($resourcesData["delete"])) {
                $this->deleteResources($resourcesData["delete"]);
            }
    
        }
    
    }
    
    protected function insertResources($resources) {
    
        foreach($resources as $projectId => $value) {
    
            foreach($value as $item) {
    
                $db    = $this->getDbo();
                $query = $db->getQuery(true);
    
                $query
                ->insert($db->quoteName("#__itptfx_resources"))
                ->set($db->quoteName('name')        ."=". $db->quote($item->name))
                ->set($db->quoteName('category')    ."=". $db->quote($item->category))
                ->set($db->quoteName('i18n_type')   ."=". $db->quote($item->i18n_type))
                ->set($db->quoteName('alias')       ."=". $db->quote($item->slug))
                ->set($db->quoteName('project_id')  ."=". $db->quote($projectId))
                ->set($db->quoteName('source_language_code')  ."=". $db->quote($item->source_language_code));
    
                $db->setQuery($query);
                $db->execute();
    
            }
        }
    
    }
    
    protected function updateResources($resources) {
    
        foreach($resources as $projectId => $value) {
    
            foreach($value as $item) {
    
                $db    = $this->getDbo();
                $query = $db->getQuery(true);
    
                $query
                ->update($db->quoteName("#__itptfx_resources"))
                ->set($db->quoteName('source_language_code') ."=". $db->quote($item->source_language_code))
                ->set($db->quoteName('name')        ."=". $db->quote($item->name))
                ->set($db->quoteName('category')    ."=". $db->quote($item->category))
                ->set($db->quoteName('i18n_type')   ."=". $db->quote($item->i18n_type))
                ->where($db->quoteName('alias')     ."=". $db->quote($item->slug));
    
                $db->setQuery($query);
                $db->execute();
    
            }
    
        }
    }
    
    protected function deleteResources($resources) {
    
        foreach($resources as $projectId => $value) {
    
            foreach($value as $item) {
    
                $db    = $this->getDbo();
                $query = $db->getQuery(true);
    
                $query
                ->update($db->quoteName("#__itptfx_resources"))
                ->set($db->quoteName('published') ."=". $db->quote("-2"))
                ->where($db->quoteName('alias')   ."=". $db->quote($item->alias));
    
                $db->setQuery($query);
                $db->execute();
    
            }
    
        }
    }
    
    /**
     * Separate resource such as are new for isnerting,
     * old which will be deleted and those that will be updated.
     *
     * @param array $resources
     * @param array $currentResources
     * @return array
     */
    protected function prepareResources($resources, $currentResources) {
    
        $insert = array();
        $update = array();
        $delete = array();
    
        foreach($resources as $projectId => $resource) {
    
            $items = json_decode($resource);
    
            if(!isset($currentResources[$projectId])) { // Insert all items because it is a new project and it does not have items.
                $insert[$projectId] = $items;
            } else { // Insert, update and delete items to existed project.
    
                foreach($currentResources[$projectId] as $currentResource) {
    
                    $deleteFlag = true;
                    foreach($items as $item) {
    
                        // If there is a resource, add it for updating.
                        if( (strcmp($currentResource->alias, $item->slug) == 0 )) {
                            $update[$projectId][] = $item;
                            $deleteFlag = false;
                        }
    
                    }
    
                    // If the resources has been removed on Transifes,
                    // it have to be removed from the system too.
                    if($deleteFlag) {
                        $delete[$projectId][] = $currentResource;
                    }
    
                }
    
            }
    
        }
    
        $data = array(
            "update" => $update,
            "insert" => $insert,
            "delete" => $delete,
        );
    
        return $data;
    }
    
    /**
     * Remove rasources which are part of project.
     *
     * @param array Projects IDs
     */
    public function removeResources($cid) {
    
        if(!empty($cid)) {
            
            $db = $this->getDbo();
            
            foreach($cid as $id) {
                
                $query = $db->getQuery(true);
                $query
                    ->delete($db->quoteName("#__itptfx_resources"))
                    ->where($db->quoteName("project_id") ."=".(int)$id);
                    
                $db->setQuery($query);
                $db->execute();
                
            }
            
        }
    
    }
    
    /**
     * Remove packages which are part of project.
     *
     * @param array Projects IDs
     */
    public function removePackages($cid) {
    
        if(!empty($cid)) {
            
            $db = $this->getDbo();
            
            foreach($cid as $id) {
                
                // Get all packages
                $query = $db->getQuery(true);
                $query
                    ->select("a.id")
                    ->from($db->quoteName("#__itptfx_packages", "a"))
                    ->where($db->quoteName("project_id") ."=". (int)$id);
                
                $db->setQuery($query);
                $packagesIds = $db->loadColumn();
                
                dump($packagesIds);
                
                if(!$packagesIds) {
                    $packagesIds = array();
                }
                
                JArrayHelper::toInteger($packagesIds);
                
                if(!empty($packagesIds)) {
                    
                    // Remove packages maps
                    $query = $db->getQuery(true);
                    $query
                        ->delete($db->quoteName("#__itptfx_packages_map"))
                        ->where($db->quoteName("package_id") ." IN ( ". implode(",", $packagesIds) . " )");
                        
                    $db->setQuery($query);
                    $db->execute();
                    
                    // Remove packages
                    $query = $db->getQuery(true);
                    $query
                        ->delete($db->quoteName("#__itptfx_packages"))
                        ->where($db->quoteName("id"). " IN ( ". implode(",", $packagesIds) . " )");
                
                    $db->setQuery($query);
                    $db->execute();
                    
                }
                
            }
        }
        
    }
    
}