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
	public static function compile_column( Database_Table_Column $column)
	{
		
	}

} // End Database_Query_Builder
