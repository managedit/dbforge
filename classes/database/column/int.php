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
	
	/*
	 * Not editable
	 */
	
	// The maximum value of the number
	public $maximum_value;
	
	// The minimum value of the number
	public $minimum_value;
	
	// Whether the number is exact
	public $is_exact;
	
	// The number's precision
	public $precision;
	
	// The number's scale
	public $scale;
	
	/*
	 * Editable
	 */
	
	// Is the field an auto_increment
	public $is_auto_increment;
	
	public function __construct( & $table, $datatype)
	{
		// Get the properties out of the datatype
		$this->is_exact = arr::get($datatype, 'exact', FALSE);
		$this->maximum_value = arr::get($datatype, 'max', NULL);
		$this->minimum_value = arr::get($datatype, 'min', NULL);
		
		parent::__construct($table, $datatype);
	}
	
	public function load_schema( & $table, $schema)
	{
		// Integers can be auto_increment
		$this->is_auto_increment = strpos($schema['EXTRA'], 'auto_increment') !== false;
		
		// Set the numeric precision 
		$this->precision = $schema['NUMERIC_PRECISION'];
		
		// Set the numeric scale
		$this->scale = $schema['NUMERIC_SCALE'];
		
		// Let the parent do the rest.
		parent::load_schema($table, $schema);
	}
	
	public function compile_constraints()
	{
		$sql = '';
		
		// If the field is set to auto_increment, then set it.
		if($this->is_auto_increment)
		{
			$sql .= 'AUTO_INCREMENT ';
		}
		
		// Add the rest of the constraints after.
		$sql .= parent::compile_constraints();
		
		// Return the SQL
		return $sql;
	}
}