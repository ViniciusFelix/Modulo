<?php
/*
* Purpose: "mascara_data_mesAno" é uma função para mascarar datas no formata de uma string única.
* Example smarty code:
*   {$my_string|mascara_data_mesAno}
*   retorna formato do tipo: dd/MM
* @param string
* @return string
*
* -------------------------------------------------------------
*/

function smarty_modifier_mascara_data_mesAno($string) {
	if ($string == "") {
		return false;
	}
	
	$TAMANHO_MAXIMO_CAMPO = 4;
	$qtd = strlen($string);
	
	while($qtd < $TAMANHO_MAXIMO_CAMPO) {
		$string = "0".$string;
		$qtd = strlen($string);
	}
	
	if ($qtd == $TAMANHO_MAXIMO_CAMPO) {
		$n_data = substr($string,0,2)."/".substr($string,2,2);
		return $n_data;
	}
	else {
		return false;
	}
}
?>
