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

$this->document->addScript('media/com_itptransifex/js/site/project_nocaptcha.js');
?>
<form method="post" action="<?php echo JRoute::_('index.php?option=com_itptransifex'); ?>" target="_blank" id="js-form-download-project" style="display: none;">
    <input type="hidden" name="id" value="<?php echo $this->project->getId(); ?>" />
    <input type="hidden" name="language" value="" id="js-form-language" />
    <input type="hidden" name="task" value="project.download" />
    <?php echo JHtml::_('form.token'); ?>
</form>