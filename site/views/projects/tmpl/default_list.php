<?php
/**
 * @package      ItpTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<?php if (!empty($this->items)) { ?>

    <?php foreach ($this->items as $item) {
        if (!empty($item->image)) {
            $image = $this->imageFolder."/".basename($item->image);
        }?>
    <div class="row pb-15px">
        <a href="<?php echo JRoute::_(ItpTransifexHelperRoute::getProjectRoute($item->slug)); ?>" class="col-md-3">
            <?php if (!empty($item->image)) { ?>
                <img src="<?php echo $image; ?>" alt="<?php echo $this->escape($item->name); ?>" width="<?php echo $this->params->get("image_width", "200"); ?>" height="<?php echo $this->params->get("image_height", "200"); ?>" class="center-block"/>
            <?php } else { ?>
                <img src="<?php echo "media/com_itptransifex/images/no_image.png"; ?>"
                     alt="<?php echo $this->escape($item->name); ?>" width="200"
                     height="200" class="center-block"/>
            <?php } ?>
        </a>

        <div class="col-md-8">
            <h3>
                <a href="<?php echo JRoute::_(ItpTransifexHelperRoute::getProjectRoute($item->slug)); ?>">
                    <?php echo $this->escape($item->name); ?>
                </a>
            </h3>
            <?php if ($this->params->get("display_description", 1)) { ?>
                <p><?php echo JHtmlString::truncate($item->description, $this->params->get("description_length", 255), true, false); ?></p>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
<?php } ?>