<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for ALTER statements.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Query_Builder_Alter extends Database_Query_Builder {
	
	// The original name of the table.
	protected $_original_name;
	
	// The new name of the table.
	protected $_new_name;
	
	// The columns to add to the table.
	protected $_add_columns = array();
	
	// The list of constraints to add to the table.
	protected $_add_constraints = array();
	
	// The list of constraints to drop from the table.
	protected $_drop_constraints = array();
	
	// The list of columns to modify
	protected $_modify_columns = array();
	
	// The list of columns to drop
	protected $_drop_columns = array();
	
	/**
	 * Create a new alter query builder.
	 *
	 * @param	string	The name of the table to alter.
	 */
	public function __construct($table_name)
	{
		// Set the table name.
		$this->_original_name = $table_name;
		
		// Because mummy says so.
		parent::__construct(Database::ALTER, '');
	}
	
	/**
	 * Rename the table.
	 *
	 * @param   string	The new name
	 * @return	Database_Query_Builder_Alter	The current object for chaining.
	 */
	public function rename($new_name)
	{
		// Set the new name
		$this->_new_name = $name;
		
		// Return the current object.
		return $this;
	}
	
	/**
	 * Modify a column.
	 *
	 * @param	array	The altered column array. To rename a column, see the rename_column() method.
	 * @return	Database_Query_Builder_Alter	The current object for chaining.
	 */
	public function modify( array $column)
	{
		// Add the modified column array
		$this->_modify_columns[] = $column;
		
		// And return the builder object.
		return $this;
	}
	
	/**
	 * Drop a column or constraint.
	 *
	 * @param	string	The name of the column to drop.
	 * @param	string	The type of object you want to drop (column or constraint).
	 * @return	Database_Query_Builder_Alter	The current object for chaining.
	 */
	public function drop($object_name, $type = 'column')
	{
		// Switch the object type
		switch(strtolower($type))
		{
			// Process it as a column
			case 'column':
				$this->_drop_columns[] = $object_name;
				break;
			
			// Process it as a constraint	
			case 'constraint':
			case 'index':
			case 'primary key':
				$this->_drop_constraints[$object_name] = $type;
				break;
				
			// Throw an exception because we dont recognise it.
			default :
				throw new Kohana_Exception('Unrecognised drop type "dtyp", use either column, primary key or constraint.', 
					array('dtyp' => $type));
		}
		
		// Return this object for chaining
		return $this;
	}
	
	/**
	 * Add a column or constraint to the table.
	 * 
	 * @param	array	The column or constraint data array.
	 * @param	string	The type of object.
	 * @return	Database_Query_Builder_Alter	The current object for chaining.
	 */
	public function add( array $data_array, $type = 'column')
	{
		// Switch the object type
		switch(strtolower($type))
		{
			// Process it as a column
			case 'column':
				$this->_add_columns[] = $data_array;
				break;
			
			// Process it as a constraint
			case 'constraint':
				$this->_add_constraints[] = $data_array;
				break;
				
			// Throw an exception because we dont recognise it.
			default :
				throw new Kohana_Exception('Unrecognised ADD type atyp, use either column or constraint.', 
					array('atyp' => $type));
		}
		
		// Return this object for chaining
		return $this;
	}
	
	/**
	 * Compile the SQL query and return it.
	 *
	 * @param   Database  Database instance.
	 * @return  string	The SQL query.
	 */
	public function compile( Database $db)
	{
		// Initiate the alter statement
		$sql = 'ALTER TABLE '.$db->quote_table($this->_original_name).' ';
		
		// If we have a name set, rename the table.
		if (isset($this->_new_name))
		{
			// Prepare the rename SQL
			$sql .= 'RENAME TO '.$db->quote_table($this->_new_name).'; ';
			
			// Update the new name
			$this->_original_name = $this->_new_name;
		}
		
		// If we have columns or constraints to add
		elseif ( ! empty($this->_add_columns) OR ! empty($this->_add_constraints))
		{
			// If we have more then one constraint or column, we need brackets
			$multi = count($this->_modify_columns) + count($this->_add_constraints) > 1;
			
			// Add the brackets and begin the statement
			$sql .= 'ADD '.($multi ? '(' : '');
			
			// Loop through each column, compiling it where necessary
			foreach($this->_add_columns as $column)
			{
				$sql .= Database_Query_Builder::compile_column($column, $db).',';
			}
			
			// Loop through each constraint, compiling it where necessary
			foreach($this->_add_constraints as $constraint)
			{
				$sql .= Database_Query_Builder::compile_constraint($column, $db).',';
			}
			
			// Remove the trailing commar and append a closing bracket where necessary
			$sql = rtrim($sql, ',').($multi ? ')' : '').';';
		}
		
		// If we have any columns to modify then modify them.
		elseif ( ! empty($this->_modify_columns))
		{
			// Check to see if we have more then one column
			$multi = count($this->_modify_columns) > 1;
			
			// Begin the modify statement, if we have multiple columns, add them as methods.
			$sql .= 'MODIFY '.($multi ? '(' : '');
			
			// Loop through each column, compile it, then add it to the sql string.
			foreach($this->_modify_columns as $column)
			{
				$sql .= Database_Query_Builder::compile_column($column, $db).',';
			}
			
			// Return the sql statement with any closing brackets where necessary
			$sql = rtrim($sql, ',').($multi ? ')' : '').';';
		}
		
		// If we have some columns to drop, then drop them
		elseif ( ! empty($this->_drop_columns) OR ! empty($this->_drop_constraints))
		{
			// Reset the sql string, multiple drop methods cannot be put in the same query.
			$sql = '';
			
			// Foreach drop column, get the SQL and create a statement for it.
			foreach($this->_drop_columns as $column)
			{
				// Start each drop statement as a new query
				$sql .= 'ALTER TABLE '.$db->quote_table($this->_original_name).' '.
						DB::drop('column', $column)->compile($db).';';
			}
			
			// Foreach drop constraint
			foreach($this->_drop_constraints as $constraint => $type)
			{
				// Start each drop statement as a new query
				$sql .= 'ALTER TABLE '.$db->quote_table($this->_original_name).' '.
						DB::drop($type, $constraint)->compile($db).';';
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
		$this->_add_constraints =
		$this->_drop_constraints =
		$this->_drop_columns = array();
		
		// Reset the names
		unset($this->_original_name);
	}
	
} // END Database_Query_Builder_Alter