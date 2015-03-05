<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('itprism.controller.default');

/**
 * ItpTransifex project controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerProject extends ITPrismControllerDefault
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

        $projectId     = $this->input->post->getInt("id");
        $languageCode  = $this->input->post->getCmd("language");

        // Get project.
        jimport("itptransifex.project");
        $project = new ItpTransifexProject(JFactory::getDbo());
        $project->load($projectId);

        // Check for validation errors.
        if (!$project->getId() or !$project->isPublished()) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_PROJECT'));
        }

        // Get project.
        jimport("itptransifex.language");
        $language = new ItpTransifexLanguage(JFactory::getDbo());
        $language->loadByCode($languageCode);

        // Check for validation errors.
        if (!$language->getId()) {
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_INVALID_LANGUAGE'));
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

            jimport("itptransifex.package.builder");
            $packageBuilder = new ItpTransifexPackageBuilder(JFactory::getDbo(), $project);
            $packageBuilder->setOptions($options);

            $filePath = $packageBuilder->buildProject($languageCode);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $fileSize = filesize($filePath);
        $fileName = basename($filePath);

        JResponse::setHeader('Content-Type', 'application/octet-stream', true);
        JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
        JResponse::setHeader('Pragma', 'no-cache', true);
        JResponse::setHeader('Expires', '0', true);
        JResponse::setHeader('Content-Disposition', 'attachment; filename=' . $fileName, true);
        JResponse::setHeader('Content-Length', $fileSize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        JResponse::sendHeaders();

        echo file_get_contents($filePath);

        JFactory::getApplication()->close();
    }
}
