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
 * @category   I18n
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
	 * string will be returned.
	 *
	 *     $hello = I18n::get('Hello friends, my name is :name');
	 *
	 * @param   string   text to translate
	 * @param   string   target language
	 * @return  string
	 */
	public static function get($string, $lang = NULL)
	{
		if ( ! $lang)
		{
			// Use the global target language
			$lang = I18n::$lang;
		}

		// Load the translation table for this language
		$table = I18n::load($lang);

		// Return the translated string if it exists
		return isset($table[$string]) ? $table[$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 * @param   string  i18n lang
	 * @return  object  Kohana_I18n_Reader
	 */
	public function load($lang)
	{
		foreach ($this->_readers as $reader)
		{
			if ($i18n = $reader->load($lang))
			{
				// Found a reader for this i18n lang
				return $i18n;
			}
		}

		// Reset the iterator
		reset($this->_readers);

		if ( ! is_object($i18n = self::$_cache[$lang] = current($this->_readers)))
		{
			throw new Kohana_Exception('No i18n readers attached');
		}

		// Load the reader as an empty array
		return $i18n->load($lang, array());
	}
} // End Kohana_I18n
