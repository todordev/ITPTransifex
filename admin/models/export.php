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

class ItpTransifexModelExport extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);

        // Load the filter state.
        $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
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
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

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

        // Create a new query object.
        $query = $db->getQuery(true);

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
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote("%" . $escaped . "%", false);
                $query->where('a.name LIKE ' . $quoted);
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering');
        $orderDirn = $this->getState('list.direction');

        return $orderCol . ' ' . $orderDirn;
    }

    /**
     * Prepare project meta data.
     *
     * @param Transifex\Project\Project $project
     * @param string $language
     *
     * @return string
     */
    public function getProject($project, $language)
    {
        // Get packages
        $options  = array(
            "project_id" => $project->getId(),
            "language" => $language
        );

        $packages = new Transifex\Package\Packages(JFactory::getDbo());
        $packages->load($options);

        $resources = $packages->getResources();
        
        return $this->prepareXML($project, $packages, $resources);
    }

    /**
     * Prepare the XML file that contains project data - information, packages, resources.
     *
     * @param Transifex\Project\Project $project
     * @param Transifex\Package\Packages $packages
     * @param array $resources
     *
     * @return string
     */
    protected function prepareXML($project, $packages, $resources)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><project/>');
        $xml->addAttribute("generator", "com_itptransifex");

        // Set project data.
        $xml->addChild("name", $project->getName());
        $xml->addChild("alias", $project->getAlias());
        $xml->addChild("description", $project->getDescription());
        $xml->addChild("source_language_code", $project->getLanguage());
        $xml->addChild("filename", $project->getFilename());

        // Create package items.
        $ignorePackageKeys = array("id", "project_id");

        foreach ($packages as $package) {

            $item = $xml->addChild("package");

            foreach ($package as $key => $value) {

                if (in_array($key, $ignorePackageKeys)) {
                    continue;
                }

                $item->addChild($key, $value);
            }
        }

        // Create resource items
        $ignorePackageKeys = array("id", "project_id", "package_id");
        foreach ($resources as $resource) {

            $item = $xml->addChild("resource");

            foreach ($resource as $key => $value) {

                if (in_array($key, $ignorePackageKeys)) {
                    continue;
                }

                $item->addChild($key, $value);
            }
        }

        $dom               = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}
