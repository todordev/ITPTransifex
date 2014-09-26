<?php
/**
 * @package      ITPTransifex
 * @subpackage   Languages
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a language.
 *
 * @package      ITPTransifex
 * @subpackage   Languages
 */
class ItpTransifexLanguage
{
    protected $id;
    protected $name;
    protected $code;
    protected $short_code;

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Load language data by ID.
     *
     * <code>
     * $id = 1;
     *
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
     * $language->load($id);
     * </code>
     *
     * @param int $id
     */
    public function load($id)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.code, a.short_code")
            ->from($this->db->quoteName("#__itptfx_languages", "a"))
            ->where("a.id = " . (int)$id);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {
            $this->bind($result);
        }
    }

    /**
     * Load language data by ID.
     *
     * <code>
     * $code = "en_GB";
     *
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
     * $language->loadByCode($code);
     * </code>
     *
     * @param string $code
     */
    public function loadByCode($code)
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.code, a.short_code")
            ->from($this->db->quoteName("#__itptfx_languages", "a"))
            ->where("a.code = " . $this->db->quote($code));

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!empty($result)) {
            $this->bind($result);
        }
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = array(
     *    "name" => "English ( United Kingdom )",
     *    "code" => "en_GB",
     * );
     *
     * $language    = new ItpTransifexLanguage(JFactory::getDbo());
     * $language->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     */
    public function bind($data, $ignored = array())
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $ignored)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Return an ID of language.
     *
     * <code>
     * $id = 1;
     *
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
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
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
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
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
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
     * $language = new ItpTransifexLanguage(JFactory::getDbo());
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
