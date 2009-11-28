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
	protected $_renam_columns = array();
	
	
	/**
	 * Set the table for alteration.
	 *
	 * @param   string The table name
	 * @return  void
	 */
	public function __construct( Database_Table $table)
	{
		if( ! $table->loaded())
		{
			throw new Database_Exception('Table tbl must be loaded to perform alter queries.', array(
				'tbl' => $table->name
			));
		}
		
		// Set the table object.
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
	 * @param	Database_Column	The column object.
	 * @param	string	The new column name.
	 * @return  void
	 */
	public function rename_column( Database_Column $column, $new_name)
	{
		// We can only rename loaded columns
		if( ! $column->loaded())
		{
			throw new Kohana_Exception('Column must be loaded to rename it.');
		}
		
		// Add it to the rename column array
		$this->_renam_columns[$new_name] = $column;
	}
	
	/**
	 * Add a column
	 *
	 * @param   object The column object.
	 * @return  void
	 */
	public function add( Database_Column $column)
	{
		if($column->loaded())
		{
			$column = clone $column;
		}
		
		$this->_add_columns[] = $column;
		
		return $this;
	}
	
	/**
	 * Modify a column.
	 *
	 * @param	string	The name of the column you wish to modify.
	 * @param   object	The new column data.
	 * @return  void
	 */
	public function modify( Database_Column $new_column, $existing_column)
	{
		if ( ! is_object($existing_column))
		{
			$existing_column = $this->_table->columns(true, $existing_column);
			
			if(count($existing_column) !== 1)
			{
				throw new Database_Exception('col could not be found in tbl', array(
					'col' => ucfirst($existing_column),
					'tbl' => $this->_table->name
				));
			}
		}
		
		$this->_modify_columns[$existing_column->name] = $new_column;
		
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
		$sql = 'ALTER TABLE '.$db->quote_table($this->_table->name).' ';
		
		// Each command will be added to this array
		$lines = array();
		
		// If we have a name set, rename the table.
		if ($this->_name !== NULL)
		{
			$sql .= 'RENAME TO '.$db->quote_table($this->_name).'; ';
		}
		
		// If we have any columns to rename do so
		if(count($this->_rename_columns) > 0)
		{
			// Loop through each column and compile the SQL.
			foreach($this->_rename_columns as $new_name => $column)
			{
				$sql .= 'RENAME COLUMN '.$column->name.' TO '.$new_name.'; ';
			}
		}
		
		// If we have columns to add, add them.
		if (count($this->_add_columns) > 0)
		{
			// Array of column SQL.
			$columns = array();
			
			// Intiate the add statement
			$sql .= 'ADD ';
			
			// Foreach column, compile it and add it to the column array
			foreach($this->_add_columns as $column)
			{
				$columns[] = Database_Query_Builder::compile_column($column);
			}
			
			// Implode the array, and seperate it with a commar.
			$sql .= implode($columns, ',').' ';
		}
		
		// If we have any columns to modify then modify them.
		if (count($this->_modify_columns) > 0)
		{
			// Array of column SQL.
			$columns = array();
			
			// Initiate the modify statement.
			$sql .= 'MODIFY ';
			
			// Foreach column, compile it in the appropriate way
			foreach($this->_modify_columns as $original_name => $column)
			{
				$columns[] = Database_Query_Builder::compile_column($column);
			}
			
			$sql .= implode($columns, ',').'; ';
		}
		
		if (count($this->_drop_columns) > 0)
		{
			foreach($this->_drop_columns as $column)
			{
				$drop = new Database_Query_Builder_Drop('column', $column);
				$sql .= $drop->compile($column->table->database).';';
			}
		}
		
		return $sql;
	}
	
	public function reset()
	{
		$this->_add_columns = 
		$this->_modify_columns = 
		$this->_drop_columns = array();
		
		$this->_name = NULL;
	}
	
} // END Database_Query_Builder_Alter