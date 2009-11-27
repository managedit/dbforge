<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database table binary column.
 *
 * @package		DBForge
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Database_Table_Column_Binary extends Database_Table_Column_String {
	
	// Well, of course its going to be binary!
	public $is_binary = TRUE;
	
}