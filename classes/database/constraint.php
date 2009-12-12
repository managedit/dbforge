<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table constraint.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
abstract class Database_Constraint {
	
	/**
	 * Initiate a PRIMARY KEY constraint.
	 *
	 * @param	array	The list of columns to make up the primary key.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Primary	The constraint object.
	 */
	public static function primary_key(array $keys, $name = NULL)
	{
		return new Database_Constraint_Primary($keys, $name);
	}
	
	/**
	 * Initiate a FOREIGN KEY constraint.
	 *
	 * @param	string	The name of the column that represents the foreign key.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Foreign	The constraint object.
	 */
	public static function forgeign_key($identifier, $name = NULL)
	{
		return new Database_Constraint_Foreign($identifier, $name);
	}
	
	/**
	 * Initiate a UNIQUE constraint.
	 *
	 * @param	string	The name of the column thats unique.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Unique	The constraint object.
	 */
	public static function unique($key, $name = NULL)
	{
		return new Database_Constraint_Unique($key, $name);
	}
	
	/**
	 * Initiate a CHECK constraint.
	 *
	 * @param	string	The name of the column that's being checked.
	 * @param	string	The operator used in the conditional statement.
	 * @param	string	The value to compare it with.
	 * @param	string	The name of the key, if this is not set, one will generated for you.
	 * @return	Database_Constraint_Check	The constraint object.
	 */
	public static function check($identifier, $operator, $value, $name = NULL)
	{
		return new Database_Constraint_Check($identifier, $operator, $value, $name);
	}
	
	/**
	 * @var Name of the key.
	 */
	public $name;
	
	/**
	 * @var	Database_Table	The table object.
	 */
	public $table;
	
	// Whether the constraint is loaded or not.
	protected $_loaded;
	
	/**
	 * Compiles the constraint into a DBForge constraint array.
	 *
	 * @param	Database	The database to compile the constraint with.
	 * @return	array	The constraint array.
	 */
	abstract public function compile( Database $db);
	
	/**
	 * Drops the constraint from the table.
	 *
	 * @return	void
	 */
	public function drop()
	{
		// If the constraint is loaded, attempt to remove it
		if($this->loaded())
		{
			// This is a nasty hard coded hack, because MySQL doesnt follow SQL-92
			switch(str_replace('Database_', '', get_class($this->table->database)))
			{
				case 'MySQL':
				{
					switch(str_replace('Database_Constraint_', '', get_class($this)))
					{
						case 'Check':
							break; // Do nothing, MySQL doesnt support dropping check constraints
							
						case 'Foreign':
						case 'Unique':
						{
							// MySQL calls foreign and unique constraints "indexes"
							DB::alter($this->table->name)
								->drop($this->name, 'index')
								->execute($this->table->database);
							break;
						}
							
						case 'Primary':
						{
							// There can only be one primary key, so no identifier is needed
							DB::alter($this->table->name)
								->drop(NULL, 'PRIMARY KEY')
								->execute($this->table->database);
							break;	
						}	
					}
					break;
				}
				default:
				{
					// All normal databases call constraints "constraints".
					DB::alter($this->table->name)
						->drop($this->name, 'constraint')
						->execute($this->table->database);
					break;
				}
			}
		}
	}
	
	/**
	 * Drops the constraint from the table.
	 *
	 * @return	void
	 */
	public function create()
	{
		$this->table->add_constraint($this);
	}
	
	/**
	 * Updates a constraint object
	 *
	 * @return	void
	 */
	public function update()
	{
		// First drop the constraint
		$this->drop();
		
		// Then re-create it/
		$this->create();
	}
	
	/**
	 * Whether the constraint is loaded or not.
	 *
	 * @return	bool
	 */
	public function loaded()
	{
		// If either we have loaded set to true
		return $this->_loaded;
	}
}