<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="itptfx-projects<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>

    <?php if (empty($this->items)) { ?>
        <p class="alert alert-warning"><?php echo JText::_('COM_ITPTRANSIFEX_NO_ITEMS_MATCHING_QUERY'); ?></p>
    <?php } ?>

    <?php echo $this->loadTemplate($this->templateView); ?>

</div>
<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) { ?>
            <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
        <?php } ?>
        <?php echo $this->pagination->getPagesLinks(); ?> </div>
<?php } ?>