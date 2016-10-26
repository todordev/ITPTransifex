<?php
/**
 * @package      Transifex\Language
 * @subpackage   Languages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Language;

use Joomla\Utilities\ArrayHelper;
use Prism\Database\Collection;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage languages.
 *
 * @package      Transifex\Language
 * @subpackage   Languages
 */
class Languages extends Collection
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
     * @throws \RuntimeException
     */
    public function load(array $options = array())
    {
        $ids = $this->getOptionIds($options);

        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.name, a.locale, a.code')
            ->from($this->db->quoteName('#__itptfx_languages', 'a'))
            ->order('a.name ASC');

        if (count($ids) > 0) {
            $query->where('a.id IN ( ' . implode(',', $ids) . ' )');
        }

        $this->db->setQuery($query);
        $this->items = (array)$this->db->loadAssocList();
    }
}
