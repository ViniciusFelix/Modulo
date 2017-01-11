<?php
class bdconsulta {
	
	function bdconsulta($db)
	{
		$this->db = $db;
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}
	
	/**
	 * Método retorna o recibo de preenchimento do primeiro passo.
	 * @param unknown $data
	 * @return unknown|NULL
	 */
	function reciboPrimeiroPasso($ano)
	{
		$sql = "select 
						dataImportacao,
						count(orgao) as 'total',
						count(inclusao) as 'inclusao',
						count(alteracao) as 'alteracao'
				from(
				select 	sp.DAT_IMPORTACAO_XML as 'dataImportacao',
						o.SEQ_ORGAO as 'orgao',
						(select org.SEQ_ORGAO
						 from corporativo.orgao org
						 where org.SEQ_ORGAO = o.SEQ_ORGAO
						 and  org.DAT_INCLUSAO = sp.DAT_IMPORTACAO_XML) as 'inclusao',
						(select org.SEQ_ORGAO
						 from corporativo.orgao org
						 where org.SEQ_ORGAO = o.SEQ_ORGAO
						 and  org.DAT_ALTERACAO = sp.DAT_IMPORTACAO_XML) as 'alteracao'
				from corporativo.orgao o
				join SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU sp on sp.SEQ_ORGAO = o.SEQ_ORGAO
				where o.COD_HIERARQUIA like '%:".$_SESSION['seq_orgao'].":,".$_SESSION['cod_hierarquia']."'
				and o.TIP_ORGAO not in ('SECAO','SUBSE','COMAE','GRA1E','GRA2E','GRA1F','GRA2F','CIRCM','TRIBE','TRIBF','TRIBL','TRIBM','TRIBS','TRIBT')
				and sp.DAT_IMPORTACAO_XML like '$ano%')tb
				group by DATE(dataImportacao)";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}	
	
	/**
	 * Método retorna o recibo de preenchimento do segundo passo.
	 * @param unknown $data
	 * @return unknown|NULL
	 */
	function reciboSegundoPasso($ano,$serventia)
	{
		foreach ($serventia as $key => $value) {
			$seqOrgao[] = $_SESSION['seq_orgao'].','.$value['seq_corporativo'].',';
		}
		$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		
		$listaSeqOrgao = NULL;
		foreach ($seqOrgao as $value) {
			$listaSeqOrgao .= $value;
		}
		
		$sql = "select 
						dataImportacao,
						count(magistrado) as 'total',
						count(inclusao) as 'inclusao',
						count(alteracao) as 'alteracao'
				from(
				select 	m.SEQ_MAGISTRADO as 'magistrado',
						m.DAT_IMPORTACAO_XML as 'dataImportacao',
						(select mag.SEQ_MAGISTRADO
						 from corregedoria_cnj.magistrado mag
						 where mag.SEQ_MAGISTRADO = m.SEQ_MAGISTRADO
						 and mag.DAT_INCLUSAO = mag.DAT_IMPORTACAO_XML) as 'inclusao',
						(select mag.SEQ_MAGISTRADO
						 from corregedoria_cnj.magistrado mag
						 where mag.SEQ_MAGISTRADO = m.SEQ_MAGISTRADO
						 and mag.DAT_ALTERACAO = mag.DAT_IMPORTACAO_XML) as 'alteracao'
				from corregedoria_cnj.magistrado m
				join serventias_prm_grau.magistrado_serventia ms on  ms.SEQ_MAGISTRADO = m.SEQ_MAGISTRADO
				where m.DAT_IMPORTACAO_XML like '$ano%'
				and ms.SEQ_ORGAO in ($listaSeqOrgao)
				and ms.FLG_STATUS = '1'
				group by m.SEQ_MAGISTRADO)tb
				group by DATE(dataImportacao)";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna o recibo de preenchimento do terceiro passo.
	 * @param unknown $data
	 * @return unknown|NULL
	 */
	function reciboTerceiroPasso($mes,$ano,$serventia)
	{
		foreach ($serventia as $key => $value) {
			$seqOrgao[] = $value['seq_corporativo'].',';
		}
		$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		
		$listaSeqOrgao = NULL;
		foreach ($seqOrgao as $value) {
			$listaSeqOrgao .= $value;
		}
		
		$sql = "select 
						mes,
						dataImportacao,
						dataInclusao,
						count(dataInclusao) as 'qtdInclusao',
						count(dataAlteracao) as 'qtdAlteracao'
				from(
				SELECT
					ps.MES_REFERENCIA as 'mes',
					ac.DAT_IMPORTACAO as 'dataImportacao',
					ps.DAT_IMPORTACAO_XML as 'dataInclusao',
					rs.DAT_ALTERACAO as 'dataAlteracao'
				FROM SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA ps
				join serventias_prm_grau.resposta_serventia rs on rs.SEQ_PRODUTIVIDADE_SERVENTIA = ps.SEQ_PRODUTIVIDADE_SERVENTIA
				join serventias_prm_grau.pergunta_serventia pp on pp.SEQ_PERGUNTA_SERVENTIA = rs.SEQ_PERGUNTA_SERVENTIA and pp.FLG_DESTINATARIO_PERGUNTA = 'S' and pp.FLG_AUXILIAR = 'P'
				LEFT join compartilhado.arquivo_modulo_xml_cron ac on ac.SEQ_ARQUIVO_MODULO_XML_CRON = ps.SEQ_ARQUIVO_MODULO_XML_CRON
				WHERE ps.SEQ_ORGAO IN ($listaSeqOrgao)
				and (ps.DAT_IMPORTACAO_XML like '$ano-$mes%' or ac.DAT_IMPORTACAO like '$ano-$mes%')
				group by ps.SEQ_PRODUTIVIDADE_SERVENTIA
				union all
				SELECT
					p.DSC_MES_REFERENCIA as 'mes',
					ac.DAT_IMPORTACAO as 'dataImportacao',
					p.DAT_IMPORTACAO_XML as 'dataInclusao',
					rs.DAT_ALTERACAO as 'dataAlteracao'
				FROM serventias_seg_grau.produtividade p
				join serventias_prm_grau.resposta_serventia rs on rs.SEQ_PRODUTIVIDADE_SERVENTIA = p.SEQ_PRODUTIVIDADE
				join serventias_prm_grau.pergunta_serventia pp on pp.SEQ_PERGUNTA_SERVENTIA = rs.SEQ_PERGUNTA_SERVENTIA and pp.FLG_DESTINATARIO_PERGUNTA = 'S' and pp.FLG_AUXILIAR = 'P'
				LEFT join compartilhado.arquivo_modulo_xml_cron ac on ac.SEQ_ARQUIVO_MODULO_XML_CRON = p.SEQ_ARQUIVO_MODULO_XML_CRON
				WHERE p.SEQ_ORGAO IN ($listaSeqOrgao)
				and (p.DAT_IMPORTACAO_XML like '$ano-$mes%' or ac.DAT_IMPORTACAO like '$ano-$mes%')
				group by p.SEQ_PRODUTIVIDADE)tb
				group by mes,DATE(dataInclusao)
				order by dataInclusao";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna o recibo de preenchimento do quarto passo.
	 * @param unknown $data
	 * @return unknown|NULL
	 */
	function reciboQuartoPasso($mes,$ano,$serventia)
	{
		foreach ($serventia as $key => $value) {
			$seqOrgao[] = $value['seq_corporativo'].',';
		}
		$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
	
		$listaSeqOrgao = NULL;
		foreach ($seqOrgao as $value) {
			$listaSeqOrgao .= $value;
		}
	
		$sql = "select 
						mes,
						dataInclusao,
						dataImportacao,
						count(DATE(dataInclusao)) as 'qtdInclusao',
						count(DATE(dataAlteracao)) as 'qtdAlteracao'
				from(select 
						org,
						mag,
						mes,
						dataInclusao,
						dataImportacao,
						dataAlteracao
				from(
				select 	pm.SEQ_ORGAO as 'org',
						pm.SEQ_MAGISTRADO as 'mag',
						pm.MES_REFERENCIA as 'mes',
						pm.DAT_IMPORTACAO_XML as 'dataInclusao',
						(select rs.DAT_ALTERACAO
						 from SERVENTIAS_PRM_GRAU.resposta_serventia rs
						 where rs.SEQ_PRODUTIVIDADE_MAGISTRADO = pm.SEQ_PRODUTIVIDADE_MAGISTRADO
						 limit 1) as 'dataAlteracao',
						ac.DAT_IMPORTACAO as 'dataImportacao'
				from SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO pm
				LEFT join compartilhado.arquivo_modulo_xml_cron ac on ac.SEQ_ARQUIVO_MODULO_XML_CRON = pm.SEQ_ARQUIVO_MODULO_XML_CRON
				where pm.SEQ_ORGAO in ($listaSeqOrgao)
				and (ac.DAT_IMPORTACAO like '$ano-$mes%' or pm.DAT_IMPORTACAO_XML like '$ano-$mes%')
				AND pm.FLG_STATUS = 1
				group by pm.SEQ_PRODUTIVIDADE_MAGISTRADO
				union all
				select  p.SEQ_ORGAO as 'org',
						p.SEQ_MAGISTRADO as 'mag',
						p.DSC_MES_REFERENCIA as 'mes',
						p.DAT_IMPORTACAO_XML as 'dataInclusao',
						(select r.DAT_ALTERACAO
						 from serventias_seg_grau.resposta r
						 where r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
						 limit 1) as 'dataAlteracao',
						ac.DAT_IMPORTACAO as 'dataImportacao'
				from serventias_seg_grau.produtividade p
				LEFT join compartilhado.arquivo_modulo_xml_cron ac on ac.SEQ_ARQUIVO_MODULO_XML_CRON = p.SEQ_ARQUIVO_MODULO_XML_CRON
				join serventias_seg_grau.resposta r on r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join serventias_seg_grau.pergunta perg on perg.SEQ_PERGUNTA = r.SEQ_PERGUNTA and perg.FLG_DESTINATARIO_PERGUNTA = 'M'
				where p.SEQ_ORGAO in ($listaSeqOrgao)
				and (ac.DAT_IMPORTACAO like '$ano-$mes%' or p.DAT_IMPORTACAO_XML like '$ano-$mes%')
				AND p.FLG_STATUS = 1
				group by p.SEQ_PRODUTIVIDADE)tb
				group by org,mag,mes,DATE(dataInclusao))tb2
				group by mes,DATE(dataInclusao)
				order by DATE(dataInclusao)";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método lista as serventias.
	 * @param unknown $uf
	 * @return unknown
	 */
	function listaServentia($uf)
	{
		if ($_SESSION['seq_orgao_pai'] != 1){
			$uf_origem = "AND IND_UF = '$uf'";
		}
		
		$sql ="SELECT 	SEQ_SERVENTIA_JUDICIAL,
						DSC_DENOM_SERVENTIA_JUDICIAL,
						IND_UF,
						COD_MUNICIPIO_LOCALIDADE,
						ID_CIDADE,
						DS_CIDADE,
						DAT_IMPORTACAO_XML,
						SEQ_ORGAO
				FROM serventias_prm_grau.serventia_judicial_prm_grau pg
				JOIN COMPARTILHADO.TB_CIDADE tc ON tc.ID_CIDADE = pg.COD_MUNICIPIO_LOCALIDADE
				WHERE FLG_STATUS_SERVENTIA = 1
				AND DAT_IMPORTACAO_XML IS NOT NULL
				$uf_origem
				ORDER BY IND_UF,DS_CIDADE,DSC_DENOM_SERVENTIA_JUDICIAL";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna os dados da serventia pelo seq passado por parametro.
	 * @param unknown $id_dados_serventia
	 * @return unknown
	 */
	function retornaDadosServentia($seqOrgao)
	{
		$sql =" SELECT
					    org.SEQ_ORGAO,
					    CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN
						CONCAT(org.INT_ORDEM_ORGAO, org.DSC_ORGAO)
						ELSE org.DSC_ORGAO END AS 'Denominacao',
						org.DSC_ORGAO,
						org.TIP_ORGAO,
						cid.DS_CIDADE AS 'Municipio',
						cid.CO_UF AS 'UF'
				FROM corporativo.orgao org
				LEFT JOIN COMPARTILHADO.TB_CIDADE cid ON org.SEQ_CIDADE = cid.ID_CIDADE
				WHERE org.SEQ_ORGAO = '$seqOrgao'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para retornar os magistrados da serventia selecionado pelo seq_serventia_judicial
	 * passado por parametro.
	 * @param unknown_type $id_dado_serventia
	 */
	function retornaMagistradoServentia($seqOrgao,$group=NULL)
	{
		$listaSeqOrgao = NULL;
		$order = NULL;
		if($group == 1)
			$group = "group by ms.SEQ_MAGISTRADO";
		
		if(count($seqOrgao) > 1){
			foreach ($seqOrgao as $value) {
				$listaSeqOrgao .= $value;
			}
			$where = "WHERE ms.SEQ_ORGAO in (".$_SESSION['seq_orgao'].",$listaSeqOrgao)";
		}else{
			if(!empty($seqOrgao))
				$where = "WHERE ms.SEQ_ORGAO in ($seqOrgao[0],".$_SESSION['seq_orgao'].")";
			else
				$where = "WHERE ms.SEQ_ORGAO = ".$_SESSION['seq_orgao'];
			$order = "ORDER BY Nome,ms.IND_TIPO_JUIZ";
		}
		$sql = "SELECT 	DISTINCT ms.SEQ_ORGAO,
						mag.SEQ_MAGISTRADO,
						mag.NOM_MAGISTRADO AS 'Nome',
						mag.NUM_CPF_MAGISTRADO,
						mag.NUM_MATRICULA,
						mag.IND_UF,
						mag.DAT_NASCIMENTO,
						mag.DSC_EMAIL_JUIZ,
						mag.NUM_TELEFONE_FIXO_JUIZ,
						mag.DAT_INGRESSO_MAGISTRATURA,
						mag.IND_SEXO,
						mag.FLG_STATUS,
						ms.IND_TIPO_JUIZ,
						ms.DAT_INGRESSO_SERVENTIA,
						ms.DAT_SAIDA_SERVENTIA,
						aux.DESCRICAO AS 'Tipo',
						org.SEQ_ORGAO,
						CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN
						CONCAT(org.INT_ORDEM_ORGAO, org.DSC_ORGAO)
						ELSE org.DSC_ORGAO END AS 'Denominacao'
				FROM SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA ms
				JOIN CORREGEDORIA_CNJ.MAGISTRADO mag ON ms.SEQ_MAGISTRADO = mag.SEQ_MAGISTRADO
				LEFT JOIN COMPARTILHADO.AUXILIAR aux ON ms.IND_TIPO_JUIZ = aux.CODIGO AND aux.NOME_COLUNA = 'ind_tipo_juiz'
				JOIN corporativo.orgao org ON org.SEQ_ORGAO = ms.SEQ_ORGAO
				$where
				$group
				$order";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna a produtividade da serventia pelos seq_serventia_judicial e pelo ano
	 * passado por parametro.
	 * @param unknown_type $id_dado_serventia
	 * @param unknown_type $ano
	 */
	function retornaProdutividadesServentia($seqOrgao)
	{
		$sql ="SELECT
						org.SEQ_ORGAO,
						ps.SEQ_PRODUTIVIDADE_SERVENTIA,
						CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN
						CONCAT(org.INT_ORDEM_ORGAO, org.DSC_ORGAO)
						ELSE org.DSC_ORGAO END AS 'Denominacao',
						cid.DSC_CIDADE AS 'municipio',
						cid.SIG_UF AS uf,
						ps.MES_REFERENCIA as 'mes',
						CONCAT(SUBSTR(ps.DAT_INICIAL,9,2),'/',
						SUBSTR(ps.DAT_INICIAL,6,2),'/',
						SUBSTR(ps.DAT_INICIAL,1,4)) AS 'data_inicial',
						CONCAT(SUBSTR(ps.DAT_FINAL,9,2),'/',
						SUBSTR(ps.DAT_FINAL,6,2),'/',
						SUBSTR(ps.DAT_FINAL,1,4)) AS 'data_final'
				FROM  corporativo.orgao org
				INNER JOIN  corporativo.cidade cid ON org.SEQ_CIDADE = cid.SEQ_CIDADE
				INNER JOIN  SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA ps ON org.SEQ_ORGAO = ps.SEQ_ORGAO AND ps.FLG_STATUS = '1'
				WHERE  org.SEQ_ORGAO = '$seqOrgao'
				AND ps.DAT_IMPORTACAO_XML IS NOT NULL";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna a produtividade dos magistrados pelos seq_serventia_judicial e ano
	 * passado por parametro
	 * @param unknown_type $id_dado_serventia
	 * @param unknown_type $ano
	 */
	function retornaProdutividadesMagistrados($seqOrgao)
	{
		$sql =" SELECT
						org.SEQ_ORGAO,
						pm.SEQ_PRODUTIVIDADE_MAGISTRADO,
						mag.NOM_MAGISTRADO AS 'magistrado',
						aux.DESCRICAO AS 'tipo',
						pm.MES_REFERENCIA AS 'mes',
						CONCAT(SUBSTR(pm.DAT_INICIAL,9,2),'/',
						SUBSTR(pm.DAT_INICIAL,6,2),'/',
						SUBSTR(pm.DAT_INICIAL,1,4)) AS 'data_inicial',
						CONCAT(SUBSTR(pm.DAT_FINAL,9,2),'/',
						SUBSTR(pm.DAT_FINAL,6,2),'/',
						SUBSTR(pm.DAT_FINAL,1,4)) AS 'data_final'
				FROM SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO pm
				INNER JOIN corporativo.orgao org ON pm.SEQ_ORGAO = org.SEQ_ORGAO
				INNER JOIN CORREGEDORIA_CNJ.MAGISTRADO mag ON pm.SEQ_MAGISTRADO = mag.SEQ_MAGISTRADO
				INNER JOIN SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA ms ON ms.SEQ_MAGISTRADO = mag.SEQ_MAGISTRADO
				INNER JOIN COMPARTILHADO.AUXILIAR aux ON ms.IND_TIPO_JUIZ = aux.CODIGO AND aux.NOME_COLUNA = 'ind_tipo_juiz'
				WHERE  org.SEQ_ORGAO = $seqOrgao
				AND pm.DAT_IMPORTACAO_XML IS NOT NULL
				GROUP BY SEQ_PRODUTIVIDADE_MAGISTRADO";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna os dados das serventias pelos seq_serventia_judicial passado por parametro.
	 * @param unknown_type $id_serventia_login
	 */
	function retornaDadosComplementares ($seqOrgao)
	{
		$sql = "SELECT  dcs.SEQ_DADOS_SERVENTIA,
						dcs.SEQ_SERVENTIA_JUDICIAL,
						dcs.DAT_INSTALACAO_SERVENTIA,
						CONCAT(SUBSTR(dcs.DAT_INSTALACAO_SERVENTIA,9,2),'/',SUBSTR(dcs.DAT_INSTALACAO_SERVENTIA,6,2),'/',SUBSTR(dcs.DAT_INSTALACAO_SERVENTIA,1,4)) AS 'DAT_INSTALACAO_FORMATADO',
						dcs.QTD_SERVIDORES_CONCURSADOS AS 'servidores',
						dcs.QTD_FUNC_TERCEIRIZADOS as 'terceirizados',
						dcs.QTD_SERVIDORES_AFASTADOS as 'afastados',
						dcs.QTD_SERVIDORES_EFETIVOS as 'efetivos',
						dcs.QTD_SERVIDORES_CEDIDOS as 'cedidos',
						dcs.QTD_OUTROS as 'outros',
						CONCAT(SUBSTR(dcs.DAT_INCLUSAO,9,2),'/',SUBSTR(dcs.DAT_INCLUSAO,6,2),'/',SUBSTR(dcs.DAT_INCLUSAO,1,4)) AS 'DAT_ULTIMA_INCLUSAO',
						dcs.DAT_INCLUSAO,
						dcs.DAT_ALTERACAO,
						dcs.COD_USU_INCLUSAO,
						dcs.DSC_IP_USU_INCLUSAO,
						LATITUDE,
						LONGITUDE
		FROM 	SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA dcs
		WHERE 	 dcs.SEQ_ORGAO = '$seqOrgao'
		ORDER BY dcs.SEQ_DADOS_SERVENTIA desc
		LIMIT 1";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Metodo retorna a lista as cidades da uf da serventia logada no sistema.
	 * @return unknown
	 */
	function listaCidade($uf)
	{
		$sql = "SELECT	*
				FROM COMPARTILHADO.TB_CIDADE
				WHERE CO_UF ='$uf'";
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Metodo retorna os dados da secretaria da serventia e seu responsável.
	 * @return unknown
	 */
	function retornaSecretaria($orgao)
	{
		$sql = "SELECT
					CONCAT(SUBSTR(NUM_CPF_RESPONSAVEL_SECRETARIA,1,3),'.',SUBSTR(NUM_CPF_RESPONSAVEL_SECRETARIA,4,3),'.',SUBSTR(NUM_CPF_RESPONSAVEL_SECRETARIA,7,3),'-',SUBSTR(NUM_CPF_RESPONSAVEL_SECRETARIA,10,2)) AS 'NUM_CPF_RESPONSAVEL_SECRETARIA',
					SEQ_SECRETARIA_SERVENTIA,
					NOM_RESPONSAVEL_SECRETARIA,
					IND_TIPO_RESPONSAVEL,
					DSC_EMAIL_SECRETARIA,
					CONCAT('(',SUBSTR(NUM_TELEFONE_SECRETARIA,1,2),')' ,SUBSTR(NUM_TELEFONE_SECRETARIA,3,4),'-',SUBSTR(NUM_TELEFONE_SECRETARIA,7,4)) AS 'NUM_TELEFONE_SECRETARIA',
					CONCAT('(',SUBSTR(NUM_TELEFONE_OUTROS,1,2),')' ,SUBSTR(NUM_TELEFONE_OUTROS,3,4),'-',SUBSTR(NUM_TELEFONE_OUTROS,7,4)) AS 'NUM_TELEFONE_OUTROS',
					IND_UF,
					COD_CIDADE,
					DSC_CIDADE,
					DSC_ENDERECO_SECRETARIA,
					COD_CEP,
					DSC_BAIRRO_SECRETARIA,
					DSC_END_COMPLEMENTO_SECRETARIA,
					NUM_END_SECRETARIA
				FROM SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA ss
				JOIN corporativo.cidade cid ON ss.COD_CIDADE = cid.SEQ_CIDADE
				WHERE SEQ_ORGAO = ".$this->util->converterTelaBanco($orgao,1)."
				ORDER BY SEQ_SECRETARIA_SERVENTIA DESC
				LIMIT 1";
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0];
	}
	
	/**
	 * Método retorna o tribunal
	 * @return unknown
	 */
	function retornaTribunal($seqOrgaoPai)
	{
		$sql = "SELECT o.SEQ_ORGAO, o.DSC_ORGAO, o.SEQ_ORGAO_PAI, o.TIP_ORGAO, so.DSC_SIGLA
				FROM corporativo.orgao o
				LEFT JOIN corporativo.sigla_orgao so on so.SEQ_ORGAO = o.SEQ_ORGAO
				WHERE o.SEQ_ORGAO = ".$seqOrgaoPai;
		print_r('<pre>'.$sql.'</pre>');
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0];
	}
	
	/**
	 * Método retorna o tipo do tribunal.
	 */
	function tipoTribunal($siglaTribunal)
	{
		$sql = "SELECT 	SUBSTR(aux.DESCRICAO,9) AS 'tipo'
				FROM COMPARTILHADO.TRIBUNAL tb
				JOIN COMPARTILHADO.AUXILIAR aux
				ON tb.IND_TIPO_JUSTICA = aux.CODIGO
				AND aux.NOME_COLUNA = 'IND_TIPO_JUSTICA'
				WHERE tb.SIG_TRIBUNAL = '".$siglaTribunal."'";
		print_r('<pre>'.$sql.'</pre>');
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0]['tipo'];
	}
	
	/**
	 * Produtividade das serventias do tribunal pelo período informado.
	 * @param unknown $periodo
	 * @return unknown|NULL
	 */
	function produtividadeServentiaPrmGrauXls($periodo,$serventia)
	{
		foreach ($serventia as $key => $value) {
			$seqOrgao[] = $value['seq_corporativo'].',';
		}
		$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		
		$listaSeqOrgao = NULL;
		foreach ($seqOrgao as $value) {
			$listaSeqOrgao .= $value;
		}
		
		$sql = "SELECT
					org.SEQ_ORGAO,
					CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN
					CONCAT(org.INT_ORDEM_ORGAO, org.DSC_ORGAO)
					ELSE org.DSC_ORGAO END AS 'Denominacao',
					pg.SEQ_PERGUNTA_SERVENTIA AS 'seqPergunta',
					pg.DSC_SIGLA_PERGUNTA AS 'sigla',
					pg.NUM_ORDEM,
					rs.VLR_RESPOSTA AS 'valor',
					ps.DSC_TEXTO_PRODUTIVIDADE as 'observacao'
				FROM SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs
				JOIN SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA ps ON rs.SEQ_PRODUTIVIDADE_SERVENTIA = ps.SEQ_PRODUTIVIDADE_SERVENTIA
				JOIN SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA pg ON rs.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA and pg.FLG_DESTINATARIO_PERGUNTA = 'S'
				join serventias_prm_grau.destinatario_tribunal_pergunta dtp on dtp.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA
				join corporativo.orgao org on org.SEQ_ORGAO = ps.SEQ_ORGAO
				WHERE ps.MES_REFERENCIA = '$periodo'
				and org.SEQ_ORGAO IN ($listaSeqOrgao)
				ORDER BY pg.NUM_ORDEM";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as resposta das produtividades das serventia de primeiro grau pelo seq_produtividade_serventia
	 * passado por parametro.
	 * @param unknown_type $seq_produtividade_serventia
	 */
	function respostaProdutividadeServentiaPrmGrau($mesReferencia,$seqOrgao)
	{
		$sql = "SELECT
						org.SEQ_ORGAO,
						CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN
						CONCAT(org.INT_ORDEM_ORGAO, org.DSC_ORGAO)
						ELSE org.DSC_ORGAO END AS 'Denominacao',
						ps.MES_REFERENCIA AS 'mes',
						pg.DSC_SIGLA_PERGUNTA AS 'sigla',
						pg.DSC_TIPO_PERGUNTA_SERVENTIA AS 'descricao',
						pg.DSC_PERGUNTA AS 'glossario',
						pg.NUM_ORDEM,
						rs.VLR_RESPOSTA AS 'valor'
				FROM 	SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs
				JOIN SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA ps ON rs.SEQ_PRODUTIVIDADE_SERVENTIA = ps.SEQ_PRODUTIVIDADE_SERVENTIA
				JOIN SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA pg ON rs.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA and pg.FLG_DESTINATARIO_PERGUNTA = 'S'
				JOIN serventias_prm_grau.destinatario_tribunal_pergunta dp on dp.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA
				join corporativo.orgao org on org.SEQ_ORGAO = ps.SEQ_ORGAO
				WHERE ps.SEQ_ORGAO = $seqOrgao
				AND   ps.MES_REFERENCIA = '$mesReferencia'
				group by rs.SEQ_PERGUNTA_SERVENTIA
				ORDER BY pg.NUM_ORDEM";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as resposta das produtividades das serventia de primeiro grau pelo seq_produtividade_serventia
	 * passado por parametro.
	 * @param unknown_type $seq_produtividade_serventia
	 */
	function respostaProdutividadeTrabalhoPrmGrau($seqOrgao)
	{
		$sql = "select sigla,
					   descricao,
					   glossario,
					   NUM_ORDEM,
					   valor
				from(
				SELECT
						pg.DSC_SIGLA_PERGUNTA AS 'sigla',
						pg.DSC_TIPO_PERGUNTA_SERVENTIA AS 'descricao',
						pg.DSC_PERGUNTA AS 'glossario',
						pg.NUM_ORDEM,
						rs.VLR_RESPOSTA AS 'valor'
				FROM 	SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs
				JOIN SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA ps ON rs.SEQ_PRODUTIVIDADE_SERVENTIA = ps.SEQ_PRODUTIVIDADE_SERVENTIA
				JOIN SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA pg ON rs.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA and pg.FLG_DESTINATARIO_PERGUNTA = 'T'
				join corporativo.orgao org on org.SEQ_ORGAO = ps.SEQ_ORGAO
				WHERE ps.SEQ_ORGAO = $seqOrgao
				ORDER BY ps.SEQ_PRODUTIVIDADE_SERVENTIA desc)dados
				group by NUM_ORDEM
				order by dados.NUM_ORDEM";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as resposta das produtividades das serventia de segundo grau pelo seq_orgao e mes de referencia
	 * passado por parametro.
	 * @param unknown_type $seq_orgao
	 * @param unknown_type $mes
	 */
	function produtividadeServentiaSegGrauXls($periodo,$serventia)
	{
		foreach ($serventia as $key => $value) {
			$seqOrgao[] = $value['seq_corporativo'].',';
		}
		$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		
		$listaSeqOrgao = NULL;
		foreach ($seqOrgao as $value) {
			$listaSeqOrgao .= $value;
		}
		
		$sql = "select  p.SEQ_ORGAO,
						perg.DSC_TITULO AS 'descricao',
						perg.DSC_SIGLA_PERGUNTA AS 'sigla',
						perg.DSC_DESCRICAO AS 'glossario',
						r.VLR_RESPOSTA AS 'valor',
						perg.NUM_ORDEM,
						perg.SEQ_PERGUNTA AS 'seqPergunta',
						p.DSC_TEXTO_PRODUTIVIDADE as 'observacao'
				from SERVENTIAS_SEG_GRAU.PRODUTIVIDADE p
				join SERVENTIAS_SEG_GRAU.RESPOSTA r on r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join SERVENTIAS_SEG_GRAU.PERGUNTA perg on perg.SEQ_PERGUNTA = r.SEQ_PERGUNTA and perg.FLG_DESTINATARIO_PERGUNTA = 'S'
				join serventias_seg_grau.destinatario_tribunal_pergunta dtp on dtp.SEQ_PERGUNTA = perg.SEQ_PERGUNTA
				where p.DSC_MES_REFERENCIA = '$periodo'
				and p.SEQ_ORGAO in ($listaSeqOrgao)";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as resposta das produtividades das serventia de segundo grau pelo seq_orgao e mes de referencia
	 * passado por parametro.
	 * @param unknown_type $seq_orgao
	 * @param unknown_type $mes
	 */
	function respostaProdutividadeServentiaSegGrau($mes,$seq_orgao)
	{
		$sql = "select  perg.DSC_TITULO AS 'descricao',
						perg.DSC_SIGLA_PERGUNTA AS 'sigla',
						perg.DSC_DESCRICAO AS 'glossario',
						r.VLR_RESPOSTA AS 'valor',
						perg.NUM_ORDEM
				from SERVENTIAS_SEG_GRAU.PRODUTIVIDADE p
				join SERVENTIAS_SEG_GRAU.RESPOSTA r on r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join SERVENTIAS_SEG_GRAU.PERGUNTA perg on perg.SEQ_PERGUNTA = r.SEQ_PERGUNTA and perg.FLG_DESTINATARIO_PERGUNTA = 'S'
				JOIN serventias_seg_grau.destinatario_tribunal_pergunta dp on dp.SEQ_PERGUNTA = perg.SEQ_PERGUNTA
				where p.DSC_MES_REFERENCIA = '$mes'
				and   p.SEQ_ORGAO = $seq_orgao
				group by perg.SEQ_PERGUNTA";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as resposta das produtividades das serventia de segundo grau pelo seq_orgao e mes de referencia
	 * passado por parametro.
	 * @param unknown_type $seq_orgao
	 * @param unknown_type $mes
	 */
	function respostaProdutividadeTrabalhoSegGrau($seq_orgao)
	{
		$sql = "select descricao,
				       sigla,
				       glossario,
					   valor,
					   NUM_ORDEM
				from(
				select  perg.DSC_TITULO AS 'descricao',
						perg.DSC_SIGLA_PERGUNTA AS 'sigla',
						perg.DSC_DESCRICAO AS 'glossario',
						r.VLR_RESPOSTA AS 'valor',
						perg.NUM_ORDEM
				from serventias_seg_grau.produtividade p
				join serventias_seg_grau.resposta r on r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join serventias_seg_grau.pergunta perg on perg.SEQ_PERGUNTA = r.SEQ_PERGUNTA and perg.FLG_DESTINATARIO_PERGUNTA = 'T'
				where p.SEQ_ORGAO = $seq_orgao
				order by p.SEQ_PRODUTIVIDADE desc)dados
				group by dados.NUM_ORDEM
				order by dados.NUM_ORDEM";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as produtividades dos magistrados para o XLS
	 * passado por parametro.
	 * @param unknown_type $periodo
	 * @param unknown_type $serventia
	 */
	function produtividadeMagistradoPrmGrauXls($periodo,$serventia)
	{
		foreach ($serventia as $key => $value) {
			$seqOrgao[] = $value['seq_corporativo'].',';
		}
		$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		
		$listaSeqOrgao = NULL;
		foreach ($seqOrgao as $value) {
			$listaSeqOrgao .= $value;
		}
		
		$sql = "select 	pm.SEQ_ORGAO, 
						pm.SEQ_MAGISTRADO,
						pm.DSC_TEXTO_PRODUTIVIDADE as 'observacao',
						pm.IND_TIPO_JUIZ as 'tipo',
						pm.DAT_INICIAL as 'datInicio',
						pm.DAT_FINAL as 'datFinal',
						pm.QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES as 'qtdDias',
						ps.DSC_SIGLA_PERGUNTA as 'sigla',
						ps.NUM_ORDEM,
						rs.VLR_RESPOSTA as 'resposta'
				from SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO pm
				join SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs on rs.SEQ_PRODUTIVIDADE_MAGISTRADO = pm.SEQ_PRODUTIVIDADE_MAGISTRADO
				join SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA ps on ps.SEQ_PERGUNTA_SERVENTIA = rs.SEQ_PERGUNTA_SERVENTIA
				 and ps.FLG_DESTINATARIO_PERGUNTA = 'M' 
				 and ps.FLG_AUXILIAR = 'P'
				where pm.SEQ_ORGAO in ($listaSeqOrgao)
				AND pm.MES_REFERENCIA = '$periodo'
				AND pm.FLG_STATUS = 1";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna as respostas das produtividades dos magistrados pelo seq_produtividade_magistrado
	 * passado por parametro.
	 * @param unknown_type $seq_produtividade_magistrado
	 */
	function respostaProdutividadeMagistradoPrmGrau($seqOrgao,$mes,$seqMagistrado=null,$tipo=null)
	{
		if($seqMagistrado)
			$andMag = "and pm.SEQ_MAGISTRADO = ".$this->util->converterTelaBanco($seqMagistrado);
		else
			$andMag = "and pm.SEQ_MAGISTRADO is NULL";

		$tipo = trim($tipo);
		if(!empty($tipo)){
			$andTipo = "and pm.IND_TIPO_JUIZ = $tipo";
		}else{ 
			if(is_numeric($tipo) && $tipo == 0)
				$andTipo = "and pm.IND_TIPO_JUIZ = $tipo";
			else 
				$andTipo = "and pm.IND_TIPO_JUIZ is NULL";
		}
		
		$sql = "SELECT
					pm.SEQ_PRODUTIVIDADE_MAGISTRADO,
					pm.SEQ_ORGAO,
					pm.SEQ_MAGISTRADO,
					mag.NOM_MAGISTRADO AS 'magistrado',
					pm.MES_REFERENCIA AS 'mes',
					pm.DAT_INICIAL as 'inicio',
					pm.DAT_FINAL as 'fim',
					pm.QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES as 'qtdDias',
					pm.DSC_TEXTO_PRODUTIVIDADE as 'obs',
					pg.DSC_TIPO_PERGUNTA_SERVENTIA AS 'descricao',
					pg.DSC_PERGUNTA AS 'glossario',
					pg.DSC_SIGLA_PERGUNTA AS 'sigla',
					pg.NUM_ORDEM,
					rs.VLR_RESPOSTA AS 'resposta',
					aux.DESCRICAO as 'tipo'
				FROM SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs
				JOIN SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO pm ON rs.SEQ_PRODUTIVIDADE_MAGISTRADO = pm.SEQ_PRODUTIVIDADE_MAGISTRADO AND pm.FLG_STATUS = '1'
				JOIN SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA pg ON rs.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA 
				 and pg.FLG_DESTINATARIO_PERGUNTA = 'M'
				 and pg.FLG_AUXILIAR = 'P'
				LEFT JOIN CORREGEDORIA_CNJ.MAGISTRADO mag ON pm.SEQ_MAGISTRADO = mag.SEQ_MAGISTRADO
				LEFT JOIN COMPARTILHADO.AUXILIAR aux ON pm.IND_TIPO_JUIZ = aux.CODIGO AND aux.NOME_COLUNA = 'IND_TIPO_JUIZ'
				WHERE rs.FLG_STATUS = '1'
				AND   pm.SEQ_ORGAO = '$seqOrgao'
				AND   pm.MES_REFERENCIA = '$mes'
				$andTipo
				$andMag
				GROUP BY  pg.SEQ_PERGUNTA_SERVENTIA
				ORDER BY  pg.NUM_ORDEM";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return array();
		}
	}
	
	/**
	 * Método retorna as produtividades dos magistrados para o XLS
	 * passado por parametro.
	 * @param unknown_type $periodo
	 * @param unknown_type $serventia
	 */
	function produtividadeMagistradoSegGrauXls($periodo,$serventia,$magistrado=NULL,$tipo=NULL)
	{
		if(count($serventia)>1){
			foreach ($serventia as $key => $value) {
				$seqOrgao[] = $value['seq_corporativo'].',';
			}
			$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		
			$listaSeqOrgao = NULL;
			foreach ($seqOrgao as $value) {
				$listaSeqOrgao .= $value;
			}
		}else{
			$listaSeqOrgao = $serventia;
		}
		
		if(count($magistrado)>1){
			foreach ($magistrado as $key => $value) {
				$seqMagistrado[] = $value['SEQ_MAGISTRADO'].',';
			}
			$seqMagistrado[count($seqMagistrado) - 1] = substr($seqMagistrado[count($seqMagistrado) - 1], 0, strlen($seqMagistrado[count($seqMagistrado) - 1])-1);
			
			foreach ($seqMagistrado as $value) {
				$listaMagistrado .= $value;
			}
		}else{
			$listaMagistrado = $magistrado;
		}
		
		$andTipo = NULL;
		if($tipo)
			$andTipo = "and p.IND_TIPO_ATUACAO_MES = $tipo";
		
		$sql = "select  p.SEQ_ORGAO,
						p.SEQ_MAGISTRADO,
						p.DSC_TEXTO_PRODUTIVIDADE as 'observacao',
						p.IND_TIPO_ATUACAO_MES as 'tipo',
						p.DAT_INICIAL as 'datInicio',
						p.DAT_FINAL as 'datFinal',
						p.QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES as 'qtdDias',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.NUM_ORDEM,
						r.VLR_RESPOSTA as 'resposta'
				from SERVENTIAS_SEG_GRAU.PRODUTIVIDADE p
				join SERVENTIAS_SEG_GRAU.RESPOSTA r on r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join SERVENTIAS_SEG_GRAU.PERGUNTA perg on perg.SEQ_PERGUNTA = r.SEQ_PERGUNTA 
				 and perg.FLG_DESTINATARIO_PERGUNTA = 'M' 
				 and perg.DSC_INFO_ADICIONAL = 'P'
				where p.DSC_MES_REFERENCIA = '$periodo'
				and p.SEQ_ORGAO in ($listaSeqOrgao)
				$andTipo";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna as resposta das produtividades das serventia de segundo grau pelo seq_orgao e mes de referencia
	 * passado por parametro.
	 * @param unknown_type $seq_orgao
	 * @param unknown_type $mes
	 */
	function respostaProdutividadeMagistradoSegGrau($seq_orgao,$mes,$seqMagistrado,$tipo)
	{
		if($seqMagistrado)
			$andMag = "and p.SEQ_MAGISTRADO = ".$this->util->converterTelaBanco($seqMagistrado);
		else
			$andMag = "and p.SEQ_MAGISTRADO is NULL";
		
		$tipo = trim($tipo);
		if(!empty($tipo)){
			$andTipo = "and p.IND_TIPO_ATUACAO_MES = $tipo";
		}else{
			if(is_numeric($tipo) && $tipo == 0)
				$andTipo = "and p.IND_TIPO_ATUACAO_MES = $tipo";
			else
				$andTipo = "and p.IND_TIPO_ATUACAO_MES is null";
		}	
		
		$sql = "select  perg.DSC_TITULO AS 'descricao',
						perg.DSC_SIGLA_PERGUNTA AS 'sigla',
						perg.DSC_DESCRICAO AS 'glossario',
						p.DAT_INICIAL as 'inicio',
						p.DAT_FINAL as 'fim',
						p.QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES as 'qtdDias',
						p.DSC_TEXTO_PRODUTIVIDADE as 'obs',
						r.VLR_RESPOSTA AS 'resposta',
						perg.NUM_ORDEM,
						aux.DESCRICAO as 'tipo'
				from SERVENTIAS_SEG_GRAU.PRODUTIVIDADE p
				join SERVENTIAS_SEG_GRAU.RESPOSTA r on r.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join SERVENTIAS_SEG_GRAU.PERGUNTA perg on perg.SEQ_PERGUNTA = r.SEQ_PERGUNTA and perg.FLG_DESTINATARIO_PERGUNTA = 'M'
				LEFT JOIN COMPARTILHADO.AUXILIAR aux ON p.IND_TIPO_ATUACAO_MES = aux.CODIGO AND aux.NOME_COLUNA = 'IND_TIPO_JUIZ'
				where p.DSC_MES_REFERENCIA = '$mes'
				and   p.SEQ_ORGAO = $seq_orgao
				$andTipo
				$andMag
				group by perg.SEQ_PERGUNTA";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	function listaMagistrado($seqOrgao)
	{
		if ($_SESSION['seq_orgao_pai'] != 1){
			$uf_origem = "AND pre.IND_UF = '$uf'";
		}
		
		$sql ="SELECT	pd.SEQ_PRODUTIVIDADE,
						pd.DSC_MES_REFERENCIA,
						mag.NOM_MAGISTRADO,
						mag.SEQ_MAGISTRADO,
						aux.DESCRICAO,
						pre.DSC_SIGLA,
						pre.IND_UF
				FROM SERVENTIAS_SEG_GRAU.PRODUTIVIDADE pd
				JOIN SERVENTIAS_SEG_GRAU.PRESIDENCIA pre ON pre.SEQ_PRESIDENCIA = pd.SEQ_PRESIDENCIA
				JOIN CORREGEDORIA_CNJ.MAGISTRADO mag ON mag.SEQ_MAGISTRADO = pd.SEQ_MAGISTRADO
				JOIN COMPARTILHADO.AUXILIAR aux ON pd.IND_TIPO_PRODUTIVIDADE = aux.CODIGO AND aux.NOME_COLUNA = 'ind_tipo_magistrado_2_grau'
				WHERE pd.DAT_IMPORTACAO_XML IS NOT NULL
				$uf_origem";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método lista as produtividade do magistrado de segundo grau pelo seq magistrado passado por parametro.
	 */
	function produtividadeMagistradoSegGrau($seq_pro_magistrado)
	{
		$sql = "SELECT pg.SEQ_PERGUNTA,
					   pg.DSC_TITULO,
					   pg.DSC_DESCRICAO,
					   pg.NUM_ORDEM,
					   gp.DSC_GRUPO_APELIDO,
					   gp.DSC_GRUPO_PERGUNTA,
					   pg.FLG_DESTINATARIO_PERGUNTA,
					   rs.VLR_RESPOSTA
				FROM SERVENTIAS_SEG_GRAU.PERGUNTA pg
				INNER JOIN SERVENTIAS_SEG_GRAU.GRUPO_PERGUNTA gp ON pg.SEQ_GRUPO_PERGUNTA = gp.SEQ_GRUPO_PERGUNTA
				INNER JOIN SERVENTIAS_SEG_GRAU.RESPOSTA rs ON pg.SEQ_PERGUNTA = rs.SEQ_PERGUNTA
				WHERE rs.SEQ_PRODUTIVIDADE  = $seq_pro_magistrado
				ORDER BY gp.DSC_GRUPO_APELIDO,pg.NUM_ORDEM";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna os dados da serventia importado no primeiro passo.
	 */
	function retornaConsultaServentia($seq_orgao)
	{
		$sql = "select 	dc.SEQ_DADOS_SERVENTIA,
						org.SEQ_ORGAO,
						IFNULL(org.INT_ORDEM_ORGAO,0) AS 'numOrdem',
						org.DSC_ORGAO,
						TRIM(CASE org.DSC_ORGAO 
							WHEN org.DSC_ORGAO NOT LIKE 'ª %' THEN SUBSTR(org.DSC_ORGAO,3)
							WHEN org.DSC_ORGAO NOT LIKE 'º %' THEN SUBSTR(org.DSC_ORGAO,3)
						ELSE org.DSC_ORGAO END) AS 'denominacao',
						org.SEQ_CIDADE,
						c.SIG_UF,
						c.DSC_CIDADE,
						sp.FLG_ACESSO_INTERNET,
						dc.DAT_INSTALACAO_SERVENTIA,
						dc.LONGITUDE,
						dc.LATITUDE,
						dc.TIP_CLASSIFICACAO_ENTRANCIA
				from corporativo.orgao org
				left join corporativo.cidade c on c.SEQ_CIDADE = org.SEQ_CIDADE
				left join SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU sp on sp.SEQ_ORGAO = org.SEQ_ORGAO
				left join SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA dc on dc.SEQ_ORGAO = org.SEQ_ORGAO
				where org.SEQ_ORGAO = $seq_orgao
				order by dc.SEQ_DADOS_SERVENTIA DESC";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para trazer todos os municipios abrangidos pela serventia.
	 */
	function retornaMunicipiosEstado($seqOrgao)
	{
		$sql = "SELECT 	ID_CIDADE,
						DS_CIDADE 
				FROM SERVENTIAS_PRM_GRAU.MUNICIPIO_ABRANGIDO_SERVENTIA mas
				JOIN COMPARTILHADO.TB_CIDADE tc ON tc.ID_CIDADE = mas.COD_CIDADE
				WHERE SEQ_ORGAO = $seqOrgao";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para trazer todos as competencias da serventia.
	 */
	function retornaCompetenciaServentia($seqOrgao)
	{
		$sql = "SELECT 	DISTINCT(cs.SEQ_COMPETENCIA_JUIZO), 
						DSC_COMPETENCIA_JUIZO 
				FROM SERVENTIAS_PRM_GRAU.COMPETENCIA_SERVENTIA cs
				JOIN SERVENTIAS_PRM_GRAU.competencia_juizo cj ON cs.SEQ_COMPETENCIA_JUIZO = cj.SEQ_COMPETENCIA_JUIZO
				WHERE SEQ_ORGAO = $seqOrgao";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna os dados da serventia importado no primeiro passo.
	 */
	function retornaProdutividadeServentiaPrmGrau($seq_orgao)
	{
		$sql = "SELECT 
						ps.SEQ_PRODUTIVIDADE_SERVENTIA as 'seqProdutividadePrm',
						ps.MES_REFERENCIA as 'mes',
						CONCAT(SUBSTR(ps.DAT_INICIAL,9,2),'/',
						   SUBSTR(ps.DAT_INICIAL,6,2),'/',
						   SUBSTR(ps.DAT_INICIAL,1,4)) AS 'data_inicial',
						CONCAT(SUBSTR(ps.DAT_FINAL,9,2),'/',
						   SUBSTR(ps.DAT_FINAL,6,2),'/',
						   SUBSTR(ps.DAT_FINAL,1,4)) AS 'data_final'
				  FROM  SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA ps
				  join SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs on rs.SEQ_PRODUTIVIDADE_SERVENTIA = ps.SEQ_PRODUTIVIDADE_SERVENTIA
	 			  JOIN SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA pg ON rs.SEQ_PERGUNTA_SERVENTIA = pg.SEQ_PERGUNTA_SERVENTIA and pg.FLG_DESTINATARIO_PERGUNTA = 'S'
	 			  join SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on dtp.SEQ_PERGUNTA_SERVENTIA = rs.SEQ_PERGUNTA_SERVENTIA
			     WHERE  ps.SEQ_ORGAO = '$seq_orgao'
			     group by ps.SEQ_PRODUTIVIDADE_SERVENTIA
				 order by ps.SEQ_PRODUTIVIDADE_SERVENTIA desc";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retonar se já existe produtividade pelos parametro passados.
	 */
	function retornaProdutividadeServentiaSegGrau($seqOrgao)
	{
		$sql = "SELECT  SEQ_PRODUTIVIDADE as 'seqProdutividadeSeg',
						NUM_MES_REFERENCIA,
					    NUM_ANO_REFERENCIA
					FROM SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
					WHERE SEQ_ORGAO = ".$this->util->converterTelaBanco($seqOrgao);
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if ($res){
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna os dados do magistrado vinculado da serventia importado no quarto passo.
	 */
	function retornaSecretariaServentia($seq_orgao)
	{
		$sql = "SELECT 	SEQ_SECRETARIA_SERVENTIA,
						NUM_CPF_RESPONSAVEL_SECRETARIA,
						NOM_RESPONSAVEL_SECRETARIA,
						IND_TIPO_RESPONSAVEL,
						IND_TIPO_DENOMINACAO,
						aux.DESCRICAO AS 'tipo_responsavel',
						aux1.DESCRICAO AS 'tipo_denominacao',
						CONCAT('(',SUBSTR(NUM_TELEFONE_SECRETARIA,1,2),')' ,SUBSTR(NUM_TELEFONE_SECRETARIA,3,4),'-',SUBSTR(NUM_TELEFONE_SECRETARIA,7,4)) AS 'NUM_TELEFONE_SECRETARIA',
						CONCAT('(',SUBSTR(NUM_TELEFONE_OUTROS,1,2),')' ,SUBSTR(NUM_TELEFONE_OUTROS,3,4),'-',SUBSTR(NUM_TELEFONE_OUTROS,7,4)) AS 'NUM_TELEFONE_OUTROS',
						DSC_ENDERECO_SECRETARIA,
						DSC_BAIRRO_SECRETARIA,
						DSC_END_COMPLEMENTO_SECRETARIA,
						NUM_END_SECRETARIA,
						IND_UF,
						COD_CIDADE,
						DS_CIDADE,
						COD_CEP,
						DSC_EMAIL_SECRETARIA
				FROM SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA sc
				INNER JOIN COMPARTILHADO.AUXILIAR aux ON sc.IND_TIPO_RESPONSAVEL = aux.CODIGO AND aux.NOME_COLUNA = 'IND_TIPO_RESPONSAVEL'
				INNER JOIN COMPARTILHADO.AUXILIAR aux1 ON sc.IND_TIPO_DENOMINACAO = aux1.CODIGO AND aux1.NOME_COLUNA = 'IND_TIPO_DENOMINACAO'
				INNER JOIN COMPARTILHADO.tb_cidade tc ON tc.ID_CIDADE = sc.COD_CIDADE
				WHERE DAT_IMPORTACAO_XML IS NOT NULL 
				AND SEQ_ORGAO = $seq_orgao";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna os dados da produtividade do magistrado vinculado a serventia.
	 */
	
	function listaProdutividadeMagistradoPrmGrau($seq_orgao)
	{
		$sql = "SELECT 	pm.SEQ_ORGAO,
						pm.SEQ_PRODUTIVIDADE_MAGISTRADO,
						mag.SEQ_MAGISTRADO,
						mag.NOM_MAGISTRADO,
						pm.MES_REFERENCIA as 'mes',
						pm.DAT_INICIAL,
						pm.DAT_FINAL,
						pm.IND_TIPO_JUIZ as 'tipo',
						aux.DESCRICAO as 'descricaoTipo',
						CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN 
					    CONCAT(CONVERT(org.INT_ORDEM_ORGAO,CHAR), CONVERT(org.DSC_ORGAO,CHAR)) 
					    ELSE CONVERT(org.DSC_ORGAO,CHAR) END AS 'Denominacao'
				FROM SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO pm
				left JOIN CORREGEDORIA_CNJ.MAGISTRADO mag ON pm.SEQ_MAGISTRADO = mag.SEQ_MAGISTRADO
				join SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA rs on rs.SEQ_PRODUTIVIDADE_MAGISTRADO = pm.SEQ_PRODUTIVIDADE_MAGISTRADO
	 			join SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA ps on ps.FLG_DESTINATARIO_PERGUNTA = 'M'
	 			join SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on dtp.SEQ_PERGUNTA_SERVENTIA = rs.SEQ_PERGUNTA_SERVENTIA
				join corporativo.orgao org on org.SEQ_ORGAO = $seq_orgao
				left join COMPARTILHADO.AUXILIAR aux on aux.CODIGO = pm.IND_TIPO_JUIZ and aux.NOME_COLUNA='IND_TIPO_JUIZ'
				WHERE pm.SEQ_ORGAO = $seq_orgao
				AND pm.FLG_STATUS = 1
				group by pm.SEQ_PRODUTIVIDADE_MAGISTRADO
				order by pm.SEQ_PRODUTIVIDADE_MAGISTRADO desc";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna os dados da produtividade do magistrado vinculado a serventia.
	 */
	
	function listaProdutividadeMagistradoSegGrau($seq_orgao)
	{
		$sql = "select p.SEQ_ORGAO,
					   p.SEQ_PRODUTIVIDADE as 'SEQ_PRODUTIVIDADE_MAGISTRADO',
					   p.SEQ_MAGISTRADO,
					   p.DAT_INICIAL,
					   p.DAT_FINAL,
					   mag.NOM_MAGISTRADO,
					   p.DSC_MES_REFERENCIA as 'mes',
					   p.IND_TIPO_ATUACAO_MES as 'tipo',
					   aux.DESCRICAO as 'descricaoTipo',
					   CASE WHEN IFNULL(org.INT_ORDEM_ORGAO,0) <> 0 THEN 
				       CONCAT(CONVERT(org.INT_ORDEM_ORGAO,CHAR), CONVERT(org.DSC_ORGAO,CHAR)) 
				       ELSE CONVERT(org.DSC_ORGAO,CHAR) END AS 'Denominacao'
				from SERVENTIAS_SEG_GRAU.PRODUTIVIDADE p
				join corporativo.orgao org on org.SEQ_ORGAO = $seq_orgao
				join SERVENTIAS_SEG_GRAU.RESPOSTA res on res.SEQ_PRODUTIVIDADE = p.SEQ_PRODUTIVIDADE
				join SERVENTIAS_SEG_GRAU.PERGUNTA perg on res.SEQ_PERGUNTA = perg.SEQ_PERGUNTA and perg.FLG_DESTINATARIO_PERGUNTA = 'M'
				left JOIN CORREGEDORIA_CNJ.MAGISTRADO mag ON p.SEQ_MAGISTRADO = mag.SEQ_MAGISTRADO
				left join COMPARTILHADO.AUXILIAR aux on aux.CODIGO = p.IND_TIPO_ATUACAO_MES and aux.NOME_COLUNA='IND_TIPO_JUIZ'
				where p.SEQ_ORGAO = $seq_orgao
				group by p.SEQ_MAGISTRADO";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método lista as uf.
	 */
	function listaUf()
	{
		$sql = "SELECT cid.CO_UF AS co_uf
                  FROM COMPARTILHADO.TB_CIDADE cid
                  join COMPARTILHADO.TRIBUNAL tu on cid.CO_UF = tu.IND_UF
                 WHERE cid.CO_UF not in ('ex')
				GROUP BY cid.CO_UF
				ORDER BY cid.CO_UF";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}	
	
	/*******************************************************/
	/*********Arquivos para gravação da CRON****************/
	/*******************************************************/
	/**
	 * Método da listagem de arquivos para a gravação pela CRON do terceiro e quarto passo.
	 * @param unknown $passo
	 * @return unknown
	 */
	function listaArquivoParaGravacao($passo)
	{
		$sql = "select  *
				from COMPARTILHADO.ARQUIVO_MODULO_XML_CRON am
				where am.TIP_ETAPA_SISTEMA = $passo
				and am.SEQ_ORGAO = ".$_SESSION['seq_orgao'];
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
}
