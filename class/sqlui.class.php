<?PHP

/*rename database e table 

RENAME [DATABASE|TABLE] [old_name] [new_name]
TWIN [DATABASE|TABLE] [origin] [destiny]

*/
set_time_limit(0);
class SQLui {

	public $return=array();
	public $database='';
	public $connected=false;
	public $error='';
	public $success=array('Notice'=>array('Command Successfully'));
	
	public function SQLui(){
		if(!is_dir('database')) mkdir('database',0644);
		if(!is_dir('database/sqlui')) mkdir('database/sqlui',0644);
		if(!is_file('database/sqlui/users.json')) {
			$this->database='sqlui';
			$this->File('users',json_decode('[{"id":"0","level":"0","username":"root","password":"8c772c65c6e2f5a92cf18fb01688cd7b"}]'));
			$this->File('levels',json_decode('[{"id":"0","level":"System"}]'));
			$this->database='';
		}
	}
	public function Connect($user,$pass){
		$this->database='sqlui';
		$users=$this->Open('users');
		foreach($users as $check){
			if ($check['username']==$user&&$check['password']==$this->PasswordKey($pass)){			
				$this->connected=true;
				break;
			}
		}
		if(!$this->connected) $this->error="Invalid login";
		$this->database=isset($_COOKIE['sqlui_take_database'])?$_COOKIE['sqlui_take_database']:'';
	}
	public function Command($commands){
		if(!strstr($commands,'TWIN DATABASE')&&!strstr($commands,'RENAME DATABASE')&&!strstr($commands,'DROP DATABASE')&&!strstr($commands,'CREATE DATABASE')&&!strstr($commands,'TAKE ')&&!strstr($commands,'SHOW DATABASE')&&empty($this->database)||!is_dir("database/".$this->database)) return $this->Error('No database selected');
		while(empty($this->return)){
			if(!empty($this->error))$this->return=$this->Error();
			if(empty($commands)){
				$this->error='Empty querys';
			}else{
				$command=explode(' ',$commands);
				$this->return=method_exists($this,$command[0])?$this->$command[0]($commands):$this->Error('Invalid command');
			}
		}
		if(!empty($this->error))return $this->Error();
		return $this->return;
	}
	public function Database($database){
		setcookie("sqlui_take_database",$database);
		$this->database=$database;
	}
	private function Functions($commands){	
		$command=explode(' ',$commands);
		if(!isset($command[1]))return $this->Error("Bad sintax");
		$function="$command[0]$command[1]";
		if(!method_exists($this,$function))return $this->Error("Command <i>$command[1]</i> not implemented");
		return $this->$function($commands);	
	}
	private function Take($commands){
		$arg=explode(' ',$commands);
		if(empty($arg[1]))return $this->Error("Empty database");
		if(!is_dir("database/$arg[1]"))return $this->Error("Database <I>$arg[1]</I> not exists");
		$this->Database($arg[1]);
		return $this->success;		
	}
	private function Open($table){
		if(is_file("database/$this->database/$table.json")){
			return json_decode(file_get_contents("database/$this->database/$table.json"),true);
		} else {
			$this->error="Table <I>$table</I> not found";
			return '';
		}
	}
	private function Error($erro=''){
		return array('Error'=>array((!empty($erro)?$erro:$this->error)));
	}
	private function Args($commands,$command,$limits){
		$first=strpos($commands,$command)+strlen($command)+1;
		$end=strlen($commands);
		$array=explode(',',$limits);
		foreach($array as $value) if(strstr($commands,$value)){
			$end=strpos($commands,$value);
			break;
		}
		return trim(substr($commands,$first,$end-$first));
	}
	private function Show($commands){
		return $this->Functions($commands);	
	}
	private function showTable($commands){
		$tableName=trim($this->Args($commands,'TABLE','NULL'));
		$table=$this->Open($tableName);
		return array('Fields'=>$this->Fields($table));
	}
	private function ShowTables(){
		$show=array();
		$handle=opendir("database/".$this->database);
		while(false!==($file=readdir($handle)))$file!='.'&&$file!='..'&&strstr($file,'.json')?$show[]=str_replace('.json','',$file):'';
		return array('Tables'=>$show);
	}
	private function ShowDatabase(){
		return array('Selected Database'=>array($this->database));
	}
	private function ShowDatabases(){
		$show=array();
		$handle=opendir("database/");
		while(false!==($file=readdir($handle)))$file!='.'&&$file!='..'&&is_dir("database/$file")?$show[]=$file:'';
		return array('Databases'=>$show);
	}
	private function Select($commands){
		$tableName=str_replace(' LEFT','',$this->Args($commands,'FROM','JOIN,WHERE,ORDER,LIMIT,INTO'));
		$table=$this->Open($tableName);
		if(empty($table))return $this->Error();
		$table=$this->Table($tableName,$table);
		if(strstr($commands,'JOIN'))$table=$this->Join($table,$commands);
		if(!empty($this->error))return $this->Error();
		$fields=$this->Fields($table);
		$requireds=$this->Requireds($fields,$commands,$tableName);
		if(!empty($this->error))return $this->Error();
		$where=strstr($commands,'WHERE')?$this->Where($commands,$fields,$tableName):'';
		if(!empty($this->error))return $this->Error();
		if(strstr($commands,'ORDER BY'))$table=$this->OrderBy($table,$commands,$tableName,$fields);
		if(!empty($this->error))return $this->Error();
		$temp=array();
		foreach($table as $registers)if(empty($where)||eval($where))$temp[]=eval("return $requireds;");
		if(empty($temp[0])||count($table)==1&&empty(join('',$table[0])))return $table;
		$table=$temp;
		if(strstr($commands,'LIMIT'))$table=$this->Limit($table,$commands);
		if(strstr($commands,'DISTINCT'))$table=$this->Distinct($table,$fields);
		if(strstr($commands,'COUNT'))$table=array(array('count'=>count($table)));
		if(strstr($commands,'INTO'))$this->File($this->Args($commands,'INTO','NULL'),$table);
		return $table;
	}
	private function Table($tableName,$table){
		$fields=$this->Fields($table);
		foreach($fields as $key => $value)$fields[$key]="$tableName.$value";
		foreach($table as $index => $registers)$table[$index]=array_combine($fields,$registers);
		return $table;
	}
	private function Requireds($fields,$commands,$tableName){
		$requireds=str_replace('DISTINCT ','',$this->Args($commands,'SELECT','FROM'));
		if(substr($requireds,-1)==')'&&strstr($requireds,'COUNT('))$requireds=str_replace('COUNT(','',substr($requireds,0,-1));
		if($requireds=='*')$requireds=join(',',$fields);
		$requireds=explode(',',$requireds);
		$temp='';
		foreach($requireds as $required){
			$password=0;
			if(strstr($required,'PASSWORD(')){
				$required=substr($required,9,-1);
				$password=1;
			}
			if(!strstr($required,'.'))$required=$tableName.'.'.$required;
			$temp.='"'.$required.'" => '.($password?'$this->PasswordKey(':"").'$registers["'.$required.'"]'.($password?")":"").',';
			if(!in_array($required,$fields))$this->error="Field <I>$required</I> not found";
		}
		return 'array('.substr($temp,0,-1).')';
	}
	private function Join($table,$commands){
		$first=strpos($commands,' LEFT ')<strpos($commands,' JOIN ')?'LEFT':'JOIN';
		$args=$first.' '.$this->Args($commands,$first,'WHERE,ORDER,LIMIT');
		$arg=explode(' ',$args);
		foreach($arg as $key=>$value){
			if($value=='JOIN') {
				$join=$this->Open($arg[$key+1]);
				if(empty($join)){
					return $this->Error();
				}else{
					$join=$this->Table($arg[$key+1],$join);
					$joinFields=$this->Fields($join);
					foreach($joinFields as $value)$null[$value]='';
					$on=explode('=',$arg[$key+3]);
					if(!isset($table[0][$on[0]])&&!isset($join[0][$on[0]])) $this->error="Field $on[0] not found";
					if(!isset($table[0][$on[1]])&&!isset($join[0][$on[1]])) $this->error="Field $on[1] not found";
					if(empty($this->error)){
						$table_left=array();
						if(!isset($table[0][$on[0]]))list($on[0],$on[1])=array($on[1],$on[0]);
						$table=$this->Joins($table,$join,$on,(isset($arg[$key-1])?$arg[$key-1]:''),$null);
					}else{
						return $this->Error();
					}
				}
			}
		}
		return $table;
	}
	private function Joins($table,$join,$on,$left,$null){
		foreach($table as $key => $value){
			$join_key=array_search($value[$on[0]],array_column($join,$on[1]));
			if($join_key>-1){
				$table[$key]=array_merge($value,$join[$join_key]);
				$table_left[]=array_merge($value,$join[$join_key]);
			}else{
				$table[$key]=array_merge($value,$null);
			}
		}
		return $left=='LEFT'?$table_left:$table;
	}
	private function Fields($table){
		return array_keys($table[0]);
	}
	private function Where($commands,$fields,$tableName){
		$where=$this->Password($this->Args($commands,'WHERE','ORDER,LIMIT,INTO'));
		preg_match_all('/([\S]+?)(<>|!=|=|<=|>=|<|>|\sLIKE\s)([\S\s]+?)(AND|OR|$)/', $where, $matches);
		$where='';
		foreach($matches[0] as $key => $value){
			$field=$matches[1][$key];
			$field=strstr($field,'.')?str_replace("$tableName.",'',$field):$field;
			if(!in_array($field,$fields)&&!in_array("$tableName.$field",$fields))$this->error="Field <I>$field</I> not found";
			$signal=$matches[2][$key]=='='?'==':$matches[2][$key];
			$where.='$registers["'.$tableName.'.'.$field.'"]'.$signal.$matches[3][$key].$matches[4][$key]." ";
		}
		if(strstr($where,'LIKE'))$where=$this->Like($tableName,trim($where));
		return "return $where;";
	}
	private function Like($tableName,$where){
		preg_match_all('/([\S]+)( LIKE | NOT LIKE )([\s\S][^AND|OR]+)/', $where, $matches);
		foreach($matches[0] as $key => $value){
			$not=strstr($matches[2][$key],'NOT')?'!':'';
			$needle=trim(str_replace('%','',$matches[3][$key]));
			$field=$matches[1][$key];
			$lenght=strlen(substr(trim($needle),1,-1));
			switch(substr_count($matches[3][$key],"%")){
				case 0:
					$this->error='Operator % not found in LIKE';
				case 1:
					$where=str_replace($value,('substr('.$field.','.(substr($matches[3][$key],1,1)=='%'?"-$lenght":"0,$lenght").')'.(empty($not)?'=':$not).'='.$needle),$where);
				case 2:
					$where=str_replace($value,($not.'strstr('.$field.','.$needle.') '),$where);
			}
		}
		return $where;
	}
	private function OrderBy($table,$commands,$tableName,$fields){
		$orderby=explode(',',$this->Args($commands,'ORDER BY','LIMIT,INTO'));
		foreach($orderby as &$value)if(!strstr($value,'.'))$value="$tableName.$value";
		$this->Check(str_replace(' ASC','',str_replace(' DESC','',$orderby)),$fields);
		if(!empty($this->error))return $this->Error();		
		usort($table, function($a, $b) use($orderby){
			$i=0;
			$cmp=0;
			while($cmp==0&&$i<count($orderby)){
				if(strstr($orderby[$i],' ')) {
					$arg=explode(' ',$orderby[$i]);
				}else{
					$arg[0]=$orderby[$i];
					$arg[1]='ASC';
				}
				$cmp=strcmp($a[$arg[0]],$b[$arg[0]])*($arg[1]=='DESC'?-1:1);
				$i++;
			}
			return $cmp;
		});		
		return $table;
	}
	private function Limit($table,$commands){
		$limit=$this->Args($commands,'LIMIT','INTO');
		$limit=!strstr($limit,',')?"0,$limit":$limit;
		$limits=explode(',',$limit);
		$table=array_slice($table,$limits[0],$limits[1]);
		return $table;
	}
	private function Distinct($table){
		$fields=$this->Fields($table);
		foreach($table as $key => $value)$table[$key]=join('*|:|*',$value);
		$table=array_unique($table);
		foreach($table as $key => $value)$table[$key]=array_combine($fields,explode('*|:|*',$value));
		return $table;
	}
	private function Insert($commands){
		$commands=$this->Password($commands);
		$tableName=explode('(',$this->Args($commands,'INTO','VALUES'));
		$table=$this->Open($tableName[0]);
		$fields=array_keys($table[0]);
		$into=isset($tableName[1])?explode(',',substr($tableName[1],0,-1)):$fields;
		$this->Check($into,$fields);
		if(!empty($this->error))return $this->Error();
		$values=explode("),(",substr($commands,strpos($commands,'VALUES')+7,-1));
		if(empty(join($table[0])))$table=array();
		foreach($values as $value){
			$content=explode((strstr($commands,"','")?"','":'","'),substr($value,1,-1));
			if(count($into)!=count($content))return $this->Error("Differences between fields and values on: <i>".join(',',$content)."</i>");
			$content=array_combine($into,$content);
			foreach($fields as $field)if(!isset($content[$field]))$content[$field]='';
			$table[]=$content;
		}
		$this->File($tableName[0],$table);
		return $this->success;
	}
	private function Truncate($commands){
		$tableName=$this->Args($commands,'TRUNCATE','NULL');
		foreach($this->Fields($this->open($tableName)) as $field)$array[$field]='';
		$this->File($tableName,array('0'=>$array));
		return $this->success;
	}
	private function Update($commands){
		$tableName=$this->Args($commands,'UPDATE','SET');
		$table=$this->Open($tableName);
		$fields=$this->Fields($table);
		$set=explode(',',$this->Password($this->Args($commands,'SET','WHERE')));
		$this->Check(array_map(function($value){return strstr($value,'=',TRUE);},$set),$fields);
		if(!empty($this->error))return $this->Error();
		$where=str_replace("$tableName.",'',(strstr($commands,'WHERE')?$this->Where($commands,$fields,$tableName):''));		
		foreach($table as $key => $registers)if(empty($where)||eval($where))foreach($set as $arg)$table[$key][strstr($arg,'=',TRUE)]=substr(strstr($arg,'='),2,-1);
		$this->File($tableName,$table);
		return $this->success;
	}
	private function Delete($commands){
		$tableName=$this->Args($commands,'FROM','WHERE');
		$table=$this->Open($tableName);
		$fields=$this->Fields($table);
		$delete=explode(',',$this->Args($commands,'DELETE','FROM'));
		if($delete[0]!='*')$this->Check($delete,$fields);
		$where=str_replace("$tableName.",'',(strstr($commands,'WHERE')?$this->Where($commands,$fields,$tableName):''));
		if(!empty($this->error))return $this->Error();
		if($delete[0]=='*'&&empty($where))$this->Truncate("TRUNCATE $tableName");
		foreach($delete as $arg)@$args.='$table[$key]["'.$arg.'"]="";';
		if(count($fields)==count($delete))$delete[0]='*';
		foreach($table as $key => $registers){
			if (empty($where)||eval($where)){
				if($delete[0]=='*'){
					unset($table[$key]);
				}else{
					eval($args);
				}
			}
		}
		if(empty($table)){
			$this->Truncate("TRUNCATE $tableName");
		}else{
			sort($table);
			$this->File($tableName,$table);
		}
		return $this->success;
	}
	private function Create($commands){
		return $this->Functions($commands);	
	}
	private function CreateDatabase($commands){
		$command=explode(' ',$commands);
		if(!isset($command[2]))return $this->Error("No database name");
		if(is_dir("database/$command[2]"))return $this->Error("Database already exists");
		mkdir("database/$command[2]",0644);
		return $this->success;
	}
	private function CreateTable($commands){
		$command=explode(' ',$commands);
		$arg=explode('(',substr($command[2],0,-1));
		if(!isset($arg[1]))return $this->Error("Bad syntax");
		$tableName=$arg[0];
		if(is_file("database/$this->database/$arg[0].json")) return $this->Error("Table <i>$arg[0]</i> already exists");
		foreach(explode(',',$arg[1]) as $value)$array[$value]='';
		$this->File($tableName,array($array));
		return $this->success;
	}
	private function Drop($commands){
		return $this->Functions($commands);	
	}
	private function DropTable($commands){
		$file="database/$this->database/".$this->Args($commands,'TABLE','NULL').".json";
		if(!is_file($file))return $this->Error('Table not found');
		unlink($file);
		return $this->success;
	}
	private function DropDatabase($commands){
		$command=explode(' ',$commands);	
		if($this->database==$command[2])setcookie("sqlui_take_database","",time()-3600);
		if(empty($command[2]))return $this->Error("No database name");
		if($command[2]=='sqlui'||!is_dir("database/$command[2]"))return $this->Error("Invalid database name");
		$this->DelTree("database/$command[2]");
		return $this->success;
	}	
	private function Check($check,$fields){
		foreach($check as $field)if(!in_array($field,$fields))$this->error="Field $field not found.";
	}
	private function Alter($commands){
		$command=explode(' ',$commands);
		$function="Alter$command[3]";
		if(!method_exists($this,$function))return $this->Error("Command <i>$command[3]</i> not implemented");
		return $this->$function($commands);
	}
	private function AlterAdd($commands){
		$tableName=$this->Args($commands,'TABLE','ADD');
		$table=$this->Open($tableName);
		$fields=explode(',',$this->Args($commands,'ADD','NULL'));
		if(empty($fields[0]))return $this->Error('You must specify at least one field');
		foreach($fields as $field)$array[$field]='';
		foreach($table as $key => $register)$table[$key]=array_merge($table[$key],$array);
		$this->File($tableName,$table);
		return $this->success;
	}
	private function AlterDrop($commands){
		$tableName=$this->Args($commands,'TABLE','DROP');
		$table=$this->Open($tableName);
		$adds=explode(',',$this->Args($commands,'DROP','NULL'));
		$this->Check($adds,$this->Fields($table));
		if(!empty($this->error))return $this->Error();
		foreach($table as $key => $register)foreach($adds as $field)unset($table[$key][$field]);
		$this->File($tableName,$table);
		return $this->success;
	}
	private function AlterChange($commands){
		$tableName=$this->Args($commands,'TABLE','CHANGE');
		$table=$this->Open($tableName);
		$fields=explode(',',$this->Args($commands,'CHANGE','NULL'));
		$this->Check(array_map(function($value){return strstr($value,' ',TRUE);},$fields),$this->Fields($table));
		if(!empty($this->error))return $this->Error();
		foreach($table as $key => $register){
			foreach($fields as $field){
				$arg=explode(' ',$field);
				$table[$key][$arg[1]]=$table[$key][$arg[0]];
				unset($table[$key][$arg[0]]);
			}
		}
		$this->File($tableName,$table);
		return $this->success;
	}
	private function Rename($commands){
		$command=explode(' ',$commands);
		$function="Rename$command[1]";
		if(!method_exists($this,$function))return $this->Error("Command <i>$command[3]</i> not implemented");
		return $this->$function($commands);
	}	
	private function RenameTable($commands){
		$command=explode(' ',$commands);
		if(!isset($command[2])||!isset($command[3]))return $this->Error("Bad Syntax");
		if(!is_file("database/$this->database/$command[2].json"))return $this->Error("Table not found");
		rename("database/$this->database/$command[2].json","database/$this->database/$command[3].json");
		return $this->success;
	}
	private function RenameDatabase($commands){
		$command=explode(' ',$commands);
		if(!isset($command[2])||!isset($command[3]))return $this->Error("Bad Syntax");
		if($command[2]=='sqlui'||!is_dir("database/$command[2]"))return $this->Error("Invalid database name");
		rename("database/$command[2]","database/$command[3]");
		$this->Database($command[3]);
		return $this->success;
	}
	private function Twin($commands){
		$command=explode(' ',$commands);
		$function="Twin$command[1]";
		if(!method_exists($this,$function))return $this->Error("Command <i>$command[1]</i> not implemented");
		return $this->$function($commands);
	}
	private function TwinTable($commands){
		$command=explode(' ',$commands);
		if(!isset($command[2])||!isset($command[3]))return $this->Error("Bad Syntax");
		if(!is_file("database/$this->database/$command[2].json"))return $this->Error("Table not found");
		if(is_file("database/$this->database/$command[3].json"))return $this->Error("Table already exists");
		copy("database/$this->database/$command[2].json","database/$this->database/$command[3].json");
		return $this->success;
	}
	private function TwinDatabase($commands){
		$command=explode(' ',$commands);
		if(!isset($command[2])||!isset($command[3]))return $this->Error("Bad Syntax");
		if(!is_dir("database/$command[2]"))return $this->Error("Database not found");
		if(is_dir("database/$command[3]"))return $this->Error("Database already exists");
		$this->DirCopy("database/$command[2]","database/$command[3]");
		return $this->success;
	}
	private function Password($commands){
		preg_match_all("/(PASSWORD\\(['|\"](.+?)['|\"][\\)])/", $commands, $matches);
		foreach($matches[2] as $key => $match)$commands=str_replace($matches[1][$key],"'".$this->PasswordKey($match)."'",$commands);
		return $commands;
	}
	private function PasswordKey($key){
		$password=sha1($key);
		for($i=0;$i<strlen($key);$i++)$password=md5($password);
		return $password;
	}
	private function DirCopy($src,$dst) { 
		$dir=opendir($src);
		@mkdir($dst);
		while(false!==($file=readdir($dir)))if(($file!='.')&&($file!='..'))is_dir("$src/$file")?recurse_copy("$src/$file","$dst/$file"):copy("$src/$file","$dst/$file");
		closedir($dir);
	}	
	private function DelTree($dir){
		$files=array_diff(scandir($dir),array('.','..'));
		foreach($files as $file)(is_dir("$dir/$file")&&!is_link($dir))?delTree("$dir/$file"):unlink("$dir/$file");
		rmdir($dir);
	}	
	private function File($table,$content){
		$handler=fopen("database/$this->database/$table.json",'w+');
		fwrite($handler,utf8_encode(json_encode($content)));
		fclose($handler);
	}
}
?>