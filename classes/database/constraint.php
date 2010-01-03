<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database constraint class.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
abstract class Database_Constraint {
	
	/**
	 * Initiate a PRIMARY KEY constraint.
	 *
	 * @param	array	The list of columns to make up the primary key.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Primary	The constraint object.
	 */
	public static function primary_key(array $keys, $name = NULL)
	{
		return new Database_Constraint_Primary($keys, $name);
	}
	
	/**
	 * Initiate a FOREIGN KEY constraint.
	 *
	 * @param	string	The name of the column that represents the foreign key.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Foreign	The constraint object.
	 */
	public static function forgeign_key($identifier, $name = NULL)
	{
		return new Database_Constraint_Foreign($identifier, $name);
	}
	
	/**
	 * Initiate a UNIQUE constraint.
	 *
	 * @param	string	The name of the column thats unique.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Unique	The constraint object.
	 */
	public static function unique($key, $name = NULL)
	{
		return new Database_Constraint_Unique($key, $name);
	}
	
	/**
	 * Returns a new check constraint object.
	 * 
	 * @param	string	The name of the column to perform the statement on.
	 * @param	string	The opertor to compare the value with.
	 * @param	mixed	The value to compare the column with via the operator.
	 * @return	Database_Constraint_Check
	 */
	public static function check($column, $operator, $value)
	{
		return new Database_Constraint_Check($column, $operator, $value);
	}
	
	/**
	 * The constraint name.
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * The parent database object.
	 * 
	 * @var Database
	 */
	protected $_db;
	
	/**
	 * Compiles the constraint into an SQL statement.
	 * 
	 * @return string
	 */
	abstract public function compile(Database $db);
	
} // End Database_Constraint