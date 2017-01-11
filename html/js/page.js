var designMode;

// tratamento padr√£o de erros
/*function myFunc(a,b,c) {
   alert("Error: "+a+" \r\n(Page: "+b+"  -   Line: "+c+")"); 
   return true;
}
window.onerror= myFunc;*/

var wiObj = null;
function msgErr(obj,msg) {
   if (msg!="") alert (msg);
   wiObj = obj;
   setTimeout("selObj()",10);
}

function selObj () {
   if (!wiObj) return;
   wiObj.value = "";
   wiObj.focus(); 
   wiObj = null;
}

function mTr(s,s1,s2) {
   var p;
   var sai="";
   for (var j=0; j<s.length; j++) {
    p=s1.indexOf(s.substring(j,j+1));
    sai=sai + (p<0 ? s.substring(j, j+1) : s2.substring(p, p+1));
   }
   return sai;
}

function Limpar(valor, validos) {
// retira caracteres invalidos da string
  var result = "";
  var aux;
  for (var i=0; i < valor.length; i++) {
    val = valor.substring(i, i+1);
    aux = validos.indexOf(val);
      if (aux>=0) {
        result += val;
      }
  }
  return result;
}



function piece(str,delim,ind) {
   var aux = str.split(delim);
   if (ind <= aux.length) {
     return aux[ind-1];
   }
}

function escondeDivMsg(){
	document.getElementById('div_msg').style.display='none';
	$.ajax(
		{
			type: "POST",
			url: '?d=index&a=index&f=_excluiMsgSessao',
			data: '',
			beforeSend: function(){
				
			},
			success: function(txt){
		
			}
		}
	);
}


// fun√ß√£o para substituir o window.open e window.location que n√£o aceita seguran√ßa ativa
//@list
function wiOpen(url, target, props) {	
	if(document.getElementById('token')){
		if (target) {
			if (target.toLowerCase()=="_blank") {
				target = Date.parse(new Date())+new Date().getMilliseconds();
			}
		} else target = "_self";
		var interrogacao 	= url.split("?");	
				
		var frm = document.createElement("FORM");		
		
		frm.name   = "frm_open";
		frm.id 	  = "frm_open";
		frm.action = interrogacao[0]+"?";
		frm.target = target;
		frm.method = "post";
		document.body.appendChild(frm);	
	
		var ecomercial		= interrogacao[1].split("&");
		var igual;
		for(j=0; j<ecomercial.length; j++){  
			var igual		= ecomercial[j].split("=");  
			var input = document.createElement('input');  
			input.setAttribute('type', 'hidden');  
			input.setAttribute('name', igual[0]);  
			input.setAttribute('id', igual[0]);  
			input.setAttribute('value', igual[1]);
			
			var add_input_form = document.getElementById('frm_open');  
			add_input_form.appendChild(input);  
		}
		
		var input = document.createElement('input');
		input.setAttribute('type', 'hidden');  
		input.setAttribute('name', 'security_token');  
		input.setAttribute('id', 'security_token');  
		input.setAttribute('value', document.getElementById('token').value);
		add_input_form.appendChild(input);
						
		//add_input_form.appendChild(document.getElementById('token'));		
					   
		var wnd;
		
		if (target!="")  {						
			wnd = window.open('',target,props);
			wnd.focus();
		}
		frm.submit();
	
		document.body.removeChild(frm);
	}else{
		alert('N„o foi encontrado um token v·lido nessa p·gina.');
	}
	return wnd;
}


