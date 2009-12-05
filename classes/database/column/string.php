<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table string column.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Column_String extends Database_Column {

	// The maximum number of characters
	public $maximum_length;
	
	// Is exact
	public $is_exact;
	
	// Is the column a binary datatype or not
	public $is_binary;
	
	protected function _load_schema($information_schema)
	{
		// Set whether the string column is exact or not
		$this->is_exact = arr::get($information_schema, 'exact', FALSE);
		
		// Set string specific properties
		$this->maximum_length = arr::get($information_schema, 'character_maximum_length');
		
		// Set whether the column is a binary type or not
		$this->is_binary = arr::get($information_schema, 'binary', FALSE);
	}
	
	protected function _compile_constraints()
	{
		// Let the parent do their stuff first
		parent::_compile_constraints();
		
		// If the string is a binary type
		if($this->is_binary)
		{
			// Add the binary keyword as a constraint
			$constraints[] = 'binary';
		}
	}
}