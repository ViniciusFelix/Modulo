<?php
class bdpergunta
{
	function bdpergunta($db)
	{
		$this->db = $db;
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}
	
	/**
	 * Lista os tribunais.
	 * @return unknown
	 */
	function listaTribunal()
	{
		$sql = "select 	o.SEQ_ORGAO,
						o.DSC_ORGAO
				from corporativo.orgao o
				where o.COD_HIERARQUIA = ':1:'
				and o.FLG_ATIVO = 's'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Lista todas as perguntas cadastradas.
	 * @return unknown
	 */
	function listaPergunta()
	{
		$sql = "select 	ps.SEQ_PERGUNTA_SERVENTIA as 'seq',
						ps.DSC_SIGLA_PERGUNTA as 'sigla',
						ps.DSC_TIPO_PERGUNTA_SERVENTIA as 'descricao',
						ps.DSC_PERGUNTA as 'glossario',
						ps.NUM_ORDEM
				from serventias_prm_grau.pergunta_serventia ps
				join serventias_prm_grau.destinatario_tribunal_pergunta dtp on dtp.SEQ_PERGUNTA_SERVENTIA = ps.SEQ_PERGUNTA_SERVENTIA
				union
				select 	pseg.SEQ_PERGUNTA as 'seq',
						pseg.DSC_SIGLA_PERGUNTA as 'sigla',
						pseg.DSC_DESCRICAO as 'descricao',
					    pseg.DSC_TITULO as 'glossario',
						pseg.NUM_ORDEM
				from serventias_seg_grau.pergunta pseg
				join serventias_prm_grau.destinatario_tribunal_pergunta dtp on dtp.SEQ_PERGUNTA_SERVENTIA = pseg.SEQ_PERGUNTA
				order by NUM_ORDEM";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}

	/**
	 * Metodo inserir perguntas de primeiro grau
	 * @param unknown $parametros
	 * @return NULL
	 */
	function inserirPerguntaPrmGrau($parametros){
		$sql =	"	INSERT INTO SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA(
						DSC_SIGLA_PERGUNTA,
						DSC_PERGUNTA,
						DSC_TIPO_PERGUNTA_SERVENTIA,
						FLG_ATIVO,
						NUM_ORDEM,
						FLG_DESTINATARIO_PERGUNTA
					) VALUES 
					(
						".$this->util->converterTelaBanco($parametros[4]).",
						".$this->util->converterTelaBanco(ucfirst($parametros[5])).",
						".$this->util->converterTelaBanco($parametros[6]).",
						'1',
						".$this->util->converterTelaBanco($parametros[7]).",
						upper(".$this->util->converterTelaBanco($parametros[8]).")
					)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();	
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo altera as informaчѕes da pergunta do primeiro grau.
	 * @param unknown $parametros
	 * @return unknown|NULL
	 */
	function alterarPerguntaPrmGrau($parametros){
		$sql =	"UPDATE SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA
					SET DSC_SIGLA_PERGUNTA		  	= ".$this->util->converterTelaBanco($parametros[4]).",
						DSC_PERGUNTA 			    = ".$this->util->converterTelaBanco(ucfirst($parametros[6])).",
						DSC_TIPO_PERGUNTA_SERVENTIA	= ".$this->util->converterTelaBanco($parametros[5]).",
						NUM_ORDEM					= ".$this->util->converterTelaBanco($parametros[7]).",
						FLG_DESTINATARIO_PERGUNTA   = upper(".$this->util->converterTelaBanco($parametros[8]).")
					WHERE SEQ_PERGUNTA_SERVENTIA = '".$parametros[1]."'";
		$q = $this->db->execute($sql);
		if ($q){
			return $parametros[1];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Vincular as perguntas ao tribunal de primeiro grau.
	 * @param unknown $parametros
	 * @return NULL
	 */
	function vinculoPerguntaTribunalPrmGrau($orgao,$seqPergunta){
		$sql =	"	INSERT INTO SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA(
						SEQ_ORGAO,
						SEQ_PERGUNTA_SERVENTIA
					) VALUES
					(
						".$this->util->converterTelaBanco($orgao).",
						".$this->util->converterTelaBanco($seqPergunta)."
					)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Excluir vinculo da pergunta de primeiro grau aos tribunais na ediчуo.
	 * @param unknown $seqPergunta
	 * @return unknown|NULL
	 */
	function excluirVinculosPerguntaPrmGrau($seqPergunta){
		$sql =	"DELETE FROM SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA 
				 WHERE SEQ_PERGUNTA_SERVENTIA = ".$seqPergunta;
		$q = $this->db->execute($sql);
		if ($q){
			return $seqPergunta;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo inserir perguntas de segundo grau
	 * @param unknown $parametros
	 * @return NULL
	 */
	function inserirPerguntaSegGrau($parametros){
		$sql =	"	INSERT INTO SERVENTIAS_SEG_GRAU.PERGUNTA(
						DSC_SIGLA_PERGUNTA,
						DSC_TITULO,
						DSC_DESCRICAO,
						DAT_INCLUSAO,
						FLG_STATUS,
						NUM_ORDEM,
						FLG_DESTINATARIO_PERGUNTA
					) VALUES
					(
						".$this->util->converterTelaBanco($parametros[4]).",
						".$this->util->converterTelaBanco($parametros[5]).",
						".$this->util->converterTelaBanco(ucfirst($parametros[6])).",
						NOW(),
						'1',
						".$this->util->converterTelaBanco($parametros[7]).",
						upper(".$this->util->converterTelaBanco($parametros[8]).")
					)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo altera as informaчѕes da pergunta do segundo grau.
	 * @param unknown $parametros
	 * @return unknown|NULL
	 */
	function alterarPerguntaSegGrau($parametros){
		$sql =	"UPDATE SERVENTIAS_SEG_GRAU.PERGUNTA
					SET DSC_SIGLA_PERGUNTA		  = '".$parametros[4]."',
						DSC_TITULO 				  = '".$parametros[6]."',
						DSC_DESCRICAO			  = '".ucfirst($parametros[5])."',
						NUM_ORDEM				  = '".$parametros[8]."'
						FLG_DESTINATARIO_PERGUNTA = upper('".$parametros[8]."')
					WHERE SEQ_PERGUNTA = '".$parametros[1]."'";
		$q = $this->db->execute($sql);
		if ($q){
			return $parametros[1];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Vincular as perguntas ao tribunal de segundo grau.
	 * @param unknown $parametros
	 * @return NULL
	 */
	function vinculoPerguntaTribunalSegGrau($tri,$seqPergunta){
		$sql =	"	INSERT INTO SERVENTIAS_SEG_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA(
						SEQ_ORGAO,
						SEQ_PERGUNTA
					) VALUES
					(
						".$this->util->converterTelaBanco($tri).",
						".$this->util->converterTelaBanco($seqPergunta)."
					)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Excluir vinculo da pergunta de segundo grau aos tribunais na ediчуo.
	 * @param unknown $seqPergunta
	 * @return unknown|NULL
	 */
	function excluirVinculosPerguntaSegGrau($seqPergunta){
		$sql =	"DELETE FROM SERVENTIAS_SEG_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA
				 WHERE SEQ_PERGUNTA_SERVENTIA = ".$seqPergunta;
		$q = $this->db->execute($sql);
		if ($q){
			return $seqPergunta;
		}else{
			return NULL;
		}
	}
}
?>