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
	 * Create a new alter table query.
	 *
	 * @param   object   The table object to alter.
	 * @return  Database_Query_Alter
	 */
	public static function alter( Database_Table $table)
	{
		return new Database_Query_Builder_Alter($table);
	}
	
	/**
	 * Create a new create table query.
	 *
	 * @param   object   The unloaded table object to create.
	 * @return  Database_Query_Create
	 */
	public static function create( Database_Table $table)
	{
		return new Database_Query_Builder_Create($table);
	}
	
	/**
	 * Create a new drop query.
	 *
	 * @param   string	 The object type to drop; 'database', 'table' or 'column'.
	 * @param   object   The object to drop.
	 * @return  Database_Query_Drop
	 */
	public static function drop($type, $object)
	{
		return new Database_Query_Builder_Drop($type, $object);
	}
	
	/**
	 * Creates a new table truncate query.
	 *
	 * @param   object   Table object to truncate.
	 * @return  Database_Query_Truncate
	 */
	public static function truncate( Database_Table $object)
	{
		return new Database_Query_Builder_Truncate($object);
	}

} // End DB