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
	
	// The type of the constraint.
	protected $_type = 'index';
	
	// The name of the column thats being indexed
	protected $_key;

	/**
	 * Initiate a UNIQUE constraint.
	 *
	 * @param	string	The name of the column thats unique.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Unique	The constraint object.
	 */
	public function __construct($key, $name = NULL)
	{
		// If the name is not given, dont set it
		if($name !== NULL)
		{
			$this->name = $name;
		}
		
		// Set the key/column value
		$this->_key = $key;
	}
	
	public function compile( Database $db)
	{
		// If the asshole hasnt given us a name, we'll generate one
		if( ! isset($this->name))
		{
			$this->name = 'key_'.$this->_key;	
		}
		
		// We assume that the constraint is created when it is compiled.
		$this->_loaded = TRUE;
		
		// Return the DBForge constraint array.
		return array(
			'name'		=> $this->name,
			'params'	=> array(
				'unique' => $db->quote_identifier($this->_key)
			)
		);
	}
}