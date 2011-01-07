<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_I18n_Exception extends Kohana_Exception {

	/**
	 * @param   string   error message
	 * @param   array    translation variables
	 * @param   integer  the exception code
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		try
		{
			parent::__construct($message, $values, $code);
		}
		catch(Exception $e)
		{
			if ( ! empty($variables))
			{
				$message = strtr($messages, $varibales);
			}

			Exception::__construct($message, $code);
		}
	}
}
