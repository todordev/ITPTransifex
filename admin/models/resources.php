<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Get a list of items
 * 
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelResources extends JModelList {
    
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
        
        // List state information.
        parent::populateState('a.id', 'asc');
        
        $app       = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);
        
        // Load the filter state.
        $value = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $value);
        
        // Get project ID
        $value = $this->getUserStateFromRequest($this->context.'.project_id', 'id');
        $this->setState('project_id', $value);
        
        // Filter type
        $value = $this->getUserStateFromRequest($this->context.'.filter.type', 'filter_type');
        $this->setState('filter.type', $value);
        
        // Filter state
        $value = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $value);

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
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('project_id');

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
                'a.id, a.name, a.alias, a.filename, a.type, ' .
                'a.published, a.source_language_code'
            )
        );
        
        $query->from($db->quoteName("#__itptfx_resources", "a"));
        
        $query->where('a.project_id = '.(int)$this->getState("project_id"));
        
        // Filter by type
        $type = $this->getState('filter.type');
        if(!empty($type)) {
            $query->where('a.type = '. $db->quote($type));
        }
        
        // Filter by state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.published = '.(int) $published);
        } else if ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }
        
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
       
    public function getLanguages() {
    
        $db    = $this->getDbo();
        
        // Prepare project folder
        $query = $db->getQuery(true);
        
        $query
            ->select("a.id, a.name, a.code, a.short_code")
            ->from($db->quoteName("#__itptfx_languages", "a"));
        
        $db->setQuery($query);
        $results = $db->loadObjectList();
        
        if(!$results) {
            $results = array();
        }
        
        return $results;
    }
    
}