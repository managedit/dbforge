<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database integer column object.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Column_Int extends Database_Column {
	
	/**
	 * The maximum value this column can be.
	 * 
	 * @var int
	 */
	public $max_value;
	
	/**
	 * The minimum value this column can be.
	 * 
	 * @var int
	 */
	public $min_value;
	
	/**
	 * The number of digits the value can be at maximum value.
	 * 
	 * @var int
	 */
	public $scale;
	
	/**
	 * The maximum value this column can be.
	 * 
	 * @var bool
	 */
	public $auto_increment;
	
	public function parameters($set = NULL)
	{
		if ($set === NULL)
		{
			return array($this->scale);
		}
		else
		{
			$this->scale = $set;
		}
	}
	
	protected function _load_schema(array $schema)
	{
		$this->scale = arr::get($schema, 'numeric_scale');
		$this->max_value = arr::get($schema, 'max');
		$this->min_value = arr::get($schema, 'min');
	}
	
	protected function _constraints()
	{
		return array();
	}
	
} // End Database_Column_Int