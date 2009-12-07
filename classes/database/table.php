<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table object.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Table {
	
	/**
	 * Retrieves the instance of an existing database table.
	 *
	 * @param   string   The name of the table.
	 * @param	Database	The database instance (optional).
	 * @return  object	The table object.
	 */
	public static function instance($name, Database $database = NULL)
	{
		// Get a default instance of the database if none is set.
		if($database === NULL)
		{
			$database = Database::instance();
		}
		
		// Get the table schema for the given name
		$table_schema = $database->list_tables($name);
		
		// Throw an exception if the schema could not be found
		if(empty($table_schema))
		{
			throw new Kohana_Exception('Unable to find table tbl', array(
				'tbl' => $name
			));
		}
		
		// Return a new table object with everything we need.
		return new self($database, $table_schema);
	}
	
	// The parent database
	public $database;
	
	// The name of the table
	public $name;
	
	// The type of the table
	public $type;
	
	// Whether the table is loaded or not
	protected $_loaded = FALSE;
	
	// The list of primary keys
	protected $_primary_keys = array();
	
	// The list of unique keys
	protected $_unique_keys = array();
	
	// The array of columns
	protected $_columns = array();
	
	// The array of user defined constraints
	protected $_constraints = array();
	
	// Table options used in compilation
	protected $_options = array();
	
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
			$this->name = $information_schema['table_name'];
			$this->type = $information_schema['table_type'];
			
			// Identify the object as loaded from live table data.
			$this->_loaded = TRUE;
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
		DB::truncate($this->name)
			->execute($this->database);
	}
	
	/**
	 * Returns the columns within the table.
	 *
	 * @param	string	The column you want to return, if there is only one.
	 * @return  Database_Table_Column	The column(s) you requested.
	 */
	public function columns($like = NULL)
	{
		// If like is not set, return all the columns
		if($like === NULL)
		{
			return $this->_columns;
		}
		else
		{
			// Return the exact column
			return $this->_columns[$like];
		}
	}
	
	/**
	 * Adds a column to the table. If the table is loaded then the action will be commited to the database.
	 *
	 * @return  void.
	 */
	public function add_column( Database_Column $column)
	{
		// Set the column table by reference.
		$column->table = $this;
		
		// If this table is loaded, add the column to the database.
		if($this->_loaded)
		{
			DB::alter($this)
				->add($column)
				->execute($this->_database);
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
		DB::drop('table', $this->name)
			->execute($this->database);
	}
	
	/**
	 * Creates the table. If the table is already loaded an error will be thrown.
	 *
	 * @return  void.
	 */
	public function create()
	{
		// Create this table
		DB::create($this->compile())
			->execute($this->database);
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
			->execute($this->_database);
			
		$this->name = $new_name;
	}
	
	/**
	 * Adds a constraint to the table.
	 *
	 * @param	Database_Constraint	The constraint object.
	 * @return  void.
	 */
	public function add_constraint(Database_Constraint $constraint)
	{
		// Add it to the array
		$this->_constraints[] = $constraint;
	}
	
	/**
	 * Retrieves an existing table option.
	 *
	 * @param	string	The keyword which the option was defined with, if you're looking for something specific.
	 * @return  array	The list of options.
	 * @return	array	The single array you were looking for.
	 */
	public function options($keyword = NULL)
	{
		if($keyword === NULL)
		{
			return $this->_options;
		}
		else
		{
			return $this->_options[$keyword];
		}
	}
	
	/**
	 * Adds a table option.
	 * 
	 * Table options are appended to the end of the create statement, typically in MySQL here you would set
	 * the database engine, auto_increment offset, comments etc. Consult your database documentation for
	 * more information.
	 * 
	 * On comilation a typical output would be; KEYWORD=`value` or if value is not set; KEYWORD
	 * 
	 * @see http://dev.mysql.com/doc/refman/5.1/en/create-table.html
	 *
	 * @param	string	The keyword of the option.
	 * @param	string	The value associated with the keyword. This is completely optional depending on your needs.
	 * @return  void.
	 */
	public function add_option($keyword, $value = NULL)
	{
		// If a value was not set, just add the value to the array
		if($value === NULL)
		{
			$this->options[] = $value;
		}
		else
		{
			$this->_options[$keyword] = $value;
		}
	}
	
	/**
	 * Adds a constraint to the table.
	 *
	 * @param	string	The name of the constraint you're looking for
	 * @return  array	The list of all the columns
	 * @return	Database_Constraint The constraint object
	 */
	public function constraints($like = NULL)
	{
		// If we have nothing to find, then return them all
		if ($like === NULL)
		{
			return $this->_constraints;
		}
		else
		{
			// Otherwise return what they are looking for
			return $this->_constraints[$like];
		}
	}
	
	public function reset()
	{
		// Get a list of columns from the database for this table
		$columns = $this->database->list_columns($this->name);
		
		// Reset the column array
		$this->_columns = array();
		
		// Loop through each column, and add it to the column array
		foreach($columns as $name => $column)
		{
			$this->_columns[$name] = Database_Column::instance($this, $name);
		}
	}
	
	public function compile()
	{
		// Pull everything together and return it as an array.
		return array(
			'name' => $this->name,
			'columns' => $this->_compile_columns(),
			'constraints' => $this->_compile_constraints(),
			'options' => $this->options()
		);
	}
	
	protected function _compile_constraints()
	{
		// Get a list of constraints
		$constraints = $this->constraints();
		
		// Foreach constraint, compile it
		foreach($constraints as $name => & $constraint)
		{
			$constraint = $constraint->compile($this->database);
		}
		
		// Return the compiled array
		return $constraints;
	}
	
	protected function _compile_columns()
	{
		// Get a list of columns
		$columns = $this->columns();
		
		// Loop through every column and change the object to an array
		foreach($columns as $name => & $column)
		{
			// Compile the column and set it
			$column = $column->compile();
		}
		
		// Return the column array
		return $columns;
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