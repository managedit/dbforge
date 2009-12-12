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
	
	// The type of the constraint.
	protected $_type = 'primary key';
	
	// The keys that make up the primary key
	protected $_keys;
	
	public function __construct( array $keys, $name = NULL)
	{
		// If the name isnt given, dont set it, we'll have to make it up later
		if($name !== NULL)
		{
			$this->name = $name;
		}
		
		// Set the keys
		$this->_keys = $keys;
	}
	
	public function compile( Database $db)
	{
		// If we dont have a name, generate one.
		if( ! isset($this->name))
		{
			$this->name = 'pk_'.implode('_', $this->_keys);	
		}
		
		// We assume that the constraint is created when it is compiled.
		$this->_loaded = TRUE;
		
		// Finally return the beautiful array.
		return array(
			'name'		=> $this->name,
			'params'	=> array(
				'primary key' => array_map(array($db, 'quote_identifier'), $this->_keys)
			)
		);
	}
}