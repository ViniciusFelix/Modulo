<?php
class bdSegGrau{
	
	function bdSegGrau($db)
	{
		$this->db = $db;
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}
	
	/**
	 * Mщtodo monta a combo da presidencia. 
	 */
	function comboPresidencia()
	{
		$sql = "SELECT *
				FROM SERVENTIAS_SEG_GRAU.PRESIDENCIA
				ORDER BY DSC_SIGLA
				";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Metodo retorna o identificador do magistrado. 
	 * @param $magistrado
	 */
	function retornaMagistrado($magistrado)
	{
		$sql = "SELECT SEQ_MAGISTRADO
				FROM CORREGEDORIA_CNJ.MAGISTRADO
				WHERE NUM_CPF_MAGISTRADO = '".$magistrado."'";
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res[0]['SEQ_MAGISTRADO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retorna periodo de preenchimento das perguntas 
	 * @param unknown_type $periodo
	 */
	function comboPeriodoPreenchimento()
	{
		$sql = "SELECT DISTINCT (DSC_PERIODO_PREENCHIMENTO),SEQ_PERIODO_PREENCHIMENTO
				FROM SERVENTIAS_SEG_GRAU.PERIODO_PREENCHIMENTO
				";
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retorna periodo de preenchimento das perguntas pelo parametro passado
	 * @param unknown_type $periodo
	 */
	function retornaReferencia($seqPeriodo)
	{
		$sql = "SELECT DSC_PERIODO_PREENCHIMENTO
				FROM SERVENTIAS_SEG_GRAU.PERIODO_PREENCHIMENTO
				WHERE SEQ_PERIODO_PREENCHIMENTO = ".$this->converterTelaBanco($seqPeriodo,1)."
				";
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo retorna o lista de perguntas. 
	 * @param $magistrado
	 */
	function retornaSeqPeriodo($param)
	{
		$sql = "SELECT SEQ_PERIODO_PREENCHIMENTO FROM SERVENTIAS_SEG_GRAU.PERIODO_PREENCHIMENTO
				WHERE DSC_PERIODO_PREENCHIMENTO = ".$this->converterTelaBanco($param);
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res[0]['SEQ_PERIODO_PREENCHIMENTO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo retorna o lista de perguntas. 
	 * @param $magistrado
	 */
	function listaPerguntas($param)
	{
		$sql = "SELECT pg.* FROM SERVENTIAS_SEG_GRAU.PERGUNTA pg
				INNER JOIN SERVENTIAS_SEG_GRAU.GRUPO_PERGUNTA gp ON pg.SEQ_GRUPO_PERGUNTA = gp.SEQ_GRUPO_PERGUNTA
				INNER JOIN SERVENTIAS_SEG_GRAU.PERIODO_PERGUNTA pp ON pg.SEQ_PERGUNTA = pp.SEQ_PERGUNTA
				INNER JOIN SERVENTIAS_SEG_GRAU.PERIODO_PREENCHIMENTO prp ON pp.SEQ_PERIODO_PREENCHIMENTO = prp.SEQ_PERIODO_PREENCHIMENTO
				WHERE pg.FLG_DESTINATARIO_PERGUNTA = ".$this->converterTelaBanco($param['FLG_DESTINATARIO_PERGUNTA'])."
				AND prp.SEQ_PERIODO_PREENCHIMENTO = ".$this->converterTelaBanco($param['SEQ_PERIODO_PREENCHIMENTO'])."
				ORDER BY gp.DSC_GRUPO_APELIDO,pg.NUM_ORDEM";
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo para trazer todas as perguntas para produtividade da serventia.
	 */
	function listaPerguntaServentiaSegGrau()
	{
		$sql = "SELECT  perg.SEQ_PERGUNTA as 'seqPergunta',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.DSC_TITULO as 'descricao',
						perg.DSC_DESCRICAO as 'glossario',
						perg.NUM_ORDEM,
						'2' as 'grau'
				FROM SERVENTIAS_SEG_GRAU.PERGUNTA perg
				join SERVENTIAS_SEG_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA = dtp.SEQ_PERGUNTA and dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
				WHERE perg.FLG_DESTINATARIO_PERGUNTA='S'
				AND perg.FLG_STATUS = '1'
				ORDER BY DSC_SIGLA_PERGUNTA";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo para trazer todas as perguntas para produtividade da serventia.
	 */
	function listaPerguntaServentiaTrabalhoSegGrau()
	{
		$sql = "SELECT  perg.SEQ_PERGUNTA as 'seqPergunta',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.DSC_TITULO as 'descricao',
						perg.DSC_DESCRICAO as 'glossario',
						perg.NUM_ORDEM,
						'2' as 'grau'
				FROM SERVENTIAS_SEG_GRAU.PERGUNTA perg
				join SERVENTIAS_SEG_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA = dtp.SEQ_PERGUNTA and dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
				WHERE perg.FLG_DESTINATARIO_PERGUNTA='T'
				AND perg.FLG_STATUS = '1'
				ORDER BY DSC_SIGLA_PERGUNTA";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo para trazer todas as perguntas dos magistrados.
	 */
	function listaPerguntaMagistradoSegGrau()
	{
		$sql = "SELECT  perg.SEQ_PERGUNTA as 'seqPergunta',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.DSC_TITULO as 'descricao',
						perg.DSC_DESCRICAO as 'glossario',
						perg.NUM_ORDEM,
						'2' as 'grau'
				FROM SERVENTIAS_SEG_GRAU.PERGUNTA perg
				join SERVENTIAS_SEG_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA = dtp.SEQ_PERGUNTA and dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
				WHERE perg.FLG_DESTINATARIO_PERGUNTA='M'
				AND perg.FLG_STATUS = '1'
				ORDER BY DSC_SIGLA_PERGUNTA";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Metodo retorna o lista de perguntas. 
	 * @param $magistrado
	 */
	function listaRespostas($param)
	{
		$sql = "SELECT * FROM SERVENTIAS_SEG_GRAU.RESPOSTA
				WHERE SEQ_PRODUTIVIDADE = ".$this->converterTelaBanco($param['SEQ_PRODUTIVIDADE']);
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retonar se jс existe produtividade pelos parametro passados.
	 */
	function retornaProdutividadeServentiaSegGrau($seqOrgao,$mes,$ano)
	{
		$sql = "SELECT SEQ_PRODUTIVIDADE
					FROM SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
					WHERE SEQ_ORGAO = ".$this->util->converterTelaBanco($seqOrgao)."
					AND NUM_MES_REFERENCIA = ".$this->util->converterTelaBanco($mes)."
					AND NUM_ANO_REFERENCIA = ".$this->util->converterTelaBanco($ano);
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if ($res){
			return $res[0]['SEQ_PRODUTIVIDADE'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retonar se jс existe produtividade pelos parametro passados.
	 */
	function retornaProdutividadeMagistradoSegGrau($seqMagistrado,$seqOrgao,$mes,$ano,$tipo)
	{
		if($seqMagistrado)
			$andMag = "AND SEQ_MAGISTRADO = ".$this->util->converterTelaBanco($seqMagistrado)."";
		else 
			$andMag = "AND SEQ_MAGISTRADO is null";
		
		if(!empty($tipo)){
			$andTipo = "and IND_TIPO_ATUACAO_MES = ".$this->util->converterTelaBanco($tipo);
		}else{
			if(is_numeric($tipo) && $tipo == 0)
				$andTipo = "and IND_TIPO_ATUACAO_MES = $tipo";
			else
				$andTipo = "and IND_TIPO_ATUACAO_MES is null";
		}	
		
		$sql = "SELECT SEQ_PRODUTIVIDADE
					FROM SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
					WHERE SEQ_ORGAO 	   = ".$this->util->converterTelaBanco($seqOrgao)."
					$andMag 
					AND NUM_MES_REFERENCIA = ".$this->util->converterTelaBanco($mes)."
					AND NUM_ANO_REFERENCIA = ".$this->util->converterTelaBanco($ano)."
					$andTipo";
		$q = $this->db->execute($sql);
		if ($q){
			$res = $q->getRows();
			return $res[0]['SEQ_PRODUTIVIDADE'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo listas as respostas da produtividade do segundo grau pelo parametro passado.
	 */
	function listaRespSeg($seqProdutividade){
		$sql = "SELECT *
				FROM SERVENTIAS_SEG_GRAU.RESPOSTA
				WHERE SEQ_PRODUTIVIDADE = ".$this->converterTelaBanco($seqProdutividade,1)."
				";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if ($res){
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retonar o indice do tipo de atuaчуo do mes pelos parametros passados.
	 */
	function retornaTipo($descricao){
		$sql = "SELECT CODIGO 
					FROM COMPARTILHADO.AUXILIAR 
				WHERE DESCRICAO = ".$this->converterTelaBanco($descricao)."";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if ($res){
			return $res[0]['CODIGO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retonar o indice do tipo de atuaчуo do mes pelos parametros passados.
	 */
	function retornaAtuacao($descricao){
		$sql = "SELECT CODIGO 
					FROM COMPARTILHADO.AUXILIAR 
				WHERE DESCRICAO LIKE '".$descricao."%'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if ($res){
			return $res[0]['CODIGO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo retorna se jс tem resposta pelos parametros passados.
	 */
	function retornaSeqResposta($seqProdutividade,$seqPergunta)
	{
		$sql = "SELECT SEQ_RESPOSTA, SEQ_PRODUTIVIDADE
				FROM  SERVENTIAS_SEG_GRAU.RESPOSTA
				WHERE SEQ_PRODUTIVIDADE = ".$seqProdutividade."
				AND SEQ_PERGUNTA = ".$seqPergunta;
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['SEQ_RESPOSTA'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo para inserir a produtividade do magistrado.
	 * @param $params
	 */
	function inserirProdutividadeServentiaSegGrau($seqOrgao,$mes,$ano,$numMes,$observacao)
	{
		$sql = "INSERT INTO SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
				(
				   		SEQ_ORGAO,
						DSC_MES_REFERENCIA,
						NUM_MES_REFERENCIA,
						NUM_ANO_REFERENCIA,
						DSC_TEXTO_PRODUTIVIDADE,
						COD_USU_INCLUSAO,
						DAT_INCLUSAO,
						FLG_STATUS,
						DAT_IMPORTACAO_XML
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqOrgao).",
						'".$mes." / ".$ano."',
						".$this->util->converterTelaBanco($numMes).",
						".$this->util->converterTelaBanco($ano).",
						".$this->util->converterTelaBanco($observacao).",
						".$_SESSION['seq_usuario'].",
						NOW(),
						1,
						NOW()
				)";	
		$q 		= $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}	
	}
	
	/**
	 * Mщtodo para inserir as respostas da produtividade.
	 * @param unknown_type $seqPerg
	 * @param unknown_type $params
	 */
	function inserirRespostaSegGrau($seqProdutividade,$seqPergunta,$resposta)
	{
		$sql = "
				INSERT INTO SERVENTIAS_SEG_GRAU.RESPOSTA
				(
				   		SEQ_PRODUTIVIDADE,
				   		SEQ_PERGUNTA,
						VLR_RESPOSTA,
						DAT_INCLUSAO,
						COD_USU_INCLUSAO,
						FLG_STATUS
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqProdutividade).",
						".$this->util->converterTelaBanco($seqPergunta).",
						".$this->util->converterTelaBanco($resposta).",
						NOW(),
						".$_SESSION['seq_usuario'].",
						1		
				)";	
		$q 		= $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}	
	}
	
	/**
	 * Mщtodo para atualizar as respostas das produtividades das do segundo grau pelos parametros passados.
	 * @param $params
	 */
	function atualizarRespostaSegGrau($resposta,$seqResposta)
	{
		$sql =	"UPDATE SERVENTIAS_SEG_GRAU.RESPOSTA
					SET VLR_RESPOSTA	  = ".$this->util->converterTelaBanco($resposta).",
						DAT_ALTERACAO	  = NOW(),
						COD_USU_ALTERACAO = ".$_SESSION['seq_usuario'].",
						FLG_STATUS		  = 1
					WHERE SEQ_RESPOSTA	  = ".$this->util->converterTelaBanco($seqResposta);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqResposta;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo para inserir a produtividade do magistrado.
	 * @param $params
	 */
	function inserirProdutividadeMagistradoSegGrau($seqOrgao,$seqMagistrado,$mes,$ano,$observacao,$tipo,$qtdDias)
	{
		$sql = "INSERT INTO SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
				(
				   		SEQ_ORGAO,
						SEQ_MAGISTRADO,
						DSC_MES_REFERENCIA,
						NUM_MES_REFERENCIA,
						NUM_ANO_REFERENCIA,
						DSC_TEXTO_PRODUTIVIDADE,
						QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES,
						IND_TIPO_ATUACAO_MES,
						COD_USU_INCLUSAO,
						DAT_INCLUSAO,
						FLG_STATUS,
						DAT_IMPORTACAO_XML
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqOrgao).",
						".$this->util->converterTelaBanco($seqMagistrado).",
						'".$this->util->qualMesNumero($mes)." / ".$ano."',
						".$this->util->converterTelaBanco($mes).",
						".$this->util->converterTelaBanco($ano).",
						".$this->util->converterTelaBanco($observacao).",
						".$this->util->converterTelaBanco($qtdDias).",
						".$this->util->converterTelaBanco($tipo).",
						".$_SESSION['seq_usuario'].",
						NOW(),
						1,
						NOW()
				)";
		$q 		= $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Mщtodo para alterar as produtividades pelo parametro passado.
	 * @param unknown_type $param
	 * @return unknown|NULL
	 */
	function alterarProdutividadeServentiaSegGrau($seqProdutividade,$observacao,$qtdDias=NULL)
	{
		$sql = "UPDATE SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
				SET
					DSC_TEXTO_PRODUTIVIDADE 				 = ".$this->util->converterTelaBanco($observacao).",
					QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES = ".$this->util->converterDataParaBanco($qtdDias).",
					DAT_IMPORTACAO_XML						 = NOW()
				WHERE
					SEQ_PRODUTIVIDADE = ".$this->util->converterTelaBanco($seqProdutividade);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqProdutividade;
		}else{
			return NULL;
		}
	}
}
?>