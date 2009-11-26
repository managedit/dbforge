<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database object creation helper methods.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_DB {
	
	/**
	 * Create a new database query of the given type.
	 *
	 * @param   string   SQL statement
	 * @return  Database_Query
	 */
	public static function alter( Database_Table $table)
	{
		return new Database_Query_Builder_Alter($table);
	}
	
	/**
	 * Create a new database query of the given type.
	 *
	 * @param   string   SQL statement
	 * @return  Database_Query
	 */
	public static function create( Database_Table $table)
	{
		return new Database_Query_Builder_Create($table);
	}
	
	/**
	 * Create a new database query of the given type.
	 *
	 * @param   integer  type: Database::SELECT, Database::UPDATE, etc
	 * @param   string   SQL statement
	 * @return  Database_Query
	 */
	public static function drop($object)
	{
		return new Database_Query_Builder_Drop($object);
	}
	
	public static function truncate($object)
	{
		return new Database_Query_Builder_Truncate($object);
	}

} // End DB