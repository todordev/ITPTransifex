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
?>
<div class="itptfx-project<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    <?php
    $layout      = new JLayoutFile('project');
    echo $layout->render($this->layoutData);
    ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo JText::_("COM_ITPTRANSIFEX_PACKAGE"); ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($this->items as $item) {?>
            <tr>
                <td class="js-pkg-title<?php echo $item->id; ?> has-context col-md-10">
                    <strong><?php echo $this->escape($item->name); ?> (<?php echo $this->escape($item->language); ?>)</strong>
                    <p><?php echo $this->escape($item->description); ?></p>
                    <div class="font-xsmall"><?php echo JText::sprintf("COM_ITPTRANSIFEX_VERSION_S", $item->version); ?></div>
                </td>
                <td class="col-md-2">
                    <button class="btn btn-primary js-pkg-btn-download" data-package-id="<?php echo (int)$this->escape($item->id); ?>">
                        <span class="fa fa-download" aria-hidden="true"></span>
                        <?php echo JText::_("COM_ITPTRANSIFEX_DOWNLOAD"); ?>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
        <div class="pagination">
            <?php if ($this->params->def('show_pagination_results', 1)) { ?>
                <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
            <?php } ?>
            <?php echo $this->pagination->getPagesLinks(); ?> </div>
    <?php } ?>

</div>

<?php
if (!$this->params->get("enable_captcha", 0)) {
    echo $this->loadTemplate("nocaptcha");
} else {
    echo $this->loadTemplate("captcha");
}