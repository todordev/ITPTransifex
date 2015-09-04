<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class ItpTransifexModelProject extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                "id", "a.id"
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering
     * @param string $direction
     *
     * @return  void
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        parent::populateState("a.name", "ASC");

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        // Set project id.
        $value = $app->input->get("id", 0, "uint");
        $this->setState($this->context . '.id', $value);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState($this->context . '.id');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a sub query object.
        $projectId = $this->getState($this->context.".id");

        $subQuery = $db->getQuery(true);
        $subQuery
            ->select("a.language")
            ->from($db->quoteName("#__itptfx_packages", "a"))
            ->where("a.project_id = ".(int)$projectId)
            ->group("a.language");

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.name, a.code, a.short_code'
            )
        );
        $query->from($db->quoteName('#__itptfx_languages', 'a'));
        $query->where("a.code IN (" .$subQuery. ")");

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState("list.ordering", "a.title");
        $orderDirn = $this->getState("list.direction", "ASC");

        $orderString = $orderCol . ' ' . $orderDirn;

        return $orderString;
    }

    public function getPackagesNumber($projectId)
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);
        $query
            ->select("COUNT(*) as number, a.language")
            ->from($db->quoteName("#__itptfx_packages", "a"))
            ->where("a.project_id = ".(int)$projectId)
            ->group("a.language");

        $db->setQuery($query);
        $results = $db->loadAssocList("language");

        if (!$results) {
            $results = array();
        }

        return $results;
    }
}
