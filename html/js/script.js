function recuperarFiltroPesquisa(idForm) {
	var query='';
	
	var inputs = $("#"+idForm+" input:text");		
	$.each(inputs, function(i, obj) {
		query+="&"+obj.id+"="+obj.value;
	});
	

	var selects = $("#"+idForm+" select");
	$.each(selects, function(i, obj) {
		query+="&"+obj.id+"="+obj.value;
	});
	
	return query;
}

function formataMoeda(fld, e) {
	var milSep = '.';
	var decSep = ','
	var sep = 0;
	var key = '';
	var i = j = 0;
	var len = len2 = 0;
	var strCheck = '0123456789';
	var aux = aux2 = '';
	var whichCode = (window.event) ? e.keyCode : e.which;
	if (whichCode == 13) return true; 
	if (whichCode == 8) return true;  
	key = String.fromCharCode(whichCode);  
	if (strCheck.indexOf(key) == -1) return false; 
	len = fld.value.length;
	
	if (len > 15)
		return false;
	
	for(i = 0; i < len; i++)
	if ((fld.value.charAt(i) != '0') && (fld.value.charAt(i) != decSep)) break;
	aux = '';
	for(; i < len; i++)
	if (strCheck.indexOf(fld.value.charAt(i))!=-1) aux += fld.value.charAt(i);
	aux += key;
	len = aux.length;
	if (len == 0) fld.value = '';
	if (len == 1) fld.value = '0'+ decSep + '0' + aux;
	if (len == 2) fld.value = '0'+ decSep + aux;
	if (len > 2) {
	aux2 = '';
	for (j = 0, i = len - 3; i >= 0; i--) {
		if (j == 3) {
			aux2 += milSep;
			j = 0;
		}
		aux2 += aux.charAt(i);
		j++;
	}
	fld.value = '';
	len2 = aux2.length;
	for (i = len2 - 1; i >= 0; i--)
		fld.value += aux2.charAt(i);
		fld.value += decSep + aux.substr(len - 2, len);
	}
	return false;
}
function trChange(obj,classe){
	if (document.getElementById(obj).className != 'ItemListaSelect') {
		document.getElementById(obj).className = classe;
	}
}

function PassaValor(Valor,classe,coluna){
	document.getElementById(coluna).value = Valor;
	var sfEls = document.getElementsByTagName("tr");
	for (var i=0; i<sfEls.length; i++) {
	sfEls[i].className = 'ItemListaInativo'
	}
	document.getElementById(Valor).className = classe;
}


function FormataData(campo,teclapres){
	var tecla = teclapres.keyCode;
	vr = document.getElementById(campo).value;
	vr = vr.replace( "/", "" );
	vr = vr.replace( "/", "" );
	vr = vr.replace( "/", "" );
	tam = vr.length + 1;

	if ( tecla != 9 && tecla != 8 ){
		if ( tam > 2 && tam < 5 )
			document.getElementById(campo).value = vr.substr( 0, tam - 2  ) + '/' + vr.substr( tam - 2, tam );
		if ( tam >= 5 && tam <= 10 )
			document.getElementById(campo).value = vr.substr( 0, 2 ) + '/'
			+ vr.substr( 2, 2 ) + '/' + vr.substr( 4, 4 );
	}
}

function ValidNume(campo, event){
	var BACKSPACE= 8;
	var key;
	var tecla;
	CheckTAB=true;
    if (navigator.appName.indexOf("Netscape")!= -1)	{
	    tecla= event.which;
        if ( tecla == 22 ) { return false; }
	} else {
	   tecla= window.event.keyCode;
	}

	key = String.fromCharCode(tecla);

    if ( tecla == 13 )
		return false;
	if ( tecla == 0 )
		return 	true;
	if ( tecla == BACKSPACE )
		return true;
	return ( isNum(key));
}

function isNum( caractere ){
	var strValidos = "0123456789"
	if ( strValidos.indexOf( caractere ) == -1 )
	return false;
	return true;
}

function mask(_mask, val) {
	var i, mki;
	var aux="";

	for(i=mki=0; i<val.length; i++, mki++){
		if(_mask.charAt(mki)=='' || _mask.charAt(mki)=='#' || _mask.charAt(i)==val.charAt(i)) {
			aux+=val.charAt(i);
		} else {
			aux+=_mask.charAt(mki)+val.charAt(i);
			mki++;
		}
	}
	return aux;
}

