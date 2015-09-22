<?php
/**
 * @package      Transifex\Filter
 * @subpackage   Filters
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Transifex\Filter;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manages filters.
 *
 * @package      Transifex\Filter
 * @subpackage   Filters
 */
class Filters
{
    protected $options = array();

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    protected static $instance;

    /**
     * Initialize the object.
     *
     * <code>
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Create and initialize the object.
     *
     * <code>
     * $filters = Transifex\Filter\Filters::getInstance(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     *                             
     * @return Filters
     */
    public static function getInstance(\JDatabaseDriver $db)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Filters($db);
        }

        return self::$instance;
    }

    /**
     * Load and return projects as options.
     *
     * <code>
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     *
     * $options = $filters->getProjects();
     * </code>
     *
     * @return array
     */
    public function getProjects()
    {
        if (!isset($this->options["projects"])) {

            $query = $this->db->getQuery(true);

            $query
                ->select("a.id AS value, a.name AS text")
                ->from($this->db->quoteName("#__itptfx_projects", "a"))
                ->group("a.name");

            $this->db->setQuery($query);
            $results = $this->db->loadAssocList();

            if (!$results) {
                $results = array();
            }

            $this->options["projects"] = $results;

        } else {
            $results = $this->options["projects"];
        }

        return $results;
    }

    /**
     * Load and return languages as options.
     *
     * <code>
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     *
     * // Could be "id", "code", "short_code".
     * $column  = "id";
     *
     * $options = $filters->getProjects($column);
     * </code>
     *
     * @param string $value Column name used for value.
     *
     * @return array
     */
    public function getLanguages($value = "id")
    {
        if (!isset($this->options["languages"])) {

            $query = $this->db->getQuery(true);

            switch ($value) {

                case "code":
                    $query->select("a.code AS value, a.name AS text");
                    break;

                case "short_code":
                    $query->select("a.short_code AS value, a.name AS text");
                    break;

                default:
                    $query->select("a.id AS value, a.name AS text");
                    break;
            }

            $query
                ->from($this->db->quoteName('#__itptfx_languages', 'a'))
                ->order("a.name ASC");

            $this->db->setQuery($query);

            $results = $this->db->loadAssocList();

            $this->options["languages"] = $results;

        } else {
            $results = $this->options["languages"];
        }

        return $results;
    }

    /**
     * Return resource types as options.
     *
     * <code>
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     *
     * $options = $filters->getResourceTypes();
     * </code>
     *
     * @return array
     */
    public function getResourceTypes()
    {
        return array(
            \JHtml::_("select.option", "component", \JText::_("COM_ITPTRANSIFEX_COMPONENT")),
            \JHtml::_("select.option", "module", \JText::_("COM_ITPTRANSIFEX_MODULE")),
            \JHtml::_("select.option", "plugin", \JText::_("COM_ITPTRANSIFEX_PLUGIN")),
            \JHtml::_("select.option", "library", \JText::_("COM_ITPTRANSIFEX_LIBRARY"))
        );
    }
}
