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

$this->document->addScript('media/' . $this->option . '/js/site/packages_nocaptcha.js');
?>
<form method="post" action="<?php echo JRoute::_("index.php?option=com_itptransifex"); ?>" target="_blank" id="js-form-download-package">
    <input type="hidden" name="id" value="<?php echo $this->project->getId(); ?>" />
    <input type="hidden" name="package_id" value="" id="js-form-package-id" />
    <input type="hidden" name="task" value="packages.download" />
    <?php echo JHtml::_('form.token'); ?>
</form>