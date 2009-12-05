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
	
	// What the constraint references
	protected $_references;
	
	// The action taken on update
	protected $_on_update = 'no action';
	
	// The action taken on delete
	protected $_on_delete = 'no action';
	
	// The column in question.
	protected $_column;
	
	// The name of the constraint.
	protected $_name;
	
	// A list of supported actions
	protected $_actions = array(
		'cascade',
		'restrict',
		'no action',
		'set null',
		'set default'
	);
	
	public function __construct($column, $name = NULL)
	{
		// If the name is not given, dont set it
		if($name !== NULL)
		{
			$this->_name = $name;
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
		if (in_array($type, $this->_actions, FALSE))
		{
			$this->_on_update = $type;
		}
		else
		{
			// Otherwise throw an error
			throw new Kohana_Exception('The foreign key constraint action act was not recognised', array(
				'act'	=> $type
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
		if (in_array($type, $this->_actions, FALSE))
		{
			$this->_on_delete = $type;
		}
		else
		{
			// Otherwise throw an exception
			throw new Kohana_Exception('The foreign key constraint action act was not recognised', array(
				'act'	=> $type
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
		if( ! isset($this->_name))
		{
			$this->_name = 'fk_'.$this->_column.'_'.$table.'_'.$column;
		}
		
		// Compile a default array supported by the DBForge compiler.
		$result = array(
			'name'	=> $this->_name,
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
		
		// Finally return our array.
		return $result;
	}
	
} // Database_Constraint_Foreign