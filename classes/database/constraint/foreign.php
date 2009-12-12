<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table FOREIGN KEY constraint.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Constraint_Foreign extends Database_Constraint {
	
	/**
	 * @var	array	The list of supported actions.
	 */
	public $actions = array(
		'cascade'		=> 'cascade',
		'restrict'		=> 'restrict',
		'no action'		=> 'no action',
		'set null'		=> 'set null',
		'set default' 	=> 'set default'
	);
	
	// The type of the constraint.
	protected $_type = 'foreign key';
	
	// What the constraint references
	protected $_references;
	
	// The action taken on update
	protected $_on_update = 'no action';
	
	// The action taken on delete
	protected $_on_delete = 'no action';
	
	// The name of the foreign key column
	protected $_column;
	
	/**
	 * Initiate a FOREIGN KEY constraint.
	 *
	 * @param	string	The name of the column that represents the foreign key.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Foreign	The constraint object.
	 */
	public function __construct($column, $name = NULL)
	{
		// If the name is not given, dont set it
		if($name !== NULL)
		{
			$this->name = $name;
		}
		
		// Set the column name we're working with
		$this->_column = $column;
	}
	
	/**
	 * The destination table and column you're referencing.
	 *
	 * @param	string	The table name you're referencing.
	 * @param	string	The column name you're referencing.
	 * @return	Database_Constraint_Foreign	The current object.
	 */
	public function references($table, $column)
	{
		// The references array should contain the destination table and column.
		$this->_references = array(
			$table,
			$column
		);
		
		// Return the current object for chaining.
		return $this;
	}
	
	/**
	 * The action to perform when the foreign record is updated. Make sure this is supported by your
	 * database, otherwise it will not work.
	 * 
	 * @throws	Kohana_Exception	If you don't use a recognised type.
	 * @param	string	The lowercase type name. Use either: 'cascade', 'restrict',
	 * 'no action', 'set null','set default'.
	 * @return	Database_Constraint_Foreign	The current object.
	 */
	public function on_update($type)
	{
		// If the type is recognised, use it
		if (in_array($type, $this->actions, FALSE))
		{
			$this->_on_update = $type;
		}
		else
		{
			// Otherwise throw an error
			throw new Kohana_Exception('The foreign key constraint action [0] was not recognised', array(
				'[0]'	=> $type
			));
		}
		
		// Finally return this for chaining.
		return $this;
	}
	
	/**
	 * The action to perform when the foreign record is updated. Make sure this is supported by your
	 * database, otherwise it will not work.
	 * 
	 * @throws	Kohana_Exception	If you don't use a recognised type.
	 * @param	string	The lowercase type name. Use either: 'cascade', 'restrict',
	 * 'no action', 'set null','set default'.
	 * @return	Database_Constraint_Foreign	The current object.
	 */
	public function on_delete($type)
	{
		// If the action is recognised, set it
		if (in_array($type, $this->actions, FALSE))
		{
			$this->_on_delete = $type;
		}
		else
		{
			// Otherwise throw an exception
			throw new Kohana_Exception('The foreign key constraint action [0] was not recognised', array(
				'[0]'	=> $type
			));
		}
		
		// Finally return this object for chaining.
		return $this;
	}
	
	public function compile( Database $db)
	{	
		// Get the table and column names out of the references array.
		list($table, $column) = $this->_references;
		
		// If the bastards haven't set a name, we'll make one up.
		if( ! isset($this->name))
		{
			$this->name = 'fk_'.$this->_column.'_'.$table.'_'.$column;
		}
		
		// Compile a default array supported by the DBForge compiler.
		$result = array(
			'name'	=> $this->name,
			'params' => array(
				'foreign key' => array(
					$db->quote_identifier($this->_column)
				),
				'references '.$db->quote_table($table) => array(
					$db->quote_identifier($column)
				),
			)
		);
		
		// If we have an on_update action, add it to our magical array.
		if (isset($this->_on_update))
		{
			$result['params'][] = 'on update '.$this->_on_update;
		}
		
		// If we have an on_delete action, add it to the array.
		if (isset($this->_on_delete))
		{
			$result['params'][] = 'on delete '.$this->_on_delete;
		}
		
		// We assume that the constraint is created when it is compiled.
		$this->_loaded = TRUE;
		
		// Finally return our array.
		return $result;
	}
	
} // Database_Constraint_Foreign