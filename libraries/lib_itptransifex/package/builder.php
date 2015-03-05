<?php
/**
 * @package      ITPTransifex
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport("joomla.filesystem.path");
jimport('joomla.filesystem.file');
jimport("joomla.filesystem.folder");
jimport('joomla.filesystem.archive');

jimport("itprism.string");
jimport("itprism.transifex.request");

JLoader::register("ItpTransifexProject", JPATH_LIBRARIES."/itptransifex/project.php");
JLoader::register("ItpTransifexPackage", JPATH_LIBRARIES."/itptransifex/package.php");
JLoader::register("ItpTransifexLanguage", JPATH_LIBRARIES."/itptransifex/language.php");

/**
 * This class contains methods that are used for managing a package.
 *
 * @package      ITPTransifex
 * @subpackage   Libraries
 */
class ItpTransifexPackageBuilder
{
    /**
     * @var ItpTransifexProject
     */
    protected $project;

    /**
     * An URI to the project on Transifex server.
     *
     * @var string
     */
    protected $serviceProjectPath;

    protected $options;

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db, ItpTransifexProject $project)
    {
        $this->db = $db;
        $this->project = $project;
    }

    /**
     * Set some options.
     * 
     * <code>
     * $options = array(
     *     "username" => $params->get("username"),
     *     "password" => $params->get("password"),
     *     "url"      => $params->get("api_url")
     * );
     *
     * $package = new ItpTransifexPackageBuilder(JFactory::getDbo());
     * $package->setOptions($options);
     * </code>
     * 
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Build a package.
     * 
     * @param ItpTransifexPackage $package
     *
     * @return string
     */
    public function build(ItpTransifexPackage $package)
    {
        $filePath = "";

        // Prepare project URL that points to Transifex.
        $this->serviceProjectPath = "project/" . $this->project->getAlias();

        // Prepare project package.
        if ($package->getId()) {

            $fileName = $package->getFileName();
            if (!$fileName) {
                $filePath = $this->createErrorFile();
                return $filePath;
            }

            $fileName .= "_".str_replace("_", "-", $package->getLanguage());

            // Check for existing package.
            $cachedFile = JPath::clean($this->options["archives_folder"] ."/". $fileName. ".zip");
            $cacheExists = false;

            if (JFile::exists($cachedFile)) {
                $time = filemtime($cachedFile);

                $fileDate = new DateTime();
                $fileDate->setTimestamp($time);

                $today    = new DateTime();
                $interval = $fileDate->diff($today);

                if ($interval->d < $this->options["cache_days"]) {
                    $cacheExists = true;
                } else {
                    // Remove the old file.
                    JFile::delete($cachedFile);
                }
            }

            // If cached file does not exist, generate new one.
            if (!$cacheExists) {
                $filePath = $this->preparePackage($package, $fileName);
            } else {
                $filePath = $cachedFile;
            }
        }

        // Create error file.
        if (!$filePath) {
            $filePath = $this->createErrorFile();
        }

        return $filePath;
    }

    public function buildProject($languageCode)
    {
        $filePath = "";

        // Get packages.
        $packages   = $this->project->getPackages(array("language" => $languageCode));

        // Extract package IDs.
        foreach ($packages as $package) {
            $ids[] = $package["id"];
        }

        // Prepare project URL that points to Transifex.
        $this->serviceProjectPath = "project/" . $this->project->getAlias();

        // Prepare project package.
        if (!empty($ids)) {
            $fileName = $this->project->getFileName();
            if (!$fileName) {
                $fileName = "UNZIPFIRST";
            }

            $fileName .= "_".$languageCode;

            // Check for existing package.
            $cachedFile = JPath::clean($this->options["archives_folder"] ."/". $fileName. ".zip");
            $cacheExists = false;

            if (JFile::exists($cachedFile)) {
                $time = filemtime($cachedFile);

                $fileDate = new DateTime();
                $fileDate->setTimestamp($time);

                $today    = new DateTime();
                $interval = $fileDate->diff($today);

                if ($interval->d < $this->options["cache_days"]) {
                    $cacheExists = true;
                } else {
                    // Remove the old file.
                    JFile::delete($cachedFile);
                }
            }

            // If cached file does not exist, generate new one.
            if (!$cacheExists) {
                $filePath = $this->prepareProjectPackage($ids, $fileName);
            } else {
                $filePath = $cachedFile;
            }
        }

        // Create error file.
        if (!$filePath) {
            $filePath = $this->createErrorFile();
        }

        return $filePath;
    }

    /**
     * Generate archive that contains language files.
     *
     * @param ItpTransifexPackage $package
     * @param bool $includeLanguageName Include or not language name to package name.
     *
     * @return string
     */
    public function preparePackage($package, $includeLanguageName = true)
    {
        $packageFile  = "";

        $packageFileName = $package->getFilename();
        $packageFolder   = JPath::clean($this->options["tmp_path"] . DIRECTORY_SEPARATOR . $packageFileName);

        // Remove old folder
        if (JFolder::exists($packageFolder)) {
            JFolder::delete($packageFolder);
        }

        // Create folder
        JFolder::create($packageFolder);

        // Get project resources
        $published   = 1;
        $resources   = $package->getResources($published);

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

        // Prepare archives folder.
        $archivesFile = JPath::clean($this->options["archives_folder"] ."/". basename($packageFile));

        // Move the file to archives.
        JFile::move($packageFile, $archivesFile);

        // Remove the temporary folder.
        JFolder::delete($packageFolder);
        
        return $archivesFile;
    }

    /**
     * Generate archive that contains all language packages.
     *
     * @param  array $packagesIds
     * @param string $fileName
     * @param bool $includeLanguageName
     *
     * @return string
     */
    protected function prepareProjectPackage(array $packagesIds, $fileName = "UNZIPFIRST", $includeLanguageName = true)
    {
        $files        = array();
        $packageFile  = "";
        $archivesFile = "";

        foreach ($packagesIds as $packageId) {

            // Get package.
            $package = new ItpTransifexPackage($this->db);
            $package->load($packageId);

            $packageFileName = $package->getFilename();
            $packageFolder   = JPath::clean($this->options["tmp_path"] . DIRECTORY_SEPARATOR . $packageFileName);

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
            $string = new ITPrismString();
            $string->generateRandomString();

            $tmpFolder   = JPath::clean($this->options["tmp_path"] . DIRECTORY_SEPARATOR . "tmp_".(string)$string);
            JFolder::create($tmpFolder);

            // Copy files to the temporary folder.
            foreach ($files as $file) {
                $baseName = $tmpFolder . DIRECTORY_SEPARATOR . basename($file);
                JFile::copy($file, $baseName);
            }

            // Create an archive with files.
            $packageFile = $this->createPackage($fileName, $tmpFolder);

            // Prepare archives folder.
            $archivesFile = JPath::clean($this->options["archives_folder"] ."/". basename($packageFile));

            // Move the file to archives.
            JFile::move($packageFile, $archivesFile);

            // Remove the temporary folder.
            JFolder::delete($tmpFolder);
        }

        return $archivesFile;
    }

    /**
     * @param ItpTransifexPackage $package
     * @param ItpTransifexResources $resources
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
        $targetAdminFolder = "administrator/components/" . $packageName . "/language/" . $langCodeDash;
        $targetSiteFolder  = "components/" . $packageName . "/language/" . $langCodeDash;

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
     * @param ItpTransifexPackage $package
     * @param ItpTransifexResources $resources
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
        $targetFolder = "modules/" . $packageName . "/language/" . $langCodeDash;

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
     * @param ItpTransifexPackage $package
     * @param ItpTransifexResources $resources
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
        $targetFolder = "plugins/" . $pluginType . "/" . $pluginName . "/language/" . $langCodeDash;

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
     * @param ItpTransifexPackage $package
     * @param ItpTransifexResources $resources
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
        $targetFolder = "libraries/" . substr($packageName, 4) . "/language/" . $langCodeDash;

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
     * Get a package filename from a resource file name.
     *
     * @param ItpTransifexResources $resources  Resources
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
     * Generate a package name.
     *
     * @param ItpTransifexPackage $package
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
            $language = new ItpTransifexLanguage(JFactory::getDbo());
            $language->loadByCode($langCode);

            $name = $package->getName() . " - ".$language->getName();
        }

        return $name;
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
        /** @var $zipAdapter JArchiveZip */

        $zipAdapter->create($destination, $filesToZip, array());

        return $destination;
    }

    protected function downloadComponentFiles($resources, $options)
    {
        $sourceAdminLangFolder = JArrayHelper::getValue($options, "source_admin_folder");
        $sourceSiteLangFolder  = JArrayHelper::getValue($options, "source_site_folder");
        $targetAdminLangFolder = JArrayHelper::getValue($options, "target_admin_folder");
        $targetSiteLangFolder  = JArrayHelper::getValue($options, "target_site_folder");
        $langCode              = JArrayHelper::getValue($options, "lang_code");
        $langCodeDash          = JArrayHelper::getValue($options, "lang_code_dash");
        $packageFolder         = JArrayHelper::getValue($options, "package_folder");

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

    /**
     * This method downloads plugin and module files and generate a string with files list.
     *
     * @param ItpTransifexResources $resources
     * @param array $options
     *
     * @return string
     */
    protected function downloadFiles($resources, $options)
    {
        $targetFolder  = JArrayHelper::getValue($options, "target_folder");
        $langCode      = JArrayHelper::getValue($options, "lang_code");
        $langCodeDash  = JArrayHelper::getValue($options, "lang_code_dash");
        $packageFolder = JArrayHelper::getValue($options, "package_folder");

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

        $transifex = new ITPrismTransifexRequest($this->options["url"]);

        $transifex->setUsername($this->options["username"]);
        $transifex->setPassword($this->options["password"]);
        $transifex->enableAuthentication();

        $path = $this->serviceProjectPath . "/resource/" . $slug . "/translation/" . $langCode . "/";

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

    protected function generateManifest($options, $filesList)
    {
        $params = JComponentHelper::getParams("com_itptransifex");
        /** @var  $params Joomla\Registry\Registry */

        $manifestFile = JArrayHelper::getValue($options, "manifest_filename");

        // Load the template file
        $templateFile = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "lang_template.xml");
        $template     = file_get_contents($templateFile);

        $author      = $params->get("author");
        $authorEmail = $params->get("author_email");
        $copyright   = $params->get("copyright");
        $site        = $params->get("site");

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

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($template);

        $template = $dom->saveXML();

        file_put_contents($manifestFile, $template);
    }

    /**
     * Create an error file, which will be returned if there was an error,
     * during the process of package creating.
     *
     * @return string
     */
    protected function createErrorFile()
    {
        $fileName = "error.txt";

        $errorFile = JPath::clean($this->options["tmp_path"] . DIRECTORY_SEPARATOR . $fileName);
        if (JFile::exists($errorFile)) {
            JFile::delete($errorFile);
        }

        $buffer = "System error!";
        JFile::write($errorFile, $buffer);

        $archiveFile = "error.zip";
        $destination = JPath::clean($this->options["tmp_path"] . DIRECTORY_SEPARATOR . $archiveFile);
        if (JFile::exists($destination)) {
            JFile::delete($destination);
        }

        $filesToZip[] = array(
            "name" => $fileName,
            "data" => file_get_contents($errorFile)
        );

        // compression type
        $zipAdapter = JArchive::getAdapter('zip');
        /** @var $zipAdapter JArchiveZip */

        $zipAdapter->create($destination, $filesToZip, array());

        return $destination;
    }
}
