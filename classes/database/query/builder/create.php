<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for CREATE statements.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Query_Builder_Create extends Database_Query_Builder {
	
	// The table object we're working with.
	protected $_table;
	
	public function __construct( array $table)
	{		
		// Set the table array
		$this->_table = $table;
		
		// Because mummy says so
		parent::__construct(Database::CREATE, '');
	}
	
	public function compile(Database $db)
	{
		// Start with the basic syntax.
		$sql = 'CREATE TABLE '.$db->quote_table($this->_table['name']);
		
		// You are allowed to create a table without any columns. Dont ask me why.
		if(count($this->_table['columns']) > 0)
		{
			// Get ready for the column data.
			$sql .= ' (';
			
			// Compile the columns in the normal way.
			foreach($this->_table['columns'] as $name => $data)
			{
				$sql .= Database_Query_Builder::compile_column($db, $data).',';
			}
			
			// Compile constraints in a normal way
			foreach($this->_table['constraints'] as $name => $data)
			{
				$sql .= Database_Query_Builder::compile_constraint($db, $data).',';
			}
			
			// Seperate the columns with commars, and add the table constraints at the end.
			$sql = rtrim($sql, ',').') ';
		}
		
		// Process table options
		foreach($this->_table['options'] as $key => $option)
		{
			$sql .= Database_Query_Builder::compile_statement(array($key => $option)).' ';
		}
		
		// Remove the trailing space.
		return rtrim($sql, ' ').';';
	}
	
	public function reset()
	{
		// Reset the table object.
		$this->_table = NULL;
	}
	
} //END Database_Query_Builder_Create