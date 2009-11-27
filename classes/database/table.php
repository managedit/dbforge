<?php defined('SYSPATH') or die('No direct script access.');

class Database_Table {
	
	// The parent database
	protected $_database;
	
	// The name of the table
	protected $_name;
	
	// The name of the catalog
	protected $_catalog;
	
	// Whether the table is loaded or not
	protected $_loaded = FALSE;
	
	// The array of columns
	protected $_columns = array();
	
	/**
	 * Creates a new table object.
	 *
	 * @param   Database   The parent database.
	 * @param	array	The table schema.
	 * @return  object	The table object.
	 */
	public function __construct(Database $database = NULL, $information_schema = NULL)
	{
		// Load the current database instance by detault
		if($database === NULL)
		{
			$database = Database::instance();
		}
		
		// Set the parent database
		$this->database = $database;
		
		// Load the information schema if its added
		if($information_schema !== NULL)
		{
			// These properties are supported by ISO standards
			$this->_name = $information_schema['TABLE_NAME'];
			$this->_catalog = $information_schema['TABLE_CATALOG'];
			
			// Identify the object as loaded from live table data.
			$this->_loaded = true;
		}
	}
	
	/**
	 * Gets read-only properties of the table.
	 *
	 * @param   string   The property name.
	 * @returns	object	The requested property.
	 */
	public function __get($name)
	{
		// Get table properties
		switch($name)
		{
			// Returns the database.
			case 'database':
				return $this->_database;
			
			// Returns the name of the table.
			case 'name':
				return $this->_name;
				
			// Returns the catalog name.
			case 'catalog':
				return $this->_catalog;
		}
	}
	
	/**
	 * Returns whether the table has been loaded from the database.
	 * 
	 * @return  bool
	 */
	public function loaded()
	{
		return $this->_loaded;
	}
	
	/**
	 * Truncates the table, this will wipe all data and reset any auto-increment counters.
	 *
	 * @return  void.
	 */
	public function truncate()
	{
		// Truncate the table.
		DB::truncate($this)
			->execute($this->_database);
	}
	
	/**
	 * Returns the columns within the table.
	 *
	 * @param	string	The column you want to return, if there is only one.
	 * @return  Database_Table_Column	The column(s) you requested.
	 */
	public function columns($like = NULL)
	{
		// If the table hasn't been loaded then return any user defined columns.
		if( ! $this->_loaded)
		{
			// Return the column array, or the column that matches the like param
			return $like === NULL ? $this->_columns : $this->_columns[$like];
		}
		
		// Get all the columns
		$columns = $this->_database->list_columns($this->name, $like);
		
		// Foreach column in the data array
		foreach($columns as & $column)
		{
			// Create a new column object
			$col = new Database_Table_Column();
			
			// Load the schema and replace it with the column object
			$column = $col->load_schema($this, clone $column);
		}
		
		// Get the columns from the information schema.
		return $columns;
	}
	
	/**
	 * Compiles the table's constraints and returns the SQL.
	 *
	 * @return  string	sql
	 */
	public function compile_constraints()
	{
		// Get everything ready
		$db = $this->database;
		$columns = $this->columns(true);
		
		$primary_keys = array();
		$unique_keys = array();
		
		// Loop through each column and add them to the key arrays where appropriate.
		foreach($columns as $column)
		{
			if($column->is_primary)
			{
				// Primary key
				$primary_keys[] = $column;
			}
			elseif($column->is_unique)
			{
				// Unique key
				$unique_keys[] = $column;
			}
		}
		
		// Constraints are the standard name for keys
		$constrains = array();
		
		// Lets compile the primary keys, prefixed with 'pk_'
		// Naming scheme: pk_{0}_{1}...
		$key_name = 'pk_';
		$keys = '';
		
		// Add each primary key column to the name and the params.
		foreach($primary_keys as $name => $key)
		{
			$key_name .= $key->name.'_';
			$keys .= $db->quote_identifier($key->name).',';
		}
		
		// Remove trailing deliminers
		$key_name = rtrim($key_name, '_');
		$keys = rtrim($keys, ',');
		
		// Add the sql to the list of constraints
		$constrains[] = 'CONSTRAINT '.$key_name.' PRIMARY KEY ('.$keys.')';
		
		// Do the same for unique keys, this is easier as unique keys shouldnt be composite!
		foreach($unique_keys as $key)
		{
			$constrains[] = 'CONSTRAINT key_'.$key->name.' UNIQUE('.$db->quote_identifier($key->name).')';
		}
		
		// Return the imploded constraint array csv.
		return implode(',', $constrains);
	}
	
	/**
	 * Adds a column to the table. If the table is loaded then the action will be commited to the database.
	 *
	 * @return  void.
	 */
	public function add_column(Database_Table_Column & $column)
	{
		// Set the column table by reference.
		$column->table =& $this;
		
		// If this table is loaded, add the column to the database.
		if($this->_loaded)
		{
			DB::alter($this)
				->add($column)
				->execute();
		}
		
		// And just add it to the list of columns.
		$this->_columns[] = $column;
	}
	
	/**
	 * Drops the table, removing it from the database. WARNING: All data will be lost.
	 *
	 * @return  void.
	 */
	public function drop()
	{
		// Drop the table
		DB::drop($this)
			->execute();
	}
	
	/**
	 * Creates the table. If the table is already loaded an error will be thrown.
	 *
	 * @return  void.
	 */
	public function create()
	{
		// Create this table
		DB::create($this)
			->execute();
	}
	
	/**
	 * Renames the table to something else.
	 *
	 * @return  void.
	 */
	public function rename($new_name)
	{
		// Make sure we dont rename something by mistake
		if( ! $this->_loaded)
		{
			throw new Kohana_Exception('You can only rename tables that have been loaded from the database');
		}
		
		// Rename this table to a new name
		DB::alter($this)
			->rename($new_name)
			->execute();
			
		$this->name = $new_name;
	}
	
	/**
	 * Cloned objects will be unloaded.
	 */
	public function __clone()
	{
		// Cloned tables dont exist in the database.
		$this->_loaded = false;
	}
}