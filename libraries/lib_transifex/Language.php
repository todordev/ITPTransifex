<?php
/**
 * @package      ITPTransifex
 * @subpackage   Languages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex;

use Prism\Database\TableImmutable;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a language.
 *
 * @package      ITPTransifex
 * @subpackage   Languages
 */
class Language extends TableImmutable
{
    protected $id;
    protected $name;
    protected $code;
    protected $short_code;

    /**
     * Load language data by ID.
     *
     * <code>
     * $keys = array(
     *    "id" => 1,
     *    "code" => "en_GB"
     * );
     *
     * $language = new Transifex\Language(\JFactory::getDbo());
     *
     * $language->load($keys);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.code, a.short_code")
            ->from($this->db->quoteName("#__itptfx_languages", "a"));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) . " = " . $this->db->quote($value));
            }
        } else {
            $query->where("a.id = " . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {
            $this->bind($result);
        }
    }

    /**
     * Return an ID of language.
     *
     * <code>
     * $id = 1;
     *
     * $language = new Transifex\Language(\JFactory::getDbo());
     * $language->load($id);
     *
     * if (!$this->getId) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return a name of language.
     *
     * <code>
     * $id = 1;
     *
     * $language = new Transifex\Language(\JFactory::getDbo());
     * $language->load($id);
     *
     * $name = $this->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return a language code.
     *
     * <code>
     * $id = 1;
     *
     * $language = new Transifex\Language(\JFactory::getDbo());
     * $language->load($id);
     *
     * $code = $this->getCode();
     * </code>
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return a short language code.
     *
     * <code>
     * $id = 1;
     *
     * $language = new Transifex\Language(\JFactory::getDbo());
     * $language->load($id);
     *
     * $shortCode = $this->getShortCode();
     * </code>
     *
     * @return string
     */
    public function getShortCode()
    {
        return $this->short_code;
    }
}
