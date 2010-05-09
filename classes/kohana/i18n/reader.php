<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract i18n reader. All i18n readers must extend this class.
 *
 * @package    Kohana
 * @category   I18n
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
abstract class Kohana_I18n_Reader extends ArrayObject {

	// I18n lang
	public $_lang;

	/**
	 * Return the current i18n table in serialized form.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return serialize($this->getArrayCopy());
	}

	/**
	 * Loads a i18n table
	 *
	 * @param   string  i18n lang
	 * @param   array   i18n table
	 * @return  $this   clone of the current object
	 */
	public function load($lang, array $messages = NULL)
	{
		if ($messages === NULL)
		{
			return FALSE;
		}

		// Set the lang
		$this->_lang = $lang;

		// Clone the current object
		$object = clone $this;

		// Swap the array with tha actual messages
		$object->exchangeArray($messages);

		// Empty the lang
		$this->_lang = NULL;

		return $object;
	}

	/**
	 * Return the raw array that is being used forthis object.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Get a message from the i18n table or return the default value.
	 *
	 * @param   string   array key
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function get($key)
	{
		return $this->offsetExists($key) ? $this->offsetGet($key) : $key;
	}

} // End Kohana_I18n_Reader
