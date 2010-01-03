<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table PRIMARY KEY constraint.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Constraint_Primary extends Database_Constraint {
	
	/**
	 * List of keys that make up the primary key.
	 * 
	 * @var	array
	 */
	protected $_keys;
	
	/**
	 * Initiates a new primary constraint object.
	 * 
	 * @param	array	The list of columns that make up the primary key.
	 * @return	void
	 */
	public function __construct(array $keys)
	{
		$this->name = uniqid('pk_');
		
		$this->_keys = $keys;
	}
	
	public function compile(Database $db)
	{
		return 'CONSTRAINT '.$db->quote_identifier($this->name).
			' PRIMARY KEY ('.implode(',', array_map(array($db, 'quote_identifier'), $this->_keys)).')';
	}
	
} // End Database_Constraint_Primary