function maskEvent(field, _mask, event){
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

	key=String.fromCharCode(rcode);

	if(strCheck.indexOf(key)==-1){
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

function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e){
    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';
    var whichCode;
	if(navigator.appName.indexOf("Netscape")!= -1)
		whichCode = e.which;
	 else
		whichCode = e.keyCode;
    if (whichCode == 13) return true;
    if (whichCode == 8) return true;
    if (whichCode == 9) return true;
    if (whichCode == 0) return true;
    key = String.fromCharCode(whichCode); // Valor para o código da Chave
    if (strCheck.indexOf(key) == -1) return false; // Chave inválida
	var Max = parseInt(document.getElementById(objTextBox.id).maxLength);
	len = parseInt(objTextBox.value.length);
	if (len < Max) {
	    for(i = 0; i < len; i++)
	        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
	    aux = '';
	    for(; i < len; i++)
	        if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) aux += objTextBox.value.charAt(i);
	    aux += key;
	    len = aux.length;
	    if (len == 0) objTextBox.value = '';
	    if (len == 1) objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
	    if (len == 2) objTextBox.value = '0'+ SeparadorDecimal + aux;
	    if (len > 2) {
	        aux2 = '';
	        for (j = 0, i = len - 3; i >= 0; i--) {
	            if (j == 3) {
	                aux2 += SeparadorMilesimo;
	                j = 0;
	            }
	            aux2 += aux.charAt(i);
	            j++;
	        }
	        objTextBox.value = '';
	        len2 = aux2.length;
	        for (i = len2 - 1; i >= 0; i--)
	        objTextBox.value += aux2.charAt(i);
	        objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
	    }
	}
    return false;
}

function FormataValor(id,tammax,teclapres){
  if(window.event) { // Internet Explorer
    var tecla = teclapres.keyCode; 
    }
  
    else if(teclapres.which) { // Nestcape / firefox
        var tecla = teclapres.which;
    }
 
    vr = document.getElementById(id).value;
    vr = vr.toString().replace( "/", "" );
    vr = vr.toString().replace( "/", "" );
    vr = vr.toString().replace( ".", "" );
    vr = vr.toString().replace( ".", "" );
    vr = vr.toString().replace( ".", "" );
    vr = vr.toString().replace( ".", "" );
    vr = vr.toString().replace( ".", "" );
    tam = vr.length;
    
    if (tam < tammax && tecla != 8){ 
      
      tam = vr.length + 1; 
      
    }
    
    if (tecla == 8 ){ 
      
      tam = tam - 1;
    
    }

    if ( tecla == 8 || tecla >= 48 && tecla <= 57 || tecla >= 96 && tecla <= 105 ){
    if ( tam <= 2 ){
    document.getElementById(id).value = vr; }
    if ( (tam > 2) && (tam <= 5) ){
    document.getElementById(id).value = vr.substr( 0, tam - 2 ) + '.' + vr.substr( tam - 2, tam ); }
    if ( (tam >= 6) && (tam <= 8) ){
    document.getElementById(id).value = vr.substr( 0, tam - 5 ) + '.' + vr.substr( tam - 5, 3 ) + '.' + vr.substr( tam - 2, tam ); }
    if ( (tam >= 9) && (tam <= 11) ){
    document.getElementById(id).value = vr.substr( 0, tam - 8 ) + '.' + vr.substr( tam - 8, 3 ) + '.' + vr.substr( tam - 5, 3 ) + '.' + vr.substr( tam - 2, tam ); }
    if ( (tam >= 12) && (tam <= 14) ){
    document.getElementById(id).value = vr.substr( 0, tam - 11 ) + '.' + vr.substr( tam - 11, 3 ) + '.' + vr.substr( tam - 8, 3 ) + '.' + vr.substr( tam - 5, 3 ) + '.' + vr.substr( tam - 2, tam ); }
    if ( (tam >= 15) && (tam <= 17) ){
    document.getElementById(id).value = vr.substr( 0, tam - 14 ) + '.' + vr.substr( tam - 14, 3 ) + '.' + vr.substr( tam - 11, 3 ) + '.' + vr.substr( tam - 8, 3 ) + '.' + vr.substr( tam - 5, 3 ) + '.' + vr.substr( tam - 2, tam );
    }
  }
  
 } 
