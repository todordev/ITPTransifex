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

/**
 * ItpTransifex packages controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerPackages extends Prism\Controller\DefaultController
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Project', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function download()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $projectId = $this->input->post->getUint("id");
        $packageId  = $this->input->post->getUint("package_id");

        // Get project.
        $project = new Transifex\Project(JFactory::getDbo());
        $project->load($projectId);

        // Check for validation errors.
        if (!$project->getId() or !$project->isPublished()) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT'));
        }

        // Get package.
        $package = new Transifex\Package(JFactory::getDbo());
        $package->load($packageId);

        // Check for validation errors.
        if (!$package->getId()) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PACKAGE'));
        }

        $params         = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Validate captcha
        if ($params->get("enable_captcha", 0)) {

            $secret     = $params->get("private_key");
            $request    = $this->input->post->getString("g-recaptcha-response");

            if (!$secret) {
                $this->setRedirect(JRoute::_(ItpTransifexHelperRoute::getProjectRoute($projectId)), JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_SECRET_KEY"));
                return;
            }

            if (!$request) {
                $this->setRedirect(JRoute::_(ItpTransifexHelperRoute::getProjectRoute($projectId)), JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_CAPTCHA_STRING"));
                return;
            }

            if (!ItpTransifexHelper::validateCaptcha($secret, $request)) {
                $this->setRedirect(JRoute::_(ItpTransifexHelperRoute::getProjectRoute($projectId)), JText::_("COM_ITPTRANSIFEX_ERROR_INVALID_CAPTCHA_STRING"));
                return;
            }
        }

        // Prepare archives folder.
        jimport("joomla.filesystem.folder");
        $archiveFolder = JPath::clean(JPATH_ROOT . "/". $params->get("archives_folder", "tmp/archives"));
        if (!JFolder::exists($archiveFolder)) {
            JFolder::create($archiveFolder);
        }

        $options = array(
            "username"          => $params->get("username"),
            "password"          => $params->get("password"),
            "url"               => $params->get("api_url"),
            "archives_folder"   => $archiveFolder,
            "cache_days"        => $params->get("cache_days", 1),
            "tmp_path"          => $app->get("tmp_path")
        );

        try {

            $packageBuilder = new Transifex\Package\Builder(JFactory::getDbo(), $project);
            $packageBuilder->setOptions($options);

            $filePath = $packageBuilder->build($package);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $fileSize = filesize($filePath);
        $fileName = basename($filePath);

        $app->setHeader('Content-Type', 'application/octet-stream', true);
        $app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $app->setHeader('Content-Transfer-Encoding', 'binary', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Content-Disposition', 'attachment; filename=' . $fileName, true);
        $app->setHeader('Content-Length', $fileSize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        $app->sendHeaders();

        echo file_get_contents($filePath);

        $app->close();
    }
}
