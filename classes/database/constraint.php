<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table constraint.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
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
	 * Initiate a CHECK constraint.
	 *
	 * @param	string	The name of the column that's being checked.
	 * @param	string	The operator used in the conditional statement.
	 * @param	string	The value to compare it with.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Check	The constraint object.
	 */
	public static function check($identifier, $operator, $value, $name = NULL)
	{
		return new Database_Constraint_Check($identifier, $operator, $value);
	}
	
	/**
	 * Compiles the constraint into a DBForge constraint array.
	 *
	 * @param	Database	The database to compile the constraint with.
	 * @return	array	The constraint array.
	 */
	abstract public function compile( Database $db);
	
}