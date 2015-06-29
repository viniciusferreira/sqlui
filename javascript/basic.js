function $(obj){return document.getElementById(obj)}
function _(fnc,arg){eval(fnc)(arg)}
function CLui(query){
	out=''
	for(yi=0;yi<=query.length;yi+=2)out+=query.substr(yi+1,1)+''+query.substr(yi,1)
	while(out.match(/\+/) )out=out.replace(/\+/,'%2b')
	return escape(out)
}