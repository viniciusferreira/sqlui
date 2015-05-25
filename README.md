SQLui 1.0

SQL user interface for JSON


By Spiderpoison is licensed under a License Creative Commons Attribution-non-Commercial Share-ShareAlike 3.0 License based on a work at sqlui.sourceforge.net.

Feel free to use these scripts how to give in the tile!!!

FOREWORD


The SQLui was created to facilitate the work of developers working with JSON files.
 The sqlui.class.php class can be used in any script in PHP to faciliar creating and manipulating JSON data files.
 SQLui is an alternative for developers who do not want to use a conventional base or NoSQL data.

 One minimal of knowledge about PHP, SQL and JSON is expected to interpret this manual. 

GETTING STARTED


All SQL statements must be written in capital letters.
 All references to fields and tables must be carried out with lower case letters.
 All records should be treated as strings.
 There should be no line breaks in query.

 The command will always return an array containing the key and the records, how to you can see in the example below.


	Query: SELECT * FROM test		
	Return: Array(
			[0] => Array(
					[field_name] => value
					[field_name] => value
					...
				)							
			[1] => Array(
					[field_name] => value
					[field_name] => value
					...
				)							
			)
	
The class yet can return arrays with 'notice' or 'error' in the case of failures.


	Query: CREATE TABLE users			
	Return: Array(
			[error] => Array(
				[0] => Table users already exists
			)
		)
		
	
or

	Query: CREATE TABLE test		
	Return: Array(
			[notice] => Array(
				[0] => Command Successfully
			)
		)
	


HOW TO DO?


By placing the files on the server and run the first access, you will automatically create a directory "database" with JSON files "users.json" and "levels.json".

 You must configure the permissions of access to server files to maintain content integrity.

 If you want to change or enter a new user the password, field password must be encrypted as follows below:

	$your_passrowd = SHA1(MD5('Your Password'));
			
After initial setup you can use the system via the web interface, posting commands directly into the bar, or load the class in your PHP script.

 To load the class add this command below.
 
	$sqlui=false;
	if (!$sqlui) $sqlui = new SQLui();
	$sqlui->Connect('your_user','your_password');
	$sqlui->Database('your_database');
	
Default user and password are respectively 'root' and 'pass'.
 All user validations will be made in databases/sqlui/users.json, so, to add or remove access to the system, manipulate this table.

	Example: $command=$sqlui->Command("UPDATE users SET password='".SHA1(MD5('Your new password'))."' WHERE id='0'");
	
	
	Return:	$comand=Array(
			[notice] => Array(
				[0] => Command Successfully
			)						
		)
	
And to read the records:

	foreach($command as $value){
		echo $value[col_name];
	}
	
And this print

	Command Successfully
	


DEFINITIONS, STATEMENTS AND SYNTAX

Commands in brackets are optional.
The items inside braces are required.
All posted values should be treated as strings between single or double quotes.



SHOW TABLES Syntax

Displays database tables. 
	SHOW TABLES
	
		Query: SHOW TABLES
		Return: Array(
				[0] => Array(
					[0] => levels
					[1] => users
				)
			)
	

CREATE TABLE Syntax

Creates a new table. 
	CREATE {tbl_name({col[,col...]})
	
		Query: CREATE TABLE test
		Return: Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)	
	

DROP TABLE Syntax

Delete a table. 
	DROP TABLE {tbl_name}
	
		Query: DROP TABLE test
		Return: Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
	

ALTER TABLE Syntax

Changes a table structure. 
	ALTER TABLE {tbl_name}
		{ADD|DROP col_name[col_name...]}|{CHANGE col_name new_name[,col_name new_name...]}
		
		Query: ALTER TABLE test ADD field
		Query: ALTER TABLE test DROP field
		Query: ALTER TABLE test CHANGE field_1 new_name_1,field_2 new_name_2
		Return: Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
	

TRUNCATE Syntax

Truncate a table. 
	TRUNCATE {tbl_name}
	
		Query: TRUNCATE test
		Return: Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
	

SELECT Syntax

Select the contents of a table. 
	
	SELECT [DISTINCT] [COUNT] {tbl_name.col_name|col_name|*}
		FROM {tbl_name} [[LEFT]JOIN {join_tbl_name} ON {where_condition}]
			[WHERE {where_condition}]
				[ORDER BY {col_name}[ASC | DESC]]
				[LIMIT {[offset,]row_count}]
				[INTO 'file_name']
				
				
	Query: SELECT tbl1.col,tbl2.col FROM tbl1 JOIN tbl2 ON tbl2.col=tbl1.col LIMIT 2
	Return: Array(
			[0] => Array(
				[tbl1.col] => string
				[tbl2.col] => string
			)
			[1] => Array(
				[tbl1.col] => string
				[tbl2.col] => string
			)
		)		
	

COUNT Syntax

Return a count matches a query. 
	COUNT(col_name[,col_name...]|*)
	
		Query: SELECT COUNT(*) FROM tbl1
		Return: Array(
				[0] => Array (
					[count] => 1
				)
			)
	

WHERE Syntax

Used to filter records. 
	
	WHERE {tbl_name.col_name|col_name}{operator}{'string'}
		[{AND|OR} {tbl_name.col_name|col_name}{operator}{'string'}...]
	

Where operators are. 
	= 	-	equal
	<>	-	not equal
	!=	-	not equal
	>	-	greater than
	<	-	less than	
	>=	-	greater than or equal
	<=	-	less than or equal
	LIKE	-	search for a pattern	
	
		Query: SELECT col FROM tbl WHERE col='needle'
		Return: Array(
				[0] => Array (
					[col] => needle
				)
				[1] => Array (
					[col] => needle
				)
			)
		

LIKE Syntax

Used to filter records using a pattern. 
	
	[NOT] LIKE {'[operator]string[operator]'}		
	

Where, operators are a signal %, and can be used this way. 
	'string%'	-	starting with
	'%string'	-	ends
	'%string%'	-	contains
	
		Query: SELECT col FROM tbl WHERE col LIKE 'ne%'
		Return: Array(
				[0] => Array (
					[col] => needle
				)
				[1] => Array (
					[col] => never
				)
				[1] => Array (
					[col] => next
				)
			)
		

INSERT INTO Syntax

Insert new records in a table. 
	INSERT INTO {tbl_name[(col_name,...)]}
		VALUES{('string'[,'string'...])}[,('string'[,'string'...])]
				
		Query: INSERT INTO tbl(id,name) VALUES('0','name 0')
		Return: Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)	
	

When you insert more than one record, use this syntax to make the process faster. 
		Query: INSERT INTO tbl VALUES('0','name 0'),('1','name 1'),('2','name 2')
		Return: Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)	
	

UPDATE Syntax

Update records in a table. 
	UPDATE {tbl_name} SET col_name1={'string'}[,col_name2={'string'}...]
		[WHERE {where_condition}]
		
		Query: UPDATE tbl SET col1='string',col2="string" WHERE col1='test'
		Return: Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)
	

DELETE Syntax

Delete records in a table. 
	DELETE {col_name|*} FROM {tbl_name} [WHERE where_condition]
	
		Query: DELETE col FROM tbl WHERE col='test'
		Return: Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)
	


© 2015 SQLui. All rights reserved.
