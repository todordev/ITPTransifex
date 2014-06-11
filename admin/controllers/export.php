<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

jimport('itprism.controller.admin');

/**
 * ItpTransifex export controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerExport extends ITPrismControllerAdmin
{
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Export', $prefix = 'ItpTransifexModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Prepare meta data about project, packages and resources.
     * Send them to the user as an archive.
     *
     * @throws Exception
     */
    public function download()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $projectId = $this->input->getInt("id");
        $language  = $this->input->getCmd("language");

        $model = $this->getModel();

        try {

            // Get project
            jimport("itptransifex.project");
            $project = new ItpTransifexProject(JFactory::getDbo());
            $project->load($projectId);

            $output   = $model->getProject($project, $language);

            // Get file name.
            $fileName = $project->getName();

            $filter   = JFilterInput::getInstance();
            $fileName = $filter->clean($fileName, "CMD")."_".$language.".xml";

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.archive');

        $tmpFolder = JPath::clean($app->get("tmp_path"));

        $archiveName = JFile::stripExt(basename($fileName));
        $archiveFile = $archiveName . ".zip";
        $destination = $tmpFolder . DIRECTORY_SEPARATOR . $archiveFile;

        // compression type
        $zipAdapter   = JArchive::getAdapter('zip');
        $filesToZip[] = array(
            'name' => $fileName,
            'data' => $output
        );

        $zipAdapter->create($destination, $filesToZip, array());

        $filesize = filesize($destination);

        JResponse::setHeader('Content-Type', 'application/octet-stream', true);
        JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
        JResponse::setHeader('Pragma', 'no-cache', true);
        JResponse::setHeader('Expires', '0', true);
        JResponse::setHeader('Content-Disposition', 'attachment; filename=' . $archiveFile, true);
        JResponse::setHeader('Content-Length', $filesize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        JResponse::sendHeaders();

        echo file_get_contents($destination);
        JFactory::getApplication()->close();
    }
}
