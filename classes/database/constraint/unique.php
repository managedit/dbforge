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
	
	// The name of the column thats going to be unique
	protected $_key;
	
	// The name of the unique key
	protected $_name;
	
	public function __construct($key, $name = NULL)
	{
		// If the name is not given, dont set it
		if($name !== NULL)
		{
			$this->_name = $name;
		}
		
		// Set the key/column value
		$this->_key = $key;
	}
	
	public function compile( Database $db)
	{
		// If the asshole hasnt given us a name, we'll generate one
		if( ! isset($this->_name))
		{
			$this->_name = 'key_'.$this->_key;	
		}
		
		// Return the DBForge constraint array.
		return array(
			'name'		=> $this->_name,
			'params'	=> array(
				'unique' => $db->quote_identifier($this->_key)
			)
		);
	}
}