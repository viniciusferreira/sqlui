<?PHP
set_time_limit(0);
class SQLui {

	public $return=array();
	public $database='';
	public $connected=false;
	public $error='';
	
	function SQLui(){
		if(!is_dir('database')) mkdir('database',0644);
		if(!is_dir('database/sqlui')) mkdir('database/sqlui',0644);
		if(!is_file('database/sqlui/users.json')) {
			$this->database='sqlui';
			$this->File('users',json_decode('[{"id":"0","level":"0","username":"root","password":"9d4e1e23bd5b727046a9e3b4b7db57bd8d6ee684"}]'));
			$this->File('levels',json_decode('[{"id":"0","level":"System"}]'));
			$this->database='';
		}
	}
	function Open($table){
		if(is_file("database/$this->database/$table.json")){
			return json_decode(file_get_contents("database/$this->database/$table.json"),true);
		} else {
			$this->error="Table <I>$table</I> not found";
			return '';
		}
	}
	function Connect($user,$pass){
		$this->database='sqlui';
		$users=$this->Open('users');
		foreach($users as $check){
			if ($check['username']==$user&&$check['password']==sha1($pass)){
				$this->connected=true;
				break;
			}
		}
		if(!$this->connected) $this->error="Invalid login";
		$this->database='';
	}
	function Database($database){
		$this->database=$database;
	}
	function Command($commands){
		if(empty($this->database)||!is_dir("database/".$this->database))$this->return=$this->Error('Invalid database');
		while(empty($this->return)){
			if(!empty($this->error))$this->return=$this->Error();
			if(empty($commands)){
				$this->error='Empty querys';
			}else{
				$command=explode(' ',$commands);
				$this->return=method_exists($this,$command[0])?$this->$command[0]($commands):$this->Error('Invalid command');
			}
		}
		return $this->return;
	}
	function Error($erro=''){
		return array('error'=>array((!empty($erro)?$erro:$this->error)));
	}
	function Args($commands,$command,$limits){
		$first=strpos($commands,$command)+strlen($command)+1;
		$end=strlen($commands);
		$array=explode(',',$limits);
		foreach($array as $value) if(strstr($commands,$value)){
			$end=strpos($commands,$value);
			break;
		}
		return trim(substr($commands,$first,$end-$first));
	}
	function Show($commands){
		$command=explode(' ',$commands);
		if(!isset($command[1]))return $this->Error("Bad sintax");
		$function="Show$command[1]";
		if(!method_exists($this,$function))return $this->Error("Command <i>$command[1]</i> not implemented");
		return $this->$function($commands);	
	}
	function ShowTables(){
		$show=array();
		$handle=opendir("database/".$this->database);
		while(false!==($file=readdir($handle)))$file!='.'&&$file!='..'&&strstr($file,'.json')?$show[]=str_replace('.json','',$file):'';
		return array($show);
	}
	function Select($commands){
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
		if(empty($temp[0])||count($table)==1&&empty(join('',$table[0])))return $this->Error('Data not found');
		$table=$temp;
		if(strstr($commands,'LIMIT'))$table=$this->Limit($table,$commands);
		if(strstr($commands,'DISTINCT'))$table=$this->Distinct($table,$fields);
		if(strstr($commands,'COUNT'))$table=array(array('count'=>count($table)));
		if(strstr($commands,'INTO'))$this->File($this->Args($commands,'INTO','NULL'),$this->FieldName($table));
		return $table;
	}
	function Table($tableName,$table){
		$fields=$this->Fields($table);
		foreach($fields as $key => $value)$fields[$key]="$tableName.$value";
		foreach($table as $index => $registers)$table[$index]=array_combine($fields,$registers);
		return $table;
	}
	function Requireds($fields,$commands,$tableName){
		$requireds=str_replace('DISTINCT ','',$this->Args($commands,'SELECT','FROM'));
		if(substr($requireds,-1)==')')$requireds=str_replace('COUNT(','',substr($requireds,0,-1));
		if($requireds=='*')$requireds=join(',',$fields);
		$requireds=explode(',',$requireds);
		$temp='';
		foreach($requireds as $required){
			if(!strstr($required,'.'))$required=$tableName.'.'.$required;
			$temp.='"'.$required.'" => $registers["'.$required.'"],';
			if(!in_array($required,$fields))$this->error="Field <I>$required</I> not found";
		}
		return 'array('.substr($temp,0,-1).')';
	}
	function Join($table,$commands){
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
	function Joins($table,$join,$on,$left,$null){
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
	function Fields($table){
		return array_keys($table[0]);
	}
	function Where($commands,$fields,$tableName){
		$where=$this->Args($commands,'WHERE','ORDER,LIMIT,INTO');
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
	function Like($tableName,$where){
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
	function OrderBy($table,$commands,$tableName,$fields){
		$orderby=explode(',',str_replace(' ASC','',str_replace(' DESC','',$this->Args($commands,'ORDER BY','LIMIT,INTO'))));
		foreach($orderby as &$value)if(!strstr($value,'.'))$value="$tableName.$value";
		$this->Check($orderby,$fields);
		if(!empty($this->error))return $this->Error();
		usort($table, function($a, $b) use($orderby){
			$i=0;
			$cmp=0;
			$args=explode(',',$orderby);
			while($cmp==0&&$i<count($args)){
				if(strstr($args[$i],' ')) {
					$arg=explode(' ',$args[$i]);
				}else{
					$arg[0]=$args[$i];
					$arg[1]='ASC';
				}
				$cmp=strcmp($a[$arg[0]],$b[$arg[0]])*($arg[1]=='DESC'?-1:1);
				$i++;
			}
			return $cmp;
		});
		return $table;
	}
	function Limit($table,$commands){
		$limit=$this->Args($commands,'LIMIT','INTO');
		$limit=!strstr($limit,',')?"0,$limit":$limit;
		$limits=explode(',',$limit);
		$table=array_slice($table,$limits[0],$limits[1]);
		return $table;
	}
	function Distinct($table){
		$fields=$this->Fields($table);
		foreach($table as $key => $value)$table[$key]=join('*|:|*',$value);
		$table=array_unique($table);
		foreach($table as $key => $value)$table[$key]=array_combine($fields,explode('*|:|*',$value));
		return $table;
	}
	function Insert($commands){
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
		return array('notice'=>array('Command Successfully'));
	}
	function Truncate($commands){
		$tableName=$this->Args($commands,'TRUNCATE','NULL');
		foreach($this->Fields($this->open($tableName)) as $field)$array[$field]='';
		$this->File($tableName,array('0'=>$array));
		return array('notice'=>array('Command Successfully'));
	}
	function Update($commands){
		$tableName=$this->Args($commands,'UPDATE','SET');
		$table=$this->Open($tableName);
		$fields=$this->Fields($table);
		$set=explode(',',$this->Args($commands,'SET','WHERE'));
		$this->Check(array_map(function($value){return strstr($value,'=',TRUE);},$set),$fields);
		if(!empty($this->error))return $this->Error();
		$where=str_replace("$tableName.",'',(strstr($commands,'WHERE')?$this->Where($commands,$fields,$tableName):''));
		foreach($table as $key => $registers)if(empty($where)||eval($where))foreach($set as $arg)$table[$key][strstr($arg,'=',TRUE)]=substr(strstr($arg,'='),2,-1);
		$this->File($tableName,$table);
		return array('notice'=>array('Command Successfully'));
	}
	function Delete($commands){
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
		return array('notice'=>array('Command Successfully'));
	}
	function Create($commands){
		$tableName=$this->Args($commands,'TABLE','(');
		if(is_file("database/$this->database/$tableName.json")) return $this->Error("Table <i>$tableName</i> already exists");
		foreach(explode(',',$this->Args($commands,$tableName,')')) as $value)$array[$value]='';
		$this->File($tableName,array($array));
		return array('notice'=>array('Command Successfully'));		
	}
	function Drop($commands){
		$file="database/$this->database/".$this->Args($commands,'TABLE','NULL').".json";
		if(!is_file($file))return $this->Error('Table not found');
		unlink($file);
		return array('notice'=>array('Command Successfully'));
	}
	function Check($check,$fields){
		foreach($check as $field)if(!in_array($field,$fields))$this->error="Field $field not found.";
	}
	function Alter($commands){
		$command=explode(' ',$commands);
		$function="Alter$command[3]";
		if(!method_exists($this,$function))return $this->Error("Command <i>$command[3]</i> not implemented");
		return $this->$function($commands);
	}	
	function AlterAdd($commands){
		$tableName=$this->Args($commands,'TABLE','ADD');
		$table=$this->Open($tableName);
		$fields=explode(',',$this->Args($commands,'ADD','NULL'));
		if(empty($fields[0]))return $this->Error('You must specify at least one field');
		foreach($fields as $field)$array[$field]='';
		foreach($table as $key => $register)$table[$key]=array_merge($table[$key],$array);
		$this->File($tableName,$table);
		return array('notice'=>array('Command Successfully'));
	}
	function AlterDrop($commands){
		$tableName=$this->Args($commands,'TABLE','DROP');
		$table=$this->Open($tableName);
		$adds=explode(',',$this->Args($commands,'DROP','NULL'));
		$this->Check($adds,$this->Fields($table));
		if(!empty($this->error))return $this->Error();
		foreach($table as $key => $register)foreach($adds as $field)unset($table[$key][$field]);
		$this->File($tableName,$table);
		return array('notice'=>array('Command Successfully'));
	}
	function AlterChange($commands){
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
		return array('notice'=>array('Command Successfully'));
	}
	function File($table,$content){
		$handler=fopen("database/$this->database/$table.json",'w+');
		fwrite($handler,utf8_encode(json_encode($content)));
		fclose($handler);
	}
}
?>