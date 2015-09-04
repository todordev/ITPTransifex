<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
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
    protected $sitePrefixes = array("site", "module", "library");
    protected $adminPrefixes = array("admin", "plugin");

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
        $id         = Joomla\Utilities\ArrayHelper::getValue($data, "id");
        $name       = Joomla\Utilities\ArrayHelper::getValue($data, "name");
        $alias      = Joomla\Utilities\ArrayHelper::getValue($data, "alias");
        $filename   = Joomla\Utilities\ArrayHelper::getValue($data, "filename");
        $desc       = Joomla\Utilities\ArrayHelper::getValue($data, "description");
        $link       = Joomla\Utilities\ArrayHelper::getValue($data, "link");
        $published  = Joomla\Utilities\ArrayHelper::getValue($data, "published");

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("name", $name);
        $row->set("alias", $alias);
        $row->set("filename", $filename);
        $row->set("description", $desc);
        $row->set("link", $link);
        $row->set("published", $published);

        $this->prepareImage($row, $data);
        $this->prepareTable($row);

        $row->store(true);

        return $row->get("id");
    }

    protected function prepareTable($table)
    {
        // get maximum order number
        if (!$table->get("id")) {

            // Set ordering to the last item if not set
            if (!$table->get("ordering")) {
                $db    = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select("MAX(ordering)")
                    ->from($db->quoteName("#__itptfx_projects"));

                $db->setQuery($query, 0, 1);
                $max = $db->loadResult();

                $table->set("ordering", $max + 1);
            }
        }

        // Fix magic quotes
        if (get_magic_quotes_gpc()) {
            $table->set("name", stripcslashes($table->get("name")));
            $table->set("description", stripcslashes($table->get("description")));
        }

        if (!$table->get("filename")) {
            $table->set("filename", null);
        }

        if (!$table->get("description")) {
            $table->set("description", null);
        }
    }

    /**
     * Prepare project image before saving.
     *
     * @param   object $table
     * @param   array  $data
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function prepareImage($table, $data)
    {
        // Prepare pitch image.
        if (!empty($data["image"])) {

            // Delete old image if I upload a new one
            if (!empty($table->image)) {

                $params       = JComponentHelper::getParams($this->option);
                $imagesFolder = $params->get("images_directory", "images/itptransifex");

                // Remove an image from the filesystem
                $image = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $imagesFolder . DIRECTORY_SEPARATOR . $table->image);

                if (JFile::exists($image)) {
                    JFile::delete($image);
                }
            }

            $table->set("image", $data["image"]);
        }
    }

    /**
     * Upload an image.
     *
     * @param  array $image
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadImage($image)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($image, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($image, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $destFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/itptransifex"));

        $tmpFolder = $app->get("tmp_path");

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        $file = new Prism\File\File();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $imageValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
        $imageValidator->setImageExtensions($imageExtensions);

        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Generate temporary file name
        $ext = Joomla\String\String::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));

        $generatedName = new Prism\String();
        $generatedName->generateRandomString(16);

        $tmpDestFile = $tmpFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext;

        // Prepare uploader object.
        $uploader = new Prism\File\Uploader\Local($uploadedFile);
        $uploader->setDestination($tmpDestFile);

        // Upload temporary file
        $file->setUploader($uploader);

        $file->upload();

        // Get file
        $tmpDestFile = $file->getFile();

        if (!is_file($tmpDestFile)) {
            throw new Exception('COM_ITPTRANSIFEX_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $image = new JImage();
        $image->loadFile($tmpDestFile);
        if (!$image->isLoaded()) {
            throw new Exception(JText::sprintf('COM_ITPTRANSIFEX_ERROR_FILE_NOT_FOUND', $tmpDestFile));
        }

        $imageName = $generatedName . ".png";
        $imageFile = JPath::clean($destFolder . DIRECTORY_SEPARATOR . $imageName);

        // Create main image
        $width  = $params->get("image_width", 200);
        $height = $params->get("image_height", 200);
        $scale  = $params->get("image_resizing_scale", 2);

        $image->resize($width, $height, false, $scale);
        $image->toFile($imageFile, IMAGETYPE_PNG);

        // Remove the temporary
        if (JFile::exists($tmpDestFile)) {
            JFile::delete($tmpDestFile);
        }

        return $imageName;
    }

    /**
     * Delete image.
     *
     * @param integer $id Item id
     */
    public function removeImage($id)
    {
        // Load category data
        $row = $this->getTable();
        $row->load($id);

        // Delete old image if I upload the new one
        if (!empty($row->image)) {

            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/itptransifex"));

            // Remove an image from the filesystem.
            $image = $imagesFolder . DIRECTORY_SEPARATOR . $row->image;

            if (JFile::exists($image)) {
                JFile::delete($image);
            }
        }

        $row->set("image", null);
        $row->store(true);
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

            $username = Joomla\Utilities\ArrayHelper::getValue($options, "username");
            $password = Joomla\Utilities\ArrayHelper::getValue($options, "password");
            $url      = Joomla\Utilities\ArrayHelper::getValue($options, "url");

            $headers = array(
                "headers" => array(
                    'Content-type: application/json',
                    'X-HTTP-Method-Override: GET'
                )
            );

            foreach ($projects as $project) {

                $transifex = new Prism\Transifex\Request($url);

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
        $username = Joomla\Utilities\ArrayHelper::getValue($options, "username");
        $password = Joomla\Utilities\ArrayHelper::getValue($options, "password");
        $url      = Joomla\Utilities\ArrayHelper::getValue($options, "url");

        $headers = array(
            "headers" => array(
                'Content-type: application/json',
                'X-HTTP-Method-Override: GET'
            )
        );

        $resources = array();

        // Get the resources from Transifex.
        foreach ($data as $alias => $itemData) {

            $transifex = new Prism\Transifex\Request($url);

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

                $prasedSlug = $this->parseSlug($item["slug"]);

                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query
                    ->insert($db->quoteName("#__itptfx_resources"))
                    ->set($db->quoteName('name') . "=" . $db->quote($item["name"]))
                    ->set($db->quoteName('i18n_type') . "=" . $db->quote($item["i18n_type"]))
                    ->set($db->quoteName('alias') . "=" . $db->quote($item["slug"]))
                    ->set($db->quoteName('project_id') . "=" . $db->quote($projectId))
                    ->set($db->quoteName('filename') . "=" . $db->quote($prasedSlug['filename']))
                    ->set($db->quoteName('type') . "=" . $db->quote($prasedSlug["type"]))
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
     * Separate resource such as are new for inserting,
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
                        $resource[$key] = Joomla\String\String::trim($value);
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

                Joomla\Utilities\ArrayHelper::toInteger($packagesIds);

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

    protected function parseSlug($slug)
    {
        $result = array(
            "type" => null,
            "filename" => null
        );

        $slugParts = explode("-", $slug);

        // Prepare type.
        if (in_array($slugParts[0], $this->sitePrefixes)) {
            $result["type"] = "site";
        } elseif (in_array($slugParts[0], $this->adminPrefixes)) {
            $result["type"] = "admin";
        }

        // Generate default file name.
        $suffix = substr($slugParts[1], -4, 4);
        if (strcmp("_sys", $suffix) != 0) {
            $result["filename"] = $slugParts[1].".ini";
        } else {
            $result["filename"] = substr($slugParts[1], 0, -4) .".sys.ini";
        }

        return $result;
    }
}
