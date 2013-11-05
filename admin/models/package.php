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

jimport('joomla.application.component.modeladmin');

/**
 * This model provides functionality for managing a package.
 * 
 * @package      ITPTransifex
 * @subpackage   Components
 */
class ItpTransifexModelPackage extends JModelAdmin {
    
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
        $name       = JArrayHelper::getValue($data, "name");
        $filename   = JArrayHelper::getValue($data, "filename");
        $desc       = JArrayHelper::getValue($data, "description");
        $version    = JArrayHelper::getValue($data, "version");
        $langCode   = JArrayHelper::getValue($data, "lang_code");
        $projectId  = JArrayHelper::getValue($data, "project_id");
        
        // Load a record from the database
        $row = $this->getTable();
        $row->load(array("hash" => $hash));
        
        $row->set("name",           $name);
        $row->set("filename",       $filename);
        $row->set("description",    $desc);
        $row->set("version",        $version);
        $row->set("lang_code",      $langCode);
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
    protected function prepareTable(&$table, $hash) {
         
        // Fix magic qutoes
        if( get_magic_quotes_gpc() ) {
            $table->name        = stripcslashes($table->name);
            $table->description = stripcslashes($table->description);
        }
        
        // Set hash if it is a new record.
        if(!$table->id) {
            $table->hash = $hash;
        }
        
    }

    /**
     * Load project data
     * @param integer $projectId
     * @param string $packageHash
     * @return null|object
     */
    public function loadPackageData($projectId, $packageHash) {
        
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("a.name, a.filename, a.description, a.version")
            ->from($db->quoteName("#__itptfx_packages", "a"))
            ->where("project_id = ".(int)$projectId)
            ->where("hash = ".$db->quote($packageHash));
            
        $db->setQuery($query);
        $result = $db->loadObject();
        
        if(!$result) {
            $result = null;
        }
        
        return $result;
    }
    

    public function prepareFiles($projectId, $resourcesIDs, $options, $serviceOptions) {
    
        $app   = JFactory::getApplication();
        /** @var $app JAdministrator **/
        
        $db    = $this->getDbo();
    
        // Prepare project folder
        $query = $db->getQuery(true);
        $query
            ->select("a.id, a.name, a.alias, a.description, a.source_language_code, a.filename")
            ->from($db->quoteName("#__itptfx_projects", "a"))
            ->where("a.id = " .(int)$projectId);
    
        $db->setQuery($query);
        $project = $db->loadObject();
    
        // Prepare project URL that poitns to Transifex
        $serviceOptions["project_url"] = $serviceOptions["url"]."/".$project->alias;
    
        jimport("filesystem.path");
        jimport("filesystem.folder");
    
        $projectFileName = JArrayHelper::getValue($options, "filename");
        $projectFolder   = JPath::clean($app->getCfg("tmp_path").DIRECTORY_SEPARATOR.$projectFileName);
        $projectURL      = JUri::root()."tmp/".$projectFileName;
    
        // Remove old folder
        if(JFolder::exists($projectFolder)) {
            JFolder::delete($projectFolder);
        }
    
        // Create folder
        JFolder::create($projectFolder);
    
        // Get project resources
        $query = $db->getQuery(true);
        $query
            ->select(
                    "a.id, a.name, a.alias, a.filename, a.type, a.i18n_type, " . 
                    "a.category, a.source_language_code, a.project_id")
            ->from($db->quoteName("#__itptfx_resources", "a"))
            ->where("a.id IN (" .implode(",", $resourcesIDs) . ")")
            ->where("a.project_id = " .(int)$projectId)
            ->where("a.published  = 1");
    
        $db->setQuery($query);
        $resources = $db->loadObjectList();
    
        $sourceAdminLangFolder = "";
        $sourceSiteLangFolder  = "";
    
        $targetAdminLangFolder = "";
        $targetSiteLangFolder  = "";
    
        $langCode = JArrayHelper::getValue($options, "lang_code");
        $langCodeDash = str_replace("_", "-", $langCode);
    
        // Create admin folder.
        foreach($resources as $resource) {
    
            if(strcmp($resource->type, "admin") == 0) {
                $projectAdminFolder = JPath::clean($projectFolder.DIRECTORY_SEPARATOR."admin");
                if(!JFolder::exists($projectAdminFolder)) {
                    JFolder::create($projectAdminFolder);
    
                    // Create language folder
                    $projectAdminLangFolder = JPath::clean($projectAdminFolder.DIRECTORY_SEPARATOR.$langCodeDash);
                    JFolder::create($projectAdminLangFolder);
    
                    $sourceAdminLangFolder = "admin/".$langCodeDash;
                    $targetAdminLangFolder = "administrator/language/".$langCodeDash;
                    break;
                }
            }
    
        }
    
        // Create site folder.
        foreach($resources as $resource) {
    
            if(strcmp($resource->type, "site") == 0) {
    
                $projectSiteFolder = JPath::clean($projectFolder.DIRECTORY_SEPARATOR."site");
                if(!JFolder::exists($projectSiteFolder)) {
                    JFolder::create($projectSiteFolder);
    
                    // Create language folder
                    $projectSiteLangFolder = JPath::clean($projectSiteFolder.DIRECTORY_SEPARATOR.$langCodeDash);
                    JFolder::create($projectSiteLangFolder);
    
                    $sourceSiteLangFolder = "site/".$langCodeDash;
                    $targetSiteLangFolder = "language/".$langCodeDash;
                    break;
                }
            }
    
        }
    
        // Prepare options
        $langFileName = $projectFileName."_" .$langCodeDash;
        $manifestFile = JPath::clean($projectFolder. DIRECTORY_SEPARATOR.$langFileName. ".xml");
    
        $options = array_merge($options,
            array(
                "source_admin_folder" => $sourceAdminLangFolder,
                "source_site_folder"  => $sourceSiteLangFolder,
                "target_admin_folder" => $targetAdminLangFolder,
                "target_site_folder"  => $targetSiteLangFolder,
                "lang_code_dash"      => $langCodeDash,
                "lang_filename"       => $langFileName,
                "manifest_filename"   => $manifestFile,
                "project_folder"      => $projectFolder
            )
        );
    
        // Download files
        $siteFilesList = $this->downloadFiles($resources, $options, $serviceOptions);
    
        // Generate manifest
        $this->generateManifest($options, $siteFilesList);
    
        $file = $this->createPackage($langFileName, $projectFolder);
    
        $projectURL = $projectURL."/".basename($file);
    
        return $projectURL;
    }
    
