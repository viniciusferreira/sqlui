<?
error_reporting(E_ALL);
require 'class/sqlui.class.php';

function CLui($variavel){
	$resultado='';
	for($i=0;$i<=strlen($variavel);$i+=2)$resultado.=substr($variavel,$i+1,1).''.substr($variavel,$i,1);
	return $resultado;
}
$sqlui=false;
if (!$sqlui) $sqlui = new SQLui();
$sqlui->Connect('root','pass');
$query=isset($_POST['query'])?$_POST['query']:(isset($_GET['query'])?$_GET['query']:'SHOW DATABASES');
if(isset($_REQUEST['query'])){
	$response=$sqlui->Command(CLui($query));
	die(json_encode($response));
}
?>
<HTML>
	<HEAD>
		<TITLE>SQlui - SQL user interface for JSON</TITLE>
		<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>
		<META name='Description' content='SQLui - SQL user interface for JSON'>
		<META name='Keywords' content='sqlui,database,sql,json,spiderpoison'>
		<META name='Author' content='spiderpoison@gmail.com'>
		<LINK rel='stylesheet' href='stylesheet/sqlui.css'>
		<SCRIPT src='javascript/box.js'></SCRIPT>
		<SCRIPT src='javascript/basic.js'></SCRIPT>
		<SCRIPT src='javascript/sqlui.js'></SCRIPT>
	</HEAD>
	<BODY bgcolor=white link=darkorange vlink=orange topmargin=0 leftmargin=0 onload="SQLuiQuery('SHOW DATABASES')">
		<TABLE border=0 cellspacing=0 cellpadding=5 width=100%>
			<TR class=title>
				<TD class=title><img src="image/logo.png">SQlui - SQL user interface for JSON</TD>
			</TR><TR class=c4>
				<TD><form method=post onsubmit="return SQLuiQuery(false,this)">
					<table width=100%>
						<tr>
							<td width=3%><IMG src="image/databases.png" title='SHOW DATABASES' onclick="SQLuiQuery('SHOW DATABASES')" class=hand align=absmiddle></td>
							<td width=93%><input type=text class=text id=query name=query size=50 value="SHOW DATABASES"></td>
							<td width=2%><input type=image src="image/ico/16/flash.png" align=absmiddle title='Run query'></td>
							<td width=2%><img src="image/ico/16/undo.png" onclick="SQLuiQuery(undo)" title='Previous query'></td>
						</tr>
					</table>
				</form></TD>
			</TR><TR>
				<TD style=padding:0 id=out></TD>
			</TR>
		</TABLE>
		<SCRIPT>
			Box('horizontal','titulo','conteudo',true)
			Box('vertical','titulo','conteudo',false)
		</SCRIPT>		
	</BODY>
</HTML>