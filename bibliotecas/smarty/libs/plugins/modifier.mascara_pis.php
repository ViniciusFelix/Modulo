<?php
/*
* Purpose: "mascara_pis" é uma função para mascarar pis.
* Example smarty code:
*   {$my_string|mascara_pis}
*   retorna formato do tipo: pis: 000.00000.00-0
* @param string
* @return string
*
* -------------------------------------------------------------
*/

function smarty_modifier_mascara_pis($string) {
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
		$n_pis = substr($string,0,3).".".substr($string,3,5).".".substr($string,8,2)."/".substr($string,10,1);
		return $n_pis;
	}
	else {
		return false;
	}
}
?>
