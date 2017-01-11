<?php
class montaArray{

	function montaArray($db, $smarty)
	{
        $this->db = $db;
		$this->smarty = $smarty;
	}

	/**
	 * Método monta o array com os seq das perguntas recebendo o array do exel.
	 */
	function arrayPerguntasFlgDesembargador($pergunta,$exel){
		$i = 7;
		foreach ($pergunta as $key=>$value) {
			$resposta[$value['SEQ_PERGUNTA']] = $exel[$i];
			$i++; 
		}
		return $resposta;
	}
	
	function arrayPerguntasFlgCorreguedor($pergunta,$exel){
		$i = 3;
		foreach ($pergunta as $key=>$value) {
			$resposta[$value['SEQ_PERGUNTA']] = $exel[$i];
			$i++; 
		}
		return $resposta;
	}
	
	function arrayPerguntasFlgPresidente($pergunta,$exel){
		$i = 3;
		foreach ($pergunta as $key=>$value) {
			$resposta[$value['SEQ_PERGUNTA']] = $exel[$i];
			$i++; 
		}
		return $resposta;
	}
}
?>
