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
class Database_Table_Column_String extends Database_Column {
	
	/*
	 * Not editable
	 */
	
	// The character set
	public $character_set;
	
	// The collation name
	public $collation_name;
	
	// The maximum length number of assigned bytes
	public $maximum_length;
	
	// Octet length
	public $octet_length;
	
	// Is exact
	public $is_exact;
	
	public function __construct( & $table, $datatype)
	{
		// Set if its exact or not.
		$this->is_exact = arr::get($datatype, 'exact', FALSE);
		
		parent::__construct($table, $datatype);
	}
	
	public function load_schema( & $table, $schema)
	{
		// Set string specific properties
		$this->character_set = $schema['CHARACTER_SET_NAME'];
		$this->collation_name = $schema['COLLATION_NAME'];
		$this->maximum_length = $schema['CHARACTER_MAXIMUM_LENGTH'];
		$this->octet_length = $schema['CHARACTER_OCTET_LENGTH'];
		
		// Let the parent method do the rest
		parent::load_schema($table, $schema);
	}
}