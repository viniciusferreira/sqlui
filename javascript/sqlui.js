var command,data
var undo='SHOW DATABASES'
function SQLuiQuery(query,form){
	undo=$('query').value
	if(query)$('query').value=query
	command=$('query').value.split(' ')
	var xmlhttp=new XMLHttpRequest()
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4&&xmlhttp.status==200){
			data=JSON.parse(xmlhttp.responseText)
			try {
				_('SQLui'+command[0],data)
			}catch(error){
				_('SQLui',data)
			}						
		}
	}
	xmlhttp.open((form?'POST':'GET'),'?random='+Math.random()+'&query='+CLui(query),true)
	fields=''
	if(form){
		for(i=0;i<form.length;i++)fields+=form[i].name+'='+CLui(form[i].value)+'&'
		xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8')
	}	
	xmlhttp.send(fields)				
	xmlhttp.close				
	return false
}
function SQLuiCheck(){
	Object.keys(data)=='Error'?SQLui(data):SQLuiQuery(undo)
}
function SQLuiOut(out){
	See('horizontal',false)
	See('vertical',false)
	$('out').innerHTML='<table class=outTable border=0>'+out+'</table>'
}
function SQLui(){
	check=['0','CREATE','DROP','RENAME','TWIN','ALTER','INSERT','UPDATE','DELETE']
	check.indexOf(command[0])?SQLuiCheck():SQLuiOut('<tr class=outHead><th class=outHead>'+Object.keys(data)+'</th></tr><tr class=outRegister><td class=outField>'+data[Object.keys(data)]+'</td></tr>')
}
function SQLuiSHOW(){
	_('SQLui'+command[0]+''+command[1],data)
}
function SQLuiSHOWDATABASES(){
	head=Object.keys(data)
	out='<tr class=outHead><th class=outHead align=left colspan=2><IMG src=image/ico/16/add.png title="CREATE DATABASE database_name" onclick="SQluiCommumBox(\'CREATE DATABASE\',\'database name\')"> '+head+'</th></tr>'
	for(i=0;i<data[head].length;i++){
		db=data[head][i]
		out+='<tr class=outRegister><td class=outField><img src=image/ico/16/database.png onclick="SQLuiQuery(this.title)" title="TAKE '+db+'"> <a class=outCommand href=# onclick=\'javascript:SQLuiQuery(this.title)\' title="TAKE '+db+'">'+db+'</a></td><td align=right class=outField><img src=image/ico/16/star.png title="TWIN DATABASE '+db+'" onclick="SQluiCommumBox(this.title,\'new database name\')"> <img src=image/ico/16/text.png title="RENAME DATABASE '+db+'" onclick="SQluiCommumBox(this.title,\'new database name\')"> <img src=image/ico/16/trash.png title="DROP DATABASE '+db+'" onclick=SQLuiDropBox(this.title)></td></tr>'
	}
	SQLuiOut(out)
}
function SQLuiSHOWTABLES(){
	undo='SHOW DATABASES'
	head=Object.keys(data)
	out="<tr class=outHead><th class=outHead align=left colspan=2><IMG src=image/ico/16/add.png title='CREATE TABLE table_name(field,field,...)' onclick=SQLuiCreateTableBox()>"+head+"</th></tr>"
	for(i=0;i<data[head].length;i++){	
		tb=data[head][i]
		out+='<tr class=outRegister><td class=outField><img src=image/ico/16/table.png onclick="SQLuiQuery(this.title)" title="SELECT * FROM '+tb+' LIMIT 50"> <a class=outCommand href=# onclick=\'SQLuiQuery(this.title)\' title="SELECT * FROM '+tb+' LIMIT 50">'+tb+'</a></td><td align=right class=outField><img src=image/ico/16/workflow.png onclick="SQLuiQuery(this.title)" title="SHOW TABLE '+tb+'"> <img src=image/ico/16/star.png title="TWIN TABLE '+tb+'" onclick="SQluiCommumBox(this.title,\'new table name\')"> <img src=image/ico/16/text.png title="RENAME TABLE '+tb+'" onclick="SQluiCommumBox(this.title,\'new table name\')"> <img src=image/ico/16/trash.png title="DROP TABLE '+tb+'" onclick="SQLuiDropBox(this.title)"></td></tr>'
	}
	SQLuiOut(out)
}
function SQLuiSHOWTABLE(){
	undo='SHOW TABLES'
	head=Object.keys(data)
	out='<tr class=outHead><th class=outHead align=left colspan=2><IMG src=image/ico/16/add.png title="ALTER TABLE '+command[2]+' ADD" onclick=\'SQluiCommumBox(this.title,"new field name")\'> '+head+'</th></tr>'
	for(i=0;i<data[head].length;i++){
		fl=data[head][i]
		out+='<tr class=outRegister><td class=outField><img src=image/ico/16/text.png title="ALTER TABLE '+command[2]+' CHANGE '+fl+'" onclick="SQluiCommumBox(this.title,\'new field name\')"> <a class=outCommand title="ALTER TABLE '+command[2]+' CHANGE '+fl+'" href=# onclick="SQluiCommumBox(this.title,\'new field name\')">'+fl+'</a></td><td class=outField align=right><img src=image/ico/16/trash.png title="ALTER TABLE '+command[2]+' DROP '+fl+'" onclick=SQLuiDropBox(this.title)></td></tr>'
	}
	SQLuiOut(out)
}
function SQLuiTAKE(){
	SQLuiQuery('SHOW TABLES')
}
function SQLuiSELECT() {
	flds=Object.keys(data[0])
	colspan=flds.length+1
	SQLuiOut("<tr class=outHead><tr class=outHead><th class=outHead colspan="+(colspan-1)+" align=left><IMG src=image/ico/16/table.png title=\"SHOW TABLES\" onclick=\"SQLuiQuery('SHOW TABLES')\"> "+SQLuiInsertBtn(flds)+"</th><th align=right class=outHead><img src=image/ico/16/save.png title='"+command.join(' ')+" INTO' onclick='SQluiCommumBox(this.title,\"new table name\")'></th></tr><tr class=outHead><th class=outHead>"+flds.join("</th><th class=outHead>")+"<th class=outHead align=right></br></th></tr>"+SQLuiGetRegister());
}
function SQLuiGetRegister(){
	out=''
	isEmpty=''
	for(i=0;i<data.length;i++){
		reg=''
		where=''
		out+="<tr class=outRegister>"
		for(j=0;j<flds.length;j++){		
			fld=data[i][flds[j]]
			where+=flds[j]+"='"+fld+"' AND "
			reg+="<td class=outField id=f"+i+""+j+" onclick=\"SQLuiUpdateBox(this.id,'"+flds[j]+"',$('where"+i+"').title)\">"+fld+"</td>"
			isEmpty+=fld
		}
		where=where.substring(0,where.length-5)
		out+=data.length==1&&!isEmpty?"<td class=outField colspan="+colspan+" align=center>Empty table</td></tr>":reg+"<td class=outField align=right><img src=image/ico/16/trash.png id=where"+i+" title=\""+where+"\" onclick=\"SQLuiDeleteBox(this.title)\"></td></tr>"		
	}
	return out
}
function SQLuiInsertBtn(flds){
	tb=[]
	insert=''
	for(i=0;i<flds.length;i++){
		arg=flds[i].split('.')
		if(tb.indexOf(arg[0])<0){
			fl=''
			for(j=0;j<flds.length;j++){
				arr=flds[j].split('.')
				if(arg[0]==arr[0])fl+=','+arr[1]
			}
			tb[i]=arg[0]
			insert+=" <IMG src=image/ico/16/add.png title=\"INSERT INTO "+arg[0]+" VALUES('string','string',...)\" onclick=\"SQLuiInsertBox('"+arg[0]+"','"+fl.substring(1)+"')\"> "+arg[0]
		}
	}
	return insert
}
function SQLuiBox(type,title,content){
	$(type+'Content').innerHTML=content
	$(type+'Title').innerHTML=title
	See(type,true,true,false)
}
function SQluiCommumBox(query,param){
	SQLuiBox('horizontal',query,"<table><tr><td><input type=text class=text name=param id=param placeholder='"+param+"'></td><td><img src=image/ico/16/flash.png onclick=\"SQLuiQuery('"+query+" '+$('param').value)\"></td></tr></table>",param)
}
function SQLuiDropBox(query){
	SQLuiBox('horizontal',query,"<img src=image/ico/16/flash.png onclick=\"SQLuiQuery('"+query+"')\">")
}
function SQLuiCreateTableBox(){
	SQLuiBox('horizontal','CREATE TABLE ','<table border=0><tr><td colspan=2><input type=text class=text name=table id=table placeholder="table name"></td><td><input type=text class=text name=field id=field placeholder="field name,field name,..."></td><td><IMG src=image/ico/16/flash.png onclick="SQLuiQuery(\'CREATE TABLE \'+$(\'table\').value+\'(\'+$(\'field\').value+\')\')" title="Create Table"></td></tr></table>')
}
function SQLuiInsertBox(tbl,fld){
	fd=fld.split(',')
	ous=''
	fds=''
	fls=''
	for(i=0;i<fd.length;i++){
		fls+=','+fd[i]
		ous+="<tr><td><input type=text class=text id='"+fd[i]+"' placeholder='"+fd[i]+"'></td></tr>"
		fds+=",\\\''+$('"+fd[i]+"').value+'\\\'"
	}
	SQLuiBox('vertical','INSERT INTO '+tbl+'('+fls.substring(1)+') VALUES',"<table width=100%>"+ous+"<tr><td align=center colspan=2><img src=image/ico/16/flash.png onclick=\"SQLuiQuery('INSERT INTO "+tbl+"("+fls.substring(1)+") VALUES("+fds.substring(1)+")')\"></td></tr></table>")
}
function SQLuiUpdateBox(id,fld,where){	
	value=$(id).innerText
	arg=fld.split('.')
	fld=fld.replace(arg[0]+'.','')
	arr=where.split(' AND ')
	where=''
	for(i=0;i<arr.length;i++)if(arr[i].indexOf(arg[0])==0)where+=arr[i].replace(/'/g,"\\\'").replace(arg[0]+'.','')+' AND '	
	SQLuiBox('horizontal',"UPDATE "+arg[0]+" SET "+fld+"=","<table><tr><td><input type=text class=text name=param id=param placeholder='"+$(id).innerText+"'></td><td><img src=image/ico/16/flash.png onclick=\"SQLuiQuery('UPDATE "+arg[0]+" SET "+fld+"=\\\''+$('param').value+'\\\' WHERE "+where.substring(0,where.length-5)+"')\"></td></table>")
}
function SQLuiDeleteBox(where){	
	arr=where.split(' AND ')
	tbl=[]
	for(i=0;i<arr.length;i++){
		arg=arr[i].split('.')
		!tbl[arg[0]]?tbl[arg[0]]='DELETE * FROM '+arg[0]+' WHERE '+arg[1]:tbl[arg[0]]+=' AND '+arg[1]
	}
	out=''
	for(x in tbl)out+='DELETE * FROM '+x+' <img src=image/ico/16/flash.png onclick="SQLuiQuery(\''+tbl[x].replace(/'/g,"\\\'")+'\')"><BR>'
	SQLuiBox('vertical','DELETE',out)
}