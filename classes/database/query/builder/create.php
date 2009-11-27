<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for CREATE statements.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Query_Builder_Create extends Database_Query_Builder {
	
	// The table object we're working with.
	protected $_table;
	
	public function __construct( Database_Table $table)
	{
		// Check if the table is already loaded
		if($table->loaded())
		{
			// You cannot create a table that already exist in the database.
			throw new Database_Exception('Cannot create loaded table :tbl. Try using the ALTER query instead.', array(
				$table->name
			));
		}
		
		// Set the table object
		$this->_table = $table;
		
		// Because mummy says so
		parent::__construct(Database::CREATE, '');
	}
	
	public function compile(Database $db)
	{
		// Start with the basic syntax.
		$sql = 'CREATE TABLE '.$db->quote_table($this->_table->name);
		
		// You are allowed to create a table without any columns. Dont ask me why.
		if(count($this->_table->columns()) > 0)
		{
			// Get ready for the column data.
			$sql .= ' (';
			
			$columns = array();
			
			// Fetch all the columns and loop through them individually.
			foreach($this->_table->columns(true) as $column)
			{
				// Compile the column and add it to the array.
				$columns[] = $column->compile();
			}
			
			// Seperate the columns with commars, and add the table constraints at the end.
			$sql .= implode($columns, ',').','.$this->_table->compile_constraints().')';
		}
		
		// Finally return the sql.
		return $sql.';';
	}
	
	public function reset()
	{
		// Reset the table object.
		$this->_table = NULL;
	}
	
} //END Database_Query_Builder_Create