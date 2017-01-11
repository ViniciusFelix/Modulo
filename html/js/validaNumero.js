//Funcao usada pela funcao ValidNume()
function isNum( caractere ) 
{ 
	var strValidos = "0123456789" 
	if ( strValidos.indexOf( caractere ) == -1 ) 
		return false; 
	return true; 
}
//Funcao para Validar Numeros
//Modo de usar:  onkeypress="return ValidNume(this, event)"
function ValidNume(campo, event) 
{ 
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
