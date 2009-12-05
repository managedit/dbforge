<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table float column.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Column_Float extends Database_Column_Int {

	// Whether the number is exact
	public $is_exact;

	protected function _load_schema($information_schema)
	{
		// Set whether the floating number is exact or not, default FALSE
		$this->is_exact = arr::get($information_schema, 'exact', FALSE);
	}
	
	protected function _compile_parameters()
	{
		// FLOAT(SCALE, PRECISION)
		return array(
			$this->scale,
			$this->precision
		);
	}
}