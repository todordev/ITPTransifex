<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * ItpTransifex export controller
 *
 * @package     ItpTransifex
 * @subpackage  Components
 */
class ItpTransifexControllerExport extends Prism\Controller\Admin
{
    /**
     * @param string $name
     * @param string $prefix
     * @param array  $config
     *
     * @return ItpTransifexModelExport
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

        $projectId = $this->input->getInt('id');
        $language  = $this->input->getCmd('language');

        $model = $this->getModel();

        try {
            // Get project
            $project = new Transifex\Project\Project(JFactory::getDbo());
            $project->load($projectId);

            $output   = $model->getProject($project, $language);

            // Get file name.
            $fileName = $project->getName();

            $filter   = JFilterInput::getInstance();
            $fileName = $filter->clean($fileName, 'CMD').'_'.$language.'.xml';

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_userideas');
            throw new Exception(JText::_('COM_ITPTRANSIFEX_ERROR_SYSTEM'));
        }

        $tmpFolder = JPath::clean($app->get('tmp_path'));

        $archiveName = JFile::stripExt(basename($fileName));
        $archiveFile = $archiveName . '.zip';
        $destination = $tmpFolder . DIRECTORY_SEPARATOR . $archiveFile;

        // compression type
        $zipAdapter   = JArchive::getAdapter('zip');
        $filesToZip[] = array(
            'name' => $fileName,
            'data' => $output
        );

        $zipAdapter->create($destination, $filesToZip, array());

        $fileSize = filesize($destination);

        $app->setHeader('Content-Type', 'application/octet-stream', true);
        $app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $app->setHeader('Content-Transfer-Encoding', 'binary', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);
        $app->setHeader('Content-Disposition', 'attachment; filename=' . $archiveFile, true);
        $app->setHeader('Content-Length', $fileSize, true);

        $doc = JFactory::getDocument();
        $doc->setMimeEncoding('application/octet-stream');

        $app->sendHeaders();

        echo file_get_contents($destination);
        $app->close();
    }
}
