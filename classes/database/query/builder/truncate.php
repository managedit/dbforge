<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder for TRUNCATE statements.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Query_Builder_Truncate extends Database_Query_Builder {
	
	// The name of the table we're about to truncate
	protected $_table;
	
	public function __construct($table)
	{
		// Set the table object.
		$this->_table = $table;
		
		// Because mummy says so.
		parent::__construct(Database::DROP, '');
	}
	
	public function compile( Database $db)
	{
		// Return the SQL, its straightforward.
		return 'TRUNCATE TABLE '.$db->quote_table($this->_table);
	}
	
	public function reset()
	{
		// Reset the table name
		$this->_table = NULL;
	}
	
} //END Database_Query_Builder_Truncate