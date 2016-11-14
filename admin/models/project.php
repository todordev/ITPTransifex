<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

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
    protected $sitePrefixes = array();
    protected $adminPrefixes = array();

    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     *
     * @see     JModelLegacy
     * @since   12.2
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->sitePrefixes  = array('site', 'module', 'library');
        $this->adminPrefixes = array('admin', 'plugin');
    }

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
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
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

        if (!$data) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data   The data about item
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @throws \Exception
     *
     * @return   int  Item ID
     */
    public function save($data)
    {
        $id         = ArrayHelper::getValue($data, 'id', 0, 'int');
        $name       = StringHelper::trim(ArrayHelper::getValue($data, 'name', '', 'string'));
        $alias      = StringHelper::trim(ArrayHelper::getValue($data, 'alias', '', 'string'));
        $filename   = StringHelper::trim(ArrayHelper::getValue($data, 'filename', '', 'string'));
        $desc       = StringHelper::trim(ArrayHelper::getValue($data, 'description', '', 'string'));
        $link       = StringHelper::trim(ArrayHelper::getValue($data, 'link', '', 'string'));
        $published  = ArrayHelper::getValue($data, 'published', 0, 'int');

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('name', $name);
        $row->set('alias', $alias);
        $row->set('filename', $filename);
        $row->set('description', $desc);
        $row->set('link', $link);
        $row->set('published', $published);

        $this->prepareImage($row, $data);
        $this->prepareTable($row);

        $row->store(true);

        return $row->get('id');
    }

    protected function prepareTable($table)
    {
        // get maximum order number
        if (!$table->get('id') and !$table->get('ordering')) {
            // Set ordering to the last item if not set
            $db    = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('MAX(ordering)')
                ->from($db->quoteName('#__itptfx_projects'));

            $db->setQuery($query, 0, 1);
            $max = $db->loadResult();

            $table->set('ordering', $max + 1);
        }

        // Fix magic quotes.
        if (get_magic_quotes_gpc()) {
            $table->set('name', stripcslashes($table->get('name')));
            $table->set('description', stripcslashes($table->get('description')));
        }

        if (!$table->get('description')) {
            $table->set('description', null);
        }

        if (!$table->get('source_language_code')) {
            $table->set('source_language_code', null);
        }

        if (!$table->get('image')) {
            $table->set('image', null);
        }

        if (!$table->get('link')) {
            $table->set('link', null);
        }
    }

    /**
     * Prepare project image before saving.
     *
     * @param   JTable $table
     * @param   array  $data
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function prepareImage($table, $data)
    {
        // Prepare pitch image.
        if (array_key_exists('image', $data) and $data['image'] !== '') {
            // Delete old image if I upload a new one
            if ($table->get('image')) {
                $params       = JComponentHelper::getParams($this->option);
                $imagesFolder = $params->get('images_directory', 'images/itptransifex');

                // Remove an image from the filesystem
                $image = JPath::clean(JPATH_ROOT .'/'. $imagesFolder .'/'. $table->get('image'), '/');

                if (JFile::exists($image)) {
                    JFile::delete($image);
                }
            }

            $table->set('image', $data['image']);
        }
    }

    /**
     * Upload an image.
     *
     * @param  array $uploadedFileData
     *
     * @throws Exception
     *
     * @return string
     */
    public function uploadImage($uploadedFileData)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams('com_media');
        /** @var  $mediaParams Joomla\Registry\Registry */

        // Prepare size validator.
        $KB            = pow(1024, 2);
        $fileSize      = ArrayHelper::getValue($uploadedFileData, 'size', 0, 'int');
        $uploadMaxSize = $mediaParams->get('upload_maxsize') * $KB;

        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(',', $mediaParams->get('upload_mime'));
        $imageValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $imageExtensions = explode(',', $mediaParams->get('image_extensions'));
        $imageValidator->setImageExtensions($imageExtensions);

        $file = new Prism\File\File($uploadedFile);
        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Upload the file in temporary folder
        $temporaryFolder = JPath::clean($app->get('tmp_path'), '/');
        $filesystemLocal = new Prism\Filesystem\Adapter\Local($temporaryFolder);
        $sourceFile      = $filesystemLocal->upload($uploadedFileData);

        if (!JFile::exists($sourceFile)) {
            throw new RuntimeException('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Load component parameters.
        $params            = JComponentHelper::getParams($this->option);
        $destinationFolder = JPath::clean(JPATH_ROOT .'/'. $params->get('images_directory', 'images/itptransifex'), '/');

        // Create main image
        $options = new Joomla\Registry\Registry();
        $options->set('filename_length', 24);
        $options->set('scale', $params->get('image_resizing_scale', \JImage::SCALE_INSIDE));
        $options->set('quality', $params->get('image_quality', Prism\Constants::QUALITY_HIGH));
        $options->set('width', $params->get('image_width', 200));
        $options->set('height', $params->get('image_height', 200));
        $options->set('filename_length', 16);

        $image   = new Prism\File\Image($sourceFile);
        $result  = $image->resize($destinationFolder, $options);

        if (JFile::exists($sourceFile)) {
            JFile::delete($sourceFile);
        }
        
        return $result['filename'];
    }

    /**
     * Delete image.
     *
     * @param integer $id Item id
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    public function removeImage($id)
    {
        // Load category data
        $row = $this->getTable();
        $row->load($id);

        // Delete old image if I upload the new one
        if ($row->get('image')) {
            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = JPath::clean(JPATH_ROOT .'/'. $params->get('images_directory', 'images/itptransifex'), '/');

            // Remove an image from the filesystem.
            $image = $imagesFolder .'/'. $row->get('image');
            if (JFile::exists($image)) {
                JFile::delete($image);
            }
        }

        $row->set('image', null);
        $row->store(true);
    }
    
    /**
     * This method loads a data about Project from Transifex.
     *
     * @param array $ids Projects IDs
     * @param array $options Options for connection to Transifex ( username, password, URL, etc. )
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function synchronize($ids, $options)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('a.id, a.alias')
            ->from($db->quoteName('#__itptfx_projects', 'a'))
            ->where('a.id IN (' . implode(',', $ids) . ')');

        $db->setQuery($query);
        $projects = (array)$db->loadObjectList('alias');

        if (count($projects) > 0) {
            $data = array();

            $username = ArrayHelper::getValue($options, 'username');
            $password = ArrayHelper::getValue($options, 'password');
            $url      = ArrayHelper::getValue($options, 'url');

            $headers = array(
                'headers' => array(
                    'Content-type: application/json',
                    'X-HTTP-Method-Override: GET'
                )
            );

            foreach ($projects as $project) {
                $transifex = new Prism\Transifex\Request($url);

                $transifex->setUsername($username);
                $transifex->setPassword($password);
                $transifex->enableAuthentication();

                $path = '/' . $project->alias . '/';

                $response = $transifex->get($path, $headers);

                // Get the data
                $data[$project->alias] = $response;
            }

            if (count($data) > 0) {
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
     *
     * @throws \RuntimeException
     */
    protected function updateProjectsData($projects)
    {
        foreach ($projects as $project) {
            $db     = $this->getDbo();
            $query  = $db->getQuery(true);
            $query
                ->update($db->quoteName('#__itptfx_projects'))
                ->set($db->quoteName('description') . '=' . $db->quote($project->description))
                ->set($db->quoteName('source_language_code') . '=' . $db->quote($project->source_language_code))
                ->where($db->quoteName('id') . '=' . $db->quote($project->id));

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
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @todo Add functionality for deleting resources, if they are removed on Transifex.
     */
    protected function updateProjectsResources($data, $options)
    {
        $username = ArrayHelper::getValue($options, 'username');
        $password = ArrayHelper::getValue($options, 'password');
        $url      = ArrayHelper::getValue($options, 'url');

        $headers = array(
            'headers' => array(
                'Content-type: application/json',
                'X-HTTP-Method-Override: GET'
            )
        );

        $resources = array();

        $transifex = new Prism\Transifex\Request($url);

        $transifex->setUsername($username);
        $transifex->setPassword($password);
        $transifex->enableAuthentication();

        // Get the resources from Transifex.
        foreach ($data as $alias => $itemData) {
            $t    = clone $transifex;
            $path = '/' . $alias . '/resources/';

            $response = $t->get($path, $headers);

            // Get the data
            $resources[$itemData->id] = $response;
        }

        // Store the data about the resources.
        if (count($resources) > 0) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('a.id, a.alias, a.project_id')
                ->from($db->quoteName('#__itptfx_resources', 'a'));

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
            if (!empty($resourcesData['update'])) {
                $this->updateResources($resourcesData['update']);
            }

            // Insert resources
            if (!empty($resourcesData['insert'])) {
                $this->insertResources($resourcesData['insert']);
            }

            // Delete resources
            if (!empty($resourcesData['delete'])) {
                $this->deleteResources($resourcesData['delete']);
            }
        }
    }

    protected function insertResources($resources)
    {
        foreach ($resources as $projectId => $value) {
            foreach ($value as $item) {
                $parsedSlug = $this->parseSlug($item['slug']);
                $category   = ArrayHelper::getValue($item['categories'], 0, '', 'string');

                $db    = $this->getDbo();
                $query = $db->getQuery(true);

                $query
                    ->insert($db->quoteName('#__itptfx_resources'))
                    ->set($db->quoteName('name') . '=' . $db->quote($item['name']))
                    ->set($db->quoteName('i18n_type') . '=' . $db->quote($item['i18n_type']))
                    ->set($db->quoteName('alias') . '=' . $db->quote($item['slug']))
                    ->set($db->quoteName('project_id') . '=' . $db->quote($projectId))
                    ->set($db->quoteName('filename') . '=' . $db->quote($parsedSlug['filename']))
                    ->set($db->quoteName('category') . '=' . $db->quote($category))
                    ->set($db->quoteName('source_language_code') . '=' . $db->quote($item['source_language_code']));

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

                $langCode = (!empty($item['source_language_code'])) ? $db->quote($item['source_language_code']) : 'NULL';
                $type     = (!empty($item['i18n_type'])) ? $db->quote($item['i18n_type']) : 'NULL';
                $category = ArrayHelper::getValue($item['categories'], 0, '', 'string');

                $query
                    ->update($db->quoteName('#__itptfx_resources'))
                    ->set($db->quoteName('name') . '=' . $db->quote($item['name']))
                    ->set($db->quoteName('i18n_type') . '=' . $type)
                    ->set($db->quoteName('source_language_code') . '=' . $langCode)
                    ->set($db->quoteName('category') . '=' . $db->quote($category))
                    ->where($db->quoteName('alias') . '=' . $db->quote($item['slug']));

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
                    ->update($db->quoteName('#__itptfx_resources'))
                    ->set($db->quoteName('published') . '=' . $db->quote(Prism\Constants::TRASHED))
                    ->where($db->quoteName('alias') . '=' . $db->quote($item->alias));

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
            foreach ($items as $key => $resource) {
                foreach ($resource as $key2 => $value) {
                    if (is_scalar($value)) {
                        $resource[$key][$key2] = StringHelper::trim($value);
                    }
                }
            }

            if (!array_key_exists($projectId, $currentResources)) { // Insert all items because it is a new project and it does not have items.
                $insert[$projectId] = $items;
            } else { // Insert, update and delete items to existed project.

                // Get the new resources.
                foreach ($items as $item) {
                    $isNew = true;
                    foreach ($currentResources[$projectId] as $currentResource) {
                        if (strcmp($item['slug'], $currentResource->alias) === 0) {
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
                        if (strcmp($currentResource->alias, $item['slug']) === 0) {
                            $update[$projectId][] = $item;
                            $deleteFlag           = false;
                        }
                    }

                    // If the resources has been removed on Transifex,
                    // it have to be removed from the system too.
                    if ($deleteFlag) {
                        $delete[$projectId][] = $currentResource;
                    }
                }
            }
        }

        $data = array(
            'update' => $update,
            'insert' => $insert,
            'delete' => $delete,
        );

        return $data;
    }

    /**
     * Remove resources which are part of project.
     *
     * @param array $projectsIds
     *
     * @throws RuntimeException
     */
    public function removeResources(array $projectsIds = array())
    {
        if (count($projectsIds) > 0) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete($db->quoteName('#__itptfx_resources'))
                ->where($db->quoteName('project_id') . ' IN (' . implode(',', $projectsIds) .')');

            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Remove packages which are part of project.
     *
     * @param array $projectsIds
     *
     * @throws \RuntimeException
     */
    public function removePackages(array $projectsIds = array())
    {
        if (count($projectsIds) > 0) {
            foreach ($projectsIds as $id) {
                $db = $this->getDbo();
                
                // Get all packages
                $query = $db->getQuery(true);
                $query
                    ->select('a.id')
                    ->from($db->quoteName('#__itptfx_packages', 'a'))
                    ->where($db->quoteName('project_id') . '=' . (int)$id);

                $db->setQuery($query);
                $packagesIds = (array)$db->loadColumn();

                $packagesIds = ArrayHelper::toInteger($packagesIds);

                if (count($packagesIds) > 0) {
                    // Remove packages maps
                    $query = $db->getQuery(true);
                    $query
                        ->delete($db->quoteName('#__itptfx_packages_map'))
                        ->where($db->quoteName('package_id') . ' IN ( ' . implode(',', $packagesIds) . ' )');

                    $db->setQuery($query);
                    $db->execute();

                    // Remove packages
                    $query = $db->getQuery(true);
                    $query
                        ->delete($db->quoteName('#__itptfx_packages'))
                        ->where($db->quoteName('id') . ' IN ( ' . implode(',', $packagesIds) . ' )');

                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
    }

    protected function parseSlug($slug)
    {
        $result = array(
            'type' => null,
            'filename' => null
        );

        $slugParts = explode('-', $slug);

        // Prepare type.
        if (in_array($slugParts[0], $this->sitePrefixes, true)) {
            $result['type'] = 'site';
        } elseif (in_array($slugParts[0], $this->adminPrefixes, true)) {
            $result['type'] = 'admin';
        }

        // Generate default file name.
        $suffix = substr($slugParts[1], -4, 4);
        if (strcmp('_sys', $suffix) !== 0) {
            $result['filename'] = $slugParts[1].'.ini';
        } else {
            $result['filename'] = substr($slugParts[1], 0, -4) .'.sys.ini';
        }

        return $result;
    }
}
