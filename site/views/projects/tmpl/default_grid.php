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

$itemSpan = (!empty($this->numberInRow)) ? round(12 / $this->numberInRow) : 4;
?>
<?php if (!empty($this->items)) { ?>
<div id="cf-categories-grid">
    <ul class="thumbnails">
        <?php foreach ($this->items as $item) { ?>

        <li class="span<?php echo $itemSpan; ?>">
            <div class="thumbnail cf-category">
                <a href="<?php echo JRoute::_(ItpTransifexHelperRoute::getCategoryRoute($item->slug)); ?>">
                    <?php if (!empty($item->image_link)) { ?>
                        <img src="<?php echo $item->image_link; ?>" alt="<?php echo $this->escape($item->title); ?>" />
                    <?php } else { ?>
                        <img src="<?php echo "media/com_crowdfunding/images/no_image.png"; ?>"
                             alt="<?php echo $this->escape($item->title); ?>" width="200"
                             height="200" />
                    <?php } ?>
                </a>

                <div class="caption height-150px absolute-bottom">
                    <h3>
                        <a href="<?php echo JRoute::_(ItpTransifexHelperRoute::getCategoryRoute($item->slug)); ?>">
                            <?php echo $this->escape($item->title); ?>
                        </a>
                        <?php
                        if ($this->displayProjectsNumber) {
                            $number = (!isset($this->projectsNumber[$item->id])) ? 0 : $this->projectsNumber[$item->id]["number"];
                            echo "( ".$number. " )";
                        } ?>
                    </h3>
                    <?php if ($this->params->get("categories_display_description", true)) { ?>
                        <p><?php echo JHtmlString::truncate($item->description, $this->descriptionLength, true, false); ?></p>
                    <?php } ?>
                </div>
            </div>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>