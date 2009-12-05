<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for DROP statements.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Query_Builder_Drop extends Database_Query_Builder {
	
	// The object thats going to be dropped.
	protected $_name;
	
	// The type of the object we're going to drop.
	protected $_drop_type;
	
	public function __construct($type, $name)
	{
		// Set the type of the object we're about to drop.
		$this->_drop_type = $type;
		
		// Set the object we're going to drop.
		$this->_name = $name;
		
		// Because mummy says so.
		parent::__construct(Database::DROP, '');
	}
	
	public function compile(Database $db)
	{
		// Lets identify the type
		switch(strtolower($this->_drop_type))
		{
			// We're dropping an entire database!
			case 'database':
				return 'DROP DATABASE '.$db->quote($this->_name);
			
			// Just a table to be dropped.
			case 'table':
				return 'DROP TABLE '.$db->quote_table($this->_name);
				
			// A column to be dropped.
			case 'column':
				return 'DROP COLUMN '.$db->quote_identifier($this->_name);
				
			// A column to be dropped.
			case 'constraint':
				return 'DROP CONSTRAINT '.$db->quote_identifier($this->_name);
				
			// Something we did not recognise.
			default:
				throw new Database_Exception('Invalid drop type :typ', array(
					'typ' => $this->_type
				));
		}
	}
	
	public function reset()
	{
		// Reset objects.
		$this->_type =
		$this->_name = NULL;
	}
	
} //END Database_Query_Builder_Drop