<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Table {
	
	// Public properties
	protected $_database;
	protected $_name;
	protected $_catalog;
	protected $_loaded = false;
	protected $_columns = array();
	
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
	
	public function __get($name)
	{
		// Get table properties
		switch($name)
		{
			case 'database':
				return $this->_database;
			case 'name':
				return $this->_name;
			case 'catalog':
				return $this->_catalog;
		}
	}
	
	public function loaded()
	{
		return $this->_loaded;
	}
	
	public function truncate()
	{
		DB::truncate($this)
			->execute($this->_database);
	}
	
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
	
	public function drop()
	{
		// Drop the table
		DB::drop($this)
			->execute();
	}
	
	public function create()
	{
		// Create this table
		DB::create($this)
			->execute();
	}
	
	public function rename($new_name)
	{
		// Rename this table to a new name
		DB::alter($this)
			->rename($new_name)
			->execute();
			
		$this->name = $new_name;
	}
	
	public function __clone()
	{
		// Cloned tables dont exist in the database.
		$this->_loaded = false;
	}
}