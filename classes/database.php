<?php defined('SYSPATH') or die('No direct script access.');

abstract class Database extends Kohana_Database {
	
	// Query types
	const CREATE =  5;
	const ALTER =  6;
	const DROP =  7;
	const TRUNCATE =  8;
	
}