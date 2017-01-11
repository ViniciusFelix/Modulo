function postUsuario(usuario){	
	document.getElementById("boxInputs").innerHTML = "<input type='hidden' name='usuario_seq' id='usuario_seq' value='' />";
	document.getElementById("usuario_seq").value = usuario;
	document.getElementById('form2').action="?d=seguranca&a=usuario&f=formUsuario";
	document.getElementById('form2').submit();
}

function postPerfil(perfil){
	var html = "<input type='hidden' name='seq_perfil' id='seq_perfil' />";	
	document.getElementById("boxInputs").innerHTML 	= html;
	document.getElementById("seq_perfil").value 	= perfil;
	

	document.getElementById('form2').action			= "?d=seguranca&a=permissao&f=formPermissao";
	document.getElementById('form2').submit();
}

function postPermissao(perfil){
	var html = "<input type='hidden' name='seq_perfil' id='seq_perfil' />";	
	document.getElementById("boxInputs").innerHTML 	= html;
	document.getElementById("seq_perfil").value 	= perfil;
	

	document.getElementById('form2').action			= "?d=seguranca&a=permissao&f=treeviewPermissao";
	document.getElementById('form2').submit();
}

function postFormulario(formulario){
	document.getElementById("boxInputs").innerHTML = "<input type='hidden' name='seq_formulario' id='seq_formulario' value='' />";
	document.getElementById("seq_formulario").value = perfil;
	document.getElementById('form2').action="?d=seguranca&a=formulario&f=formFormulario";
	document.getElementById('form2').submit();
}