<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table CHECK constraint.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Constraint_Check extends Database_Constraint {
	
	// The key name
	protected $_name;
	
	// The checks array
	protected $_checks = array();
	
	public function __construct($identifier, $operator, $value, $name = NULL)
	{
		// Set the name/key of the check
		if($name !== NULL)
		{
			$this->_name = $name;
		}
		
		// Add the initial check to the array
		$this->_checks[] = array(
			$identifier,
			$operator,
			$value
		);
	}
	
	/**
	 * Adds a check statement using the AND keyword.
	 *
	 * @param	string	The column name.
	 * @param	string	The operator used in the conditional statement.
	 * @param	object	The value to compare the column with.
	 * @return	Database_Constraint_Check	The current object.
	 */
	public function check_and($identifier, $operator, $value)
	{
		// Compile the check into an array and save it
		$this->_checks[] = array(
			'AND' => array(
				$identifier,
				$operator,
				$value
			)
		);
		
		// Return the current object for chaining
		return $this;
	}
	
	/**
	 * Adds a check statement using the OR keyword.
	 *
	 * @param	string	The column name.
	 * @param	string	The operator used in the conditional statement.
	 * @param	object	The value to compare the column with.
	 * @return	Database_Constraint_Check	The current object.
	 */
	public function check_or($identifier, $operator, $value)
	{
		// Compile the check into an array and save it
		$this->_checks[] = array(
			'OR' => array(
				$identifier,
				$operator,
				$value
			)
		);
		
		// Return the current object for chaining
		return $this;
	}
	
	/**
	 * Compiles the current object into a string.
	 *
	 * @param	string	The column name.
	 * @param	string	The operator used in the conditional statement.
	 * @param	object	The value to compare the column with.
	 * @return	Database_Constraint_Check	The current object.
	 */
	protected function _compile_check( array $data, Database $db)
	{
		// We have a keyword
		if(is_array(reset($data)))
		{
			// AND or OR
			$keyword = key(reset($data));
			
			// Compile the check params into a single string
			return $keyword.' '.$db->quote_identifier($data[0]).' '.$data[1].' '.$db->quote($data[2]);
		}
		else
		{
			// Compile the check params into a single string
			return $db->quote_identifier($data[0]).' '.$data[1].' '.$db->quote($data[2]);
		}
	}
	
	public function compile( Database $db)
	{
		// Initiate the array to return
		$result = array(
			'name'	=> '',
			'params' => array('check' => 
				array('')
			)
		);
		
		// Prepare a name string
		$name = 'ck';
		
		// Compile each check and add it to the array
		foreach($this->_checks as $check)
		{
			// Ads all the identifiers to the check name
			$name .= '_'.$check[0];
			
			// Compiles the check and adds it to the array
			$result['params']['check'][0] .= $this->_compile_check($check, $db);
		}
		
		// Set the name to either the user set name, or the one we generated.
		$result['name'] = isset($this->_name) ? $this->_name : $name;
		
		// Returns the result.
		return $result;
	}
}