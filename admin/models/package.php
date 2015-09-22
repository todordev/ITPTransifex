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
     * @return  JTable  A database object
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
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.package', 'package', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.package.data', array());

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
        $id        = Joomla\Utilities\ArrayHelper::getValue($data, "id", 0, "int");
        $name      = Joomla\Utilities\ArrayHelper::getValue($data, "name");
        $alias     = Joomla\Utilities\ArrayHelper::getValue($data, "alias");
        $filename  = Joomla\Utilities\ArrayHelper::getValue($data, "filename");
        $desc      = Joomla\Utilities\ArrayHelper::getValue($data, "description");
        $version   = Joomla\Utilities\ArrayHelper::getValue($data, "version");
        $type      = Joomla\Utilities\ArrayHelper::getValue($data, "type");
        $language  = Joomla\Utilities\ArrayHelper::getValue($data, "language");
        $projectId = Joomla\Utilities\ArrayHelper::getValue($data, "project_id", 0, "int");

        if (!$desc) {
            $desc = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("name", $name);
        $row->set("alias", $alias);
        $row->set("filename", $filename);
        $row->set("description", $desc);
        $row->set("version", $version);
        $row->set("language", $language);
        $row->set("type", $type);
        $row->set("project_id", $projectId);

        $this->prepareTable($row);

        $row->store(true);

        return $row->get("id");
    }

    /**
     * Save the resources to package map database.
     *
     * @param integer $packageId
     * @param array $resourcesIDs
     */
    public function saveResourcesIds($packageId, $resourcesIDs)
    {
        $db = $this->getDbo();

        // Get existed resources
        $query = $db->getQuery(true);
        $query
            ->select("a.resource_id")
            ->from($db->quoteName("#__itptfx_packages_map", "a"))
            ->where("a.package_id = " . (int)$packageId);

        $db->setQuery($query);
        $results = $db->loadColumn();

        $results = Joomla\Utilities\ArrayHelper::toInteger($results);

        // Prepare these resources that does not exist.
        foreach ($results as $resourceId) {
            $key = array_search($resourceId, $resourcesIDs);
            if (false !== $key) {
                unset($resourcesIDs[$key]);
            }
        }

        // Add newest resources to the map.
        if (!empty($resourcesIDs)) {

            $columns = array("package_id", "resource_id");

            $values = array();
            foreach ($resourcesIDs as $resourceId) {
                $values[] = (int)$packageId . "," . (int)$resourceId;
            }

            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName("#__itptfx_packages_map"))
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
            $table->set("name", stripcslashes($table->get("name")));
            $table->set("description", stripcslashes($table->get("description")));
        }

        if (!$table->get("filename")) {
            $table->set("filename", null);
        }

        if (!$table->get("description")) {
            $table->set("description", null);
        }

        // If an alias does not exist, I will generate the new one using the title.
        if (!$table->get("alias")) {
            $table->set("alias", $table->get("name")."-".$table->get("language"));
        }
        $table->set("alias", JFilterOutput::stringURLSafe($table->get("alias")));

        // Prepare language code.
        $langCode1 = str_replace("-", "_", substr($table->get("alias"), -5, 5));
        $langCode2 = JString::strtolower($table->get("language"));
        if (strcmp($langCode1, $langCode2) == 0) {
            $alias = substr($table->get("alias"), 0, -5);
            $table->set("alias", $alias . $langCode2);
        }

        // Check for existing alias.
        if (!$table->get("id") and $this->isAliasExists($table->get("alias"))) {
            $table->set("alias", Prism\String\StringHelper::generateRandomString(16) ."-". $langCode2);
        }
    }

    protected function isAliasExists($alias)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__itptfx_packages", "a"))
            ->where("a.alias = " . $db->quote($alias));

        $db->setQuery($query);

        return (bool)$db->loadResult();
    }

    /**
     * Prepare a package - download all files, create a manifest file,...
     *
     * @param int $packageId
     * @param bool $includeLanguageName Include or not language name to package name.
     *
     * @return string
     */
    public function preparePackage($packageId, $includeLanguageName = true)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $db = $this->getDbo();

        $packageFile = "";

        // Get package.
        $package = new Transifex\Package\Package($db);
        $package->load($packageId);

        // Prepare project folder
        $project = new Transifex\Project\Project($db);
        $project->load($package->getProjectId());

        // Prepare project URL that points to Transifex.
        $this->options["project_path"] = "project/" . $project->getAlias();

        $packageFileName = $package->getFilename();
        $packageFolder   = JPath::clean($app->get("tmp_path") . DIRECTORY_SEPARATOR . $packageFileName);

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

            case "component":
                $packageFile = $this->prepareComponent($package, $resources, $packageFolder, $includeLanguageName);
                break;

            case "module":
                $packageFile = $this->prepareModule($package, $resources, $packageFolder, $includeLanguageName);
                break;

            case "plugin":
                $packageFile = $this->preparePlugin($package, $resources, $packageFolder, $includeLanguageName);
                break;

            case "library":
                $packageFile = $this->prepareLibrary($package, $resources, $packageFolder, $includeLanguageName);
                break;
        }

        return $packageFile;

    }

    /**
     * @param  array $packagesIds
     * @param  string $fileName
     * @param  bool $includeLanguageName
     *
     * @return string
     */
    public function prepareProjectPackage(array $packagesIds, $fileName = "UNZIPFIRST", $includeLanguageName = true)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $db = $this->getDbo();

        $files       = array();
        $packageFile = "";

        foreach ($packagesIds as $packageId) {
            
            // Get package.
            $package = new Transifex\Package\Package($db);
            $package->load($packageId);
    
            // Prepare project folder
            $project = new Transifex\Project\Project($db);
            $project->load($package->getProjectId());

            // Prepare project URL that points to Transifex.
            $this->options["project_path"] = "project/" . $project->getAlias();

            $packageFileName = $package->getFilename();
            $packageFolder   = JPath::clean($app->get("tmp_path") . DIRECTORY_SEPARATOR . $packageFileName);
    
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
    
                case "component":
                    $packageFile = $this->prepareComponent($package, $resources, $packageFolder, $includeLanguageName);
                    break;
    
                case "module":
                    $packageFile = $this->prepareModule($package, $resources, $packageFolder, $includeLanguageName);
                    break;
    
                case "plugin":
                    $packageFile = $this->preparePlugin($package, $resources, $packageFolder, $includeLanguageName);
                    break;

                case "library":
                    $packageFile = $this->prepareLibrary($package, $resources, $packageFolder, $includeLanguageName);
                    break;
            }
            
            $files[] = $packageFile;
        }

        // Make an archive.
        if (!empty($files)) {

            // Create temporary folder.
            $tmpFolder   = JPath::clean($app->get("tmp_path") . DIRECTORY_SEPARATOR . "tmp_".(string)Prism\String\StringHelper::generateRandomString());
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
     * @param Transifex\Package\Package $package
     * @param Transifex\Resource\Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @return string
     */
    protected function prepareComponent($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageName = $this->getPackageName($resources, "component");

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace("_", "-", $langCode);

        // Generate target folder of the language files.
        if (strcmp("extension_folders", $this->options["files_location"]) == 0) {
            $targetAdminFolder = "administrator/components/" . $packageName . "/language/" . $langCodeDash;
            $targetSiteFolder  = "components/" . $packageName . "/language/" . $langCodeDash;
        } else {// "language_folders"
            $targetAdminFolder = "administrator/language/" . $langCodeDash;
            $targetSiteFolder  = "language/" . $langCodeDash;
        }

        $sourceAdminFolder = "admin/" . $langCodeDash;
        $sourceSiteFolder  = "site/" . $langCodeDash;

        // Prepare options
        $manifestFileName = $langCodeDash . "." . $packageName;
        $manifestFile     = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . ".xml");

        // Create admin folder.
        $packageAdminFolder = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . "admin");
        if (!JFolder::exists($packageAdminFolder)) {
            JFolder::create($packageAdminFolder);
        }

        // Create admin language folder
        $packageAdminLangFolder = JPath::clean($packageAdminFolder . DIRECTORY_SEPARATOR . $langCodeDash);
        if (!JFolder::exists($packageAdminLangFolder)) {
            JFolder::create($packageAdminLangFolder);
        }

        // Create site folder.
        $packageSiteFolder = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . "site");
        if (!JFolder::exists($packageSiteFolder)) {
            JFolder::create($packageSiteFolder);
        }

        // Create site language folder
        $projectSiteLangFolder = JPath::clean($packageSiteFolder . DIRECTORY_SEPARATOR . $langCodeDash);
        if (!JFolder::exists($projectSiteLangFolder)) {
            JFolder::create($projectSiteLangFolder);
        }

        $date = new JDate();

        $name = $this->generatePackageName($package, $langCode, $includeLanguageName);

        // Prepare options
        $options = array(
            "name"                => $name,
            "description"         => $package->getDescription(),
            "version"             => $package->getVersion(),
            "creation_date"       => $date->format("d F, Y"),
            "source_admin_folder" => $sourceAdminFolder,
            "source_site_folder"  => $sourceSiteFolder,
            "target_admin_folder" => $targetAdminFolder,
            "target_site_folder"  => $targetSiteFolder,
            "lang_code"           => $langCode,
            "lang_code_dash"      => $langCodeDash,
            "manifest_filename"   => $manifestFile,
            "package_folder"      => $packageFolder
        );

        // Download files
        $filesList = $this->downloadComponentFiles($resources, $options);

        // Generate manifest
        $this->generateManifest($options, $filesList);

        $packageFileName = $package->getFileName() . "_" . $langCodeDash;
        $packageFile     = $this->createPackage($packageFileName, $packageFolder);

        return $packageFile;

    }

    /**
     * @param Transifex\Package\Package $package
     * @param Transifex\Resource\Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @return string
     */
    protected function prepareModule($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageName = $this->getPackageName($resources, "module");

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace("_", "-", $langCode);

        // Generate target folder of the language files.
        if (strcmp("extension_folders", $this->options["files_location"]) == 0) {
            $targetFolder = "modules/" . $packageName . "/language/" . $langCodeDash;
        } else { // "language_folders"
            $targetFolder = "language/" . $langCodeDash;
        }

        // Prepare options
        $manifestFileName = $langCodeDash . "." . $packageName;
        $manifestFile     = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . ".xml");

        $date = new JDate();

        $name = $this->generatePackageName($package, $langCode, $includeLanguageName);

        $options = array(
            "name"              => $name,
            "description"       => $package->getDescription(),
            "version"           => $package->getVersion(),
            "creation_date"     => $date->format("d F, Y"),
            "target_folder"     => $targetFolder,
            "lang_code"         => $langCode,
            "lang_code_dash"    => $langCodeDash,
            "manifest_filename" => $manifestFile,
            "package_folder"    => $packageFolder
        );

        // Download files
        $filesList = $this->downloadFiles($resources, $options);

        // Generate manifest
        $this->generateManifest($options, $filesList);

        $packageFileName = $package->getFileName() . "_" . $langCodeDash;
        $packageFile     = $this->createPackage($packageFileName, $packageFolder);

        return $packageFile;
    }

    /**
     * @param Transifex\Package\Package $package
     * @param Transifex\Resource\Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @return string
     */
    protected function preparePlugin($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageNames = $this->getPackageName($resources, "plugin");

        $pluginType = $packageNames[1];
        $pluginName = $packageNames[2];

        $packageName = $packageNames[3];

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace("_", "-", $langCode);

        // Generate target folder of the language files.
        if (strcmp("extension_folders", $this->options["files_location"]) == 0) {
            $targetFolder = "plugins/" . $pluginType . "/" . $pluginName . "/language/" . $langCodeDash;
        } else { // "language_folders"
            $targetFolder = "administrator/language/" . $langCodeDash;
        }

        // Prepare options
        $manifestFileName = $langCodeDash . "." . $packageName;
        $manifestFile     = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . ".xml");

        $date = new JDate();

        $name = $this->generatePackageName($package, $langCode, $includeLanguageName);

        $options = array(
            "name"              => $name,
            "description"       => $package->getDescription(),
            "version"           => $package->getVersion(),
            "creation_date"     => $date->format("d F, Y"),
            "target_folder"     => $targetFolder,
            "lang_code"         => $langCode,
            "lang_code_dash"    => $langCodeDash,
            "manifest_filename" => $manifestFile,
            "package_folder"    => $packageFolder
        );

        // Download files
        $siteFilesList = $this->downloadFiles($resources, $options, "plugin");

        // Generate manifest
        $this->generateManifest($options, $siteFilesList);

        $packageFileName = $package->getFileName() . "_" . $langCodeDash;
        $packageFile     = $this->createPackage($packageFileName, $packageFolder);

        return $packageFile;
    }

    /**
     * Prepare a package for a library.
     *
     * @param Transifex\Package\Package $package
     * @param Transifex\Resource\Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @return string
     */
    protected function prepareLibrary($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageName = $this->getPackageName($resources, "library");

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace("_", "-", $langCode);

        // Generate target folder of the language files.
        if (strcmp("extension_folders", $this->options["files_location"]) == 0) {
            $targetFolder = "libraries/" . JString::ucfirst(substr($packageName, 4)) . "/language/" . $langCodeDash;
        } else { // "language_folders"
            $targetFolder = "language/" . $langCodeDash;
        }

        // Prepare options
        $manifestFileName = $langCodeDash . "." . $packageName;
        $manifestFile     = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . ".xml");

        $date = new JDate();

        $name = $this->generatePackageName($package, $langCode, $includeLanguageName);

        $options = array(
            "name"              => $name,
            "description"       => $package->getDescription(),
            "version"           => $package->getVersion(),
            "creation_date"     => $date->format("d F, Y"),
            "target_folder"     => $targetFolder,
            "lang_code"         => $langCode,
            "lang_code_dash"    => $langCodeDash,
            "manifest_filename" => $manifestFile,
            "package_folder"    => $packageFolder
        );

        // Download files
        $filesList = $this->downloadFiles($resources, $options);

        // Generate manifest
        $this->generateManifest($options, $filesList);

        $packageFileName = $package->getFileName() . "_" . $langCodeDash;
        $packageFile     = $this->createPackage($packageFileName, $packageFolder);

        return $packageFile;
    }

    /**
     * Generate a package name.
     *
     * @param Transifex\Package\Package $package
     * @param string $langCode
     * @param bool $includeLanguageName
     *
     * @return string
     */
    protected function generatePackageName($package, $langCode, $includeLanguageName)
    {
        if (!$includeLanguageName) {
            $name = $package->getName();
        } else {

            $keys = array(
                "code" => $langCode
            );

            $language = new Transifex\Language\Language(JFactory::getDbo());
            $language->load($keys);

            $name = $package->getName() . " - ".$language->getName();
        }

        return $name;
    }

    /**
     * This method downloads plugin and module files and generate a string with files list.
     *
     * @param Transifex\Resource\Resources $resources
     * @param array $options
     *
     * @return string
     */
    protected function downloadFiles($resources, $options)
    {
        $targetFolder  = Joomla\Utilities\ArrayHelper::getValue($options, "target_folder");
        $langCode      = Joomla\Utilities\ArrayHelper::getValue($options, "lang_code");
        $langCodeDash  = Joomla\Utilities\ArrayHelper::getValue($options, "lang_code_dash");
        $packageFolder = Joomla\Utilities\ArrayHelper::getValue($options, "package_folder");

        $filesList = array();

        // Download files

        $filesList[] = '<files target="' . $targetFolder . '">';

        foreach ($resources as $resource) {

            $filename    = JFile::makeSafe($langCodeDash . "." . $resource["filename"]);
            $destination = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $filename);

            $this->downloadFile($resource["alias"], $langCode, $destination);

            $filesList[] = '<filename>' . $filename . '</filename>';

        }

        $filesList[] = '</files>';

        return implode("\n", $filesList);

    }

    protected function downloadComponentFiles($resources, $options)
    {
        $sourceAdminLangFolder = Joomla\Utilities\ArrayHelper::getValue($options, "source_admin_folder");
        $sourceSiteLangFolder  = Joomla\Utilities\ArrayHelper::getValue($options, "source_site_folder");
        $targetAdminLangFolder = Joomla\Utilities\ArrayHelper::getValue($options, "target_admin_folder");
        $targetSiteLangFolder  = Joomla\Utilities\ArrayHelper::getValue($options, "target_site_folder");
        $langCode              = Joomla\Utilities\ArrayHelper::getValue($options, "lang_code");
        $langCodeDash          = Joomla\Utilities\ArrayHelper::getValue($options, "lang_code_dash");
        $packageFolder         = Joomla\Utilities\ArrayHelper::getValue($options, "package_folder");

        $adminFiles = array();
        $siteFiles  = array();

        // Separate admin files and site ones.
        foreach ($resources as $resource) {
            if (strcmp("admin", $resource["type"]) == 0) { // Admin folder
                $adminFiles[] = array(
                    "filename" => $langCodeDash . "." . $resource["filename"],
                    "slug"     => $resource["alias"],
                );
            }

            if (strcmp("site", $resource["type"]) == 0) { // Site folder
                $siteFiles[] = array(
                    "filename" => $langCodeDash . "." . $resource["filename"],
                    "slug"     => $resource["alias"],
                );
            }
        }

        // Download admin files
        if (!empty($adminFiles)) {

            foreach ($adminFiles as $fileData) {
                $destination = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $sourceAdminLangFolder . DIRECTORY_SEPARATOR . $fileData["filename"]);
                $this->downloadFile($fileData["slug"], $langCode, $destination);
            }

        }

        // Download site files
        if (!empty($siteFiles)) {

            foreach ($siteFiles as $fileData) {
                $destination = JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $sourceSiteLangFolder . DIRECTORY_SEPARATOR . $fileData["filename"]);
                $this->downloadFile($fileData["slug"], $langCode, $destination);
            }

        }

        // Generate the list of files
        $filesList = array();

        // A list with admin files
        if (!empty($adminFiles)) {
            $filesList[] = '<files folder="' . $sourceAdminLangFolder . '" target="' . $targetAdminLangFolder . '">';
            foreach ($adminFiles as $fileData) {
                $filesList[] = '<filename>' . $fileData["filename"] . '</filename>';
            }
            $filesList[] = '</files>';
        }

        // A list with site files
        if (!empty($siteFiles)) {
            $filesList[] = '<files folder="' . $sourceSiteLangFolder . '" target="' . $targetSiteLangFolder . '">';
            foreach ($siteFiles as $fileData) {
                $filesList[] = '<filename>' . $fileData["filename"] . '</filename>';
            }
            $filesList[] = '</files>';
        }

        return implode("\n", $filesList);

    }

    protected function generateManifest($options, $filesList)
    {
        $params = JComponentHelper::getParams("com_itptransifex");
        /** @var  $params Joomla\Registry\Registry */

        $manifestFile = Joomla\Utilities\ArrayHelper::getValue($options, "manifest_filename");

        // Load the template file
        $templateFile = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "lang_template.xml");
        $template     = file_get_contents($templateFile);

        $author      = $params->get("author");
        $authorEmail = $params->get("author_email");
        $copyright   = $params->get("copyright");
        $site        = $params->get("site");

        $name         = Joomla\Utilities\ArrayHelper::getValue($options, "name");
        $creationDate = Joomla\Utilities\ArrayHelper::getValue($options, "creation_date");
        $description  = Joomla\Utilities\ArrayHelper::getValue($options, "description");
        $version      = Joomla\Utilities\ArrayHelper::getValue($options, "version");

        $template = str_replace("{NAME}", $name, $template);
        $template = str_replace("{AUTHOR}", $author, $template);
        $template = str_replace("{AUTHOR_EMAIL}", $authorEmail, $template);
        $template = str_replace("{COPYRIGHT}", $copyright, $template);
        $template = str_replace("{SITE}", $site, $template);
        $template = str_replace("{CREATION_DATE}", $creationDate, $template);
        $template = str_replace("{VERSION}", $version, $template);
        $template = str_replace("{DESCRIPTION}", $description, $template);

        $template = str_replace("{FILES_LIST}", $filesList, $template);

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
            "headers" => array(
                'Content-type: application/json',
                'X-HTTP-Method-Override: GET'
            )
        );

        $transifex = new Prism\Transifex\Request($this->options["url"]);

        $transifex->setUsername($this->options["username"]);
        $transifex->setPassword($this->options["password"]);
        $transifex->enableAuthentication();

        $path = $this->options["project_path"] . "/resource/" . $slug . "/translation/" . $langCode . "/";

        $response = $transifex->get($path, $headers);

        if (!empty($response->content)) {
            JFile::write($destination, $response->content);
        }

        // Copy index.html
        $indexFile = dirname($destination) . DIRECTORY_SEPARATOR . "index.html";
        $html      = '<html><body style="background-color: #fff;"></body></html>';
        if (true !== JFile::write($indexFile, $html)) {
            JLog::add(JText::sprintf("COM_ITPTRANSIFEX_ERROR_CANNOT_CREATE_FILE", $indexFile));
        }
    }

    protected function createPackage($packageName, $projectFolder)
    {
        $archiveFile = $packageName . ".zip";
        $destination = $projectFolder . DIRECTORY_SEPARATOR . $archiveFile;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectFolder));

        $filesToZip = array();
        foreach ($iterator as $key => $value) {
            $key = JPath::clean($key);
            if (!is_dir($key)) {
                $filesToZip[] = array(
                    "name" => substr($key, strlen($destination) - strlen(basename($destination))),
                    "data" => file_get_contents($key)
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
            ->delete($db->quoteName("#__itptfx_packages_map"))
            ->where($db->quoteName("package_id") ." IN ( " . implode(",", $cid) . " )");

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
    protected function getPackageName($resources, $type)
    {
        $fileName = JFile::makeSafe($resources[0]["filename"]);

        $fileName = JFile::stripExt($fileName);
        if (false !== strpos($fileName, ".sys")) {
            $fileName = JFile::stripExt($fileName);
        }

        switch ($type) {

            case "component":
                break;

            case "module":
                break;

            case "library":
                break;

            case "plugin":

                $fileNames    = explode("_", $fileName);
                $fileNames[3] = $fileName;

                $fileName = $fileNames;

                break;

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

        $fileName = "error.txt";

        $errorFile = JPath::clean($app->get("tmp_path") . DIRECTORY_SEPARATOR . $fileName);
        if (JFile::exists($errorFile)) {
            JFile::delete($errorFile);
        }

        $buffer = "System error!";
        JFile::write($errorFile, $buffer);

        $archiveFile = "error.zip";
        $destination = JPath::clean($app->get("tmp_path") . DIRECTORY_SEPARATOR . $archiveFile);
        if (JFile::exists($destination)) {
            JFile::delete($destination);
        }

        $filesToZip[] = array(
            "name" => $fileName,
            "data" => file_get_contents($errorFile)
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
            ->delete($db->quoteName("#__itptfx_packages_map"))
            ->where($db->quoteName("package_id")  ." = ". (int)$packageId)
            ->where($db->quoteName("resource_id") ." = ". (int)$resourceId);
        
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Get a list with resources searching by string
     *
     * @param string $string
     *
     * @return array
     */
    public function getResources($string)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $search = $db->quote("%".$db->escape($string, true) . '%');

        $query
            ->select("a.id, a.name")
            ->from($db->quoteName("#__itptfx_resources", "a"))
            ->where("a.name LIKE " . $search);

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
            ->select("COUNT(*)")
            ->from($db->quoteName("#__itptfx_packages_map", "a"))
            ->where("a.package_id = " . (int)$packageId)
            ->where("a.resource_id = " . (int)$resourceId);

        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();

        // Add a resource.
        if (!$result) {

            // Add the record to database.
            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName("#__itptfx_packages_map"))
                ->set($db->quoteName("package_id") ." = " . (int)$packageId)
                ->set($db->quoteName("resource_id") ." = " . (int)$resourceId);

            $db->setQuery($query);
            $db->execute();

        }

        return (!$result) ? true : false;
    }

    public function copyPackages(array $packagesIds, $language)
    {
        $options = array(
            "ids" => $packagesIds
        );
        $packages = new Transifex\Package\Packages(JFactory::getDbo());
        $packages->load($options);

        // Check for existing packages.
        if (count($packages) > 0) {

            $toPackageLanguageCode = JString::strtolower($language);

            foreach ($packages as $key => $package) {

                $newAlias        = JString::substr($package["alias"], 0, -5);
                $endString       = JString::substr($package["alias"], -5, 5);

                $fromPackageLanguageCode = JString::strtolower($package["language"]);

                // If the end of string does not match old language code, or
                // the end of string match new language code,
                // I am going to generate a new string.
                if ((strcmp($endString, $fromPackageLanguageCode) != 0) or (strcmp($endString, $toPackageLanguageCode) == 0)) {
                    $newAlias = Prism\String\StringHelper::generateRandomString(32);
                } else { // or I am going to add the new language code to the end of alias string.
                    $newAlias .= $toPackageLanguageCode;
                }

                $package["alias"]    = $newAlias;
                $package["language"] = $language;

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
            $aliases[] = $db->quote($package["alias"]);
        }

        $query = $db->getQuery(true);

        $query
            ->select("a.alias")
            ->from($db->quoteName("#__itptfx_packages", "a"))
            ->where("a.alias IN (". implode(",", $aliases) . ")");

        $db->setQuery($query);
        $results = $db->loadColumn();

        if (!empty($aliases)) {
            foreach ($results as $alias) {

                foreach ($packages as $key => $package) {
                    if (strcmp($alias, $package["alias"]) == 0) {
                        $package["alias"] = Prism\String\StringHelper::generateRandomString(32);
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

            $packageId  = $package["id"];
            unset($package["id"]);

            $options = array(
                "package_id" => $packageId
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
                ->insert($db->quoteName("#__itptfx_packages_map"))
                ->set($db->quoteName("package_id")  ."=". (int)$packageId)
                ->set($db->quoteName("resource_id") ."=". $resource["id"]);

            $db->setQuery($query);
            $db->execute();
        }
    }

    public function changeVersion(array $packagesIds, $newVersion)
    {
        $packagesIds = Joomla\Utilities\ArrayHelper::toInteger($packagesIds);

        if (!empty($packagesIds)) {
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName("#__itptfx_packages"))
                ->set($db->quoteName("version") . " = " . (float)$newVersion)
                ->where($db->quoteName("id") . " IN (" . implode(",", $packagesIds) . ")");

            $db->setQuery($query);
            $db->execute();
        }
    }

    public function replaceText(array $packagesIds, $search, $replace)
    {
        $packagesIds = Joomla\Utilities\ArrayHelper::toInteger($packagesIds);

        if (!empty($packagesIds) and !empty($search) and !empty($replace)) {
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName("#__itptfx_packages"))
                ->set($db->quoteName("description") . " = REPLACE(" . $db->quoteName("description") .", ".$db->quote($search).", ".$db->quote($replace).")")
                ->where($db->quoteName("id") . " IN (" . implode(",", $packagesIds) . ")");

            $db->setQuery($query);
            $db->execute();
        }
    }
}
