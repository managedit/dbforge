<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database query builder.
 *
 * @package    Database
 * @author     Oliver Morgan
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Database_Query_Builder extends Kohana_Database_Query_Builder {
	
	/**
	 * Compiles an array into a SQL statement.
	 * 
	 * Statements differ from methods as they only take one parameter, and contain a prefix operator
	 * rather than surrounding brackets. These are used for column constraints and table options
	 * 
	 * @example Database_Query_Builder::compile_statement(array('default', '\'foo@bar.baz\''), ' '); => 
	 * DEFAULT 'foo@bar.baz';
	 * 
	 * @param	array	The statement constaining the keyword and the value where appropriate.
	 * @param   string	The operator seperating the keyword with the parameter
	 * @param	array	The callback to be performed on the value.
	 * @return  string	The SQL Syntax.
	 */
	public static function compile_statement( array $statement, $operator = '=', array $value_callback = NULL)
	{
		// If the statement is associative, there is a parameter
		if ( ! is_int(key($statement)))
		{
			// If we have a value callback, apply it to the value
			if ($value_callback === NULL)
			{
				// Return the statement with the operator
				return strtoupper(key($statement)).$operator.reset($statement);
			}
			else
			{
				// Return the statement with the operator
				return strtoupper(key($statement)).$operator.call_user_func($value_callback, reset($statement));
			}
		}
		else
		{
			// Otherwise its just a keyword
			return strtoupper(reset($statement));
		}
	}
	
	/**
	 * Compiles an array into a SQL method.
	 * 
	 * This is method is used when compiling array'ed data into SQL. Use the callback method to do any
	 * formatting on the parameters. If you set data as an array, make sure it only contains one item,
	 * otherwise an error will be thrown.
	 * 
	 * @example
	 * Database_Query_Builder::compile_method(array(
	 * 	'enum' => array('foo', 'bar', 'baz');
	 * ), array(Database::instance(), 'quote'));
	 * Will output:
	 * 'ENUM('foo','bar','baz')'
	 * 
	 * @param	array/string	The data to be processed
	 * @param   array   The callback to perform to perform on all values.
	 * @return  string	The SQL Syntax.
	 */
	public static function compile_method($data, array $value_callback = NULL)
	{
		// If we have an array we may be dealing with params
		if (is_array($data))
		{
			// If there is more then one item in the array, we can't process it
			if (count($data) > 1)
			{
				throw new Kohana_Exception('Parameters arrays must only have one index, to add parameters add them as associated values.');
			}
			
			// If the data is associative, we have parameters
			elseif ( ! is_int(key($data)))
			{
				// Extract the key and values from the array
				$value = reset($data);
				$key   = strtoupper(key($data));
				
				// If the value is null
				if(is_null($value) OR (is_array($value) AND is_null(reset($value))))
				{
					// Return just the key
					return strtoupper($key);
				}
				
				// If the value is an array
				if (is_array($value))
				{
					// Check if we've been given a callback
					if ($value_callback !== NULL)
					{
						// If we have, apply it to every item in the array and implode
						$value = implode(',', array_map($value_callback, $value));
					}
					else
					{
						// Otherwise just implode
						$value = implode(',', $value);
					}
				}
				elseif ($value_callback !== NULL)
				{
					// If we dont have an array but do have a value callback, use it on the value
					$value = call_user_func($value_callback, $value);
				}
				
				// Return the method with brackets around the parameters
				return strtoupper($key).'('.(string) $value.')';

			}
			else
			{
				// Some mug didnt read the API, and just had a keyword in the array. Needless to say, we'll deal with that.
				return strtoupper(reset($data));
			}
		}
		else
		{
			// This person did read the API, so make the keyword uppercase and return it.
			return strtoupper($data);
		}
	}
	
	/**
	 * Compiles a constraint array into SQL syntax.
	 * 
	 * @param	Database	The active database instance.
	 * @param   array   The constraint array.
	 * @return  string	The SQL syntax.
	 */
	public static function compile_constraint( Database $db, array $constraint, $escape = TRUE)
	{
		// Extract the constraint name and any available parameters
		$name = reset($constraint);
		$params = next($constraint);
		
		// Begin the constraint
		$sql = 'CONSTRAINT '.$db->quote_identifier($name).' ';
		
		// If the data is given as an array then there could be parameters
		foreach($params as $key => $data)
		{
			// Compile the constraint and add it to the sql
			$sql .= self::compile_method(array($key => $data)).' ';
		}
		
		// Remove the trailing space
		$sql = rtrim($sql, ' ');
		
		// Finally return the SQL
		return $sql;
	}
	
	/**
	 * Compiles a column array into SQL syntax.
	 * 
	 * @param	Database	The active database instance.
	 * @param   array   The column array.
	 * @return  string	The SQL syntax.
	 */
	public static function compile_column( Database $db, array $column)
	{		
		// Start with the column name
		$sql = $db->quote_identifier($column['name']).' ';
		
		// Compile the datatype
		$sql .= self::compile_method($column['datatype'], array($db, 'quote')).' ';

		// Compile the column constraints
		foreach($column['constraints'] as $name => $data)
		{
			// Use the compile statement method to compile the statement
			$sql .= Database_Query_Builder::compile_statement(
				array($name => $data),
				' ',
				array($db, 'quote')
			).' ';
		}
		
		// Remove the trailing space
		$sql = rtrim($sql, ' ');
		
		// Return the SQL as is.
		return $sql;
	}

} // End Database_Query_Builder
