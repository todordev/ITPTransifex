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
class JFormFieldTransifexProject extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'transifexproject';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions()
    {
        $projects = new Transifex\Project\Projects(JFactory::getDbo());
        $projects->load();

        $options = $projects->toOptions("id", "name");

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
