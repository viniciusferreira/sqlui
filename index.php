<?PHP
require 'sqlui.class.php';

function getmicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
}
$tempo_no_inicio=getmicrotime();

$sqlui=false;
if (!$sqlui) $sqlui = new SQLui();
$sqlui->Connect('root','pass');
$sqlui->Database('sqlui');

$query=isset($_POST['query'])?$_POST['query']:'';
$command=$sqlui->Command($query);

$tempo=getmicrotime()-$tempo_no_inicio;

ECHO "<HTML>
	<HEAD>
		<TITLE>SQLui - SQL user interface for JSON</TITLE>
		<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>
		<META name='Description' content='SQLui - SQL user interface for JSON'>
		<META name='Keywords' content='sqlui,database,sql,json,spiderpoison'>
		<META name='Author' content='spiderpoison@gmail.com'>
		<STYLE>
			.title{background-color:silver;font:bold 100% Verdana;color:dimgray}
			td{font:100% Verdana;color:gray}
		</STYLE>
	</HEAD>
	<BODY bgcolor=white link=darkorange vlink=orange topmargin=0 leftmargin=0 rightmargin=0 bottommargin=0>
		<TABLE border=1 cellspacing=0 width=100%>
			<TR class=title>
				<TD class=title>SQLui - SQL user interface for JSON</TD>
			</TR><TR>
				<TD><B>Query:</B> <form method=post style=display:inline><INPUT type=text name=query size=50 value=\"$query\"><input type=submit value=ok> $tempo Sec.</form></TD>
			</TR><TR>
				<TD><PRE>".print_r($command,true)."</PRE></TD>
			</TR>
		</TABLE>
	</BODY>
</HTML>
";
?>