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

/**
 * This model provides functionality for managing user project.
 *
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelProject extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'ItpTransifexTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
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
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.project.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data   The data about item
     *
     * @return   int  Item ID
     */
    public function save($data)
    {

        $id       = JArrayHelper::getValue($data, "id");
        $name     = JArrayHelper::getValue($data, "name");
        $alias    = JArrayHelper::getValue($data, "alias");
        $filename = JArrayHelper::getValue($data, "filename");
        $desc     = JArrayHelper::getValue($data, "description");

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("name", $name);
        $row->set("alias", $alias);
        $row->set("filename", $filename);
        $row->set("description", $desc);

        $this->prepareTable($row);

        $row->store(true);

        return $row->get("id");
    }

    /**
     * Prepare and sanitise the table prior to saving.
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        // Fix magic quotes
        if (get_magic_quotes_gpc()) {
            $table->set("name", stripcslashes($table->get("name")));
            $table->set("description", stripcslashes($table->get("description")));
        }

        if ($table->get("filename")) {
            $table->set("filename", null);
        }

        if ($table->get("description")) {
            $table->set("description", null);
        }

    }


    /**
     * This method loads a data about Project from Transifex.
     *
     * @param array $ids Projects IDs
     * @param array $options Options for connection to Transifex ( username, password, URL, etc. )
     */
    public function synchronize($ids, $options)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("a.id, a.alias")
            ->from($db->quoteName("#__itptfx_projects", "a"))
            ->where("a.id IN (" . implode(",", $ids) . ")");

        $db->setQuery($query);
        $projects = $db->loadObjectList("alias");

        if (!empty($projects)) {

            $data = array();

            $username = JArrayHelper::getValue($options, "username");
            $password = JArrayHelper::getValue($options, "password");
            $url      = JArrayHelper::getValue($options, "url");

            jimport("itprism.transifex.request");

            $headers = array(
                "headers" => array(
                    'Content-type: application/json',
                    'X-HTTP-Method-Override: GET'
                )
            );

            foreach ($projects as $project) {

                $transifex = new ITPrismTransifexRequest($url);

                $transifex->setUsername($username);
                $transifex->setPassword($password);
                $transifex->enableAuthentication();

                $path = "/" . $project->alias . "/";

                $response = $transifex->get($path, $headers);

                // Get the data
                $data[$project->alias] = $response;

            }

            if (!empty($data)) {

                $this->prepareProjectsData($projects, $data);
                $this->updateProjectsData($projects);
                $this->updateProjectsResources($projects, $options);

            }
        }
    }

    /**
     * Prepare some data, that comes from Transifex. Encode JSON strings, etc.
     *
     * @param array $projects
     * @param array $data
     */
    public function prepareProjectsData(&$projects, $data)
    {
        foreach ($data as $key => $item) {
            $projects[$key]->description          = $item->description;
            $projects[$key]->source_language_code = $item->source_language_code;
        }
    }

    /**
     * Store the data that comes from Transifex.
     *
     * @param array $projects
     */
    protected function updateProjectsData($projects)
    {
        $db = $this->getDbo();

        foreach ($projects as $project) {
            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName("#__itptfx_projects"))
                ->set($db->quoteName('description') . "=" . $db->quote($project->description))
                ->set($db->quoteName('source_language_code') . "=" . $db->quote($project->source_language_code))
                ->where($db->quoteName('id') . "=" . $db->quote($project->id));

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
    protected function updateProjectsResources($data, $options)
    {
        $username = JArrayHelper::getValue($options, "username");
        $password = JArrayHelper::getValue($options, "password");
        $url      = JArrayHelper::getValue($options, "url");

        $headers = array(
            "headers" => array(
                'Content-type: application/json',
                'X-HTTP-Method-Override: GET'
            )
        );

        $resources = array();

        // Get the resources from Transifex.
        foreach ($data as $alias => $itemData) {

            $transifex = new ITPrismTransifexRequest($url);

            $transifex->setUsername($username);
            $transifex->setPassword($password);
            $transifex->enableAuthentication();

            $path = "/" . $alias . "/resources/";

            $response = $transifex->get($path, $headers);

            // Get the data
            $resources[$itemData->id] = $response;

        }

        // Store the data about the resources.
        if (!empty($resources)) {

            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select("a.id, a.alias, a.project_id")
                ->from($db->quoteName("#__itptfx_resources", "a"));

            $db->setQuery($query);
            $currentResources_ = $db->loadObjectList();

            // Set project ID as index
            $currentResources = array();
            foreach ($currentResources_ as $value) {
                $currentResources[$value->project_id][] = $value;
            }
            unset($currentResources_);

            // Prepare the resources for inserting, updating and deleting.
            $resourcesData = $this->prepareResources($resources, $currentResources);

            // Update resources
            if (!empty($resourcesData["update"])) {
                $this->updateResources($resourcesData["update"]);
            }

            // Insert resources
            if (!empty($resourcesData["insert"])) {
                $this->insertResources($resourcesData["insert"]);
            }

            // Delete resources
            if (!empty($resourcesData["delete"])) {
                $this->deleteResources($resourcesData["delete"]);
            }
        }
    }

    protected function insertResources($resources)
    {
        foreach ($resources as $projectId => $value) {

            foreach ($value as $item) {

                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query
                    ->insert($db->quoteName("#__itptfx_resources"))
                    ->set($db->quoteName('name') . "=" . $db->quote($item["name"]))
                    ->set($db->quoteName('i18n_type') . "=" . $db->quote($item["i18n_type"]))
                    ->set($db->quoteName('alias') . "=" . $db->quote($item["slug"]))
                    ->set($db->quoteName('project_id') . "=" . $db->quote($projectId))
                    ->set($db->quoteName('source_language_code') . "=" . $db->quote($item["source_language_code"]));

                $db->setQuery($query);
                $db->execute();

            }
        }
    }

    protected function updateResources($resources)
    {
        foreach ($resources as $value) {

            foreach ($value as $item) {

                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $langCode = (!empty($item["source_language_code"])) ? $db->quote($item["source_language_code"]) : "NULL";
                $type     = (!empty($item["i18n_type"])) ? $db->quote($item["i18n_type"]) : "NULL";

                $query
                    ->update($db->quoteName("#__itptfx_resources"))
                    ->set($db->quoteName('name') . "=" . $db->quote($item["name"]))
                    ->set($db->quoteName('i18n_type') . "=" . $type)
                    ->set($db->quoteName('source_language_code') . "=" . $langCode)
                    ->where($db->quoteName('alias') . "=" . $db->quote($item["slug"]));

                $db->setQuery($query);
                $db->execute();

            }

        }
    }

    protected function deleteResources($resources)
    {
        foreach ($resources as $value) {

            foreach ($value as $item) {

                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query
                    ->update($db->quoteName("#__itptfx_resources"))
                    ->set($db->quoteName('published') . "=" . $db->quote("-2"))
                    ->where($db->quoteName('alias') . "=" . $db->quote($item->alias));

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
     *
     * @return array
     */
    protected function prepareResources($resources, $currentResources)
    {
        $insert = array();
        $update = array();
        $delete = array();

        foreach ($resources as $projectId => $items) {

            // Clear white spaces.
            foreach ($items as &$resource) {
                foreach ($resource as $key => $value) {

                    if (is_scalar($value)) {
                        $resource[$key] = JString::trim($value);
                    }
                }

            }

            if (!isset($currentResources[$projectId])) { // Insert all items because it is a new project and it does not have items.
                $insert[$projectId] = $items;
            } else { // Insert, update and delete items to existed project.

                // Get the new resources.
                foreach ($items as $item) {

                    $isNew = true;
                    foreach ($currentResources[$projectId] as $currentResource) {
                        if (strcmp($item["slug"], $currentResource->alias) == 0) {
                            $isNew = false;
                            break;
                        }
                    }

                    if ($isNew) {
                        $insert[$projectId][] = $item;
                    }

                }

                // Update current resources and remove missing ones.
                foreach ($currentResources[$projectId] as $currentResource) {

                    $deleteFlag = true;
                    foreach ($items as $item) {

                        // If there is a resource, add it for updating.
                        if ((strcmp($currentResource->alias, $item["slug"]) == 0)) {
                            $update[$projectId][] = $item;
                            $deleteFlag           = false;
                        }

                    }

                    // If the resources has been removed on Transifes,
                    // it have to be removed from the system too.
                    if ($deleteFlag) {
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
     * Remove resources which are part of project.
     *
     * @param array $cid Projects IDs
     */
    public function removeResources($cid)
    {

        if (!empty($cid)) {

            $db = $this->getDbo();

            foreach ($cid as $id) {

                $query = $db->getQuery(true);
                $query
                    ->delete($db->quoteName("#__itptfx_resources"))
                    ->where($db->quoteName("project_id") . "=" . (int)$id);

                $db->setQuery($query);
                $db->execute();

            }

        }

    }

    /**
     * Remove packages which are part of project.
     *
     * @param array $cid Projects IDs
     */
    public function removePackages($cid)
    {

        if (!empty($cid)) {

            $db = $this->getDbo();

            foreach ($cid as $id) {

                // Get all packages
                $query = $db->getQuery(true);
                $query
                    ->select("a.id")
                    ->from($db->quoteName("#__itptfx_packages", "a"))
                    ->where($db->quoteName("project_id") . "=" . (int)$id);

                $db->setQuery($query);
                $packagesIds = $db->loadColumn();

                if (!$packagesIds) {
                    $packagesIds = array();
                }

                JArrayHelper::toInteger($packagesIds);

                if (!empty($packagesIds)) {

                    // Remove packages maps
                    $query = $db->getQuery(true);
                    $query
                        ->delete($db->quoteName("#__itptfx_packages_map"))
                        ->where($db->quoteName("package_id") . " IN ( " . implode(",", $packagesIds) . " )");

                    $db->setQuery($query);
                    $db->execute();

                    // Remove packages
                    $query = $db->getQuery(true);
                    $query
                        ->delete($db->quoteName("#__itptfx_packages"))
                        ->where($db->quoteName("id") . " IN ( " . implode(",", $packagesIds) . " )");

                    $db->setQuery($query);
                    $db->execute();

                }

            }
        }

    }
}
