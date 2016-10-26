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
 * This model provides functionality for managing a package.
 *
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelPackage extends JModelAdmin
{
    protected $options = array();

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array $config Configuration array for model. Optional.
     *
     * @return  ItpTransifexTablePackage|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Package', $prefix = 'ItpTransifexTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.package', 'package', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.package.data', array());

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
     * @return   int  Item ID
     */
    public function save($data)
    {
        $id             = ArrayHelper::getValue($data, 'id', 0, 'int');
        $name           = StringHelper::trim(ArrayHelper::getValue($data, 'name'));
        $alias          = StringHelper::trim(ArrayHelper::getValue($data, 'alias'));
        $filename       = StringHelper::trim(ArrayHelper::getValue($data, 'filename'));
        $description    = StringHelper::trim(ArrayHelper::getValue($data, 'description'));
        $version        = StringHelper::trim(ArrayHelper::getValue($data, 'version'));
        $type           = ArrayHelper::getValue($data, 'type');
        $language       = ArrayHelper::getValue($data, 'language');
        $projectId      = ArrayHelper::getValue($data, 'project_id', 0, 'int');

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('name', $name);
        $row->set('alias', $alias);
        $row->set('filename', $filename);
        $row->set('description', $description);
        $row->set('version', $version);
        $row->set('language', $language);
        $row->set('type', $type);
        $row->set('project_id', $projectId);

        $this->prepareTable($row);

        $row->store(true);

        return $row->get('id');
    }

    /**
     * Save the resources to package map database.
     *
     * @param integer $packageId
     * @param array $resourcesIDs
     *
     * @throws \RuntimeException
     */
    public function saveResourcesIds($packageId, $resourcesIDs)
    {
        $db = $this->getDbo();

        // Get existed resources
        $query = $db->getQuery(true);
        $query
            ->select('a.resource_id')
            ->from($db->quoteName('#__itptfx_packages_map', 'a'))
            ->where('a.package_id = ' . (int)$packageId);

        $db->setQuery($query);
        $results = $db->loadColumn();

        $results = ArrayHelper::toInteger($results);

        // Prepare these resources that does not exist.
        foreach ($results as $resourceId) {
            $key = array_search((int)$resourceId, $resourcesIDs, true);
            if (false !== $key) {
                unset($resourcesIDs[$key]);
            }
        }

        // Add new resources to the map.
        if (count($resourcesIDs) > 0) {
            $columns = array('package_id', 'resource_id');

            $values = array();
            foreach ($resourcesIDs as $resourceId) {
                $values[] = (int)$packageId . ',' . (int)$resourceId;
            }

            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName('#__itptfx_packages_map'))
                ->columns($columns)
                ->values($values);

            $db->setQuery($query);
            $db->execute();
        }
    }
    
    protected function prepareTable($table)
    {
        // Fix magic quotes
        if (get_magic_quotes_gpc()) {
            $table->set('name', stripcslashes($table->get('name')));
            $table->set('description', stripcslashes($table->get('description')));
        }

        if (!$table->get('filename')) {
            $table->set('filename', null);
        }

        if (!$table->get('description')) {
            $table->set('description', null);
        }

        // If an alias does not exist, I will generate the new one using the title.
        if (!$table->get('alias')) {
            $table->set('alias', $table->get('name').'-'.$table->get('language'));
        }
        $table->set('alias', Prism\Utilities\StringHelper::stringUrlSafe($table->get('alias')));

        // Prepare language code.
        $langCode1 = str_replace('-', '_', substr($table->get('alias'), -5, 5));
        $langCode2 = StringHelper::strtolower($table->get('language'));
        if (strcmp($langCode1, $langCode2) === 0) {
            $alias = substr($table->get('alias'), 0, -5);
            $table->set('alias', $alias . $langCode2);
        }

        // Check for existing alias.
        if (!$table->get('id') and $this->isAliasExists($table->get('alias'))) {
            $table->set('alias', Prism\Utilities\StringHelper::generateRandomString(16) .'-'. $langCode2);
        }
    }

    protected function isAliasExists($alias)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__itptfx_packages', 'a'))
            ->where('a.alias = ' . $db->quote($alias));

        $db->setQuery($query, 0, 1);

        return (bool)$db->loadResult();
    }

    /**
     * Prepare a package - download all files, create a manifest file,...
     *
     * @param int $packageId
     * @param bool $includeLanguageName Include or not language name to package name.
     *
     * @throws Exception
     * @return string
     */
    public function preparePackage($packageId, $includeLanguageName = true)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $db = $this->getDbo();

        // Get package.
        $package = new Transifex\Package\Package($db);
        $package->load($packageId);

        // Prepare project folder
        $project = new Transifex\Project\Project($db);
        $project->load($package->getProjectId());

        // Prepare project URL that points to Transifex.
        $this->options['project_path'] = 'project/' . $project->getAlias();

        $packageFileName = $package->getFilename();
        $packageFolder   = JPath::clean($app->get('tmp_path') . DIRECTORY_SEPARATOR . $packageFileName);

        // Remove old folder
        if (JFolder::exists($packageFolder)) {
            JFolder::delete($packageFolder);
        }

        // Create folder
        JFolder::create($packageFolder);

        // Get project resources
        $published = 1;
        $resources = $package->getResources($published);

        $packageType = $package->getType();

        if (strcmp('plugin', $packageType) === 0) {
            $packageFile = $this->preparePlugin($package, $resources, $packageFolder, $includeLanguageName);
        } else {
            $packageFile = $this->prepareExtension($package, $resources, $packageFolder, $includeLanguageName);
        }

        return $packageFile;
    }

    /**
     * @param  array $packagesIds
     * @param  string $fileName
     * @param  bool $includeLanguageName
     *
     * @throws Exception
     * @return string
     */
    public function prepareProjectPackage(array $packagesIds, $fileName = 'UNZIPFIRST', $includeLanguageName = true)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $db = $this->getDbo();

        $files       = array();
        $packageFile = '';

        foreach ($packagesIds as $packageId) {
            // Get package.
            $package = new Transifex\Package\Package($db);
            $package->load($packageId);
    
            // Prepare project folder
            $project = new Transifex\Project\Project($db);
            $project->load($package->getProjectId());

            // Prepare project URL that points to Transifex.
            $this->options['project_path'] = 'project/' . $project->getAlias();

            $packageFileName = $package->getFilename();
            $packageFolder   = JPath::clean($app->get('tmp_path') . DIRECTORY_SEPARATOR . $packageFileName);
    
            // Remove old folder
            if (JFolder::exists($packageFolder)) {
                JFolder::delete($packageFolder);
            }
    
            // Create folder
            JFolder::create($packageFolder);
    
            // Get project resources
            $published = 1;
            $resources = $package->getResources($published);
    
            $packageType = $package->getType();

            switch ($packageType) {
                case 'plugin':
                    $packageFile = $this->preparePlugin($package, $resources, $packageFolder, $includeLanguageName);
                    break;

                case 'library':
                case 'component':
                case 'module':
                    $packageFile = $this->prepareExtension($package, $resources, $packageFolder, $includeLanguageName);
                    break;
            }
            
            $files[] = $packageFile;
        }

        // Make an archive.
        if (count($files) > 0) {
            // Create temporary folder.
            $tmpFolder   = JPath::clean($app->get('tmp_path') . DIRECTORY_SEPARATOR . 'tmp_'.(string)Prism\Utilities\StringHelper::generateRandomString());
            JFolder::create($tmpFolder);

            // Copy files to the temporary folder.
            foreach ($files as $file) {
                $baseName = JPath::clean($tmpFolder . DIRECTORY_SEPARATOR . basename($file));
                JFile::copy($file, $baseName);
            }

            // Create an archive with files.
            $packageFile = $this->createPackage($fileName, $tmpFolder);
        }

        return $packageFile;
    }
    
    /**
     * Prepare component, module, library or other package.
     *
     * @param Transifex\Package\Package $package
     * @param Transifex\Resource\Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @return string
     */
    protected function prepareExtension($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageName = $this->getPackageName($resources);

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace('_', '-', $langCode);

        // Prepare options
        $manifestFileName = $langCodeDash . '.' . $packageName;
        $manifestFile     = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . '.xml');

        $date = new JDate();

        $name = $this->generatePackageName($package, $langCode, $includeLanguageName);

        // Prepare options
        $options = array(
            'name'                => $name,
            'description'         => $package->getDescription(),
            'version'             => $package->getVersion(),
            'creation_date'       => $date->format('d F, Y'),
            'lang_code'           => $langCode,
            'lang_code_dash'      => $langCodeDash,
            'manifest_filename'   => $manifestFile,
            'package_folder'      => $packageFolder
        );

        // Download files
        $filesList = $this->fetchFiles($resources, $options);

        // Generate manifest
        $this->generateManifest($options, $filesList);

        $packageFileName = $package->getFilename() . '_' . $langCodeDash;

        return $this->createPackage($packageFileName, $packageFolder);
    }

    /**
     * Prepare plugin package.
     *
     * @param Transifex\Package\Package $package
     * @param Transifex\Resource\Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @return string
     */
    protected function preparePlugin($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageNames = $this->getPackageName($resources, 'plugin');

//        $pluginType  = $packageNames[1];
//        $pluginName  = $packageNames[2];
        $packageName = $packageNames[3];

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace('_', '-', $langCode);

        // Prepare options
        $manifestFileName = $langCodeDash . '.' . $packageName;
        $manifestFile     = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . '.xml');

        $date = new JDate();

        $name = $this->generatePackageName($package, $langCode, $includeLanguageName);

        $options = array(
            'name'              => $name,
            'description'       => $package->getDescription(),
            'version'           => $package->getVersion(),
            'creation_date'     => $date->format('d F, Y'),
            'lang_code'         => $langCode,
            'lang_code_dash'    => $langCodeDash,
            'manifest_filename' => $manifestFile,
            'package_folder'    => $packageFolder
        );

        // Download files
        $filesList = $this->fetchFiles($resources, $options);

        // Generate manifest
        $this->generateManifest($options, $filesList);

        $packageFileName = $package->getFilename() . '_' . $langCodeDash;

        return $this->createPackage($packageFileName, $packageFolder);
    }

    /**
     * Generate a package name.
     *
     * @param Transifex\Package\Package $package
     * @param string $langCode
     * @param bool $includeLanguageName
     *
     * @throws \RuntimeException
     * @return string
     */
    protected function generatePackageName($package, $langCode, $includeLanguageName)
    {
        if (!$includeLanguageName) {
            $name = $package->getName();
        } else {
            $keys = array(
                'locale' => $langCode
            );

            $language = new Transifex\Language\Language(JFactory::getDbo());
            $language->load($keys);

            $name = $package->getName() . ' - '.$language->getName();
        }

        return $name;
    }

    protected function fetchFiles($resources, $options)
    {
        $langCode              = ArrayHelper::getValue($options, 'lang_code');
        $langCodeDash          = ArrayHelper::getValue($options, 'lang_code_dash');
        $packageFolder         = ArrayHelper::getValue($options, 'package_folder');

        $files = array();

        // Separate admin files and site ones.
        foreach ($resources as $resource) {
            if (!empty($resource['path'])) {
                $path = md5($resource['path']);

                $files[$path]['paths'] = array(
                    'source_folder' => $resource['source'],
                    'target_path'   => $resource['path'] .'/'. $langCodeDash,
                );
                
                $files[$path]['files'][] = array(
                    'filename'      => $langCodeDash . '.' . $resource['filename'],
                    'slug'          => $resource['alias']
                );
            }
        }

        // Download files
        foreach ($files as $key => $filesData) {
            $paths        = $filesData['paths'];
            $sourceFolder = $paths['source_folder'] ?: '';

            // Create site folder.
            $sourceFolderPath = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $sourceFolder);
            
            if (!JFolder::exists($sourceFolderPath)) {
                JFolder::create($sourceFolderPath);
            }

            foreach ($filesData['files'] as $fileData) {
                $destination = JPath::clean($sourceFolderPath . DIRECTORY_SEPARATOR . $fileData['filename']);
                
                try {
                    $this->downloadFile($fileData['slug'], $langCode, $destination);
                } catch (\Exception $e) {
                    unset($files[$key]);
                    JLog::add($e->getMessage() . "\n SLUG: ".$fileData['slug'], JLog::ERROR, 'com_userideas');
                }
            }
        }

        // Generate the list of files
        $filesList = array();

        // A list with admin files
        if (count($files) > 0) {
            foreach ($files as $filesData) {
                $paths       = $filesData['paths'];
                if ($paths['source_folder'] !== null and $paths['source_folder'] !== '') {
                    $filesList[] = '<files folder="' . $paths['source_folder'] . '" target="' . $paths['target_path'] . '">';
                } else {
                    $filesList[] = '<files target="' . $paths['target_path'] . '">';
                }
                
                foreach ($filesData['files'] as $fileData) {
                    $filesList[] = '<filename>' . $fileData['filename'] . '</filename>';
                }

                $filesList[] = '</files>';
            }
        }

        return implode("\n", $filesList);
    }

    protected function generateManifest($options, $filesList)
    {
        $params = JComponentHelper::getParams('com_itptransifex');
        /** @var  $params Joomla\Registry\Registry */

        $manifestFile = ArrayHelper::getValue($options, 'manifest_filename');

        // Load the template file
        $templateFile = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'lang_template.xml');
        $template     = file_get_contents($templateFile);

        $author      = $params->get('author');
        $authorEmail = $params->get('author_email');
        $copyright   = $params->get('copyright');
        $site        = $params->get('site');

        $name         = ArrayHelper::getValue($options, 'name');
        $creationDate = ArrayHelper::getValue($options, 'creation_date');
        $description  = ArrayHelper::getValue($options, 'description');
        $version      = ArrayHelper::getValue($options, 'version');

        $template = str_replace('{NAME}', $name, $template);
        $template = str_replace('{AUTHOR}', $author, $template);
        $template = str_replace('{AUTHOR_EMAIL}', $authorEmail, $template);
        $template = str_replace('{COPYRIGHT}', $copyright, $template);
        $template = str_replace('{SITE}', $site, $template);
        $template = str_replace('{CREATION_DATE}', $creationDate, $template);
        $template = str_replace('{VERSION}', $version, $template);
        $template = str_replace('{DESCRIPTION}', $description, $template);

        $template = str_replace('{FILES_LIST}', $filesList, $template);

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($template);

        $template = $dom->saveXML();

        file_put_contents($manifestFile, $template);
    }

    /**
     * Download translated files from Transifex.
     *
     * @param string $slug Unique alias of the resource.
     * @param string $langCode Language code
     * @param string $destination The folder in which files will be saved.
     */
    protected function downloadFile($slug, $langCode, $destination)
    {
        $headers = array(
            'headers' => array(
                'Content-type: application/json',
                'X-HTTP-Method-Override: GET'
            )
        );

        $transifex = new Prism\Transifex\Request($this->options['url']);

        $transifex->setUsername($this->options['username']);
        $transifex->setPassword($this->options['password']);
        $transifex->enableAuthentication();

        $path = $this->options['project_path'] . '/resource/' . $slug . '/translation/' . $langCode . '/';

        $response = $transifex->get($path, $headers);

        if (!empty($response->content)) {
            JFile::write($destination, $response->content);
        }

        // Copy index.html
        $indexFile = dirname($destination) . DIRECTORY_SEPARATOR . 'index.html';
        $html      = '<html><body style="background-color: #fff;"></body></html>';
        if (true !== JFile::write($indexFile, $html)) {
            JLog::add(JText::sprintf('COM_ITPTRANSIFEX_ERROR_CANNOT_CREATE_FILE', $indexFile));
        }
    }

    protected function createPackage($packageName, $projectFolder)
    {
        $archiveFile = $packageName . '.zip';
        $destination = $projectFolder . DIRECTORY_SEPARATOR . $archiveFile;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectFolder));

        $filesToZip = array();
        foreach ($iterator as $key => $value) {
            $key = JPath::clean($key);
            if (!is_dir($key)) {
                $filesToZip[] = array(
                    'name' => substr($key, strlen($destination) - strlen(basename($destination))),
                    'data' => file_get_contents($key)
                );
            }
        }

        // compression type
        $zipAdapter = JArchive::getAdapter('zip');
        $zipAdapter->create($destination, $filesToZip, array());

        return $destination;
    }

    /**
     * Remove records from package map table.
     *
     * @param array $cid
     */
    public function removeResourcesFromMap($cid)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query
            ->delete($db->quoteName('#__itptfx_packages_map'))
            ->where($db->quoteName('package_id') .' IN ( ' . implode(',', $cid) . ' )');

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Get a package filename from a resource file name.
     *
     * @param Transifex\Resource\Resources $resources  Resources
     * @param string $type Extension type - component, module or plugin.
     *
     * @return array|string
     */
    protected function getPackageName($resources, $type = '')
    {
        $fileName = JFile::makeSafe($resources[0]['filename']);

        $fileName = JFile::stripExt($fileName);
        if (false !== strpos($fileName, '.sys')) {
            $fileName = JFile::stripExt($fileName);
        }

        if (strcmp($type, 'plugin') === 0) {
            $fileNames    = explode('_', $fileName);
            $fileNames[3] = $fileName;

            $fileName = $fileNames;
        }

        return $fileName;
    }

    /**
     * Set options to the object.
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Create an error file, which will be returned if there was an error,
     * during the process of package creating.
     *
     * @return string
     */
    public function createErrorFile()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $fileName = 'error.txt';

        $errorFile = JPath::clean($app->get('tmp_path') . DIRECTORY_SEPARATOR . $fileName);
        if (JFile::exists($errorFile)) {
            JFile::delete($errorFile);
        }

        $buffer = 'System error!';
        JFile::write($errorFile, $buffer);

        $archiveFile = 'error.zip';
        $destination = JPath::clean($app->get('tmp_path') . DIRECTORY_SEPARATOR . $archiveFile);
        if (JFile::exists($destination)) {
            JFile::delete($destination);
        }

        $filesToZip[] = array(
            'name' => $fileName,
            'data' => file_get_contents($errorFile)
        );

        // compression type
        $zipAdapter = JArchive::getAdapter('zip');
        $zipAdapter->create($destination, $filesToZip, array());

        return $destination;
    }
    
    public function removeResource($packageId, $resourceId)
    {
        // Remove resource
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $query
            ->delete($db->quoteName('#__itptfx_packages_map'))
            ->where($db->quoteName('package_id')  .' = '. (int)$packageId)
            ->where($db->quoteName('resource_id') .' = '. (int)$resourceId);
        
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Get a list with resources searching by string
     *
     * @param string $string
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getResources($string)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $search = $db->quote('%'.$db->escape($string, true) . '%');

        $query
            ->select('a.id, a.name')
            ->from($db->quoteName('#__itptfx_resources', 'a'))
            ->where('a.name LIKE ' . $search)
            ->where('a.published = ' . (int)Prism\Constants::PUBLISHED);

        $db->setQuery($query, 0, 8);
        $results = $db->loadAssocList();

        return (array)$results;
    }

    /**
     * Add a new resource to a package.
     *
     * @param int $packageId
     * @param int $resourceId
     *
     * @return bool
     */
    public function storeResource($packageId, $resourceId)
    {
        $db    = $this->getDbo();

        // Check for existed resource.
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__itptfx_packages_map', 'a'))
            ->where('a.package_id = ' . (int)$packageId)
            ->where('a.resource_id = ' . (int)$resourceId);

        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();

        // Add a resource.
        if (!$result) {
            // Add the record to database.
            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName('#__itptfx_packages_map'))
                ->set($db->quoteName('package_id') .' = ' . (int)$packageId)
                ->set($db->quoteName('resource_id') .' = ' . (int)$resourceId);

            $db->setQuery($query);
            $db->execute();

        }

        return (!$result) ? true : false;
    }

    public function copyPackages(array $packagesIds, $language)
    {
        $options = array(
            'ids' => $packagesIds
        );
        $packages = new Transifex\Package\Packages(JFactory::getDbo());
        $packages->load($options);

        // Check for existing packages.
        if (count($packages) > 0) {
            $toPackageLanguageCode = StringHelper::strtolower($language);

            foreach ($packages as $key => $package) {
                $newAlias        = StringHelper::substr($package['alias'], 0, -5);
                $endString       = StringHelper::substr($package['alias'], -5, 5);

                $fromPackageLanguageCode = StringHelper::strtolower($package['language']);

                // If the end of string does not match old language code, or
                // the end of string match new language code,
                // I am going to generate a new string.
                if ((strcmp($endString, $fromPackageLanguageCode) !== 0) or (strcmp($endString, $toPackageLanguageCode) === 0)) {
                    $newAlias = Prism\Utilities\StringHelper::generateRandomString(32);
                } else { // or I am going to add the new language code to the end of alias string.
                    $newAlias .= $toPackageLanguageCode;
                }

                $package['alias']    = $newAlias;
                $package['language'] = $language;

                $packages[$key] = $package;
            }

            $this->preventDuplications($packages);
            $this->createPackages($packages);
        }
    }

    /**
     * Check for existing packages with same aliases in database.
     * If there are duplications, I am going to generate a new alias.
     *
     * @param Transifex\Package\Packages $packages
     */
    protected function preventDuplications($packages)
    {
        $db    = $this->getDbo();

        // Get aliases.
        $aliases = array();
        foreach ($packages as $package) {
            $aliases[] = $db->quote($package['alias']);
        }

        $query = $db->getQuery(true);

        $query
            ->select('a.alias')
            ->from($db->quoteName('#__itptfx_packages', 'a'))
            ->where('a.alias IN ('. implode(',', $aliases) . ')');

        $db->setQuery($query);
        $results = $db->loadColumn();

        if (!empty($aliases)) {
            foreach ($results as $alias) {
                foreach ($packages as $key => $package) {
                    if (strcmp($alias, $package['alias']) == 0) {
                        $package['alias'] = Prism\Utilities\StringHelper::generateRandomString(32);
                        $packages[$key] = $package;
                    }
                }

            }
        }
    }

    /**
     * Create new packages.
     *
     * @param Transifex\Package\Packages $packages
     */
    protected function createPackages($packages)
    {
        foreach ($packages as $package) {
            $packageId  = $package['id'];
            unset($package['id']);

            $options = array(
                'package_id' => $packageId
            );

            // Get package resources.
            $resources = new Transifex\Resource\Resources(JFactory::getDbo());
            $resources->load($options);

            // Create a new package.
            $p = new Transifex\Package\Package(JFactory::getDbo());
            $p->bind($package);
            $p->store();

            $packageId = $p->getId();
            $this->copyResources($packageId, $resources);
        }
    }

    /**
     * Copy all resources from a package to a new one.
     *
     * @param int $packageId
     * @param Transifex\Resource\Resources $resources
     */
    protected function copyResources($packageId, $resources)
    {
        $db    = $this->getDbo();

        foreach ($resources as $resource) {
            $query = $db->getQuery(true);

            $query
                ->insert($db->quoteName('#__itptfx_packages_map'))
                ->set($db->quoteName('package_id')  .'='. (int)$packageId)
                ->set($db->quoteName('resource_id') .'='. $resource['id']);

            $db->setQuery($query);
            $db->execute();
        }
    }

    public function changeVersion(array $packagesIds, $newVersion)
    {
        $packagesIds = ArrayHelper::toInteger($packagesIds);

        if (!empty($packagesIds)) {
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName('#__itptfx_packages'))
                ->set($db->quoteName('version') . ' = ' . (float)$newVersion)
                ->where($db->quoteName('id') . ' IN (' . implode(',', $packagesIds) . ')');

            $db->setQuery($query);
            $db->execute();
        }
    }

    public function replaceText(array $packagesIds, $search, $replace)
    {
        $packagesIds = ArrayHelper::toInteger($packagesIds);

        if (!empty($packagesIds) and !empty($search) and !empty($replace)) {
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName('#__itptfx_packages'))
                ->set($db->quoteName('description') . ' = REPLACE(' . $db->quoteName('description') .', '.$db->quote($search).', '.$db->quote($replace).')')
                ->where($db->quoteName('id') . ' IN (' . implode(',', $packagesIds) . ')');

            $db->setQuery($query);
            $db->execute();
        }
    }
}
