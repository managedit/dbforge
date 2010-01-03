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
	
	/**
	 * The name of the table.
	 * 
	 * @var	string
	 */
	protected $_table;
	
	/**
	 * The list of columns to modify.
	 * 
	 * @var array
	 */
	protected $_modify = array();
	
	/**
	 * The list of columns to add.
	 * 
	 * @var	array
	 */
	protected $_add_columns = array();
	
	/**
	 * The list of constraints to add.
	 * 
	 * @var	array
	 */
	protected $_add_constraints = array();
	
	/**
	 * The list of drops to perform by the 
	 * 
	 * @var unknown_type
	 */
	protected $_drops = array();
	
	/**
	 * Create a new alter query builder.
	 *
	 * @param	string	The name of the table to alter.
	 * @return	void
	 */
	public function __construct($table)
	{
		$this->_table = $table;

		parent::__construct(Database::ALTER, '');
	}
	
	/**
	 * Modify a column based on the object model. Note the columns need the same name.
	 * 
	 * @param	Database_Column	The column object to modify.
	 * @return	Database_Query_Builder_Alter
	 */
	public function modify(Database_Column $column)
	{
		$this->_modify[] = $column;
		
		return $this;
	}
	
	/**
	 * Drop a column or constraint.
	 *
	 * @throws	Kohana_Exception	If the type isn't recognised.
	 * @param	string	The name of the column to drop.
	 * @param	string	The type of object you want to drop.
	 * @return	Database_Query_Builder_Alter	The current object for chaining.
	 */
	public function drop($name, $type = 'column')
	{
		$this->_drops[] = array($type => $name);
		
		return $this;
	}
	
	/**
	 * Adds a column or constraint to the table.
	 * 
	 * @throws	Kohana_Exception	If the object isn't a column or constraint.
	 * @param	object	Either the column or constraint object.
	 * @return	Database_Query_Builder_Alter
	 */
	public function add($object)
	{
		if ($object instanceof Database_Column)
		{
			$this->_add_columns[] = $object;
		}
		elseif ($object instanceof Database_Constraint)
		{
			$this->_add_constraints[] = $object;
		}
		else
		{
			throw new Kohana_Exception('Unrecognised add object :obj', array(
				':obj' => $object
			));
		}
		
		return $this;
	}
	
	public function compile(Database $db)
	{
		return $this->_compile_add($db).
			$this->_compile_modify($db).
			$this->_compile_drop($db);
	}
	
	public function reset()
	{
		$this->_table = NULL;
		
		$this->_drops =
		$this->_add_columns =
		$this->_add_constraints =
		$this->_modify = array();
	}
	
	/**
	 * Compiles all added columns / constraints into SQL.
	 * 
	 * @param	Database	The database object.
	 * @return	string
	 */
	protected function _compile_add(Database $db)
	{
		$sql = '';
		
		if ( ! empty($this->_add_columns) OR ! empty($this->_add_constraints))
		{
			$sql = 'ALTER TABLE '.$db->quote_table($this->_table).' ';
			
			$multi = count($this->_modify_columns) + count($this->_add_constraints) > 1;
			
			$sql .= 'ADD '.($multi ? '(' : '');
			
			foreach ($this->_add_columns as $column)
			{
				$sql .= $column->compile().',';
			}
			
			foreach ($this->_add_constraints as $constraint)
			{
				$sql .= $constraint->compile($db).',';
			}
			
			$sql = rtrim($sql, ',').($multi ? ')' : '').';';
		}
		
		return $sql;
	}
	
	/**
	 * Compile all modify column statements into SQL.
	 * 
	 * @param	Database	The database object.
	 * @return	string
	 */
	protected function _compile_modify(Database $db)
	{
		$sql = '';
		
		if ( ! empty($this->_modify_columns))
		{
			$sql = 'ALTER TABLE '.$db->quote_table($this->_table).' ';
			
			$multi = count($this->_modify_columns) > 1;
			
			$sql .= 'MODIFY '.($multi ? '(' : '');
			
			foreach ($this->_modify_columns as $column)
			{
				$sql .= $column->compile().',';
			}
			
			$sql = rtrim($sql, ',').($multi ? ')' : '').';';
		}
		
		return $sql;
	}
	
	/**
	 * Compiles all drop statements into SQL.
	 * 
	 * @param	Database	The database object.
	 * @return	string
	 */
	protected function _compile_drop(Database $db)
	{
		$sql = '';
		
		if ( ! empty($this->_drops))
		{
			foreach ($this->_drops as $type => $name)
			{
				$sql .= 'ALTER TABLE '.$db->quote_table($this->_original_name).' '.
						DB::drop($type, $name)->compile($db).';';
			}
		}
		
		return $sql;
	}
	
} // End Database_Query_Builder_Alter