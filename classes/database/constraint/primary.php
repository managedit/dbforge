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
	
	// The keys that make up the primary key
	protected $_keys;
	
	// The name of the constrint
	protected $_name;
	
	public function __construct( array $keys, $name = NULL)
	{
		// If the name isnt given, dont set it, we'll have to make it up later
		if($name !== NULL)
		{
			$this->_name = $name;
		}
		
		// Set the keys
		$this->_keys = $keys;
	}
	
	public function compile( Database $db)
	{
		// If we dont have a name, generate one.
		if( ! isset($this->_name))
		{
			$this->_name = 'pk_'.implode('_', $this->_keys);	
		}
		
		// Finally return the beautiful array.
		return array(
			'name'		=> $this->_name,
			'params'	=> array(
				'primary key' => array_map(array($db, 'quote_identifier'), $this->_keys)
			)
		);
	}
}