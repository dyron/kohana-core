<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File-based i18n reader
 *
 * @package   I18n
 * @author    David Pommer
 */
class Kohana_I18n_File extends Kohana_I18n_Reader {

	protected $_directory;

	public function __construct($directory = 'i18n')
	{
		// Set the i18n directory
		$this->_directory = trim($directory, '/');

		// Load the empty array
		parent::__construct();
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 * @param string language to load
	 * @return array
	 */
	public function load($lang, array $messages = NULL)
	{
		if ($messages === NULL)
		{
			$messages = array();
		}

		// Split the language: language, region, locale, etc
		$parts = explode('-', $lang);

		do
		{
			// Create a path for this set of parts
			$path = implode(DIRECTORY_SEPARATOR, $parts);

			if ($files = Kohana::find_file($this->_directory, $path))
			{
				$table = array();
				foreach($files as $file)
				{
					// Merge the language strings into the sub table
					$table = array_merge($table, Kohana::load($file));
				}

				// Append the sub table, preventing less specific language
				// files from overloading more specific files
				$messages += $table;
			}

			// Remove the last part
			array_pop($parts);
		}
		while ($parts);

		return parent::load($lang, $messages);
	}
} // End Kohana_I18n_File
