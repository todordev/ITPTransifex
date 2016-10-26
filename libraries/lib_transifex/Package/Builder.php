<?php
/**
 * @package      ITPTransifex
 * @subpackage   Libraries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Package;

use Prism\Constants;
use Transifex\Project\Project;
use Transifex\Resource\Resources;
use Transifex\Language\Language;
use Prism\Transifex\Request;
use Prism\Utilities\StringHelper;
use Joomla\String\StringHelper as JoomlaStringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a package.
 *
 * @package      ITPTransifex
 * @subpackage   Libraries
 */
class Builder
{
    /**
     * @var Project
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
     * @var \JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * @param \JDatabaseDriver $db
     * @param Project $project
     */
    public function __construct(\JDatabaseDriver $db, Project $project)
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
     * $package = new Transifex\Package\Builder(\JFactory::getDbo());
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
     * @param Package $package
     *
     * @throws \UnexpectedValueException
     * @return string
     */
    public function build(Package $package)
    {
        $filePath = '';

        // Prepare project URL that points to Transifex.
        $this->serviceProjectPath = 'project/' . $this->project->getAlias();

        // Prepare project package.
        if ($package->getId()) {
            $fileName = $package->getFilename();
            if (!$fileName) {
                $filePath = $this->createErrorFile();
                return $filePath;
            }

            $fileName .= '_'.str_replace('_', '-', $package->getLanguage());

            // Check for existing package.
            $cacheExists = false;
            $cachedFile  = \JPath::clean($this->options['archives_folder'] . '/' . $fileName . '.zip');
            if ($this->getOption('cache_days') and \JFile::exists($cachedFile)) {
                $time = filemtime($cachedFile);

                $fileDate = new \DateTime();
                $fileDate->setTimestamp($time);

                $today    = new \DateTime();
                $interval = $fileDate->diff($today);

                if ($interval->days < $this->getOption('cache_days', 7)) {
                    $cacheExists = true;
                } else {
                    // Remove the old file.
                    \JFile::delete($cachedFile);
                }
            }

            // If cached file does not exist, generate new one.
            $filePath = $cachedFile;
            if (!$cacheExists) {
                $filePath = $this->preparePackage($package, (bool)$this->getOption('include_lang_name', 1));
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
        $filePath = '';

        // Get packages.
        $packages   = $this->project->getPackages(array('language' => $languageCode));
        
        // Extract package IDs.
        $ids = array();
        foreach ($packages as $package) {
            $ids[] = $package['id'];
        }

        // Prepare project URL that points to Transifex.
        $this->serviceProjectPath = 'project/' . $this->project->getAlias();

        // Prepare project package.
        if (count($ids) > 0) {
            $fileName = $this->project->getFilename();
            if (!$fileName) {
                $fileName = 'UNZIPFIRST_'.\JFilterOutput::stringURLSafe($this->project->getName());
            }

            $fileName .= '_'.$languageCode;

            // Check for existing package.
            $cachedFile = \JPath::clean($this->options['archives_folder'] .'/'. $fileName. '.zip');
            $cacheExists = false;

            if (\JFile::exists($cachedFile)) {
                $time = filemtime($cachedFile);

                $fileDate = new \DateTime();
                $fileDate->setTimestamp($time);

                $today    = new \DateTime();
                $interval = $fileDate->diff($today);

                if ($interval->d < $this->options['cache_days']) {
                    $cacheExists = true;
                } else {
                    // Remove the old file.
                    \JFile::delete($cachedFile);
                }
            }

            // If cached file does not exist, generate new one.
            $filePath = $cachedFile;
            if (!$cacheExists) {
                $filePath = $this->prepareProjectPackage($ids, $fileName, (bool)$this->getOption('include_lang_name', 1));
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
     * @param Package $package
     * @param bool $includeLanguageName Include or not language name to package name.
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return string
     */
    protected function preparePackage($package, $includeLanguageName = true)
    {
        $archivesFile    = '';
        $packageFile     = '';
        
        $packageFileName = $package->getFilename();
        $packageFolder   = \JPath::clean($this->options['tmp_path'] . DIRECTORY_SEPARATOR . $packageFileName);

        // Remove old folder
        if (\JFolder::exists($packageFolder)) {
            \JFolder::delete($packageFolder);
        }

        // Create folder
        \JFolder::create($packageFolder);

        // Get project resources
        $resources   = $package->getResources(Constants::PUBLISHED);
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

        if ($packageFile !== null and $packageFile !== '') {
            // Prepare archives folder.
            $archivesFile = \JPath::clean($this->options['archives_folder'] . '/' . basename($packageFile));

            // Move the file to archives.
            \JFile::move($packageFile, $archivesFile);
        }

        // Remove the temporary folder.
        \JFolder::delete($packageFolder);
        
        return $archivesFile;
    }

    /**
     * Generate archive that contains all language packages.
     *
     * @param array $packagesIds
     * @param string $fileName
     * @param bool $includeLanguageName
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return string
     */
    protected function prepareProjectPackage(array $packagesIds, $fileName = 'UNZIPFIRST', $includeLanguageName = true)
    {
        $files        = array();
        $archivesFile = '';

        foreach ($packagesIds as $packageId) {
            $packageFile  = '';
            
            // Get package.
            $package = new Package($this->db);
            $package->load($packageId);

            $packageFileName = $package->getFilename();
            $packageFolder   = \JPath::clean($this->options['tmp_path'] . DIRECTORY_SEPARATOR . $packageFileName);

            // Remove old folder
            if (\JFolder::exists($packageFolder)) {
                \JFolder::delete($packageFolder);
            }

            // Create folder
            \JFolder::create($packageFolder);

            // Get project resources
            $resources   = $package->getResources(Constants::PUBLISHED);

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

            if ($packageFile !== null and $packageFile !== '') {
                $files[] = $packageFile;
            }
        }

        // Make an archive.
        if (count($files) > 0) {
            // Create temporary folder.
            $tmpFolder   = \JPath::clean($this->options['tmp_path'] . DIRECTORY_SEPARATOR . 'tmp_'.(string)StringHelper::generateRandomString());
            \JFolder::create($tmpFolder);

            // Copy files to the temporary folder.
            foreach ($files as $file) {
                $baseName = $tmpFolder . DIRECTORY_SEPARATOR . basename($file);
                \JFile::copy($file, $baseName);
            }

            // Create a package.
            $packageFile = $this->createPackage($fileName, $tmpFolder);

            // Move the package to archive folder.
            if ($packageFile !== null and $packageFile !== '') {
                $archivesFile = \JPath::clean($this->options['archives_folder'] . '/' . basename($packageFile));

                // Move the file to archives.
                \JFile::move($packageFile, $archivesFile);
            }

            // Remove the temporary folder.
            \JFolder::delete($tmpFolder);
        }

        return $archivesFile;
    }

    /**
     * Prepare component, module, library or other package.
     *
     * @param Package $package
     * @param Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @return string
     */
    protected function prepareExtension($package, $resources, $packageFolder, $includeLanguageName)
    {
        // Get the name of the extension folder from resource name.
        $packageName = $this->getPackageName($resources, 'component');

        // Get package language code. Generate a language code with dash.
        $langCode     = $package->getLanguage();
        $langCodeDash = str_replace('_', '-', $langCode);

        // Prepare options
        $manifestFileName = $langCodeDash . '.' . $packageName;
        $manifestFile     = \JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . '.xml');

        $date = new \JDate();

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
     * @param Package $package
     * @param Resources $resources
     * @param string  $packageFolder
     * @param bool  $includeLanguageName
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
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
        $manifestFile     = \JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $manifestFileName . '.xml');

        $date = new \JDate();

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
        $siteFilesList = $this->fetchFiles($resources, $options);

        // Generate manifest
        $this->generateManifest($options, $siteFilesList);

        $packageFileName = $package->getFilename() . '_' . $langCodeDash;

        return $this->createPackage($packageFileName, $packageFolder);
    }

    /**
     * Get a package filename from a resource file name.
     *
     * @param Resources $resources  Resources
     * @param string $type Extension type - component, module or plugin.
     *
     * @return array|string
     */
    protected function getPackageName($resources, $type)
    {
        $fileName = \JFile::makeSafe($resources[0]['filename']);

        $fileName = \JFile::stripExt($fileName);
        if (false !== strpos($fileName, '.sys')) {
            $fileName = \JFile::stripExt($fileName);
        }

        if (strcmp($type, 'plugin') === 0) {
            $fileNames    = explode('_', $fileName);
            $fileNames[3] = $fileName;

            $fileName = $fileNames;
        }

        return $fileName;
    }

    /**
     * Generate a package name.
     *
     * @param Package $package
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

            $language = new Language(\JFactory::getDbo());
            $language->load($keys);

            $name = $package->getName() . ' - '.$language->getName();
        }

        return $name;
    }

    protected function createPackage($packageName, $projectFolder)
    {
        $archiveFile = $packageName . '.zip';
        $destination = $projectFolder . DIRECTORY_SEPARATOR . $archiveFile;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($projectFolder));

        $filesToZip = array();
        foreach ($iterator as $key => $value) {
            $key = \JPath::clean($key);
            if (!is_dir($key)) {
                $filesToZip[] = array(
                    'name' => substr($key, strlen($destination) - strlen(basename($destination))),
                    'data' => file_get_contents($key)
                );
            }
        }

        // compression type
        $zipAdapter = \JArchive::getAdapter('zip');
        /** @var $zipAdapter \JArchiveZip */

        $zipAdapter->create($destination, $filesToZip);

        return $destination;
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
            $sourceFolderPath = \JPath::clean($packageFolder . DIRECTORY_SEPARATOR . $sourceFolder);

            if (!\JFolder::exists($sourceFolderPath)) {
                \JFolder::create($sourceFolderPath);
            }

            foreach ($filesData['files'] as $fileData) {
                $destination = \JPath::clean($sourceFolderPath . DIRECTORY_SEPARATOR . $fileData['filename']);
                
                try {
                    $this->downloadFile($fileData['slug'], $langCode, $destination);
                } catch (\Exception $e) {
                    unset($files[$key]);
                    \JLog::add($e->getMessage() . "\n SLUG: ".$fileData['slug'], \JLog::ERROR, 'com_userideas');
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

        $transifex = new Request($this->options['url']);

        $transifex->setUsername($this->options['username']);
        $transifex->setPassword($this->options['password']);
        $transifex->enableAuthentication();

        $path = $this->serviceProjectPath . '/resource/' . $slug . '/translation/' . $langCode . '/';

        $response = $transifex->get($path, $headers);

        if (!empty($response->content)) {
            \JFile::write($destination, $response->content);
        }

        // Copy index.html
        $indexFile = dirname($destination) . DIRECTORY_SEPARATOR . 'index.html';
        $html      = '<html><body style="background-color: #fff;"></body></html>';
        if (true !== \JFile::write($indexFile, $html)) {
            \JLog::add(\JText::sprintf('COM_ITPTRANSIFEX_ERROR_CANNOT_CREATE_FILE', $indexFile));
        }
    }

    protected function generateManifest($options, $filesList)
    {
        $params = \JComponentHelper::getParams('com_itptransifex');
        /** @var  $params Registry */

        $manifestFile = ArrayHelper::getValue($options, 'manifest_filename');

        // Load the template file
        $templateFile = \JPath::clean(JPATH_BASE . '/administrator/components/com_itptransifex/assets/lang_template.xml');
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

        $dom = new \DOMDocument();
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
     * @throws \UnexpectedValueException
     * @return string
     */
    protected function createErrorFile()
    {
        $fileName = 'error.txt';

        $errorFile = \JPath::clean($this->options['tmp_path'] . DIRECTORY_SEPARATOR . $fileName);
        if (\JFile::exists($errorFile)) {
            \JFile::delete($errorFile);
        }

        $buffer = 'System error!';
        \JFile::write($errorFile, $buffer);

        $archiveFile = 'error.zip';
        $destination = \JPath::clean($this->options['tmp_path'] . DIRECTORY_SEPARATOR . $archiveFile);
        if (\JFile::exists($destination)) {
            \JFile::delete($destination);
        }

        $filesToZip[] = array(
            'name' => $fileName,
            'data' => file_get_contents($errorFile)
        );

        // compression type
        $zipAdapter = \JArchive::getAdapter('zip');
        /** @var $zipAdapter \JArchiveZip */

        $zipAdapter->create($destination, $filesToZip);

        return $destination;
    }

    /**
     * Return value from options.
     *
     * <code>
     * $options = array(
     *     "username" => $params->get("username"),
     *     "password" => $params->get("password"),
     *     "url"      => $params->get("api_url")
     * );
     *
     * $package = new Transifex\Package\Builder(\JFactory::getDbo());
     * $package->setOptions($options);
     *
     * if (!$package->getOption("cache_days")) {
     * ...
     * }
     * </code>
     *
     * @param string     $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }
}
