<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table datetime column.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Column_Datetime extends Database_Column {
	
	// The format of the datetime
	public $format;
	
	// Set the datetime format
	protected function _load_schema($information_schema)
	{
		$this->format = arr::get($information_schema, 'format');
	}
	
	protected function _compile_constraints()
	{
		// Let the parent process the constraints first.
		parent::_compile_constraints();
		
		// Defaults given for datetimes 
		if(isset($this->default))
		{
			// If the default is a valid date format, then quote it, otherwise leave it as is
			$constraints['default'] = strtotime($this->default) ?
				$this->table->database->quote($this->default) :
				$this->default;
		}
		
		return $constraints;
	}
}