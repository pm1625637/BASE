#BASE
<pre>
BASE is a data file management system.

I present this project very humbly, and wish to find enthusiastic programmers
to help me make this project a respectable open source.

Data structure
All data is stored in a three-dimensional array.
So, for each data, a coordinate [table] [row] [column].

But where to store the table names in this case?
Table names are stored at indices [0] [0] [n] for example:

$ data [0] [0] [1] = 'tableOnes';
$ data [0] [0] [2] = 'tableTwos';

Column names are stored in line [n] [0] [n] for example:
$ data [1] [0] [1] = 'column1';
$ data [1] [0] [2] = 'column2';
$ data [2] [0] [1] = 'column1';
$ data [2] [0] [2] = 'column2';

Concrete example of a data file in php ...
$ data [0] [0] [1] = 'people';
$ data [1] [0] [1] = 'name';
$ data [1] [0] [2] = 'firstname';
$ data [1] [1] [1] = 'trump';
$ data [1] [1] [2] = 'donald';
$ data [1] [2] [1] = 'obama';
$ data [1] [2] [2] = 'barack';

By convention, the names of the tables will be alphabetical and plural,
the names of the columns will be alphanumeric and singular.

ACCESS TO DATA
Several functions exist for working with arrays in PHP.
However I have created a Model class which works with a three dimensional array.
It allows, among other things, to add, modify or delete a table, a column or a record.
Note that the data file is named data.php for your understanding, but any filename
can be used and you can even connect to different files in one controller.

Example of connection to the database. File format (.php)
The main controller Controller loads, among other things, the model: Get

Functions belonging to the <strong> Model </strong> class

The Get model is the extension of Model.

<pre>
class Get extends Model

connect - Loads the data file.
get_version - Returns the version of the Model class.
get_data - Returns the data array.
set_data - Loads the data array.
count_tables - Returns the number of tables in the data file.
count_columns - Returns the number of columns in a table.
count_max_columns - Returns the number of columns in the table with the most.
count_lines - Returns the number of records in a table.
count_max_lines - Returns the number of records in the table with the most.
export - Create or overwrite a data file with the current data from the table.
import - Imports the data file into the instantiated object of the Model class.
serialize - Create 3 format files (.php, .json, .ser) before exporting.
escape - Replaces single quotes and <and> in the data file.
unescape - Performs the opposite of escape.
verif_alpha - Checks if a value is alphabetic.
verif_alpha_num - Checks if a value is alphanumeric.

The tables

add_table - Add a table to the data file.
edit_table - Allows you to rename a table.
delete_table - Delete a table.
get_id_table - Returns the position of a table in the data file.
get_table - Returns an array containing all the records in a table.
table_exists - Returns true if the table name exists.
get_table_name - Returns the name of a table based on the index.
get_tables - Returns an array containing all table names.
The columns
add_column - Adds a column to a table.
delete_column - Deletes a column from a table.
set_column - Name a column in a table.
get_column_name - Returns the name of a column based on its index.
get_columns - Returns an array of column names from a table.
column_exists - Returns true if a column name exists for a table.
filter_columns - Returns an array containing all the values ​​of array1 that are present in array2. Note that the keys are preserved.
get_id_column - Returns the position of a column in a table.

Coordinates

set_cell - Assigns a value to a coordinate ($ int, $ int, $ int).
get_cell - Returns a value at one coordinate ($ int, $ int, $ int).
del_cell - Deletes a one-coordinate value ($ int, $ int, $ int).
set_line - Save a record.
get_line - Return a record.
add_line - Add a record.
del_line - Delete a record.
get_real_id - Returns the row of a record.
get - Returns a value at a coordinate ($ string, $ int, $ string).
combine - Joins an array of columns to an array of values.

Data query

get_where_unique - Returns a record with a unique column value.
get_where_multiple - Returns records with multiple column values.
get_columns_of - Returns an array containing the column names of a table.
get_field_value_where_unique - Returns a value of a cell, passing a column name whose value is unique as a parameter.
get_record - Returns a record as an associative array column => value.
select - Returns a record based on a choice of columns.
select_where - Returns a record based on a choice of columns and the value of one of the columns.

And many others...
</pre>
