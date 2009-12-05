<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for ALTER statements.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Query_Builder_Alter extends Database_Query_Builder {
	
	// Table name - ALTER TABLE '_table'; ...
	protected $_table;
	
	// New table name - ALTER TABLE '_table' RENAME '_name';
	protected $_name = NULL;
	
	// Columns to add - ALTER TABLE '_table' ADD COLUMNS ( ... )
	protected $_add_columns = array();
	
	// Columns to modify - ALTER TABLE '_table' MODIFY COLUMNS ( ... )
	protected $_modify_columns = array();
	
	// Columns to add - ALTER TABLE '_table' DROP COLUMN ...
	protected $_drop_columns = array();
	
	// Columns to rename.
	protected $_rename_columns = array();
	
	
	/**
	 * Set the table for alteration.
	 *
	 * @param   string The table name
	 * @return  void
	 */
	public function __construct($table)
	{
		// Set the table name.
		$this->_table = $table;
		
		// Because mummy says so.
		parent::__construct(Database::ALTER, '');
	}
	
	/**
	 * Rename the table.
	 *
	 * @param   string The new name
	 * @return  void
	 */
	public function rename($name)
	{
		$this->_name = $name;
		
		$this->execute();
		
		return $this;
	}
	
	/**
	 * Rename the column.
	 *
	 * @param	string	The current column name.
	 * @param	string	The new column name.
	 * @return  void
	 */
	public function rename_column($old_name, $new_name)
	{
		// Add it to the rename column array
		$this->_renam_columns[$new_name] = $column;
	}
	
	/**
	 * Add a column
	 *
	 * @param   array The column data array.
	 * @return  void
	 */
	public function add_column( array $column)
	{
		$this->_add_columns[] = $column;
		
		return $this;
	}
	
	public function add_constraint( array $constraint)
	{
		
	}
	
	/**
	 * Modify a column.
	 *
	 * @param   array	The altered column array. To rename a column, see the rename_column() method.
	 * @return  void
	 */
	public function modify(varray $modified_column)
	{
		$this->_modify_columns[] = $modified_column;
		
		return $this;
	}
	
	/**
	 * Drop a column.
	 *
	 * @param   string The name of the column to drop
	 * @return  void
	 */
	public function drop($column)
	{
		$this->_drop_columns[] = $column;
		
		return $this;
	}
	
	/**
	 * Compile the SQL query and return it.
	 *
	 * @param   object  Database instance
	 * @return  string
	 */
	public function compile( Database $db)
	{
		// Initiate the alter statement
		$sql = 'ALTER TABLE '.$db->quote_table($this->_table).' ';
		
		// If we have a name set, rename the table.
		if ($this->_name !== NULL)
		{
			$sql .= 'RENAME TO '.$db->quote_table($this->_name).'; ';
		}
		
		// If we have any columns to rename do so
		if (count($this->_rename_columns) > 0)
		{
			// Loop through each column and compile the SQL.
			foreach($this->_rename_columns as $old_name => $new_name)
			{
				$sql .= 'RENAME COLUMN '.$db->quote_identifier($old_name).'
				TO '.$db->quote_identifier($new_name).'; ';
			}
		}
		
		// If we have columns to add, add them.
		elseif (count($this->_add_columns) > 0)
		{
			// Array of column SQL.
			$columns = array();
			
			// Intiate the add statement
			$sql .= 'ADD (';
			
			// Foreach column, compile it and add it to the column array
			foreach($this->_add_columns as $column)
			{
				$columns[] = Database_Query_Builder::compile_column($column);
			}
			
			// Implode the array, and seperate it with a commar and close the bracket.
			$sql .= implode($columns, ',').'); ';
		}
		
		// If we have any columns to modify then modify them.
		elseif (count($this->_modify_columns) > 0)
		{
			// Array of column SQL.
			$columns = array();
			
			// Initiate the modify statement.
			$sql .= 'MODIFY (';
			
			// Foreach column, compile it in the appropriate way
			foreach($this->_modify_columns as $modified_column)
			{
				$columns[] = Database_Query_Builder::compile_column($modified_column);
			}
			
			$sql .= implode($columns, ',').'); ';
		}
		
		// If we have some columns to drop, then drop them
		elseif (count($this->_drop_columns) > 0)
		{
			// Foreach drop column, get the SQL and create a statement for it.
			foreach($this->_drop_columns as $column)
			{
				$drop = new Database_Query_Builder_Drop('column', $column);
				$sql .= $drop->compile($db).';';
			}
		}
		
		// return the SQL.
		return $sql;
	}
	
	public function reset()
	{
		// Reset the arrays
		$this->_add_columns = 
		$this->_modify_columns = 
		$this->_rename_columns =
		$this->_drop_columns = array();
		
		// Reset the names
		$this->_table =
		$this->_name = NULL;
	}
	
} // END Database_Query_Builder_Alter