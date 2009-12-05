<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database object creation helper methods.
 *
 * @package    Database
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class DB extends Kohana_DB {
	
	/**
	 * Create a new alter table query.
	 *
	 * @param   string   The name of the table to alter.
	 * @return  Database_Query_Alter
	 */
	public static function alter($table)
	{
		return new Database_Query_Builder_Alter($table);
	}
	
	/**
	 * Create a new create table query.
	 *
	 * @param   array   The table array to create.
	 * @return  Database_Query_Create
	 */
	public static function create( array $table)
	{
		return new Database_Query_Builder_Create($table);
	}
	
	/**
	 * Create a new drop query.
	 *
	 * @param   string	 The type of object to drop; 'database', 'table', 'column' or 'constraint.
	 * @param   string   The name of the object to drop.
	 * @return  Database_Query_Drop
	 */
	public static function drop($type, $object)
	{
		return new Database_Query_Builder_Drop($type, $object);
	}
	
	/**
	 * Creates a new table truncate query.
	 *
	 * @param   string   The table name to truncate.
	 * @return  Database_Query_Truncate
	 */
	public static function truncate($table)
	{
		return new Database_Query_Builder_Truncate($table);
	}

} // End DB