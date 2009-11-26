<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Database_Table_Column {
	
	// Editable
	public $name;
	public $default;
	public $is_nullable;
	public $is_primary;
	public $datatype;
	public $is_unique;
	
	// Not editable
	public $ordinal_position;
	public $table;
	
	protected $_loaded = false;
	protected $_original_name;
	
	public function __construct($datatype)
	{
		$this->set_datatype($datatype);
	}
	
	public function load_schema( & $table, $schema)
	{
		// Set the table by reference.S
		$this->table =& $table;
		
		// Set the original name
		$this->_original_name = $this->name = $schema['COLUMN_NAME'];
		
		// Set some ISO standard params
		$this->default = $schema['COLUMN_DEFAULT'];
		$this->is_nullable = $schema['IS_NULLABLE'] == 'YES';
		$this->is_primary = $schema['COLUMN_KEY'] == 'PRI';
		$this->ordinal_position = $schema['ORDINAL_POSITION'];
		
		// Lets fetch any aditional parametres eg enum()
		preg_match("/^\S+\((.*?)\)/", $schema['COLUMN_TYPE'], $matches);
				
		if(isset($matches[1]))
		{
			// Replace all quotations
			$params = str_replace('\'', '', $matches[1]);
					
			if(strpos($params, ',') === false)
			{
				// Return value as it is
				$this->datatype = array($schema['DATA_TYPE'], $params);
			}
			else
			{
				// Comma seperated values are exploded into an array
				$this->datatype = array($schema['DATA_TYPE'], explode(',', $params));
			}
		}
		else
		{
			// No additional params
			$this->datatype = array($schema['DATA_TYPE']);
		}
		
		// Column has been loaded from the database.
		$this->_loaded = true;
	}
	
	public function create()
	{
		// Alter the table
		DB::alter($this->table)
			->add($this)
			->execute();
	}
	
	public function drop()
	{
		// Drops the column
		DB::alter($this->table)
			->drop($this)
			->execute();
	}
	
	public function loaded()
	{
		return $this->_loaded;
	}
	
	public function update()
	{
		// Updates the existing column
		DB::alter($this->table)
			->modify($this, $this->_original_name)
			->execute();
	}
	
	public function set_datatype($type, array $params = NULL)
	{
		$this->datatype = array(
			$type,
			$params
		);
	}
	
	public function compile()
	{
		return Database_Query_Builder::compile_column($this);
	}
	
	public function compile_datatype()
	{
		// Get the table's database
		$db = $this->table->database;
		
		// Extract the datatype
		list($type, $params) = $this->datatype;
		
		$sql = strtoupper($type);
		
		// Compile datatype params
		if(is_array($params) AND count($params) > 0)
		{
			// Add it to the sql
			$sql .= '('.implode(array_map(array($db, 'escape'), $params), ',').')';
		}
		elseif (isset($params))
		{			
			// Add it to the sql
			$sql .= '('.$db->escape($params).')';
		}
		
		return $sql;
	}
	
	public function compile_constraints()
	{
		$db = $this->table->database;
		
		$sql = '';
		
		// Compile nullable constraint
		if( ! $this->is_nullable)
		{
			$sql .= 'NOT NULL ';
		}
		
		// Compile default constraint
		if($this->default != NULL)
		{
			$sql .= 'DEFAULT '.$db->escape($column->default);
		}
		
		return $sql;
	}
}