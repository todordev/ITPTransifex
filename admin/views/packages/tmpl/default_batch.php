<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_ITPTRANSIFEX_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
        <p class="sticky">
            <?php echo JText::_('COM_ITPTRANSIFEX_BATCH_NOTE'); ?>
        </p>
        <form action="<?php echo JRoute::_("index.php?option=com_itptransifex&task=package.batch&format=raw"); ?>" method="post" id="js-itptfx-batch-form">
            <label><?php echo JText::_('COM_ITPTRANSIFEX_LANGUAGE'); ?></label>
            <?php echo JHtml::_("select.genericlist", $this->languages, "language"); ?>
        </form>
	</div>
	<div class="modal-footer">
        <img src="../media/com_itptransifex/images/ajax-loader.gif" width="16" height="16" style="display: none;" id="js-batch-ajaxloader" />
		<button class="btn" type="button" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" id="js-itptfx-btn-batch">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
