<?php
/**
 * @package      Transifex\Filter
 * @subpackage   Filters
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
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
     * Load and return projects as options.
     *
     * <code>
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     *
     * $options = $filters->getProjects();
     * </code>
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getProjects()
    {
        if (!array_key_exists('projects', $this->options)) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.id AS value, a.name AS text')
                ->from($this->db->quoteName('#__itptfx_projects', 'a'))
                ->group('a.name');

            $this->db->setQuery($query);
            $results = $this->db->loadAssocList();

            if (!$results) {
                $results = array();
            }

            $this->options['projects'] = $results;
        } else {
            $results = $this->options['projects'];
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
     * @throws \RuntimeException
     * @return array
     */
    public function getLanguages($value = 'id')
    {
        if (!array_key_exists('languages', $this->options)) {
            $query = $this->db->getQuery(true);

            switch ($value) {
                case 'locale':
                    $query->select('a.locale AS value, a.name AS text');
                    break;

                case 'code':
                    $query->select('a.code AS value, a.name AS text');
                    break;

                default:
                    $query->select('a.id AS value, a.name AS text');
                    break;
            }

            $query
                ->from($this->db->quoteName('#__itptfx_languages', 'a'))
                ->order('a.name ASC');

            $this->db->setQuery($query);

            $results = $this->db->loadAssocList();

            $this->options['languages'] = $results;
        } else {
            $results = $this->options['languages'];
        }

        return $results;
    }

    /**
     * Return the languages and the number of packages for those languages.
     *
     * <code>
     * $projectId  = 1;
     *
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     * $options = $filters->getPackageLanguages($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getPackageLanguages($projectId)
    {
        if (!array_key_exists('package_languages', $this->options)) {
            $query = $this->db->getQuery(true);

            $query
                ->select('a.locale AS value, a.name AS text ')
                ->from($this->db->quoteName('#__itptfx_languages', 'a'))
                ->order('a.name ASC');

            $this->db->setQuery($query);
            $results = $this->db->loadAssocList();

            $query = $this->db->getQuery(true);
            $query
                ->select('a.language, COUNT(a.id) as number')
                ->from($this->db->quoteName('#__itptfx_packages', 'a'))
                ->where('a.project_id = ' . (int)$projectId)
                ->group('a.language');

            $this->db->setQuery($query);
            $resultsNumber = $this->db->loadAssocList('language', 'number');

            foreach ($results as &$result) {
                if (array_key_exists($result['value'], $resultsNumber)) {
                    $result['text'] .= ' ['.$resultsNumber[$result['value']].']';
                } else {
                    $result['text'] .= ' [0]';
                }
            }

            unset($result);

            $this->options['package_languages'] = $results;
        } else {
            $results = $this->options['package_languages'];
        }

        return $results;
    }

    /**
     * Load and return languages as options.
     *
     * <code>
     * $projectId  = 1;
     *
     * $filters = new Transifex\Filter\Filters(\JFactory::getDbo());
     * $options = $filters->getCategories($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getCategories($projectId = 0)
    {
        if (!array_key_exists('categories', $this->options)) {
            $categories = array();
            $query = $this->db->getQuery(true);

            $query
                ->select('DISTINCT a.category')
                ->from($this->db->quoteName('#__itptfx_resources', 'a'))
                ->order('a.category ASC');

            if ($projectId > 0) {
                $query->where('a.project_id = ' .(int)$projectId);
            }

            $query->where('a.category IS NOT NULL');

            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList();
            foreach ($results as $result) {
                $categories[] = array('text' => $result['category'], 'value' => $result['category']);
            }

            $this->options['categories'] = $categories;
        }

        return $this->options['categories'];
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
     * @throws \InvalidArgumentException
     * @return array
     */
    public function getResourceTypes()
    {
        return array(
            \JHtml::_('select.option', 'component', \JText::_('COM_ITPTRANSIFEX_COMPONENT')),
            \JHtml::_('select.option', 'module', \JText::_('COM_ITPTRANSIFEX_MODULE')),
            \JHtml::_('select.option', 'plugin', \JText::_('COM_ITPTRANSIFEX_PLUGIN')),
            \JHtml::_('select.option', 'library', \JText::_('COM_ITPTRANSIFEX_LIBRARY'))
        );
    }
}
