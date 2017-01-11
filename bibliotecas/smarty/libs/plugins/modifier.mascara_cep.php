<?php
/*
* Purpose: "mascara_cep" é uma função para mascarar cep.
* Example smarty code:
*   {$my_string|mascara_cep}
*   retorna formato do tipo: cep: 00.000-000
* @param string
* @return string
*
* -------------------------------------------------------------
*/

function smarty_modifier_mascara_cep($string) {
	if ($string == "") {
		return false;
	}
	
	$TAMANHO_MAXIMO_CAMPO = 8;
	$qtd = strlen($string);
	
	while($qtd < $TAMANHO_MAXIMO_CAMPO) {
		$string = "0".$string;
		$qtd = strlen($string);
	}
	
	if ($qtd == $TAMANHO_MAXIMO_CAMPO) {
		$n_cep = substr($string,0,2).".".substr($string,2,3)."-".substr($string,5,3);
		return $n_cep;
	}
	else {
		return false;
	}
}
?>
