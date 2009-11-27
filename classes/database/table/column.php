<?php defined('SYSPATH') or die('No direct script access.');

class Database_Table_Column {
	
	// The name of the column
	public $name;
	
	// The column's default value
	public $default;
	
	// Whether the column is nullable or not
	public $is_nullable;
	
	// Whether the column is a primary key
	public $is_primary;
	
	// The normalised datatype of the column
	public $datatype;
	
	// Any additional parameters that were not identified
	public $parameters;
	
	// Whether the column is a unique key or not
	public $is_unique;
	
	// Not editable
	
	// The ordinal position of the column
	protected $_ordinal_position;
	
	// The parent table object
	protected $_table;
	
	// Whether the column has been loaded from the database or not.
	protected $_loaded = FALSE;
	
	// The original name of the column.
	protected $_original_name;
	
	/**
	 * Loads a SQL Information Schema into the column object.
	 *
	 * @param   object   The parent table.
	 * @param	array	The column schema
	 * @return  object	This column.
	 */
	public function load_schema( & $table, $schema)
	{
		// Set the table by reference.
		$this->table =& $table;
		
		// Set the original name
		$this->_original_name = $this->name = $schema['COLUMN_NAME'];
		
		// Set some ISO standard params
		$this->default = $schema['COLUMN_DEFAULT'];
		$this->is_nullable = $schema['IS_NULLABLE'] == 'YES';
		$this->is_primary = $schema['COLUMN_KEY'] == 'PRI';
		$this->ordinal_position = $schema['ORDINAL_POSITION'];
		
		// Lets fetch any aditional parametres eg enum()
		preg_match("/^\S+\((.*?)\)/", $schema['COLUMN_TYPE'], $matches);
		
		$this->datatype = $this->table->database->get_type($schema['DATA_TYPE']); 
				
		if(isset($matches[1]))
		{
			// Replace all quotations
			$params = str_replace('\'', '', $matches[1]);
					
			if(strpos($params, ',') === false)
			{
				$this->table->database->get_type($schema['DATA_TYPE']);
				
				// Return value as it is
				$this->parameters = $params;
			}
			else
			{
				// Comma seperated values are exploded into an array
				$this->parameters = explode(',', $params);
			}
		}
		else
		{
			// No additional params
			$this->datatype = array($schema['DATA_TYPE']);
		}
		
		// Column has been loaded from the database.
		$this->_loaded = true;
		
		return $this;
	}
	
	/**
	 * Creates the table if it is not already loaded.
	 * 
	 * @returns void
	 */
	public function create()
	{
		// Alter the table
		DB::alter($this->table)
			->add($this)
			->execute();
	}
	
	/**
	 * Drops the loaded column.
	 * 
	 * @returns void
	 */
	public function drop()
	{
		// Drops the column
		DB::alter($this->table)
			->drop($this)
			->execute();
	}
	
	/**
	 * Creates the table if it is not already loaded.
	 * 
	 * @returns	bool	Whether the column is loaded or not.
	 */
	public function loaded()
	{
		return $this->_loaded;
	}
	
	/**
	 * Updates the current column if you have modified any properties.
	 * 
	 * @returns void
	 */
	public function update()
	{
		// Updates the existing column
		DB::alter($this->table)
			->modify($this, $this->_original_name)
			->execute();
	}
	
	/**
	 * Compiles the column into SQL
	 * 
	 * @returns string	sql
	 */
	public function compile()
	{
		return Database_Query_Builder::compile_column($this);
	}
	
	/**
	 * Compiles the column's datatype.
	 * 
	 * @returns string	sql
	 */
	public function compile_datatype()
	{
		// Get the table's database
		$db = $this->table->database;
		
		// Get the datatype and parameters
		$type = $this->datatype;
		$params = $this->parameters;
		
		$sql = strtoupper($type);
		
		// Compile datatype params
		if(is_array($params) AND count($params) > 0)
		{
			// Add it to the sql
			$sql .= '('.implode(array_map(array($db, 'escape'), $params), ',').')';
		}
		elseif (isset($params))
		{			
			// Add it to the sql
			$sql .= '('.$db->escape($params).')';
		}
		
		return $sql;
	}
	
	/**
	 * Compiles the column's constraints.
	 * 
	 * @returns string	sql
	 */
	public function compile_constraints()
	{
		$db = $this->table->database;
		
		$sql = '';
		
		// Compile nullable constraint
		if( ! $this->is_nullable)
		{
			$sql .= 'NOT NULL ';
		}
		
		// Compile default constraint
		if($this->default != NULL)
		{
			$sql .= 'DEFAULT '.$db->escape($column->default);
		}
		
		return $sql;
	}
}