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

jimport('joomla.application.component.modeladmin');

/**
 * This model provides functionality for managing a package.
 * 
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelPackage extends JModelAdmin {
    
    protected $serviceOptions;
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   type    The table type to instantiate
     * @param   string  A prefix for the table class name. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Package', $prefix = 'ItpTransifexTable', $config = array()){
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
        $form = $this->loadForm($this->option.'.package', 'package', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.package.data', array());
        
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
        
        $hash       = JArrayHelper::getValue($data, "hash");
        
        $name       = JString::trim(JArrayHelper::getValue($data, "name"));
        $filename   = JString::trim(JArrayHelper::getValue($data, "filename"));
        $desc       = JString::trim(JArrayHelper::getValue($data, "description"));
        $version    = JString::trim(JArrayHelper::getValue($data, "version"));
        $type       = JString::trim(JArrayHelper::getValue($data, "type"));
        $language   = JArrayHelper::getValue($data, "language");
        $projectId  = JArrayHelper::getValue($data, "project_id");
        
        if(!$desc) { $desc = null; }
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load(array("hash" => $hash));
        
        $row->set("name",           $name);
        $row->set("filename",       $filename);
        $row->set("description",    $desc);
        $row->set("version",        $version);
        $row->set("language",       $language);
        $row->set("type",           $type);
        $row->set("project_id",     $projectId);
        
        $this->prepareTable($row, $hash);
        
        $row->store(true);
        
        return $row->id;
    }
    
    /**
     * Save the resources to package map database.
     * 
     * @param integer $packageId
     * @param integer $resourcesIDs
     */
    public function saveResourcesIds($packageId, $resourcesIDs) {
        
        $db      = $this->getDbo();
        
        // Get existed resources
        $query   = $db->getQuery(true);
        $query
            ->select("a.resource_id")
            ->from($db->quoteName("#__itptfx_packages_map", "a"))
            ->where("a.package_id = ". (int)$packageId);
            
        $db->setQuery($query);
        $results = $db->loadColumn();
        
        JArrayHelper::toInteger($results);
        
        // Prepare these resources that does not exist. 
        foreach($results as $resourceId) {
            $key = array_search($resourceId, $resourcesIDs);
            if(false !== $key) {
                unset($resourcesIDs[$key]);
            }
        }
        
        // Add newest resources to the map.
        if(!empty($resourcesIDs)) {
            
            $columns = array("package_id", "resource_id");
            
            $values = array();
            foreach($resourcesIDs as $resourceId) {
                $values[] = (int)$packageId.",".(int)$resourceId;
            }
            
            $query   = $db->getQuery(true);
            $query
                ->insert($db->quoteName("#__itptfx_packages_map"))
                ->columns($columns)
                ->values($values);
            
            $db->setQuery($query);
            $db->execute();
        }
        
    }
    
    /**
     * Prepare and sanitise the table prior to saving.
     * @since	1.6
     */
    protected function prepareTable($table) {
         
        // Fix magic qutoes
        if( get_magic_quotes_gpc() ) {
            $table->name        = stripcslashes($table->name);
            $table->description = stripcslashes($table->description);
        }
        
    }

    public function prepareFiles($packageId) {
    
        $app   = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $db    = $this->getDbo();
    
        $packageFile = "";
        
        // Get package.
        jimport("itptransifex.package");
        $package = new ItpTransifexPackage($db);
        $package->load($packageId);
        
        // Prepare project folder
        jimport("itptransifex.project");
        $project = new ItpTransifexProject($db);
        $project->load($package->getProjectId());
        
        // Prepare project URL that points to Transifex.
        $this->serviceOptions["project_path"] = "project/".$project->getAlias();
    
        jimport("filesystem.path");
        jimport("filesystem.folder");
    
        $packageFileName = $package->getFilename();
        $packageFolder   = JPath::clean($app->getCfg("tmp_path").DIRECTORY_SEPARATOR.$packageFileName);
    
        // Remove old folder
        if(JFolder::exists($packageFolder)) {
            JFolder::delete($packageFolder);
        }
    
        // Create folder
        JFolder::create($packageFolder);
    
        // Get package.
        $package = new ItpTransifexPackage($db);
        $package->load($packageId);
        
        // Get project resources
        $published = 1;
        $resources = $package->getResources($published);
        
        $packageType = $package->getType();
        
        switch($packageType) {
            
            case "component":
                $packageFile = $this->prepareComponent($package, $resources, $packageFolder);
                break;
            
            case "module":
                $packageFile = $this->prepareModule($package, $resources, $packageFolder);
                break;
                
            case "plugin":
                $packageFile = $this->preparePlugin($package, $resources, $packageFolder);
                break;
        }
    
        return $packageFile;
        
    }
    
    protected function prepareComponent($package, $resources, $packageFolder) {
        
        // Get the name of the extension folder from resource name.
        $packageName  = $this->getPackageName($resources, "component");
        
        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace("_", "-", $langCode);
        
        // Generate target folder of the language files.
        $targetAdminFolder = "administrator/components/".$packageName."/language/".$langCodeDash;
        $targetSiteFolder  = "components/".$packageName."/language/".$langCodeDash;
        
        $sourceAdminFolder = "admin/".$langCodeDash;
        $sourceSiteFolder  = "site/".$langCodeDash;
        
        // Prepare options
        $manifestFileName = $langCodeDash.".".$packageName;
        $manifestFile     = JPath::clean($packageFolder .DIRECTORY_SEPARATOR. $langCodeDash.".".$packageName. ".xml");
        
        // Create admin folder.
        $packageAdminFolder = JPath::clean($packageFolder.DIRECTORY_SEPARATOR."admin");
        if(!JFolder::exists($packageAdminFolder)) {
            JFolder::create($packageAdminFolder);
        }
        
        // Create admin language folder
        $packageAdminLangFolder = JPath::clean($packageAdminFolder.DIRECTORY_SEPARATOR.$langCodeDash);
        if(!JFolder::exists($packageAdminLangFolder)) {
            JFolder::create($packageAdminLangFolder);
        }
        
        // Create site folder.
        $packageSiteFolder = JPath::clean($packageFolder.DIRECTORY_SEPARATOR."site");
        if(!JFolder::exists($packageSiteFolder)) {
            JFolder::create($packageSiteFolder);
        }
        
        // Create site language folder
        $projectSiteLangFolder = JPath::clean($packageSiteFolder.DIRECTORY_SEPARATOR.$langCodeDash);
        if(!JFolder::exists($projectSiteLangFolder)) {
            JFolder::create($projectSiteLangFolder);
        }
        
        $date = new JDate();
        
        // Prepare options
        $options =  array(
            "name"                => $package->getName(),
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
        
        $packageFileName = $package->getFileName()."_".$langCodeDash;
        $packageFile = $this->createPackage($packageFileName, $packageFolder);
        
        return $packageFile;
        
    }
    
    protected function prepareModule($package, $resources, $packageFolder) {
        
        // Get the name of the extension folder from resource name.
        $packageName  = $this->getPackageName($resources, "module");
        
        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace("_", "-", $langCode);
        
        // Generate target folder of the language files.
        $targetFolder = "modules/".$packageName."/language/".$langCodeDash;
        
        // Prepare options
        $manifestFileName = $langCodeDash.".".$packageName;
        $manifestFile     = JPath::clean($packageFolder .DIRECTORY_SEPARATOR. $manifestFileName. ".xml");
        
        $date = new JDate();
        
        $options =  array(
            "name"                => $package->getName(),
            "description"         => $package->getDescription(),
            "version"             => $package->getVersion(),
            "creation_date"       => $date->format("d F, Y"),
            "target_folder"       => $targetFolder,
            "lang_code"           => $langCode,
            "lang_code_dash"      => $langCodeDash,
            "manifest_filename"   => $manifestFile,
            "package_folder"      => $packageFolder
        );
        
        // Download files
        $filesList = $this->downloadFiles($resources, $options);
        
        // Generate manifest
        $this->generateManifest($options, $filesList);
        
        $packageFileName = $package->getFileName()."_".$langCodeDash;
        $packageFile     = $this->createPackage($packageFileName, $packageFolder);
        
        return $packageFile;
        
    }
    
    protected function preparePlugin($package, $resources, $packageFolder) {
        
        // Get the name of the extension folder from resource name.
        $packageNames  = $this->getPackageName($resources, "plugin");
        
        $pluginType    = $packageNames[1];
        $pluginName    = $packageNames[2];
        
        $packageName   = $packageNames[3];
        
        // Get package language code. Generate a language code with dash.
        $langCode      = $package->getLanguage();
        $langCodeDash  = str_replace("_", "-", $langCode);
        
        // Generate target folder of the language files.
        $targetFolder = "plugins/".$pluginType."/".$pluginName."/language/".$langCodeDash;
        
        // Prepare options
        $manifestFileName = $langCodeDash.".".$packageName;
        $manifestFile     = JPath::clean($packageFolder .DIRECTORY_SEPARATOR. $manifestFileName. ".xml");
        
        $date = new JDate();
        
        $options =  array(
            "name"                => $package->getName(),
            "description"         => $package->getDescription(),
            "version"             => $package->getVersion(),
            "creation_date"       => $date->format("d F, Y"),
            "target_folder"       => $targetFolder,
            "lang_code"           => $langCode,
            "lang_code_dash"      => $langCodeDash,
            "manifest_filename"   => $manifestFile,
            "package_folder"      => $packageFolder
        );
        
        // Download files
        $siteFilesList = $this->downloadFiles($resources, $options, "plugin");
        
        // Generate manifest
        $this->generateManifest($options, $siteFilesList);
        
        $packageFileName = $package->getFileName()."_".$langCodeDash;
        $packageFile     = $this->createPackage($packageFileName, $packageFolder);
        
        return $packageFile;
        
    }
    
    /**
     * This method downloads plugin and module files and generate a string with files list.
     * 
     * @param array   $resources
     * @param array   $options
     * @return string
     */
    protected function downloadFiles($resources, $options) {
    
        jimport("itprism.transifex.request");
        
        $targetFolder           = JArrayHelper::getValue($options, "target_folder");
        $langCode               = JArrayHelper::getValue($options, "lang_code");
        $langCodeDash           = JArrayHelper::getValue($options, "lang_code_dash");
        $packageFolder          = JArrayHelper::getValue($options, "package_folder");
    
        $files     = array();
        $filesList = array();
        
        // Download files
        
        $filesList[] = '<files target="'.$targetFolder.'">';
        
        foreach($resources as $resource) {
            
            $filename    = JFile::makeSafe($langCodeDash.".".$resource["filename"]);
            $destination = JPath::clean($packageFolder .DIRECTORY_SEPARATOR. $filename);
            
            $this->downloadFile($resource["alias"], $langCode, $destination);
            
            $filesList[] = '<filename>'.$filename.'</filename>';
            
        }
    
        $filesList[] = '</files>';
        
        return implode("\n", $filesList);
    
    }
    
    protected function downloadComponentFiles($resources, $options) {
    
        jimport("itprism.transifex.request");
        
        $sourceAdminLangFolder  = JArrayHelper::getValue($options, "source_admin_folder");
        $sourceSiteLangFolder   = JArrayHelper::getValue($options, "source_site_folder");
        $targetAdminLangFolder  = JArrayHelper::getValue($options, "target_admin_folder");
        $targetSiteLangFolder   = JArrayHelper::getValue($options, "target_site_folder");
        $langCode               = JArrayHelper::getValue($options, "lang_code");
        $langCodeDash           = JArrayHelper::getValue($options, "lang_code_dash");
        $packageFolder          = JArrayHelper::getValue($options, "package_folder");
    
        $adminFiles = array();
        $siteFiles  = array();
        
        // Separate admin files and site ones.
        foreach($resources as $resource) {
            if(strcmp("admin", $resource["type"]) == 0) { // Admin folder
                $adminFiles[] = array(
                    "filename" => $langCodeDash.".".$resource["filename"],
                    "slug"     => $resource["alias"],
                );
            }
    
            if(strcmp("site", $resource["type"]) == 0) { // Site folder
                $siteFiles[] = array(
                    "filename" => $langCodeDash.".".$resource["filename"],
                    "slug"     => $resource["alias"],
                );
            }
        }
    
        // Download admin files
        if(!empty($adminFiles)) {
    
            foreach($adminFiles as $fileData) {
                $destination = JPath::clean($packageFolder .DIRECTORY_SEPARATOR. $sourceAdminLangFolder .DIRECTORY_SEPARATOR. $fileData["filename"]);
                $this->downloadFile($fileData["slug"], $langCode, $destination);
            }
    
        }
    
        // Download site files
        if(!empty($siteFiles)) {
    
            foreach($siteFiles as $fileData) {
                $destination = JPath::clean($packageFolder .DIRECTORY_SEPARATOR. $sourceSiteLangFolder .DIRECTORY_SEPARATOR. $fileData["filename"]);
                $this->downloadFile($fileData["slug"], $langCode, $destination);
            }
    
        }
    
        // Generate the list of files
        $filesList = array();
        
        // A list with admin files
        if(!empty($adminFiles)) {
            $filesList[] = '<files folder="'.$sourceAdminLangFolder.'" target="'.$targetAdminLangFolder.'">';
            foreach($adminFiles as $fileData) {
                $filesList[] = '<filename>'.$fileData["filename"].'</filename>';
            }
            $filesList[] = '</files>';
        }
    
        // A list with site files
        if(!empty($siteFiles)) {
            $filesList[] = '<files folder="'.$sourceSiteLangFolder.'" target="'.$targetSiteLangFolder.'">';
            foreach($siteFiles as $fileData) {
                $filesList[] = '<filename>'.$fileData["filename"].'</filename>';
            }
            $filesList[] = '</files>';
        }
    
        return implode("\n", $filesList);
    
    }
    
    protected function generateManifest($options, $filesList) {
    
        $params       = JComponentHelper::getParams("com_itptransifex");
    
        $manifestFile = JArrayHelper::getValue($options, "manifest_filename");
    
        // Load the template file
        $templateFile = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR."assets". DIRECTORY_SEPARATOR."lang_template.xml");
        $template     = file_get_contents($templateFile);
    
        $author       = $params->get("author");
        $authorEmail  = $params->get("author_email");
        $copyright    = $params->get("copyright");
        $site         = $params->get("site");
    
        $name         = JArrayHelper::getValue($options, "name");
        $creationDate = JArrayHelper::getValue($options, "creation_date");
        $description  = JArrayHelper::getValue($options, "description");
        $version      = JArrayHelper::getValue($options, "version");
    
        $template = str_replace("{NAME}", $name, $template);
        $template = str_replace("{AUTHOR}", $author, $template);
        $template = str_replace("{AUTHOR_EMAIL}", $authorEmail, $template);
        $template = str_replace("{COPYRIGHT}", $copyright, $template);
        $template = str_replace("{SITE}", $site, $template);
        $template = str_replace("{CREATION_DATE}", $creationDate, $template);
        $template = str_replace("{VERSION}", $version, $template);
        $template = str_replace("{DESCRIPTION}", $description, $template);
    
        $template = str_replace("{FILES_LIST}", $filesList, $template);
    
        file_put_contents($manifestFile, $template);
    }
    
    /**
     * Download translated files from Transifex.
     * 
     * @param string Unique alias of the resource.
     * @param string Language code
     * @param string The folder in which files will be saved.
     */
    protected function downloadFile($slug, $langCode, $destination) {
    
        $headers = array(
            "headers" => array(
                'Content-type: application/json',
                'X-HTTP-Method-Override: GET'
            )
        );
        
        $transifex = new ITPrismTransifexRequest($this->serviceOptions["url"]);
        
        $transifex->setUsername($this->serviceOptions["username"]);
        $transifex->setPassword($this->serviceOptions["password"]);
        $transifex->enableAuthentication();
        
        $path       = $this->serviceOptions["project_path"]."/resource/".$slug."/translation/".$langCode."/";
        
        $response   = $transifex->get($path, $headers);
    
        if(!empty($response->content)) {
            file_put_contents($destination, $response->content);
        }
    
    }
    
    protected function createPackage($packageName, $projectFolder) {
    
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');
    
        $tmpFolder   = JPath::clean($app->getCfg("tmp_path"));
    
        $archiveFile = $packageName.".zip";
        $destination = $projectFolder.DIRECTORY_SEPARATOR.$archiveFile;
    
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectFolder));
    
        $filesToZip = array();
        foreach ($iterator as $key => $value) {
            $key = JPath::clean($key);
            if (!is_dir($key)) {
                $filesToZip[] = array(
                    "name" => substr($key, strlen($destination) - strlen(basename($destination))),
                    "data" => JFile::read($key)
                );
            }
        }
    
        // compression type
        $zipAdapter   = JArchive::getAdapter('zip');
        $zipAdapter->create($destination, $filesToZip, array());
    
        return $destination;
    
    }
    
    /**
     * Remove records from package map table.
     * 
     * @param array $cid
     */
    public function removeResourcesFromMap($cid) {
        
        $db = $this->getDbo();
        
        $query = $db->getQuery(true);
        $query
            ->delete($db->quoteName("#__itptfx_packages_map"))
            ->where("package_id IN ( ". implode(",", $cid) . " )");
        
        $db->setQuery($query);
        $db->execute();
        
    }
    
    /**
     * Get a package filename from a resource file name.
     *
     * @param array  Resources
     * @param string Extension type - component, module or plugin.
     * 
     * @return array|string
     */
    protected function getPackageName($resources, $type) {
    
        $fileName = JFile::makeSafe($resources[0]["filename"]);

        $fileName = JFile::stripExt($fileName);
        if(false !== strpos($fileName, ".sys")) {
            $fileName = JFile::stripExt($fileName);
        }
        
        switch($type) {
            
            case "component":
                break;
                
            case "module":
                break;
                
            case "plugin":
                
                $fileNames = array();
                
                $fileNames    = explode("_", $fileName);
                $fileNames[3] = $fileName;
                
                $fileName  = $fileNames;
                
                break;
                
        }
    
        return $fileName;
    }
    
    /**
     * Set Transifex service options to the object.
     * 
     * @param array $options
     */
    public function setTransifexOptions($options) {
        $this->serviceOptions = $options;
    }
    
    /**
     * Create an error file, which will be returned if there was an error, 
     * during the process of package creating.
     * 
     * @return string
     */
    public function createErrorFile() {
        
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');
        
        $app   = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $fileName    = "error.txt";
        
        $errorFile   = JPath::clean($app->getCfg("tmp_path").DIRECTORY_SEPARATOR.$fileName);
        if(JFile::exists($errorFile)) {
            JFile::delete($errorFile);
        }
        
        $buffer = "System error!";
        JFile::write($errorFile, $buffer);
        
        $archiveFile = "error.zip";
        $destination = JPath::clean($app->getCfg("tmp_path") .DIRECTORY_SEPARATOR. $archiveFile);
        if(JFile::exists($destination)) {
            JFile::delete($destination);
        }
        
        $filesToZip[] = array(
            "name" => $fileName,
            "data" => file_get_contents($errorFile)
        );
        
        // compression type
        $zipAdapter   = JArchive::getAdapter('zip');
        $zipAdapter->create($destination, $filesToZip, array());
        
        return $destination;
    }
}