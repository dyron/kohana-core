<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Internationalization (i18n) class. Provides language loading and translation
 * methods without dependancies on [gettext](http://php.net/gettext).
 *
 * Typically this class would never be used directly, but used via the __()
 * function, which loads the message and replaces parameters:
 *
 *     // Display a translated message
 *     echo __('Hello, world');
 *
 *     // With parameter replacement
 *     echo __('Hello, :user', array(':user' => $username));
 *
 * [!!] The __() function is declared in `SYSPATH/base.php`.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_I18n {

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $lang = 'en-us';

	// Cache of loaded languages
	protected static $_cache = array();

	// Singleton static instance
	protected static $_instance;

	/**
	 * Get the singleton instance of Kohana_I18n
	 *
	 *     // Get the current language
	 *     $lang = I18n::lang();
	 * @return  Kohana_I18n
	 */
	public static function instance($lang = NULL)
	{
		if (self::$_instance === NULL)
		{
			// Create a new instance
			self::$_instance = new self;

			if ($lang !== NULL)
			{
				self::lang($lang);
			}
		}

		return self::$_instance;
	}

	// I18n readers
	protected $_readers = array();

	/**
	 * Attach a i18n reader.
	 *
	 * @param   object   Kohana_I18n_Reader instance
	 * @param   boolean  add the reader as the first used object
	 * @return  $this
	 */
	public function attach(Kohana_I18n_Reader $reader, $first = TRUE)
	{
		if ($first === TRUE)
		{
			// Place the i18n reader at the top of the stack
			array_unshift($this->_readers, $reader);
		}
		else
		{
			// Place the i18n reader at the bottom of the stack
			$this->_readers[] = $reader;
		}

		return $this;
	}

	/**
	 * Detaches a i18n reader.
	 *
	 * @param   object  Kohana_I18n_Reader instance
	 * @return  $this

	 */
	public function detach(Kohana_I18n_Reader $reader)
	{
		if (($key = array_search($reader, $this->_readers)))
		{
			// Remove the reader
			unset($this->_readers[$key]);
		}

		return $this;
	}

	/**
	 * Get and set the target language.
	 *
	 * @param    string   new language setting
	 * @return   string
	 */
	public static function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			self::$lang = strtolower(str_replace(array(' ', '_'), '-', $lang));
		}

		return self::$lang;
	}

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned. No parameters are replaced.
	 *
	 *     $hello = I18n::get('Hello friends, my name is :name');
	 *
	 * @param   string   text to translate
	 * @return  string
	 */
	public static function get($string)
	{
		if (empty(I18n::$_readers))
		{
			// No reader found, use the string
			return $string;
		}

		if ( ! isset(I18n::$_cache[I18n::$lang]))
		{
			// Load the translation table
			I18n::$_cache[I18n::$lang] = Kohana::$i18n->load(I18n::$lang, $string);
		}

		// Return the translated string if it exists
		return isset(I18n::$_cache[I18n::$lang][$string]) ? I18n::$_cache[I18n::$lang][$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 *     // Get all defined Spanish messages
	 *     $messages = I18n::load('es-es');
	 *
	 * @param   string  i18n lang
	 * @return  object  Kohana_I18n_Reader
	 */
	public function load($lang, $string)
	{
		foreach ($this->_readers as $reader)
		{
			if ($i18n = $reader->load($lang))
			{
				// Found a reader for this lang
				return $i18n;
			}
		}

		// Load the reader as an empty array
		return array();
	}


	final private function __construct()
	{
		// Enforce singleton behavior
	}

	final private function __clone()
	{
		// Enforce singleton behavior
	}

} // End Kohana_I18n
