<?php
/*
* Purpose: "mascara_cpf" é uma função para mascarar cpf.
* Example smarty code:
*   {$my_string|mascara_cpf}
*   retorna formato do tipo: cpf: 000.000.000-00
* @param string
* @return string
*
* -------------------------------------------------------------
*/

function smarty_modifier_mascara_cpf($string) {
	if ($string == "") {
		return false;
	}
	
	$TAMANHO_MAXIMO_CAMPO = 11;
	$qtd = strlen($string);
	
	while($qtd < $TAMANHO_MAXIMO_CAMPO) {
		$string = "0".$string;
		$qtd = strlen($string);
	}
	
	if ($qtd == $TAMANHO_MAXIMO_CAMPO) {
		$n_cpf = substr($string,0,3).".".substr($string,3,3).".".substr($string,6,3)."-".substr($string,9,2);
		return $n_cpf;
	}
	else {
		return false;
	}
}
?>
