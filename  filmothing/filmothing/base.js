function createXmlHttpReqObj() {
	var xmlHttp=new Object();

	if (window.XMLHttpRequest) {
		try {xmlHttp = new XMLHttpRequest();}
		catch(e){xmlHttp = false;}
	}
	else if (window.ActiveXObject) {
		try {xmlHttp = new ActiveXObject("MSXML2.XMLHTTP");}
		catch (e)
		{
			try {xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');}
			catch (e){xmlHttp = false;}
		}
	}

	if(!xmlHttp )
		alert('error creating xml obj!');

	return xmlHttp;
}

var xhttp;
var mode;
var coord;
var oBall;// = new Object();
var retry;

function init() {
    xhttp=createXmlHttpReqObj();
	mode='uncwn';
    if(xhttp) {
		sendi("","");
    }
}

function sendi(name,data) {

	oBall = new Object();
	oBall.name=name;
	oBall.data=data;

	var sendMessMetaData;
	if( xhttp.readyState == 4 || xhttp.readyState == 0 ) {
		sendMessMetaData = "ball="+JSON.stringify(oBall);
        //alert("transmitted: "+sendMessMetaData);
		xhttp.open("POST", 'ask.php', true);
		
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		//xhttp.setRequestHeader("Content-length", sendMessMetaData.length);
		//xhttp.setRequestHeader("Connection", "close");

		xhttp.onreadystatechange = recProcedure;
		xhttp.send(sendMessMetaData);
     //           clearTimeout(timer1);
      //          timer2=setTimeout("sendi('refresh','')", 60000);
	    showHide('status','visible');
	}
	else {
		clearTimeout(timer1);
		timer1=setTimeout("sendi(data)", 30000);
		alert("повторная попытка переслать запрос...");
	}
}


function recProcedure() {
    if(xhttp.readyState == 4 && xhttp.status == 200) {
			var str=xhttp.responseText;
			//alert("incoming:"+str);
			var re = /(\[[^\b]+\])/;
			var ocm_text = str.match(re);
			
			if( ocm_text!=null ) {
				str=ocm_text[0];
			} 
			
			try {
				var ocm;
				//alert("trying: "+str);
				ocm = JSON.parse(str);
				for (var cline in ocm) {
					//alert(cline);
					//alert(ocm[cline].target+", "+ocm[cline].data+", "+ocm[cline].mode);
					addToElem(ocm[cline].target, ocm[cline].data, ocm[cline].mode);
					//addToElem('debug',ocm[cline],1);
					//mode=ocm[cline].mode;
					//parse_mess(ocm[cline].cmd, ocm[cline].val, ocm[cline].data);
				}
			} catch(e) {
				alert('error json parsing: '+e.message+", "+e.name+", "+e.description);//+','+);error.message
				alert('response: '+xhttp.responseText);
				//	showHide('status','hidden');
			}
		//alert("received ( length: "+xhttp.responseText.length+" ) : "+xhttp.responseText);
		 showHide('status','hidden');
    }
}

function addToElem(_obj_name, _value, _mode) {
	_id=document.getElementById(_obj_name);
	if( _id!=undefined ) {
		if(_mode!=0) {
			_id.innerHTML+=_value;
		} else {
			_id.innerHTML=_value;
		}
	}
}


function sendf(form) {
	//alert(form.name);
	var arr=form.elements;
	var formdata=new Object();
	var rest = [''];//, 'cmd', 'val'];
	for (var key in arr) {
		if( arr[key].name != rest[0]) {		// && arr[key].name != rest[1] && arr[key].name != rest[2] ) {
			formdata[arr[key].name] = arr[key].value;
		}
	}
	//sendi(arr['cmd'].value, arr['val'].value, formdata);
	sendi(form.name, formdata);
	return false;
}











function setObjProp(_obj_name, _name, _value) {
	_id=document.getElementById(_obj_name);
	//alert(_id);
    if(_id!=undefined) {
		//alert(_id+","+_name+", "+_value);
		set_prop(_id, _name, _value);
	}
}

function set_prop(_obj, _name, _value) {
	//alert("setting_prop: "+_obj+", "+_name+", "+_value);
    switch(_name) {
        case 'pid':
            _obj.setAttribute('id', _value);
            break;
        case 'px':
            _obj.style.left=_value;
            break;
        case 'py':
            _obj.style.top=_value;
            break;
        case 'ps':
            _obj.style.width=_value;
            _obj.style.height=_value;
            break;
        case 'pw':
            _obj.style.width=_value;
            break;
        case 'ph':
            _obj.style.height=_value;
            break;
        case 'pb':
            _obj.style.border='2px '+_value+' dotted';
            break;
        case 'ppic':
			_obj.style.opacity = 0.5;
			_obj.style.filter='alpha(opacity='+0.5*100+')';
            _obj.style.backgroundImage = "url('images/"+_value+"')";
			_obj.style.opacity = 1;
			_obj.style.filter='alpha(opacity='+1*100+')';
            break;
		case 'pcontent':
			_obj.innerHTML=_value;
			break
		case 'psrc':
			_obj.src=_value;
			break
		case 'pvalue':
			//alert('');
			_obj.value=_value;
			break;
        case 'pd':
            _obj.lastChild.innerHTML=_value;
            break;
        case 'pselect':
            switch(_value) {
				case 'area':
					_obj.setAttribute('onmousedown', "handleMouseDown(event,'"+_obj.getAttribute('id')+"')");
					_obj.setAttribute('onmouseup', "handleMouseUp(event,'"+_obj.getAttribute('id')+"')");
					break;
                case 'coord':
                    _obj.setAttribute('onmousedown', "handleMouseDown(event,'"+_obj.getAttribute('id')+"')");
                    break;
                case 'object':
                    _obj.setAttribute('onmousedown', "handleSelectObj(event,'"+_obj.getAttribute('id')+"')");
                    break;
                default:
                     _obj.setAttribute('onmousedown', "undefined");
                    break;
            }
            break;
        default:
			alert("undefined property setting:" + _name);
 //           _obj.innerHTML=_value;
            break;
    }
}

function merge_object(_tobj, _obj) {
	for (var key in _obj) {
		_tobj[key] = _obj[key];
	}
}

function parse_mess(_cmd, _val, _data) {
	//alert( _cmd + ", " + _val + ", " + _data );
	switch(_cmd) {
		case 'add': //add object
			 _id=document.getElementById(_val);
            if(_id!=undefined) {

				var n = document.createElement('div');
                var nd = document.createElement('div');
                var ndd = document.createElement('div');

				for (var key in _data) {
					switch(key) {
						case 'pd':
							ndd.innerHTML=_data.pd;
							break;
						case 'pid':
							set_prop(nd, 'pid', _data.pid);
							set_prop(ndd, 'pid', _data.pid+"_desc");
							nd.setAttribute('onmouseover', "handleMouseOver(event,'"+_data.pid+"')");
							nd.setAttribute('onmouseout', "handleMouseOut(event,'"+_data.pid+"')");
							break;
						default:
							set_prop(nd, key, _data[key]);
							break;
					}
				}

                n.style.position='relative';
                nd.style.position='absolute';
                ndd.style.position='relative';

                ndd.style.top=0;
                ndd.style.left=parseInt(nd.style.width)/2;
                ndd.style.width=150;
                ndd.style.height='auto';
              
                ndd.style.visibility='hidden';
                ndd.style.opacity=0.8;
                ndd.style.zIndex=10;

                ndd.style.color='#ffe400';
				ndd.style.fontSize='14pt';
                ndd.style.backgroundColor='#000000';
                ndd.style.border='1px dotted #00ff00';

                nd.appendChild(ndd);
                n.appendChild(nd);
                _id.appendChild(n);
			} else {
                alert("add - '"+_val+"' not found");
            }
			break;
		case 'del': //del object
			break;
		case 'itemcontent':
			 _id=document.getElementById(_val);
            if(_id!=undefined ) {
				alert(_id['val'].value);
			}
			break
		case 'set': //change objects properties
			 _id=document.getElementById(_val);
            if(_id!=undefined ) {
				for (var key in _data) {
					set_prop(_id, key, _data[key]);
				}
            }
			break;
		case 'replace': //replace objects content
            _id=document.getElementById(_val);
            if(_id!=undefined) {
                _id.innerHTML=_data;
            } else {
                alert("replace - '"+_val+"' not found");
            }
			break;
		case 'merge': //merge new content with the current one
		     _id=document.getElementById(_val);
            if(_id!=undefined) {
                _id.innerHTML=_id.innerHTML+_data;
                _id.scrollTop=_id.scrollHeight;
            } else {
                alert("merge - '"+_val+"' not found");
            }
            break;
		case 'alert':
			alert(_data);
			break;
		case 'table':
			_id=document.getElementById(_val);
			if(_id!=undefined) {
				
				var adds="";
				//if(_data.actions) {
				//	alert('');
					for (var key in _data.actions) {
						adds+="<td><a href='#' onclick=\"sendi('"+_data.actions[key].cmd+"','"+_data.actions[key].val+"','"+_data.actions[key].col+"'); \">"+_data.actions[key].cap+"</a></td>";
					}
			//	}
			
				var tabl="<table>";
				
				tabl+="<tr>";
                for (var key in _data.header) {
					tabl+="<th>"+_data.header[key]+"</th>";
				}
				tabl+="</tr>";
				
			
                for (var rec in _data.records) {
					tabl+="<tr>";
					//alert("recs len:"+_data.records[rec].length);
					for (var key in _data.records[rec]) {
						//alert(key+", "+_data.records[rec][key]);
						if(!isNaN(parseInt(key))) {
							tabl+="<td>"+_data.records[rec][key]+"</td>";
						}
					}
					for (var key2 in _data.actions) {
						if(!isNaN(parseInt(key2))) {
							tabl+="<td><a href='#' onclick=\"sendi('"+_data.actions[key2].cmd+"','"+_data.actions[key2].val+"','"+_data.records[rec][_data.actions[key2].col]+"'); \">"+_data.actions[key2].cap+"</a></td>";
						}
					}
					//+=adds;
					tabl+="</tr>";
				}
				tabl+="</table>";
				alert(tabl);
				_id.innerHTML=tabl;
                //_id.scrollTop=_id.scrollHeight;
            } else {
                alert("table - '"+_val+"' not found");
            }
			break;
		default:
			break;
	}
}






function getOffsetSum(elem) {
    var top=0, left=0;
    while(elem) {
        top = top + parseInt(elem.offsetTop);
        left = left + parseInt(elem.offsetLeft);
        elem = elem.offsetParent;
    }
    return {top: top, left: left}
}

function handleSelectObj(_e, _name) {
    var elem = document.getElementById(_name);
    if(elem!=undefined) {
        sendi('select',_name,'mouse');

    } else alert(_name+' is not defined! (hSelObj)');
//    return false;
}

function handleMouseDown(_e, _name) {
    var elem = document.getElementById(_name);
	
    //alert('!');

    if(elem!=undefined) {
        var _divOffset=getOffsetSum(elem);
        if (_e.clientX || _e.clientY) {
			//var coord = new Object();
            coord.x =  _e.clientX+document.body.scrollLeft+document.documentElement.scrollLeft-_divOffset.left - 1;
            coord.y =  _e.clientY+document.body.scrollTop+document.documentElement.scrollTop-_divOffset.top - 1;
			draw('clear');
            sendi(_name,'down',coord);
        }
    } else alert(_name+' is not defined! (hMouseDown)' );
//    return false;
}

function handleMouseUp(_e, _name) {
    var elem = document.getElementById(_name);
    //alert('!');
    if(elem!=undefined) {
        var _divOffset=getOffsetSum(elem);
        if (_e.clientX || _e.clientY) {
			//var coord = new Object();
			_x=coord.x;
			_y=coord.y;
            coord.x =  _e.clientX+document.body.scrollLeft+document.documentElement.scrollLeft-_divOffset.left - 1; 
            coord.y =  _e.clientY+document.body.scrollTop+document.documentElement.scrollTop-_divOffset.top - 1 ;
			draw('continue');
			coord.x=coord.x-_x;
			coord.y=coord.y-_y;
            sendi(_name,'up',coord);
        }
    } else alert(_name+' is not defined! (hMouseDown)' );
//    return false;
}

function handleMouseOver(_e,_name) {
    var elem = document.getElementById(_name+'_desc');
    if(elem!=undefined) {
        elem.style.visibility='visible';
        elem.style.display="block";
    } //else alert(_name+' is not defined! (handleMouseOver) ');
}

function handleMouseOut(_e,_name) {
    var elem = document.getElementById(_name+'_desc');
    if(elem!=undefined) {
        elem.style.visibility='hidden';
    }// else alert(_name+' is not defined! (handleMouseOut) ');
}

function showHide(_name, _v) {
	var elem = document.getElementById(_name);

    if(elem!=undefined) {
		if(elem.style.visibility=='visible') {
			elem.style.visibility='hidden';
		} else {
			elem.style.visibility='visible';
		}
		
		if(_v!=undefined) {
			elem.style.visibility=_v;
		}
		
    } else alert(_name+' is not defined! (showHide) ');
}

function draw(dtype) {
	var canva_id=document.getElementById("canvas");
	if(canva_id!=undefined) {
		var canvas=canva_id.getContext("2d");
		canvas.save();
		canvas.clearRect(0,0,640,480);
		canvas.moveTo(coord.x,coord.y-10);
		canvas.lineTo(coord.x,coord.y+10);
		canvas.moveTo(coord.x-10,coord.y);
		canvas.lineTo(coord.x+10,coord.y);
		canvas.strokeStyle = "red";
		canvas.fillStyle = "red";
		canvas.lineWidth = 1;
		//canvas.lineCap = "round";
		canvas.stroke();
		canvas.restore();
	}
}
