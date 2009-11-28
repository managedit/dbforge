<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table column object.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
abstract class Database_Column {
	
	/**
	 * Creates a new database column with the specified datatype.
	 *
	 * @param   string	Datatype.
	 * @return  object	Database column object.
	 */
	public static function factory($datatype, & $table)
	{
		// Get the normalised datatype
		$datatype = $this->table->database->get_type($datatype);
		
		// Get the appropriate type
		$class = 'Database_Column_'.ucfirst($datatype['type']);
		
		// If the class exists return it.
		if(class_exists($class))
		{
			return new $class($table);
		}
		
		// Otherwise throw an error, we don't support the column type.
		throw new Kohana_Exception('Unsupported database column driver :dvr', array(
			'dvr' => $datatype
		));
	}
	
	/*
	 * Editable
	 */
	
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
	
	/*
	 * Not editable
	 */
	
	// The ordinal position of the column
	public $ordinal_position;
	
	// The parent table object
	public $table;
	
	// Whether the column has been loaded from the database or not.
	protected $_loaded = FALSE;
	
	// The original name of the column.
	protected $_original_name;
	
	/**
	 * Create a new column object.
	 *
	 * @param	object	The parent table.
	 * @return	object	This column.
	 */
	protected function __construct( & $table, $datatype)
	{
		// Set the table by reference.
		$this->table =& $table;
	}
	
	/**
	 * Loads a SQL Information Schema into the column object.
	 *
	 * @param	object	The parent table.
	 * @param	array	The column schema
	 * @return	object	This column.
	 */
	public function load_schema($schema)
	{	
		// Set the original name
		$this->_original_name = $this->name = $schema['COLUMN_NAME'];
		
		// Set some ISO standard params
		$this->default = $schema['COLUMN_DEFAULT'];
		$this->is_nullable = $schema['IS_NULLABLE'] == 'YES';
		$this->is_primary = $schema['COLUMN_KEY'] == 'PRI';
		$this->ordinal_position = $schema['ORDINAL_POSITION'];
		
		// Lets fetch any aditional parametres eg enum()
		preg_match("/^\S+\((.*?)\)/", $schema['COLUMN_TYPE'], $matches);
		
		// Normalise and set the datatype
		$this->datatype = array(
			$schema['DATA_TYPE'],
			$this->table->database->get_type($schema['DATA_TYPE']));
		
		// Process the datatype parameters
		if(isset($matches[1]))
		{
			// Replace all quotations
			$params = str_replace('\'', '', $matches[1]);
					
			if(strpos($params, ',') === FALSE)
			{
				// Return value as it is
				$this->parameters = $params;
			}
			else
			{
				// Comma seperated values are exploded into an array
				$this->parameters = explode(',', $params);
			}
		}
		
		// Set the column as loaded.
		$this->_loaded = TRUE;
		
		// Let the specific variables be parsed.
		$this->_init_schema();
		
		// Return the current object.
		return $this;
	}
	
	/**
	 * Allows for column drivers to load specific values from the schema.
	 * 
	 * @returns	void
	 */
	abstract protected function _init_schema();
	
	/**
	 * Creates the table if it is not already loaded.
	 * 
	 * @returns	void
	 */
	public function create()
	{
		// Alter the table
		DB::alter($this->table)
			->add($this)
			->execute($this->table->database);
	}
	
	/**
	 * Drops the loaded column.
	 * 
	 * @returns	void
	 */
	public function drop()
	{
		// Drops the column
		DB::alter($this->table)
			->drop($this)
			->execute($this->table->database);
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
	 * @returns	void
	 */
	public function update()
	{
		// Updates the existing column
		DB::alter($this->table)
			->modify($this, $this->_original_name)
			->execute($this->table->database);
	}
	
	/**
	 * Compiles the column into SQL
	 * 
	 * @returns	string	sql
	 */
	public function compile()
	{
		return Database_Query_Builder::compile_column($this);
	}
	
	/**
	 * Compiles the column into SQL
	 * 
	 * @returns	string	sql
	 */
	public function rename($new_name)
	{
		DB::alter($this->table)
			->rename_column($this, $new_name)
			->execute($this->table->database);
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