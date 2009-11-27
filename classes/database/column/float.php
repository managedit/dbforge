<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table object.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Kohana_Database_Table_Column_Float extends Database_Table_Column_Int {
	
	public $exact;
	
	public function __construct($datatype, $exact = false)
	{
		$this->exact = $exact;
		
		parent::__construct($datatype);
	}
}