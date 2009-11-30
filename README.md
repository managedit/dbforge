# DBForge for Kohana 3.0

##Installation

###Requirements
* Kohana 3.x
* Database Module

###Instructions
* Copy files to your modules folder.
* Add DBForge to your list of modules in your boostrap.
* Happy coding!

## QuickStart Guide
Below is a few quick tutorials of how to use this library. Full API listings are given at the end.

#### Creating a table.
The Database_Table class is a object mapping of a database table. To create a new table object you simply type:

	$table = new Database_Table;

If you want to assign the table to the non-default database instance, you have to specify it in this construct or using the database property.

	$table = new Database_Table($database);
OR
	$table = new Database_Table;
	$table->database = $database;

Before creating the table we must give it a name and some columns.

	$table->name = 'users';

This example is going to create a basic user table. The dbforge library will automatically add the table prefix defined in the database you have assigned to the table, so do NOT add it here!

	$column = Database_Column::factory();
	$column->datatype = 'int';
	$column->is_nullable = TRUE;
	$column->is_auto_increment = TRUE;



## API Reference

### Database_Table
Below are a list of short tutorials and API references to do with the Database_Table class.

#### Basic / Common API
These are common properties / methods that you would use on this object.

* `database` - Gets or sets the parent database.
* `name` - Gets or sets the name of the table.
* `loaded()` - Gets the boolean value of whether the table object exists and has been loaded from the database.
* `truncate()` - Truncates the table, this will wipe all records in that table, and reset the auto_increment key.
* `columns($like)` - Returns all the collumns objects assigned to the table object. If you specify the like parameter it will return the column with the same name as that value. If no column is found it will throw an error.
* `add_column( Database_Column $column)` - Assigns a column to the table. The is taken by reference and so the table is automatically asigned as its parent table when you use this method on it. If the table is loaded the column will be automatically added to the database table. Otherwise it will be stored in an array until the * `create()` method is called.
* `drop_column($column_name)` - The opposite of `add_column()` this will either drop the column from the database table if it is loaded, or will remove the item from the array if it is not. If no column is found with that name an error will be thrown.
* `drop()` - This method is only available if the table is loaded. It will drop the table from the database, removing all data.
* `create()` - This method is only available if the table is NOT loaded. To duplicate a table use the `clone` keyword, a cloned table will have its loaded value set to false. This method will create the table will all keys and columns associated with it. The table will automatically be loaded if this process completes successfully allowing you to then drop / alter it.

#### Advanced API
* `compile_constraints` - Compiles the table's primary key and unique key constraints and returns them as a SQL-92 string. This method is useful only for code generation.