    protected function downloadFiles($resources, $options, $serviceOptions) {
    
        $sourceAdminLangFolder  = JArrayHelper::getValue($options, "source_admin_folder");
        $sourceSiteLangFolder   = JArrayHelper::getValue($options, "source_site_folder");
        $targetAdminLangFolder  = JArrayHelper::getValue($options, "target_admin_folder");
        $targetSiteLangFolder   = JArrayHelper::getValue($options, "target_site_folder");
        $langCode               = JArrayHelper::getValue($options, "lang_code");
        $langCodeDash           = JArrayHelper::getValue($options, "lang_code_dash");
        $projectFolder          = JArrayHelper::getValue($options, "project_folder");
    
        $adminFiles = array();
        $siteFiles  = array();
        
        // Separate admin files and site ones.
        foreach($resources as $resource) {
            if(strcmp("admin", $resource->type) == 0) { // Admin folder
                $adminFiles[] = array(
                    "filename" => $langCodeDash.".".$resource->filename,
                    "slug"     => $resource->alias,
                );
            }
    
            if(strcmp("site", $resource->type) == 0) { // Site folder
                $siteFiles[] = array(
                    "filename" => $langCodeDash.".".$resource->filename,
                    "slug"     => $resource->alias,
                );
            }
        }
    
        // Download admin files
        if(!empty($adminFiles)) {
    
            foreach($adminFiles as $fileData) {
                $destination = JPath::clean($projectFolder."/".$sourceAdminLangFolder."/").$fileData["filename"];
                $this->downloadFile($fileData["slug"], $langCode, $serviceOptions, $destination);
            }
    
        }
    
        // Download site files
        if(!empty($siteFiles)) {
    
            foreach($siteFiles as $fileData) {
                $destination = JPath::clean($projectFolder."/".$sourceSiteLangFolder."/").$fileData["filename"];
                $this->downloadFile($fileData["slug"], $langCode, $serviceOptions, $destination);
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
    
    protected function downloadFile($slug, $langCode, $serviceOptions, $destination) {
    
        $url        = JArrayHelper::getValue($serviceOptions, "url");
        $projectUrl = JArrayHelper::getValue($serviceOptions, "project_url");
        $username   = JArrayHelper::getValue($serviceOptions, "username");
        $password   = JArrayHelper::getValue($serviceOptions, "password");
    
        $resourceUrl = $projectUrl."/resource/".$slug."/translation/".$langCode."/";
    
        $ch         = curl_init();
    
        $headers = array();
        $headers[] = 'Content-type: application/json';
        $headers[] = 'X-HTTP-Method-Override: GET';
    
        curl_setopt($ch, CURLOPT_URL, $resourceUrl);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        // Get the data
        $data = curl_exec($ch);
    
        // Get info about the request
        $info = curl_getinfo($ch);
    
        // Close the request
        curl_close($ch);
    
        $data = json_decode($data);
    
        if(!empty($data->content)) {
            file_put_contents($destination, $data->content);
        }
    
    }
    
    protected function createPackage($projectName, $projectFolder) {
    
        $app = JFactory::getApplication();
        /** @var $app JAdministrator **/
    
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');
    
        $tmpFolder   = JPath::clean($app->getCfg("tmp_path"));
    
        $archiveFile = $projectName.".zip";
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
}