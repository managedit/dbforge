<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Database_Query_Builder extends Kohana_Database_Query_Builder {
	
	/**
	 * Compiles a column object into SQL syntax.
	 *
	 * @param   object   The column object.
	 * @return  strin	The SQL.
	 */
	public static function compile_column( Database_Column $column)
	{
		// Get the table's database
		$db = $column->table->database;
		
		// Return each compilation seperated by a space
		return implode(' ', array(
			$db->quote_identifier($column->name),
			$column->compile_datatype(),
			$column->compile_constraints()
		));
	}

} // End Database_Query_Builder
