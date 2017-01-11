<?php
class bdmagistrado
{
	function bdmagistrado($db)
	{
		$this->db = $db;
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}

	/**
	 * Método para trazer todos os municipios abrangidos pela serventia.
	 */
	function retornaUf($uf)
	{
		$sql = "select c.SIG_UF 
				from corporativo.cidade c
				where c.SIG_UF = '".strtoupper($uf)."'";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo busca se o magistrado já esta cadastrado pelo CPF passado por parametro.
	 * @param unknown $param
	 * @return NULL
	 */
	function buscarMagistrado($cpf)
	{
		$sql 	= " SELECT SEQ_MAGISTRADO
						FROM CORREGEDORIA_CNJ.MAGISTRADO
					WHERE NUM_CPF_MAGISTRADO = ".$cpf."";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['SEQ_MAGISTRADO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo busca se o magistrado já esta cadastrado pelo CPF passado por parametro.
	 * @param unknown $param
	 * @return NULL
	 */
	function buscarMagistradoPeloSeq($seqMagistrado){
		$sql 	= " SELECT SEQ_MAGISTRADO
						FROM CORREGEDORIA_CNJ.MAGISTRADO
					WHERE SEQ_MAGISTRADO = ".$seqMagistrado."";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['SEQ_MAGISTRADO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo inseri novo magistrado
	 * @param unknown $parametros
	 * @return NULL
	 */
	function inserirMagistrado($param)
	{
		$sql =	"	INSERT INTO CORREGEDORIA_CNJ.MAGISTRADO(
						NUM_CPF_MAGISTRADO,
						NOM_MAGISTRADO,
						NUM_MATRICULA,
						IND_UF,
						DAT_NASCIMENTO,
						DSC_EMAIL_JUIZ,
						NUM_TELEFONE_FIXO_JUIZ,
						DAT_INGRESSO_MAGISTRATURA,	
						IND_SEXO,
						DAT_INCLUSAO,
						DAT_IMPORTACAO_XML,
						FLG_STATUS
					) VALUES 
					(
						'".$this->util->converterTelaBanco($param['cpf'])."',
						".$this->util->converterTelaBanco($param['nome']).",
						".$this->util->converterTelaBanco($param['matricula']).",
						'".strtoupper($param['uf'])."',
						".$this->util->converterTelaBanco($param['datNascimento']).",
						".$this->util->converterTelaBanco($param['email']).",
						".$this->util->converterTelaBanco($param['telefone']).",
						".$this->util->converterTelaBanco($param['datPosse']).",
						".$this->util->converterTelaBanco($param['sexo']).",
						NOW(),
						NOW(),
						'".$this->util->converterTelaBanco($param['status'])."'
					)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();	
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo altera os dados do magistrado pelo seq passado por parametro.
	 * @param unknown $parametros
	 * @return unknown|NULL
	 */
	function alterarMagistrado($param)
	{
		$sql =	"UPDATE CORREGEDORIA_CNJ.MAGISTRADO
					SET NUM_CPF_MAGISTRADO		  = '".$this->util->converterTelaBanco($param['cpf'])."',
						NOM_MAGISTRADO 			  = ".$this->util->converterTelaBanco($param['nome']).",
						NUM_MATRICULA			  = ".$this->util->converterTelaBanco($param['matricula']).",
						IND_UF	 				  = ".$this->util->converterTelaBanco($param['uf']).",						
						DAT_NASCIMENTO			  = ".$this->util->converterTelaBanco($param['datNascimento']).",
						DSC_EMAIL_JUIZ			  = ".$this->util->converterTelaBanco($param['email']).",
						NUM_TELEFONE_FIXO_JUIZ	  = ".$this->util->converterTelaBanco($param['telefone']).",
						DAT_INGRESSO_MAGISTRATURA = ".$this->util->converterTelaBanco($param['datPosse']).",
						IND_SEXO				  = ".$this->util->converterTelaBanco($param['sexo']).",
						FLG_STATUS				  = '".$this->util->converterTelaBanco($param['status'])."',
						DAT_IMPORTACAO_XML		  = NOW(),
						DAT_ALTERACAO			  = NOW()
					WHERE SEQ_MAGISTRADO = ".$this->util->converterTelaBanco($param['SEQ_MAGISTRADO']);
		$q = $this->db->execute($sql);
		if ($q){
			return $param['SEQ_MAGISTRADO'];	
		}else{
			return NULL;
		}	
	}
	
	/**
	 * Método retorna se o magistrado já está vinculado ao orgão.
	 * @return unknown|NULL
	 */
	function magistradoVinculadoOrgao($seqMagistrado)
	{
		$sql 	= " SELECT SEQ_MAGISTRADO
					FROM SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA 
					WHERE SEQ_ORGAO = ".$_SESSION['seq_orgao']."
					AND SEQ_MAGISTRADO = $seqMagistrado
					AND FLG_STATUS = '1'";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['SEQ_MAGISTRADO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo que vincula o magistrado ao orgão.
	 * @param unknown $seqMagistrado
	 * @return unknown|NULL
	 */
	function vincularOrgao($seqMagistrado){
		$sql =	"INSERT INTO SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA 
						(SEQ_ORGAO,
						SEQ_MAGISTRADO,
						DAT_INCLUSAO,
						FLG_STATUS,
						COD_USU_INCLUSAO,
						DAT_IMPORTACAO_XML
						)
						VALUES
						(".$_SESSION['seq_orgao'].",
						".$seqMagistrado.",
						NOW(),
						1,
						".$_SESSION['seq_usuario'].",
						NOW()
						)";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqMagistrado;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo que altera o magistrado ao orgão.
	 * @param unknown $seqMagistrado
	 * @return unknown|NULL
	 */
	function alterarOrgao($seqMagistrado){
		$sql =	"UPDATE SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA
				SET SEQ_ORGAO = '".$_SESSION['seq_orgao']."',
					DAT_INCLUSAO = NOW(),
					FLG_STATUS = 1,
					COD_USU_INCLUSAO = '".$_SESSION['seq_usuario']."',
					DAT_IMPORTACAO_XML = NOW()
				WHERE SEQ_MAGISTRADO = '".$seqMagistrado."'";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqMagistrado;
		}else{
			return NULL;
		}
	}
}
?>