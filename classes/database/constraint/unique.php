<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table UNIQUE constraint.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Constraint_Unique extends Database_Constraint {
	
	/**
	 * The list of keys that constitutes the unique index.
	 * 
	 * @var array
	 */
	protected $_keys;
	
	/**
	 * Initiate a UNIQUE constraint.
	 *
	 * @param	array	The list of keys that constitude the unique constraint.
	 * @return	Database_Constraint_Unique	The constraint object.
	 */
	public function __construct(array $keys)
	{
		$this->name = uniqid('key_');
		
		$this->_keys = $keys;
	}
	
	public function compile(Database $db)
	{
		return 'CONSTRAINT '.$db->quote_identifier($this->name).' UNIQUE ('.
			implode(',', array_map(array($db, 'quote_identifier'))).')';
	}
	
} // End Database_Constraint_Unique