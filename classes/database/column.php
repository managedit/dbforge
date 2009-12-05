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
	
	// Lets define some default column datatypes
	const STRING 	= 'varchar';
	const BINARY 	= 'blob';
	const BOOL	 	= 'boolean';
	const DATETIME 	= 'timestamp';
	const FLOAT		= 'float';
	const INT		= 'int';
	
	/**
	 * Creates a new database column with the specified datatype.
	 *
	 * @param	Database_Table	The parent table object.
	 * @param   string	The datatype of the column.
	 * @return  Database_Column	Database column object.
	 */
	public static function factory($table, $datatype)
	{
		// Get the normalised datatype
		$schema = $table->database->datatype($datatype);
		$schema['data_type'] = $datatype;
		
		// Get the appropriate type
		$class = 'Database_Column_'.ucfirst($schema['type']);
		
		// If the class exists return it.
		if(class_exists($class))
		{
			return new $class($table, $schema);
		}
		
		// Otherwise throw an error, we don't support the column type.
		throw new Kohana_Exception('Unsupported database column driver dvr', array(
			'dvr' => $class
		));
	}
	
	/**
	 * Retrieves an instance of a database column in a table.
	 *
	 * @param   Database_Table	The parent table object.
	 * @param	string	The name of the column.
	 * @return  object	Database column object.
	 */
	public static function instance($table, $name)
	{
		// Get the column's information schema
		$schema = $table->columns($name);
		
		// Get the appropriate type
		$class = 'Database_Column_'.ucfirst($schema['type']);
		
		// If the class exists return it.
		if(class_exists($class))
		{
			return new $class($table, $schema, TRUE);
		}
		
		// Otherwise throw an error, we don't support the column type.
		throw new Kohana_Exception('Unsupported database column driver :dvr', array(
			'dvr' => $schema['type']
		));
	}
	
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
	 * @param	Database_Table	The parent table.
	 * @param	array	The column SQL92 information schema.
	 * @param	bool	Whether the column information schema was generated from the database.
	 * @return	object	The column object.
	 */
	private final function __construct($table, array $information_schema = NULL, $from_db = FALSE)
	{
		// Set the table by reference.
		$this->table = $table;
		
		if($information_schema !== NULL)
		{
			// Set the original name and current name to the same thing
			$this->_original_name = $this->name = arr::get($information_schema, 'column_name');
			
			// Set some ISO standard params
			$this->default 			= arr::get($information_schema, 'column_default');
			$this->is_nullable 		= arr::get($information_schema, 'is_nullable')	== 'YES';
			$this->is_primary 		= arr::get($information_schema, 'column_key')	== 'PRI';
			$this->is_unique 		= arr::get($information_schema, 'column_key')	== 'UNI';
			$this->ordinal_position = arr::get($information_schema, 'ordinal_position');
			
			// Normalise and set the datatype and any parameters
			$this->datatype = arr::get($information_schema, 'data_type');
			$this->parameters = arr::get($information_schema, 'parameters');
			
			// Let column drivers manage the schema themselves
			$this->_load_schema($information_schema);
			
			// Set the column as loaded.
			$this->_loaded = $from_db;
		}
	}
	
	/**
	 * Loads a SQL Information Schema into the column object.
	 *
	 * @param	array	The column's information schema
	 * @return	void
	 */
	abstract protected function _load_schema($information_schema);
	
	/**
	 * Creates the table if it is not already loaded.
	 * 
	 * @return	void
	 */
	public function create()
	{
		// If the table is loaded it can't be created
		if($this->loaded())
		{
			throw new Kohana_Exception('Unable to create loaded column :col', array(
				'col' => $this->name
			));
		}
		
		// Alter the table
		DB::alter($this->table->name)
			->add($this->compile())
			->execute($this->table->database);
	}
	
	/**
	 * Drops the loaded column.
	 * 
	 * @return	void
	 */
	public function drop()
	{
		// You cannot drop a column that doesnt exist
		if( ! $this->loaded())
		{
			throw new Kohana_Exception('Unable to drop unloaded column :col', array(
				'col' => $this->name
			));
		}
		
		// Drops the column
		DB::alter($this->table->name)
			->drop($this->name)
			->execute($this->table->database);
	}
	
	/**
	 * Creates the table if it is not already loaded.
	 * 
	 * @return	bool	Whether the column is loaded or not.
	 */
	public function loaded()
	{
		return $this->_loaded;
	}
	
	/**
	 * Updates the current column if you have modified any properties.
	 * 
	 * @return	void
	 */
	public function update()
	{
		if( ! $this->loaded())
		{
			throw new Kohana_Exception('Unable to modify an unloaded column :col', array(
				'col'	=> $this->name
			));
		}
		
		// Updates the existing column
		DB::alter($this->table)
			->modify($this->compile(), $this->_original_name)
			->execute($this->table->database);
	}
	
	/**
	 * Compiles the column object into an array. This can be then used in the query builder.
	 * 
	 * @return	array	The column array.
	 */
	public function compile()
	{
		// Bring everything together and return the array
		return array(
			'name' => $this->name,
			'datatype' => array($this->datatype => $this->_compile_parameters()),
			'constraints' => $this->_compile_constraints()
		);
	}
	
	/**
	 * Renames the column.
	 * 
	 * @return	void
	 */
	public function rename($new_name)
	{
		// If the column isn't loaded then it can't be renamed
		if( ! $this->loaded())
		{
			throw new Kohana_Exception('Unable to rename unloaded column :col', array(
				'col'	=> $this->name
			));
		}
		
		// Perform the query
		DB::alter($this->table->name)
			->rename_column($this->name, $new_name)
			->execute($this->table->database);
	}
	
	/**
	 * Prepares the column's parameters.
	 * 
	 * @return	array	The column's datatype
	 */
	protected function _compile_parameters()
	{
		// Return the column's parameters
		return $this->parameters;
	}

	/**
	 * Compiles the column constraints into an array
	 * 
	 * @return	array	Column constraints.
	 */
	protected function _compile_constraints()
	{
		// Prepare the constraints array
		$constraints = array();
		
		// Compile the not null constraint
		if( ! $this->is_nullable)
		{
			$constraints[] = 'not null';
		}
		
		// Compile the default constraint
		if(isset($this->default))
		{
			$constraints['default'] = $this->default;
		}
		
		// Return the constraints array
		return $constraints;
	}
}