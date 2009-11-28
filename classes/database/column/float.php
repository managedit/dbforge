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
	
	public function __construct( & $table, $datatype)
	{
		// Get the properties out of the datatype
		$this->is_exact = arr::get($datatype, 'exact', FALSE);

		parent::__construct($table, $datatype);
	}
}