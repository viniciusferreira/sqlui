SQLui

SQL user interface for JSON

	By Spiderpoison is licensed under a License Creative Commons Attribution-non-Commercial Share-ShareAlike 3.0 License based on a work at sqlui.sourceforge.net.
	
	Feel free to use these scripts how to give in the tile!!!
	
1. FOREWORD

	The SQLui was created to facilitate the work of developers working with JSON files. The sqlui.class.php class can be used in any script in PHP to faciliar creating and manipulating JSON data files. SQLui is an alternative for developers who do not want to use a conventional base or NoSQL data.

	One minimal of knowledge about PHP, SQL and JSON is expected to interpret this manual.

2. GETTING STARTED

	All SQL statements must be written in capital letters. All references to fields and tables must be carried out with lower case letters. All records should be treated as strings. There should be no line breaks in query.

	The command will always return an array containing the key and the records, how to you can see in the example below.

	Query:
	
		SELECT * FROM test
		
	Return:
	
		Array(
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

	Query:
	
	  CREATE TABLE users
	  
	Return:
	
		Array(
			[error] => Array(
				[0] => Table users already exists
			)
		)
		
	or

	Query:
	
		CREATE TABLE test
		
	Return:
	
		Array(
			[notice] => Array(
				[0] => Command Successfully
			)
		)
	
3. HOW TO DO?

	By placing the files on the server and run the first access, you will automatically create a directory "database" with JSON files "users.json" and "levels.json".

	You must configure the permissions of access to server files to maintain content integrity.

	If you want to change or enter a new user the password, field password must be encrypted as follows below:

	  UPDATE users SET password=PASSWROD('your password') WHERE id='0'
	  
	or

	  INSERT INTO users VALUES('id','name',PASSWROD('your password'))
	  
	After initial setup you can use the system via the web interface, posting commands directly into the bar, or load the class in your PHP script.

	To load the class add this command below.

		$sqlui=false;
		if (!$sqlui) $sqlui = new SQLui();
		$sqlui->Connect('your_user','your_password');
		$sqlui->Database('your_database');
		
	The Database method is optional , you can use TAKE command to select or change a database used.

		$sqlui=false;
		if (!$sqlui) $sqlui = new SQLui();
		$sqlui->Connect('your_user','your_password');
		$sqlui->Command('TAKE database_name');
		
	Default user and password are respectively 'root' and 'pass'. All user validations will be made in databases/sqlui/users.json, so, to add or remove access to the system, manipulate this table.

		$command=$sqlui->Command("UPDATE users SET password=PASSWROD('Your new password') WHERE id='0'");
	
	Return:

		$command=Array(
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
	
4. DEFINITIONS, STATEMENTS AND SYNTAX

	Commands in brackets are optional. The items inside braces are required. All posted values should be treated as strings between single or double quotes.

	4.1. SHOW Syntax

		Display informations about databases or tables.

			SHOW {DATABASES|TABLES|TABLE {tbl_name}}
			
		4.1.1. Display databases.

			SHOW DATABASES
			Array(
				[0] => Array(
					[0] => database_name
					[1] => database_name
				)
			)
			
		4.1.2. Displays selected database.

			SHOW DATABASE
			Array(
				[0] => Array(
					[0] => database_name
			)
			
		4.1.3. Displays database tables.

			SHOW TABLES
			Array(
				[0] => Array(
					[0] => table_name
					[1] => table_name
					...
				)
			)
			
		4.1.4. Displays tables fields.

			SHOW TABLE table_name
			Array(
				[0] => Array(
					[0] => field_name
					[1] => field_name
					...
				)
			)
		
	4.2. CREATE Syntax

		Use this function to create tables or databases.

			CREATE {DATABASE {db_name}|TABLE {tbl_name({col[,col...]})}
			
		4.2.1. Create Database.

			CREATE DATABASE test
			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
			
		4.2.2. Creates a new table.

			CREATE TABLE test(id,name)
			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)   
		
	4.3. DROP Syntax

		You can delete your databases and tables.

			DROP [DATABASE {db_name}|TABLE {tbl_name}]
			
		4.3.1. Delete database.

			DROP DATABASE test
			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
			
		4.3.2. Delete a table.

			DROP TABLE test
			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
		
	4.4. TAKE syntax
		
		Select or change a database.

			TAKE db_name
		
		Example:

			TAKE database
			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
		
	4.5. ALTER TABLE Syntax

		Changes a table structure.

			ALTER TABLE {tbl_name}
				{ADD|DROP col_name[col_name...]}|{CHANGE col_name new_name[,col_name new_name...]}

		4.5.1. Add a field

			ALTER TABLE test ADD field
			
		4.5.2. Delete a field

			ALTER TABLE test DROP field
			
		4.5.3. Change name of a field

			ALTER TABLE test CHANGE field_1 new_name_1,field_2 new_name_2
			
		All Return:

			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
		
	4.6. TRUNCATE Syntax

		Truncate a table.

			TRUNCATE {tbl_name}
		Example:

			TRUNCATE test
			Array(
				[notice] => Array(
					[0] => Command Successfully
				)
			)
		
	4.7. SELECT Syntax

		Select the contents of a table.

			SELECT [DISTINCT] [COUNT] {tbl_name.col_name|col_name|*}
				FROM {tbl_name} [[LEFT]JOIN {join_tbl_name} ON {where_condition}]
					[WHERE {where_condition}]
						[ORDER BY {col_name}[ASC | DESC]]
						[LIMIT {[offset,]row_count}]
						[INTO 'file_name']
		Example:

			SELECT tbl1.col,tbl2.col FROM tbl1 JOIN tbl2 ON tbl2.col=tbl1.col LIMIT 2
			Array(
				[0] => Array(
					[tbl1.col] => string
					[tbl2.col] => string
				)
				[1] => Array(
					[tbl1.col] => string
					[tbl2.col] => string
				)
			)
		
	4.8. COUNT Syntax

		Return a count matches a query.

			COUNT(col_name[,col_name...]|*)

		Example:

				SELECT COUNT(*) FROM tbl1
			Array(
				[0] => Array (
					[count] => 1
				)
			)
		
	4.9. WHERE Syntax

		Used to filter records.

			WHERE {tbl_name.col_name|col_name}{operator}{'string'}
				[{AND|OR} {tbl_name.col_name|col_name}{operator}{'string'}...]
		
		Where operators are.

		Operator	Result
		=		- equal
		<>		- not equal
		!=		- not equal
		>		- greater than
		<		- less than
		>=		- greater than or equal
		<=		- less than or equal
		LIKE	- search for a pattern
		
		Example

			SELECT col FROM tbl WHERE col='needle'
			Array(
				[0] => Array (
					[col] => needle
				)
				[1] => Array (
					[col] => needle
				)
			)
		
	4.10. LIKE Syntax

		Used to filter records using a pattern.

			[NOT] LIKE {'[operator]string[operator]'}       
		Where, operators are a signal %, and can be used this way.

		Operator	Find word
		'string%'	starting with
		'%string'	ends
		'%string%'	contains
		Example:

			 SELECT col FROM tbl WHERE col LIKE 'ne%'
			Array(
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

	4.11. INSERT INTO Syntax

		Insert new records in a table.

			INSERT INTO {tbl_name[(col_name,...)]}
				VALUES{('string'[,'string'...])}[,('string'[,'string'...])]
		Example:

			INSERT INTO tbl(id,name) VALUES('0','name 0')
			Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)   
		When you insert more than one record, use this syntax to make the process faster.

			INSERT INTO tbl VALUES('0','name 0'),('1','name 1'),('2','name 2')
			Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)
		
	4.12. UPDATE Syntax

		Update records in a table.

			UPDATE {tbl_name} SET col_name1={'string'}[,col_name2={'string'}...]
				[WHERE {where_condition}]
		Example:

			UPDATE tbl SET col1='string',col2="string" WHERE col1='test'
			Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)

	4.13. DELETE Syntax

		Delete records in a table.

			DELETE {col_name|*} FROM {tbl_name} [WHERE where_condition]
		Example:

			DELETE col FROM tbl WHERE col='test'
			Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)
		
	4.14. PASSWORD Syntax

		Encrypts you text using SQLui Password.

			PASSWORD(col_name|'string')
			
		4.14.1 Apply encryption in the selected field.

			SELECT PASSWORD(field) FROM tbl1
			Array(
				[0] => Array (
					[0] => 8c772c65c6e2f5a92cf18fb01688cd7b
				)
			)
			
		4.14.2 Apply encryption on update or insert.

			INSERT INTO users VALUES('1','name',PASSWORD('password'))
		or

			UPDATE users SET password=PASSWORD('password')
			Array(
				[notice] => Array (
					[0] => Command Successfully
				)
			)
			
		4.14.3 Apply encryption on where clause.

			SELECT * FROM users WHERE password=PASSWORD('pass')
			Array(
				[0] => Array (
					[0] => 0
					[0] => user
					[0] => 8c772c65c6e2f5a92cf18fb01688cd7b
				)
			)
	
© 2015 SQLui. All rights reserved.