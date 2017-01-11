//Essa função é utilizada pela Função maskEvent
//Modo de Usar: <script>document.write(mask('####/####','{$res[i].nu_contrato}'))</script> 
function mask(_mask, val) {
	var i, mki;
	var aux="";
	
	for(i=mki=0; i<val.length; i++, mki++) {
		if(_mask.charAt(mki)=='' || _mask.charAt(mki)=='#' || _mask.charAt(i)==val.charAt(i)) {
			aux+=val.charAt(i);
		} else {
			aux+=_mask.charAt(mki)+val.charAt(i);
			mki++;
		}
	}
	return aux;
}
//Função Generica para Mascaras
//Modo de Usar: onKeyPress="return(maskEvent(this, '####/####',event))"
function maskEvent(field, _mask, event) {
	var key ='';
	var aux='';
	var len=0;
	var i=0;
	var strCheck = '0123456789';
	if(navigator.appName.indexOf("Netscape")!= -1)	 
		rcode= event.which;	 
	 else	 
		rcode= event.keyCode;	 
	
	if(rcode == 8) {
		return true;
	}
		
	if(rcode == 13 || rcode == 0 || field.value.length == _mask.length  ) {
		//Enter
		key=String.fromCharCode(rcode);
				
		if(rcode!=13 && rcode!=0) {
			return false;
		}
				
		return true;
	}
	
	//Obtenha valor chave de código chave
	key=String.fromCharCode(rcode);
	
	if(strCheck.indexOf(key)==-1) {
		//Not a valid key
		return false;
	}
	
	aux=field.value+key;
	//window.alert(aux);
	aux=mask(_mask,aux);
	//window.alert(aux);
	field.value=aux;	
	return false;
}
function maskEvent2(field, _mask, event) {
	var key ='';
	var aux='';
	var len=0;
	var i=0;
	var strCheck = 'xX0123456789';
	if(navigator.appName.indexOf("Netscape")!= -1)	 
		rcode= event.which;	 
	 else	 
		rcode= event.keyCode;	 
	
	if(rcode == 8) {
		return true;
	}
		
	if(rcode == 13 || rcode == 0 || field.value.length == _mask.length  ) {
		//Enter
		key=String.fromCharCode(rcode);
				
		if(rcode!=13 && rcode!=0) {
			return false;
		}
				
		return true;
	}
	
	//Obtenha valor chave de código chave
	key=String.fromCharCode(rcode);
	
	if(strCheck.indexOf(key)==-1) {
		//Not a valid key
		return false;
	}
	
	aux=field.value+key;
	//window.alert(aux);
	aux=mask(_mask,aux);
	//window.alert(aux);
	field.value=aux;	
	return false;
}