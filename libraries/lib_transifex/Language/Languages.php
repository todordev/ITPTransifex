<?php
/**
 * @package      Transifex\Language
 * @subpackage   Languages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Language;

use Prism\Database\ArrayObject;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage languages.
 *
 * @package      Transifex\Language
 * @subpackage   Languages
 */
class Languages extends ArrayObject
{
    /**
     * Load the languages.
     *
     * <code>
     * $options = array(
     *     "ids" => array(1,2,3,4)
     * );
     *
     * $languages = new Transifex\Language\Languages(\JFactory::getDbo());
     *
     * $languages->load($options);
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $ids = ArrayHelper::getValue($options, "ids", array(), "array");
        $ids = ArrayHelper::toInteger($ids);

        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.code, a.short_code')
            ->from($this->db->quoteName('#__itptfx_languages', 'a'))
            ->order("a.name ASC");

        if (!empty($ids)) {
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }
}
