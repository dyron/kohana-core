<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Internationalization (i18n) class. Provides language loading and translation
 * methods without dependencies on [gettext](http://php.net/gettext).
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
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_I18n {

	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public $lang = 'en-us';

	/**
	 * @var  string  source language: en-us, es-es, zh-cn, etc
	 */
	public $source = 'en-us';

	/**
	 * @var  string  target time zone: America/Chicago, Europe/London, etc
	 */
	public $time_zone = 'America/Chicago';

	/**
	 * @var  array  cache of loaded languages
	 */
	protected $_cache = array();

	/**
	 * @var  Kohana_I18n  Singleton static instance
	 */
	protected static $_instance;

	/**
	 * Get the singleton instance of Kohana_I18n
	 *
	 * @return  Kohana_I18n
	 */
	public static function instance($lang = NULL, $time_zone = NULL)
	{
		if (self::$_instance === NULL)
		{
			// Create a new instance
			self::$_instance = new self;

			self::$_instance->defaults($lang, $time_zone);
		}

		return self::$_instance;
	}

	/**
	 * @var  array  I18n readers
	 */
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
	public function lang($lang = NULL)
	{
		$lang = ($lang === NULL) ? $this->lang : $lang;

		/**
		 * Set the default locale.
		 *
		 * @see  http://kohanaframework.org/guide/using.configuration
		 * @see  http://php.net/setlocale
		 */
		setlocale(LC_ALL, $lang.'.'.Kohana::$charset);

		// Normalize the language
		$this->lang = strtolower(str_replace(array(' ', '_'), '-', $lang));

		return $this->lang;
	}

	/**
	 * Get and set the target time zone.
	 *
	 * @param    string   new time zone setting
	 * @return   string
	 */
	public function time_zone($time_zone = NULL)
	{
		$time_zone = ($time_zone === NULL) ? $this->time_zone : $time_zone;

		/**
		 * Set the default time zone.
		 *
		 * @see  http://kohanaframework.org/guide/using.configuration
		 * @see  http://php.net/timezones
		 */
		date_default_timezone_set($time_zone);

		$this->time_zone = $time_zone;

		return $this->time_zone;
	}

	/**
	 * Get and set the defaults.
	 *
	 * @param    string   new language setting
	 * @return   string
	 */
	public function defaults($lang = NULL, $time_zone = NULL)
	{
		$this->lang($lang);

		$this->time_zone($time_zone);

		return $this;
	}

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned.
	 *
	 *     $hello = Kohana::$i18n->get('Hello friends, my name is :name');
	 *
	 * @param   string   text to translate
	 * @param   string   target language
	 * @return  string
	 */
	public function get($string, $lang = NULL)
	{
		if ( ! $lang)
		{
			// Use the global target language
			$lang = $this->lang;
		}

		// Load the translation table for this language
		$table = $this->load($lang);

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

		if ( ! is_object($i18n = current($this->_readers)))
		{
			throw new Kohana_I18n_Exception('No i18n readers attached');
		}

		// Load the reader as an empty array
		return $i18n->load($lang, array());
	}
} // End Kohana_I18n
