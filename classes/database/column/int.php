<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table int column.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Column_Int extends Database_Column {
	
	// The maximum value of the number
	public $maximum_value;
	
	// The minimum value of the number
	public $minimum_value;
	
	// The number's precision
	public $precision;
	
	// The number's scale
	public $scale;

	// Is the field an auto_increment
	public $is_auto_increment;
	
	protected function _load_schema($information_schema)
	{
		// Integers can be auto_increment
		$this->is_auto_increment = strpos(arr::get($information_schema, 'extra'), 'auto_increment') !== false;
		
		// Set the numeric precision 
		$this->precision = arr::get($information_schema, 'numeric_precision');
		
		// Set the numeric scale
		$this->scale = arr::get($information_schema, 'numeric_scale');
		
		// Set the maximum and minimum values
		$this->maximum_value = arr::get($information_schema, 'max');
		$this->minimum_value = arr::get($information_schema, 'min');
	}
	
	protected function _compile_constraints()
	{
		// Let the parent do their bit first
		parent::_compile_constraints();
		
		// If the field is set to auto_increment, then set it.
		if($this->is_auto_increment)
		{
			$constraints[] = 'auto_increment';
		}
	}
	
	protected function _compile_parameters()
	{
		// FLOAT(SCALE, PRECISION)
		return array(
			$this->scale,
		);
	}
}