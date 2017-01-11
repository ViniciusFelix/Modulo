<?php
/*
* Purpose: "mascara_cnpj" é uma função para mascarar cnpj.
* Example smarty code:
*   {$my_string|mascara_cnpj}
*   retorna formato do tipo: cnpj: 00.000.000/0000-00
* @param string
* @return string
*
* -------------------------------------------------------------
*/

function smarty_modifier_mascara_cnpj($string) {
	if ($string == "") {
		return false;
	}
	
	$TAMANHO_MAXIMO_CAMPO = 14;
	$qtd = strlen($string);
	
	while($qtd < $TAMANHO_MAXIMO_CAMPO) {
		$string = "0".$string;
		$qtd = strlen($string);
	}
	
	if ($qtd == $TAMANHO_MAXIMO_CAMPO) {
		$n_cnpj = substr($string,0,2).".".substr($string,2,3).".".substr($string,5,3)."/".substr($string,8,4)."-".substr($string,12,2);
		return $n_cnpj;
	}
	else {
		return false;
	}
}
?>
