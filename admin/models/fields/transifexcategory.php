<?php
/**
 * @package      ITPTransifex
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

jimport('Prism.init');
jimport('Transifex.init');

/**
 * Form field class that loads languages as options,
 * using code with 4 letters for ID.
 *
 * @package      ITPTransifex
 * @subpackage   Components
 * @since        1.6
 */
class JFormFieldTransifexCategory extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'transifexcategory';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions()
    {
        $filters    = new \Transifex\Filter\Filters(JFactory::getDbo());
        $categories = $filters->getCategories();

        $defaultCategories = array(
            ['text' => 'component', 'value' => 'component'],
            ['text' => 'module', 'value' => 'module'],
            ['text' => 'plugin', 'value' => 'plugin']
        );

        $categories = array_merge($defaultCategories, $categories);
        $options = array_map('unserialize', array_unique(array_map('serialize', $categories)));

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
