<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_itptransifex&view=resources'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
    <?php else : ?>
	<div id="j-main-container">
    <?php endif;?>
    
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
    			<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_ITPTRANSIFEX_SEARCH_IN_NAME');?></label>
    			<input type="text" name="filter_search" class="hasTooltip" id="filter_search" placeholder="<?php echo JText::_('COM_ITPTRANSIFEX_SEARCH_IN_NAME'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_ITPTRANSIFEX_SEARCH_IN_NAME_TOOLTIP'); ?>" />
    		</div>
    		<div class="btn-group pull-left">
    			<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
    			<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" id="js-search-filter-clear"><i class="icon-remove"></i></button>
    		</div>
    		<div class="btn-group pull-right hidden-phone">
    			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
    			<?php echo $this->pagination->getLimitBox(); ?>
    		</div>
    		<div class="btn-group pull-right hidden-phone">
    			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
    			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
    				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
    				<option value="asc" <?php if ($this->listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
    				<option value="desc" <?php if ($this->listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
    			</select>
    		</div>
    		<div class="btn-group pull-right hidden-phone">
    			<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
    			<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
    				<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
    				<?php echo JHtml::_('select.options', $this->sortFields, 'value', 'text', $this->listOrder);?>
    			</select>
    		</div>
    		
        </div>
        <div class="clearfix"> </div>
    
        <table class="table table-striped" id="resourcesList">
           <thead><?php echo $this->loadTemplate('head');?></thead>
    	   <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
    	   <tbody><?php echo $this->loadTemplate('body');?></tbody>
    	</table>

        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" id="filter_order" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
        <input type="hidden" name="project_id" value="<?php echo $this->projectId; ?>" id="js-project-id" />
        <?php echo JHtml::_('form.token'); ?>
        
        <input type="hidden" name="lang_code"     value="" id="js-langcode-target" />
        <input type="hidden" name="version"       value="" id="js-version-target" />
        <input type="hidden" name="description"   value="" id="js-desc-target" />
        <input type="hidden" name="name"          value="" id="js-name-target" />
        <input type="hidden" name="filename"      value="" id="js-filename-target" />
        <input type="hidden" name="store_data"    value="" id="js-store-data-target" />
        
    </div>
</form>

<div class="modal hide fade" id="js-cp-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_("COM_ITPTRANSIFEX_PACKAGE_OPTIONS");?></h3>
        <img src="../media/com_itptransifex/images/ajax-loader.gif" width="16" height="16" style="display: none;" id="js-ajaxloader"/>
        
    </div>
    <div class="modal-body">
        
        <div class="row-fluid">
            <div class="span8">
                <div class="control-group">
                    <div class="control-label"><label for="js-name-source"><?php echo JText::_("COM_ITPTRANSIFEX_NAME");?></label></div>
        			<div class="controls">
        			    <input type="text" name="name" value="" id="js-name-source" class="span10">  
        			</div>
                </div>
                
                <div class="control-group">
                    <div class="control-label"><label for="js-filename-source"><?php echo JText::_("COM_ITPTRANSIFEX_FILENAME");?></label></div>
        			<div class="controls">
        			    <input type="text" name="filename" value="" id="js-filename-source" class="span10">  
        			</div>
                </div>
                
                <div class="control-group">
                    <div class="control-label"><label for="js-version-source"><?php echo JText::_("COM_ITPTRANSIFEX_VERSION");?></label></div>
        			<div class="controls">
        			    <input type="text" name="version" value="" id="js-version-source" class="span10">  
        			</div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label for="js-desc-source"><?php echo JText::_("COM_ITPTRANSIFEX_DESCRIPTION");?></label></div>
        			<div class="controls">
        			    <textarea name="description" id="js-desc-source" class="span10"></textarea>  
        			</div>
                </div>
                        
                <div class="control-group">
        			<div class="controls">
        			     <label for="js-store-data-source" class="checkbox">
        			         <input name="store_data" type="checkbox" value="1" id="js-store-data-source" />  
        			         <?php echo JText::_("COM_ITPTRANSIFEX_STORE_DATA");?>
        			     </label>
        			</div>
                </div>
            </div>
        
            <div class="span4">
                <h4><?php echo JText::_("COM_ITPTRANSIFEX_LANGUAGE");?></h4>
                <?php foreach($this->languages as $lagnauge) {?>
                <label class="radio">
                    <input type="radio" name="optionsRadios" value="<?php echo $this->escape($lagnauge->code);?>" class="js-languages">
                    <?php echo $this->escape($lagnauge->name);?>
                </label>
                <?php }?>
                
                <button class="btn" id="js-btn-loaddata">
                    <i class="icon-refresh"></i>
                    <?php echo JText::_("COM_ITPTRANSIFEX_LOAD_DATA")?>
                </button>
                <img src="../media/com_itptransifex/images/ajax-loader.gif" width="16" height="16" style="display: none;" id="js-ajaxloader-load-data"/>
            </div>
        </div>
        
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-primary" id="js-btn-sp"><?php echo JText::_("COM_ITPTRANSIFEX_SUBMIT");?></a>
        <a href="#" class="btn" id="js-btn-cp-cancel"><?php echo JText::_("COM_ITPTRANSIFEX_CANCEL");?></a>
    </div>
</div>