//is_firefox
ff=navigator.userAgent.indexOf("Firefox")!=-1
/*capture mouse position
mouse.x		-> horizontal position of mouse in window
mouse.y		-> vertical position of mouse in window
o			-> object above mouse
mouse.oX 	-> horizontal position of mouse in object 
mouse.oY 	-> vertical position of mouse in object 
*/
mouse={x:0,y:0,o:'',oX:0,oY:0}
window.document.onmousemove=function(e){
	if(e==null)e=window.event;
	if(document.body&&mouse){
		mouse.x=e.clientX+document.body.scrollLeft
		mouse.y=e.clientY+document.body.scrollTop
		mouse.o=ff?e.target:e.srcElement
		position=Position(mouse.o)
		mouse.oX=position.x
		mouse.oY=position.y
	}
	if(drag){
		dragObj.style.left=(mouse.x-clickX)+'px'
		dragObj.style.top=(mouse.y-clickY)+'px'
	}
}
/*Show or hide object -> See('object',[true|false],[true|false],[true|false])
obj			-> object to see or hide
show		-> show or not object
move		-> move box to mouse location
hidesfather	-> hide father box of new box
*/
seeing=''
function See(obj,show,move,hidesfather){
	if (seeing&&hidesfather) $(seeing).style.display='none'
	seeing=obj
	$(obj).style.display=!show?'none':'table'
	if(move){
		screenHeight=document.body.clientHeight+document.body.scrollTop
		screenWidth=document.body.clientWidth+document.body.scrollLeft
		$(obj).style.left=mouse.x+$(obj).clientWidth>screenWidth&&$(obj).clientWidth<screenWidth?screenWidth-$(obj).clientWidth-5:mouse.x
		$(obj).style.top=mouse.y+$(obj).clientHeight>screenHeight&&$(obj).clientHeight<screenHeight?screenHeight-$(obj).clientHeight-5:mouse.y
	}
}
/*Return position of a object -> position=Position(object)
position.x -> horizontal position of object
position.y -> vertical position of object
*/
function Position(obj){
	var offsetTrail=obj
	var offsetLeft=0
	var offsetTop=0
	while (offsetTrail){
		offsetLeft+=offsetTrail.offsetLeft
		offsetTop+=offsetTrail.offsetTop
		offsetTrail=offsetTrail.offsetParent
	}
	if (navigator.userAgent.indexOf("Mac")!=-1&&typeof document.body.leftMargin!="undefined"){
		offsetLeft+=document.body.leftMargin
		offsetTop+=document.body.topMargin
	}
	return {x:offsetLeft,y:offsetTop}
}
/*Create a box -> Box('object','title','content',[true|false],[width],[height])
obj			-> name of object box
title		-> title of box
content		-> content of box
orientation	-> true as horizontal box and false is vertical
width		-> width, only to horizontal box
height		-> height, only to horizontal box
*/
drag=false
dragObj={id:'',bkpIndex:0}
function Box(obj,title,content,orientation,width,height){
	if(obj){
		if(orientation){
			document.write("<table border=0 id="+obj+" style=display:none;position:absolute;top:0;left:0; class='boxTable'><tr><td style=cursor:"+(ff?'pointer':'hand')+" class='boxTitle' id="+obj+"Bar><img src=image/0.gif height=10></td>"+(title=title?"<td id="+obj+"Title class='boxContent'>"+title+" </td>":"")+"<td colspan=2 id="+obj+"Content class=boxContent align=center>"+(content?content:"")+"</td><td align=right class='boxContent'><img src=image/ico/16/error.png onclick=See('"+obj+"',false)></td></tr></table>")
		}else{
			document.write("<table "+(width?"width="+width:"")+";"+(height?"height="+height:"")+" style=display:none;position:absolute;top:0;left:0;"+(width?"width:"+width:"")+";"+(height?"height:"+height:"")+" id="+obj+" class='boxTable'><tr height=10 id="+obj+"Bar style=cursor:"+(ff?'pointer':'hand')+" class='boxTitle'><td id="+obj+"Title class='boxTitle'>"+(title=title?title:"<BR>")+"</td><td align=right class='boxTitle'><img src=image/ico/16/error.png onclick=See('"+obj+"',false)></td></tr><tr><td colspan=2 id="+obj+"Content class=boxContent align=center>"+(content?content:"<img src=image/load.gif>")+"</td></tr></table>")
		}
	}
	$(obj+"Bar").onmousedown=function(){
		dragObj=$(obj)
		drag=true
		position=Position(dragObj)
		clickX=mouse.x-position.x
		clickY=mouse.y-position.y
		document.body.focus();
		dragObj.bkpIndex=dragObj.style.zIndex
		dragObj.style.zIndex=1000
		dragObj.ondragstart=function(){return false}
		document.onselectstart=function(){return false}
	}
	$(obj+"Bar").onmouseup=function parar(){
		drag=false
		dragObj.style.zIndex=dragObj.bkpIndex
		document.onselectstart=function(){return false}
	}
}