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

jimport('joomla.application.component.modellist');

/**
 * This class contains methods that manage projects.
 * 
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelProjects extends JModelList {
    
	 /**
     * Constructor.
     *
     * @param   array   An optional associative array of configuration settings.
     * @see     JController
     * @since   1.6
     */
    public function  __construct($config = array()) {
        
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
            );
        }

        parent::__construct($config);
		
    }
    
    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null) {
        
        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);
        
        // Load the filter state.
        $value = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $value);

        // List state information.
        parent::populateState('a.id', 'asc');
        
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string      $id A prefix for the store id.
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '') {
        
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }
    
   /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.name, a.alias, a.filename'
            )
        );
        
        $query->from($db->quoteName("#__itptfx_projects", "a"));

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote("%" . $escaped . "%", false);
                $query->where('a.name LIKE '.$quoted);
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }
    
    protected function getOrderString() {
        
        $orderCol   = $this->getState('list.ordering');
        $orderDirn  = $this->getState('list.direction');
        
        return $orderCol.' '.$orderDirn;
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
     * Count resources of projects.
     * 
     * @return array
     */
    public function countResources() {
        
        $db    = $this->getDbo();
        
        $query = $db->getQuery(true);
        $query
            ->select("a.project_id, COUNT(*) AS number")
            ->from($db->quoteName("#__itptfx_resources", "a"))
            ->where($db->quoteName("a.published") . "= 1")
            ->group("a.project_id");
        
        $db->setQuery($query);
        $results = $db->loadObjectList("project_id");
        
        if(!$results) {
            $results = array();
        }
        
        return $results;
    }
}