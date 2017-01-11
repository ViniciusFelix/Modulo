<?php
class bdImportarArquivos{
	
	function bdImportarArquivos($db)
	{
		$this->db = $db;
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}

	/**
	 * Método transforma data com barras em formato do banco.
	 * @param $data
	 */
	function data_para_iso($data)
    {
        $d = explode("/",$data);
        return $d[2] . "-" . $d[1] . "-" . $d[0];
    } 
	
	/****************************************/
	/* 		Mètodos do Primeiro Passo		*/
	/****************************************/
	
    /**
     * Método monta o combo dos anos
     */
    function buscaAnos()
    {
    	$sql = "SELECT 		auxiliar.CODIGO,
							auxiliar.NOME_COLUNA,
							auxiliar.DESCRICAO,
							auxiliar.ATIVO
					FROM 	compartilhado.auxiliar auxiliar
					WHERE 	auxiliar.NOME_COLUNA='ind_anos'
					AND 	auxiliar.ATIVO = '1'
    				AND		auxiliar.DESCRICAO > 2014";
    	$q = $this->db->execute($sql);
    	$res = $q->getRows();
    	return $res;
    }
    
	/**
	 * Método para trazer todas as serventias estaduais trazidas pela session.
	 */
	function retornaServentiaEstado()
	{
		if(empty($_POST['salvar']))
			$campo = "municipio,";
			
		$sql = "SELECT  seq_corporativo,
						numOrdem,
						CASE numOrdem WHEN 0 
						THEN denominacao
						ELSE CONCAT(CONVERT(numOrdem,CHAR),'º ',CONVERT(denominacao,CHAR)) END nome_vara,
						uf,
						sp.FLG_ACESSO_INTERNET,
						seqCidade,
						$campo
						DATE_FORMAT(dcs.DAT_INSTALACAO_SERVENTIA ,'%d/%m/%Y') AS DAT_INSTALACAO_SERVENTIA,
						(SELECT GROUP_CONCAT(mas.COD_CIDADE)
						 FROM serventias_prm_grau.municipio_abrangido_serventia mas
						 WHERE mas.SEQ_ORGAO = tb.seq_corporativo) as municipioAbrangido,
						dcs.LATITUDE,
						dcs.LONGITUDE
						FROM(SELECT     DISTINCT org.SEQ_ORGAO AS 'seq_corporativo',
								IFNULL(org.INT_ORDEM_ORGAO,0) AS 'numOrdem',
								TRIM(CASE org.DSC_ORGAO 
									WHEN org.DSC_ORGAO NOT LIKE 'ª %' THEN SUBSTR(org.DSC_ORGAO,3)
									WHEN org.DSC_ORGAO NOT LIKE 'º %' THEN SUBSTR(org.DSC_ORGAO,3)
								ELSE org.DSC_ORGAO END) AS 'denominacao',
								uf.SIG_UF AS 'uf',
								org.FLG_ATIVO as 'ativo',
								org_p1.SEQ_ORGAO_PAI AS 'orgaoPai',
								org_p.DSC_ORGAO AS 'municipio',
								org.SEQ_CIDADE AS 'seqCidade',
								SUBSTR(org_p1.DSC_ORGAO,11) AS 'tribunal'
								FROM corporativo.orgao org
								JOIN corporativo.orgao org_p    ON org.SEQ_ORGAO_PAI    = org_p.SEQ_ORGAO
								JOIN corporativo.orgao org_p1   ON org_p.SEQ_ORGAO_PAI  = org_p1.SEQ_ORGAO
								JOIN corporativo.orgao org_p2   ON org_p1.SEQ_ORGAO_PAI = org_p2.SEQ_ORGAO
								JOIN corporativo.tribunal_uf uf ON org_p2.SEQ_ORGAO = uf.SEQ_ORGAO
								WHERE org_p1.DSC_ORGAO LIKE '%1º Grau%'
								AND org_p.DSC_ORGAO NOT LIKE '%seção Judiciária%')tb
						left join SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA dcs on dcs.SEQ_ORGAO = tb.seq_corporativo
						left join SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU sp on sp.SEQ_ORGAO = tb.seq_corporativo
						join COMPARTILHADO.TRIBUNAL tri on tri.SEQ_ORGAO = tb.orgaoPai
						WHERE tribunal = '".$_SESSION['sig_tribunal']."'
						and ativo = 'S'
						group by seq_corporativo
						ORDER BY uf,municipio,numOrdem,denominacao";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para trazer todas as serventias.
	 */
	function retornaServentia($seqOrgao=NULL)
	{
		$and = NULL;
		$where = NULL;
		if($seqOrgao){
			$andSeqOrgao = "and o.SEQ_ORGAO = ".$seqOrgao;
			if($_SESSION['seq_orgao'] == $seqOrgao)
				$andHeranca = "and o.COD_HIERARQUIA like '%".$_SESSION['cod_hierarquia']."'";
			else
				$andHeranca = "and o.COD_HIERARQUIA like '%:".$_SESSION['seq_orgao'].":,".$_SESSION['cod_hierarquia']."'";
		}else{
			$andHeranca = "and o.COD_HIERARQUIA like '%:".$_SESSION['seq_orgao'].":,".$_SESSION['cod_hierarquia']."'";
		}
		
		$sql = "select o.SEQ_ORGAO AS 'seq_corporativo', 
					   o.INT_ORDEM_ORGAO as 'numOrdem',
					   o.DSC_ORGAO,
					   o.NUM_GRAU_JURISDICAO_ORGAO as 'grauNovo',
					   o.SEQ_ORGAO_PAI,
					   o.COD_HIERARQUIA,
					   c.SIG_UF AS 'uf',
					   c.SEQ_CIDADE AS 'seqCidade',
					   c.DSC_CIDADE AS 'municipio',
					   sp.FLG_ACESSO_INTERNET,
					   (select dc.DAT_INSTALACAO_SERVENTIA
					    from SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA dc 
					    where dc.SEQ_ORGAO = o.SEQ_ORGAO
					    order by dc.SEQ_DADOS_SERVENTIA desc
					    limit 1) as 'DAT_INSTALACAO_SERVENTIA',
					   tor.INT_ORDEM as 'grau',
					   o.TIP_ORGAO,
					   o.FLG_ATIVO
				from corporativo.orgao o
				left join corporativo.cidade c on c.SEQ_CIDADE = o.SEQ_CIDADE
				left join SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU sp on sp.SEQ_ORGAO = o.SEQ_ORGAO
				join corporativo.tipo_orgao tor on tor.TIP_ORGAO = o.TIP_ORGAO
				where o.TIP_ORGAO not in ('SECAO','SUBSE','COMAE','GRA1E','GRA2E','GRA1F','GRA2F','TRIBE','TRIBF','TRIBL','TRIBM','TRIBS','TRIBT')
				$andHeranca
				$andSeqOrgao
				group by o.SEQ_ORGAO";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	function municipiosAbrangidosServentia($seqOrgao)
	{
		$sql = "SELECT mas.COD_CIDADE
				FROM SERVENTIAS_PRM_GRAU.MUNICIPIO_ABRANGIDO_SERVENTIA mas
				WHERE mas.SEQ_ORGAO = $seqOrgao";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna o municipio pelo seq passado por parametro.
	 * @param unknown $seqCidade
	 * @return unknown
	 */
	function retornaMunicipio($seqCidade)
	{
		$sql = "SELECT dsc_cidade
				FROM corporativo.cidade
				where seq_cidade = $seqCidade";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['dsc_cidade'];
		}else{
			return NULL;
		}
	}
	
	function tribunalUf()
	{
		$sql = "select SIG_UF
				from corporativo.tribunal_uf tu
				where tu.SEQ_ORGAO = ".$_SESSION['seq_orgao'];
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			if(count($res) > 1){
				foreach ($res as $key => $value) {
					$uf .= "'".$value['SIG_UF']."',";
				}
				$uf = substr($uf, 0, strlen($uf)-1);
				return $uf;
			}else{
				return "'".$res[0]['SIG_UF']."'";
			}
		}else{
			return NULL;
		}
		
	}
	
	/**
	 * Método para trazer todos os municipios do estado passado pela session.
	 */
	function retornaMunicipiosEstado()
	{
		if($_SESSION['seq_orgao'] == 13309){
			$where = "";
		}else{
			$tribunalUf = $this->tribunalUf();
			if($tribunalUf){
				$where = "WHERE sig_uf in ($tribunalUf)";
			}
		}

		$sql = "SELECT seq_cidade as 'ID_CIDADE',
					   sig_uf as 'CO_UF',
					   dsc_cidade as 'DS_CIDADE',
					   COD_IBGE  
				FROM corporativo.cidade
				$where";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}

	/**
	 * Método para trazer todos os municipios do estado passado pela session.
	 */
	function retornaCodMunicipio($nomCidade)
	{
		$sql = "SELECT ID_CIDADE
				FROM COMPARTILHADO.TB_CIDADE
				WHERE DS_CIDADE = ".$this->util->converterTelaBanco($nomCidade)."
				";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método lista as competências das serventias.
	 */
	function listaCompetencia($seqComp=NULL)
	{
		$and = NULL;
		if($seqComp)
			$and = "and SEQ_COMPETENCIA_JUIZO = $seqComp";
		
		$sql = "SELECT SEQ_COMPETENCIA_JUIZO,
					   DSC_COMPETENCIA_JUIZO
				FROM SERVENTIAS_PRM_GRAU.COMPETENCIA_JUIZO
				where FLG_ATIVO = 'S'
				$and";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna destinatario da pergunta.
	 */
	function destinatarioPergunta($seqPergunta,$destinatario)
	{
		$sql = "select 
					   (select dtp.SEQ_DESTINATARIO_TRIBUNAL_PERGUNTA
						from serventias_prm_grau.destinatario_tribunal_pergunta dtp
						join serventias_prm_grau.pergunta_serventia perg on perg.FLG_DESTINATARIO_PERGUNTA = '$destinatario' and perg.SEQ_PERGUNTA_SERVENTIA = $seqPergunta
						join serventias_prm_grau.pergunta_serventia
						where dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
						and dtp.SEQ_PERGUNTA_SERVENTIA = $seqPergunta
						group by dtp.SEQ_DESTINATARIO_TRIBUNAL_PERGUNTA) as 'primeiro',
					   (select dtp.SEQ_DESTINATARIO_TRIBUNAL_PERGUNTA 
						from serventias_seg_grau.destinatario_tribunal_pergunta dtp
						join serventias_seg_grau.pergunta pergSeg on pergSeg.FLG_DESTINATARIO_PERGUNTA = '$destinatario' and pergSeg.SEQ_PERGUNTA = $seqPergunta
						where dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
						and dtp.SEQ_PERGUNTA = $seqPergunta
						group by dtp.SEQ_DESTINATARIO_TRIBUNAL_PERGUNTA) as 'segundo'
				from corporativo.orgao org
				where org.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai'];
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método verifica se é orgão valido cadastrado no corporativo.
	 */
	function retornaOrgaoCorporativo($seqOrgao)
	{
		$sql = "select org.SEQ_ORGAO
				from corporativo.orgao org
				where org.SEQ_ORGAO = $seqOrgao
				and org.TIP_ORGAO <> 'COMAE'";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna o tipo do juiz.
	 * @param unknown_type $seqMagistrado
	 * @return unknown
	 */
	function retornaTipoJuiz($seqMagistrado,$seqOrgao){
		$sql = "SELECT 	IND_TIPO_JUIZ
				FROM SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA
				WHERE SEQ_MAGISTRADO = ".$this->util->converterTelaBanco($seqMagistrado,1)."
				AND SEQ_ORGAO = ".$this->util->converterTelaBanco($seqOrgao,1);
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if($res)
			return $res[0]['IND_TIPO_JUIZ'];
		else
			return NULL;
	}
	
	/**
	 * Método retorna a lista de serventias estaduais cadastradas.
	 * @param unknown_type $parametros
	 */
	function retornaOrgao($seqOrgao)
	{
		$sql = "SELECT  org.SEQ_ORGAO,
						org.SEQ_ORGAO_PAI,
					   	org.COD_HIERARQUIA
				FROM corporativo.orgao org
				WHERE org.SEQ_ORGAO = $seqOrgao";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna a lista de serventias federais cadastradas.
	 * @param unknown_type $parametros
	 */
	function retornaVarasCorporativoFederais($seqOrgao)
	{
		$sql ="SELECT   seq_corporativo
						FROM(SELECT    	   DISTINCT org.SEQ_ORGAO AS 'seq_corporativo',
								   REPLACE(org3.DSC_ORGAO,'1º Grau - ','') AS 'tribunal'
								    FROM corporativo.orgao org
								    JOIN corporativo.orgao org1 ON org1.SEQ_ORGAO = org.SEQ_ORGAO_PAI
								    JOIN corporativo.orgao org2 ON org2.SEQ_ORGAO = org1.SEQ_ORGAO_PAI
								    JOIN corporativo.orgao org3 ON org3.SEQ_ORGAO = org2.SEQ_ORGAO_PAI
								    WHERE org.TIP_ORGAO = 'VARAF')tb
				         left join SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA ss on ss.SEQ_ORGAO = tb.seq_corporativo
						 left join COMPARTILHADO.TRIBUNAL tri on tri.SIG_TRIBUNAL = '".$_SESSION['sig_tribunal']."'
						 left join COMPARTILHADO.AUXILIAR aux on tri.IND_TIPO_JUSTICA = aux.CODIGO and aux.NOME_COLUNA = 'IND_TIPO_JUSTICA'
						 WHERE seq_corporativo = $seqOrgao";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Retorna codigo herança pelo tipo e grau.
	 * @param unknown $grau
	 * @param unknown $tipo
	 * @param unknown $cidade
	 * @return unknown|NULL
	 */
	function retornaCodHerancaTipoGrau($grau,$tipo)
	{
		if($tipo == 'E'){
			if($grau == 1 || $grau == 3 || $grau == 4){
				$tipoGrau = "and o.TIP_ORGAO = 'GRA1E'";
			}else if($grau == 2){
				$tipoGrau = "and o.TIP_ORGAO = 'GRA2E'";
			}
		}
		$sql = "select 	o.SEQ_ORGAO,
						o.COD_HIERARQUIA
					from corporativo.orgao o
					where o.SEQ_ORGAO_PAI = ".$_SESSION['seq_orgao']."
					$tipoGrau";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return null;
		}
	}
	
	/**
	 * Retorna codigo herança pelo tipo e grau.
	 * @param unknown $grau
	 * @param unknown $tipo
	 * @param unknown $cidade
	 * @return unknown|NULL
	 */
	function retornaCodHerancaTipoGrauFederal($grau,$cidade,$excecao=NULL)
	{
		if($excecao){
			$andExcecao = "and o.DSC_ORGAO like '%$excecao%'";
		}
		
		if($grau == 2){
			$sql = "select 	o.SEQ_ORGAO,
						o.COD_HIERARQUIA
					from corporativo.orgao o
					where o.COD_HIERARQUIA like '%:".$_SESSION['seq_orgao'].":,".$_SESSION['cod_hierarquia']."'
					and o.TIP_ORGAO = 'GRA2F'";
		}else{
			$sql = "select 	o.SEQ_ORGAO,
							o.COD_HIERARQUIA
					from corporativo.orgao o
					where o.COD_HIERARQUIA like '%:".$_SESSION['seq_orgao'].":,".$_SESSION['cod_hierarquia']."'
					and o.TIP_ORGAO = 'SUBSE'
					and o.SEQ_CIDADE = $cidade
					$andExcecao";
		}
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return null;
		}
	}
	
	/**
	 * Retorna código herança estadual.
	 * @param unknown $municipio
	 * @param unknown $seqCidade
	 * @return unknown|NULL
	 */
	function retornaCodHerancaEstadual($municipio,$seqCidade)
	{
		$sql = "select 	o.SEQ_ORGAO,
						o.COD_HIERARQUIA
				from corporativo.orgao o
				where o.DSC_ORGAO like '%".addslashes($municipio)."%'
				and o.SEQ_CIDADE = $seqCidade
				and o.TIP_ORGAO = 'COMAE'";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return null;
		}
	}
	
	/**
	 * Retorna codigo herança dos federais pela UF.
	 * @param unknown $municipio
	 * @param unknown $seqCidade
	 * @return unknown|NULL
	 */
	function retornaCodHerancaFederalUf($uf)
	{
		$sql = "select 	o.SEQ_ORGAO,
						o.COD_HIERARQUIA
				from corporativo.orgao o
				join corporativo.cidade c on c.SEQ_CIDADE = o.SEQ_CIDADE
				where o.TIP_ORGAO  = 'SUBSE'
				and o.COD_HIERARQUIA like '%:".$_SESSION['seq_orgao'].":,".$_SESSION['cod_hierarquia']."'
				and c.SIG_UF = '$uf'";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return null;
		}
	}
	
	/**
	 * Salva nova comarca.
	 * @param unknown $denominacao
	 * @param unknown $codHeranca
	 * @param unknown $municipio
	 * @param unknown $seqOrgaoPai
	 * @return NULL
	 */
	function salvarNovaComarca($denominacao,$codHeranca,$municipio,$seqOrgaoPai)
	{
		$sql =	"INSERT INTO corporativo.orgao (
						INT_ORDEM_ORGAO,
						DSC_ORGAO,
						SEQ_ORGAO_PAI,
						COD_HIERARQUIA,
						TIP_ORGAO,
						SEQ_CIDADE,
						DAT_INCLUSAO,
						USU_INCLUSAO,
						DSC_IP_USU_INCLUSAO,
						FLG_ATIVO
				 )
				 VALUES
				 (
						".$this->util->converterTelaBanco($num).",
						UPPER(".$this->util->converterTelaBanco($denominacao)."),
						".$this->util->converterTelaBanco($seqOrgaoPai).",
						".$this->util->converterTelaBanco($codHeranca).",
						'COMAE',
						".$this->util->converterTelaBanco($municipio).",
						NOW(),
						".$this->util->converterTelaBanco($_SESSION['sigUsuario']).",
						".$this->util->converterTelaBanco($_SERVER['REMOTE_ADDR']).",
						'S'
				 )";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para verificar cod_heranca do corporativo para a nova serventia a ser salva.
	 */
	function retornaCodHeranca()
	{
		$sql = "select org.COD_HIERARQUIA,
					   org.SEQ_ORGAO
				from corporativo.orgao org 
				where org.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai'];
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return null;
		}
	}
	
	/**
	 * Retorna se a serventia com os parametros passados e com o seq_serventia informado como 0 já está cadastrada.
	 * @param unknown $grau
	 * @return unknown|NULL
	 */
	function retornaServentiaRepetida($descricao,$seqCidade,$num,$codHeranca)
	{
		$andNum = NULL;
		$andCid = NULL;
		if($num)
			$andNum = "and o.INT_ORDEM_ORGAO = ".$this->util->converterTelaBanco($num);
		
		if($seqCidade)
			$andCid = "and o.SEQ_CIDADE = ".$this->util->converterTelaBanco($seqCidade);
		
		$sql = "select o.SEQ_ORGAO
				from corporativo.orgao o
				where o.DSC_ORGAO = ".$this->util->converterTelaBanco($descricao)."
				and   o.COD_HIERARQUIA like '%".$codHeranca."'
				$andNum
				$andCid";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if($res){
			return $res[0]['SEQ_ORGAO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Retorna o tip_orgao para cadastro da serventia.
	 * @param unknown $grau
	 * @return unknown|NULL
	 */
	function retornaTipoOrgao($grau=NULL){
		if($grau)
			$andGrau = "and t.INT_ORDEM = $grau";
		
		$sql = "select t.TIP_ORGAO 
				from corporativo.tipo_orgao t
				where t.TIP_ORGAO_PAI = '".$_SESSION['tip_orgao']."'
				$andGrau";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['TIP_ORGAO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para inserir dados de uma nova servantia pelos parametros passados.
	 * @param unknown $num
	 * @param unknown $denominacao
	 * @param unknown $seqOrgaoPai
	 * @param unknown $codHeranca
	 * @param unknown $municipio
	 * @param unknown $seqUsuario
	 * @return NULL
	 */
	function inserirOrgaoCorporativo($grau,$num,$denominacao,$codHeranca,$municipio,$seqOrgaoPai,$tipOrgao,$status)
	{
		$sql =	"INSERT INTO corporativo.orgao (
						INT_ORDEM_ORGAO,
						DSC_ORGAO,
						SEQ_ORGAO_PAI,
						COD_HIERARQUIA,
						TIP_ORGAO,
						SEQ_CIDADE,
						NUM_GRAU_JURISDICAO_ORGAO,	
						DAT_INCLUSAO,
						USU_INCLUSAO,
						DSC_IP_USU_INCLUSAO,
						FLG_ATIVO
				 ) 
				 VALUES 
				 (
						".$this->util->converterTelaBanco($num).",
						UPPER(".$this->util->converterTelaBanco($denominacao)."),
						".$this->util->converterTelaBanco($seqOrgaoPai).",
						".$this->util->converterTelaBanco($codHeranca).",
						".$this->util->converterTelaBanco($tipOrgao).",	
						".$this->util->converterTelaBanco($municipio).",
						".$this->util->converterTelaBanco($grau).",
						NOW(),
						".$this->util->converterTelaBanco($_SESSION['sigUsuario']).",
						".$this->util->converterTelaBanco($_SERVER['REMOTE_ADDR']).",
						".$this->util->converterTelaBanco(strtoupper($status))."
				 )";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para atualizar dados da servantia pelos parametros passados.
	 * @param unknown $numServ
	 * @param unknown $denominacao
	 * @param unknown $municipio
	 * @param unknown $seqServentia
	 * @return unknown|NULL
	 */
	function atualizarOrgao($grau,$numServ,$denominacao,$municipio,$seqOrgao,$tipOrgao,$status,$codHerarquia,$seqOrgaoPai,$tipoAnterior)
	{
		if(empty($grau))
			$grau = 0;
		
		$sql =	"UPDATE corporativo.orgao
					SET INT_ORDEM_ORGAO		= ".$this->util->converterTelaBanco($numServ).",
						NUM_GRAU_JURISDICAO_ORGAO = '$grau',
						DSC_ORGAO			= UPPER(".$this->util->converterTelaBanco($denominacao)."),
						SEQ_CIDADE			= ".$this->util->converterTelaBanco($municipio).",
						DAT_ALTERACAO		= NOW(),
						USU_ALTERACAO		= ".$this->util->converterTelaBanco($_SESSION['sigUsuario']).",
						TIP_ORGAO		    = ".$this->util->converterTelaBanco($tipOrgao).",
						SEQ_ORGAO_PAI	= ".$this->util->converterTelaBanco($seqOrgaoPai).",
						$codHierarquia
						FLG_ATIVO			= UPPER(".$this->util->converterTelaBanco($status).")
					WHERE SEQ_ORGAO	 = ".$this->util->converterTelaBanco($seqOrgao);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqOrgao;	
		}else{
			return NULL;
		}	
	}
	
	/**
	 * Método retorna se tem dados da internet salvo
	 * @param unknown $seqOrgao
	 * @param unknown $internet
	 * @return NULL
	 */
	function temDadosInternet($seqOrgao)
	{
		$sql =	"select SEQ_ORGAO
				from SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU
				where SEQ_ORGAO = $seqOrgao";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if ($res){
			return $res[0]['SEQ_ORGAO'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método insere o flg_internet da serventia
	 * @param unknown $seqOrgao
	 * @param unknown $internet
	 * @return NULL
	 */
	function inserirFlgInternetServentia($seqOrgao,$internet)
	{
		$sql =	"INSERT INTO SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU (
						FLG_ACESSO_INTERNET,
						DAT_IMPORTACAO_XML,
						SEQ_ORGAO
				 )
				 VALUES
				 (
						".$this->util->converterTelaBanco(strtoupper($internet)).",
						NOW(),
						".$this->util->converterTelaBanco($seqOrgao)."
				 )";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para atualizar dados da servantia pelos parametros passados.
	 * @param unknown $seqOrgao
	 * @param unknown $internet
	 * @return unknown|NULL
	 */
	function atualizarFlgInternetServentia($seqOrgao,$internet)
	{
		$sql =	"UPDATE SERVENTIAS_PRM_GRAU.SERVENTIA_JUDICIAL_PRM_GRAU
					SET FLG_ACESSO_INTERNET = ".$this->util->converterTelaBanco(strtoupper($internet)).",
						DAT_IMPORTACAO_XML  = NOW()	
					WHERE SEQ_ORGAO	 = ".$this->util->converterTelaBanco($seqOrgao);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqOrgao;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna os dados das serventias pelos seq_serventia_judicial passado por parametro.
	 * @param unknown_type $id_serventia_login
	 */
	function retornaDadosComplementares ($seqOrgao)
	{
		$sql = "SELECT
					dcs.SEQ_DADOS_SERVENTIA,
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
					dcs.DSC_IP_USU_INCLUSAO
				FROM 		SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA dcs
				WHERE 	    dcs.SEQ_ORGAO = '$seqOrgao'
				ORDER BY    dcs.DAT_INCLUSAO DESC, dcs.SEQ_DADOS_SERVENTIA LIMIT 0,1";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if($res){
			return $res[0];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para salvar dados complementeres da serventia pelos parametros passados
	 * @param unknown $seqOrgao
	 * @param unknown $instalacao
	 * @param unknown $longitude
	 * @param unknown $latitude
	 * @return NULL
	 */
	function inserirDadosComplementares($seqOrgao,$instalacao,$longitude,$latitude,$entrancia)
	{
		if($entrancia == NULL)
			$entrancia = 0;
		
		$sql =	"	INSERT INTO SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA (
							SEQ_ORGAO,
							DAT_INSTALACAO_SERVENTIA,
							LATITUDE,
							LONGITUDE,
							DAT_INCLUSAO,
							COD_USU_INCLUSAO,
							DSC_IP_USU_INCLUSAO,
							TIP_CLASSIFICACAO_ENTRANCIA
					) 
					VALUES 
					(
							".$this->util->converterTelaBanco($seqOrgao).",
							".$this->util->converterTelaBanco($instalacao).",
							".$this->util->converterTelaBanco($latitude).",
							".$this->util->converterTelaBanco($longitude).",		
							NOW(),
							".$this->util->converterTelaBanco($_SESSION['seq_usuario']).",
							".$this->util->converterTelaBanco($_SERVER['REMOTE_ADDR']).",
							'$entrancia'
					)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();	
		}else{
			return NULL;
		}	
	}
	
	/**
	 * Metodo para retirar os municipios abrangidos antigos para a inclusão dos novos.
	 * @param unknown $seqOrgao
	 * @return unknown|NULL
	 */
	function excluirDadosComplementares($seqOrgao){
		$sql = "delete from SERVENTIAS_PRM_GRAU.DADOS_COMPLEMENTARES_SERVENTIA where seq_orgao = $seqOrgao";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqOrgao;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para inserir as competencias da serventia passada por parametro.
	 * @param unknown $serventia
	 * @param unknown $seq_competencia
	 * @return NULL
	 */
	function inserirMunicipioAbrangido($seqOrgao,$codCidade)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.MUNICIPIO_ABRANGIDO_SERVENTIA
				(
						SEQ_ORGAO,
						COD_CIDADE
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqOrgao).",
						".$this->util->converterTelaBanco($codCidade)."
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo para retirar os municipios abrangidos antigos para a inclusão dos novos.
	 * @param unknown $seqOrgao
	 * @return unknown|NULL
	 */
	function excluirMunicipioAbrangido($seqOrgao){
		$sql = "delete from SERVENTIAS_PRM_GRAU.MUNICIPIO_ABRANGIDO_SERVENTIA where seq_orgao = $seqOrgao";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqOrgao;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para inserir as competencias da serventia passada por parametro.
	 * @param unknown $serventia
	 * @param unknown $seq_competencia
	 * @return NULL
	 */
	function inserirCompetencia($seqOrgao,$seq_competencia)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.COMPETENCIA_SERVENTIA
				(
						SEQ_ORGAO,
						SEQ_COMPETENCIA_JUIZO,
						DAT_INCLUSAO,
						COD_USU_INCLUSAO
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqOrgao).",
						".$this->util->converterTelaBanco($seq_competencia).",
						NOW(),
						".$this->util->converterTelaBanco($_SESSION['seq_usuario'])."
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Metodo para retirar os municipios abrangidos antigos para a inclusão dos novos.
	 * @param unknown $seqOrgao
	 * @return unknown|NULL
	 */
	function excluirCompetencia($seqOrgao){
		$sql = "delete from SERVENTIAS_PRM_GRAU.COMPETENCIA_SERVENTIA where seq_orgao = $seqOrgao";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqOrgao;
		}else{
			return NULL;
		}
	}
	
	/****************************************/
	/* 		Mètodos do Segundo Passo		*/
	/****************************************/
	
	/**
	 * Método para trazer todas as perguntas para produtividade da serventia.
	 */
	function listaPerguntaServentiaPrmGrau()
	{
		$sql = "SELECT  perg.SEQ_PERGUNTA_SERVENTIA as 'seqPergunta',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.DSC_TIPO_PERGUNTA_SERVENTIA as 'descricao',
						perg.DSC_PERGUNTA as 'glossario',
						perg.NUM_ORDEM,
						'1' as 'grau'
				FROM SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA perg
				join SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA_SERVENTIA = dtp.SEQ_PERGUNTA_SERVENTIA and dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
				WHERE FLG_DESTINATARIO_PERGUNTA='S'
				AND FLG_ATIVO = '1'
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
	 * Método para trazer todas as perguntas para produtividade da serventia.
	 */
	function listaPerguntaServentiaTrabalhoPrmGrau()
	{
		$sql = "SELECT  perg.SEQ_PERGUNTA_SERVENTIA as 'seqPergunta',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.DSC_TIPO_PERGUNTA_SERVENTIA as 'descricao',
						perg.DSC_PERGUNTA as 'glossario',
						perg.NUM_ORDEM,
						'1' as 'grau'
				FROM SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA perg
				join SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA_SERVENTIA = dtp.SEQ_PERGUNTA_SERVENTIA and dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
				WHERE FLG_DESTINATARIO_PERGUNTA='T'
				AND FLG_ATIVO = '1'
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
	 * Método retorna se já existe produtividade da serventia.
	 */
	function retornaProdServentiaPrmGrau($seqOrgao,$mes,$ano)
	{
		$sql = "SELECT SEQ_PRODUTIVIDADE_SERVENTIA
				FROM  SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA
				WHERE SEQ_ORGAO = ".$seqOrgao."
				AND MES_REFERENCIA = '".$mes." / ".$ano."'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if($res){
			return $res[0]['SEQ_PRODUTIVIDADE_SERVENTIA'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna se já existe produtividade da serventia.
	 */
	function listaPerguntas()
	{
		$sql = "SELECT SEQ_PERGUNTA_SERVENTIA 
				FROM SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA ps
				WHERE ps.FLG_ATIVO = '1'
				AND ps.FLG_DESTINATARIO_PERGUNTA = 'S'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna se já existe produtividade da serventia.
	 */
	function respostaSalva($params)
	{
		$sql = "SELECT SEQ_PERGUNTA_SERVENTIA
				FROM  SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				WHERE SEQ_PRODUTIVIDADE_SERVENTIA = ".$params;
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna se já tem resposta pelos parametros passados.
	 */
	function retornaSeqResposta($seqServentia,$seqPergunta)
	{
		$sql = "SELECT SEQ_RESPOSTA_SERVENTIA, SEQ_PRODUTIVIDADE_SERVENTIA
				FROM  SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				WHERE SEQ_PRODUTIVIDADE_SERVENTIA = ".$seqServentia."
				AND SEQ_PERGUNTA_SERVENTIA = ".$seqPergunta;
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if($res){
			return $res[0]['SEQ_RESPOSTA_SERVENTIA'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna se existe respostas para produtividade pelo parametro passado 
	 * @param unknown_type $seqProd
	 */
	function listaRespServ($seqProd){
		$sql = "SELECT SEQ_RESPOSTA_SERVENTIA
				FROM  SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				WHERE SEQ_PRODUTIVIDADE_SERVENTIA = ".$seqProd;
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	function inserirFilaGravacaoCron($nomeArquivo,$referencia)
	{
	
		$sql = "INSERT INTO COMPARTILHADO.ARQUIVO_MODULO_XML_CRON
				(
						NOM_ARQUIVO,
						TIP_ETAPA_SISTEMA,
						TIP_SITUACAO_ARQUIVO,
						DAT_IMPORTACAO,
						SEQ_USUARIO,
						SEQ_ORGAO
				)
				VALUES
				(
						".$this->util->converterTelaBanco($nomeArquivo).",
						".$this->util->converterTelaBanco($referencia).",
						'0',
						NOW(),
						".$_SESSION['seq_usuario'].",
						".$_SESSION['seq_orgao']."		
				)";
		$q 		= $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para salvar a produtividade da serventia pelos parametros passados.
	 * @param unknown $seqServentia Seq da serventia passado por parametro.
	 * @param unknown $obs Observeção da produtividade.
	 * @param unknown $ano Ano da produtividade.
	 * @param unknown $mesNum Mes númerico informado no arquivo da produtividade.
	 * @param unknown $ultimoDia Ultimo dia do mes da produtividade.
	 * @param unknown $mes Mes da produtividade
	 * @return NULL
	 */
	function inserirProdServentiaPrmGrau($seqOrgao,$obs,$ano,$mesNum,$ultimoDia)
	{

		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA
				(
						SEQ_ORGAO,
						DSC_TEXTO_PRODUTIVIDADE,
						MES_REFERENCIA,
						DAT_INICIAL,
						DAT_FINAL,
						COD_USUARIO_INCLUSAO,
						DAT_INCLUSAO,
						FLG_STATUS,
						DAT_IMPORTACAO_XML
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqOrgao).",
						".$this->util->converterTelaBanco($obs).",
						'".$this->util->qualMesNumero($mesNum)." / ".$ano."',
						'".$ano."-".$mesNum."-1',
						".$this->util->converterTelaBanco($ultimoDia).",
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
	 * Método para atualizar as respostas das produtividades das serventias pelos parametros passados.
	 * @param unknown $seqServentia Seq da serventia passado por parametro.
	 * @param unknown $obs Observeção da produtividade.
	 * @param unknown $ano Ano da produtividade.
	 * @param unknown $mesNum Mes númerico informado no arquivo da produtividade.
	 * @param unknown $ultimoDia Ultimo dia do mes da produtividade.
	 * @param unknown $mes Mes da produtividade
	 * @param unknown $seqProdutividade SeqProdutividade passado por parametro.
	 */
	function atualizarProdServentiaPrmGrau($seqOrgao,$obs,$ano,$numMes,$mes,$ultimoDia,$seqProdutividade)
	{
		$sql = "UPDATE SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA
					SET 
					SEQ_ORGAO				= ".$this->util->converterTelaBanco($seqOrgao).",
					DSC_TEXTO_PRODUTIVIDADE	= ".$this->util->converterTelaBanco($obs).",
					MES_REFERENCIA	 	   	= '".$this->util->qualMesNumero($numMes)." / ".$ano."',
					DAT_INICIAL		   		= '".$ano."-".$numMes."-1',
					DAT_FINAL				= ".$this->util->converterTelaBanco($ultimoDia).",
					FLG_STATUS				= 1,
					DAT_IMPORTACAO_XML		= NOW()
				WHERE SEQ_PRODUTIVIDADE_SERVENTIA	= ".$seqProdutividade;
		$q = $this->db->execute($sql);
		if ($q){
			return $seqProdutividade;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para salvar resposta das serventias pelos parametros passados.
	 * @param $params
	 */
	function inserirRespServentiaPrmGrau($seqProdutividade,$seqPerg,$resp)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				(
				   		SEQ_PRODUTIVIDADE_SERVENTIA,
				   		SEQ_PERGUNTA_SERVENTIA,
						VLR_RESPOSTA,
						DAT_INCLUSAO,
						COD_USU_INCLUSAO,
						FLG_STATUS
				)
				VALUES
				(
						".$seqProdutividade.",
						".$seqPerg.",
						".$this->util->converterTelaBanco($resp).",
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
	
	/****************************************/
	/* 		Mètodos do Terceiro Passo		*/
	/****************************************/

	/**
	 * Método para trazer todos os magistrados do estado passado pela session.
	 */
	function retornaMagistradoEstado()
	{
		$tribunal = $this->tribunalUf();
		$sql = "SELECT 	mg.SEQ_MAGISTRADO,
						mg.NUM_CPF_MAGISTRADO,
						mg.NOM_MAGISTRADO,
						mg.NUM_MATRICULA,
						mg.IND_UF,
						mg.DAT_NASCIMENTO,
						mg.DSC_EMAIL_JUIZ,
						mg.NUM_TELEFONE_FIXO_JUIZ,
						mg.DAT_INGRESSO_MAGISTRATURA,
						case mg.IND_SEXO when 0 then 'M'
						else 'F' end as 'sexo'
				FROM CORREGEDORIA_CNJ.MAGISTRADO mg
				where mg.IND_UF in ($tribunal)
				group by mg.SEQ_MAGISTRADO
				ORDER BY mg.NOM_MAGISTRADO";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para retornar todos os tipos dos magistrados.
	 */
	function retornaTipoMagistrado($tipo=NULL)
	{
		$where = NULL;
		if($tipo){
			$where = "AND CODIGO = $tipo";
		}
		$sql = "SELECT 	CODIGO,
						DESCRICAO,
						DESCRICAO_COMPLETA
				FROM COMPARTILHADO.AUXILIAR
				WHERE NOME_COLUNA='IND_TIPO_JUIZ'
				$where";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para retornar todos os status dos magistrados.
	 */
	function retornaStatusMagistrado($cod=NULL)
	{
		$and = NULL;
		if(is_numeric($cod))
			$and = "and aux.codigo = $cod";
		
		$sql = "SELECT  aux.CODIGO,
						aux.DESCRICAO,
						aux.DESCRICAO_COMPLETA
				FROM COMPARTILHADO.AUXILIAR aux
				WHERE NOME_COLUNA = 'ind_status_juiz'
				and aux.ATIVO = 1
				$and
				order by ABS(aux.codigo)";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	function retornaTipoAtualMagistrado($seqOrgao,$seqMagistrado,$tipo)
	{
		$sql = "SELECT 	SEQ_MAGISTRADO_SERVENTIA,
						IND_TIPO_JUIZ,
						FLG_STATUS
				FROM SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA
				WHERE SEQ_MAGISTRADO = ".$seqMagistrado."
				AND	  SEQ_ORGAO	= ".$seqOrgao."
				AND   IND_TIPO_JUIZ = ".$tipo."
				order by SEQ_MAGISTRADO_SERVENTIA desc";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para excluir todos os magistrados da serventia passada por parametro.
	 */
	function excluirMagistradoServentia($seqOrgao,$seqMagistrado)
	{
		$sql = "DELETE FROM SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA 
				WHERE SEQ_ORGAO = ".$seqOrgao."
				AND	  SEQ_MAGISTRADO = $seqMagistrado";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqOrgao;
		}else{
			return NULL;
		}
	}
	
	function inativarTipoAnterior($seqMagistradoServentia,$dataSaida)
	{
		$sql = "UPDATE SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA
					SET FLG_STATUS = '0',
				        DAT_SAIDA_SERVENTIA = ".$this->util->converterTelaBanco($dataSaida)."
				WHERE SEQ_MAGISTRADO_SERVENTIA = ".$this->util->converterTelaBanco($seqMagistradoServentia);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqMagistradoServentia;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para salvar os magistrados nas serventias pelos parametros passados.
	 * @param unknown $seqServentia
	 * @param unknown $seqMagistrado
	 * @param unknown $tipo
	 * @return NULL
	 */
	function alterarMagistradoServentia($seqMagistradoServentia)
	{
		if($dataSaida)
			$status = ",FLG_STATUS = 0";
		
		$sql = "UPDATE SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA
					SET	COD_USU_INCLUSAO	   = ".$_SESSION['seq_usuario'].",
						DAT_IMPORTACAO_XML     = NOW(),
						DSC_IP_USU_INCLUSAO    = '".$_SERVER['REMOTE_ADDR']."'
						$status
				WHERE SEQ_MAGISTRADO_SERVENTIA = ".$this->util->converterTelaBanco($seqMagistradoServentia);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqMagistradoServentia;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para salvar os magistrados nas serventias pelos parametros passados.
	 * @param unknown $seqServentia
	 * @param unknown $seqMagistrado
	 * @param unknown $tipo
	 * @return NULL
	 */
	function inserirMagistradoServentia($seqOrgao,$seqMagistrado,$tipo)
	{
		$sql = "
				INSERT INTO SERVENTIAS_PRM_GRAU.MAGISTRADO_SERVENTIA
				(
				   		SEQ_ORGAO,
				   		SEQ_MAGISTRADO,
						IND_TIPO_JUIZ,
						DAT_INCLUSAO,
						FLG_STATUS,
						COD_USU_INCLUSAO,
						DSC_IP_USU_INCLUSAO,
						DAT_IMPORTACAO_XML
				)
				VALUES
				(
						".$seqOrgao.",
						".$seqMagistrado.",
						".$tipo.",
						NOW(),
						1,
						".$_SESSION['seq_usuario'].",
						'".$_SERVER['REMOTE_ADDR']."',
						NOW()
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}	
	}
	
	/****************************************/
	/* 		Mètodos do Quarto Passo			*/
	/****************************************/

	/**
	 * Método para trazer todos os tipos de responsavel.
	 */
	function retornaTipoResponsavel()
	{
		$sql = "SELECT 	CODIGO, 
						DESCRICAO
				FROM COMPARTILHADO.AUXILIAR
				WHERE NOME_COLUNA='IND_TIPO_RESPONSAVEL'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para trazer todos os tipos de denominação.
	 */
	function retornaTipoDenominacao()
	{
		$sql = "SELECT 	CODIGO, 
						DESCRICAO
				FROM COMPARTILHADO.AUXILIAR
				WHERE NOME_COLUNA='IND_TIPO_DENOMINACAO'";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para trazer todas as secretarias do estado passado pela session.
	 */
	function retornaSecretariaEstado()
	{
		$sql = "SELECT  sc.SEQ_SECRETARIA_SERVENTIA,
						seq_corporativo,
						CASE numOrdem WHEN 0 
						THEN denominacao
						ELSE CONCAT(CONVERT(numOrdem,CHAR),'º ',CONVERT(denominacao,CHAR)) END DSC_DENOM_SERVENTIA_JUDICIAL,
						sc.NOM_RESPONSAVEL_SECRETARIA,
						sc.IND_TIPO_RESPONSAVEL,
						sc.IND_TIPO_DENOMINACAO,
						sc.NUM_TELEFONE_OUTROS,
						sc.NUM_TELEFONE_SECRETARIA,
						sc.DSC_ENDERECO_SECRETARIA,
						sc.DSC_BAIRRO_SECRETARIA,
						sc.DSC_END_COMPLEMENTO_SECRETARIA,
						sc.NUM_END_SECRETARIA,
						sc.IND_UF,
						sc.COD_CIDADE,
						sc.COD_CEP,
						sc.DSC_EMAIL_SECRETARIA,
						sc.FLG_STATUS,
						DS_CIDADE
						FROM(SELECT     DISTINCT org.SEQ_ORGAO AS 'seq_corporativo',
								IFNULL(org.INT_ORDEM_ORGAO,0) AS 'numOrdem',
								TRIM(CASE org.DSC_ORGAO 
									WHEN org.DSC_ORGAO NOT LIKE 'ª %' THEN SUBSTR(org.DSC_ORGAO,3)
									WHEN org.DSC_ORGAO NOT LIKE 'º %' THEN SUBSTR(org.DSC_ORGAO,3)
								ELSE org.DSC_ORGAO END) AS 'denominacao',
								uf.SIG_UF AS 'uf',
								org.FLG_ATIVO as 'ativo',
								org_p1.SEQ_ORGAO_PAI AS 'orgaoPai',
								org_p.DSC_ORGAO AS DS_CIDADE,
								org.SEQ_CIDADE AS 'seqCidade',
								SUBSTR(org_p1.DSC_ORGAO,11) AS 'tribunal'
								FROM corporativo.orgao org
								JOIN corporativo.orgao org_p    ON org.SEQ_ORGAO_PAI    = org_p.SEQ_ORGAO
								JOIN corporativo.orgao org_p1   ON org_p.SEQ_ORGAO_PAI  = org_p1.SEQ_ORGAO
								JOIN corporativo.orgao org_p2   ON org_p1.SEQ_ORGAO_PAI = org_p2.SEQ_ORGAO
								JOIN corporativo.tribunal_uf uf ON org_p2.SEQ_ORGAO = uf.SEQ_ORGAO
								WHERE org_p1.DSC_ORGAO LIKE '%1º Grau%'
								AND org_p.DSC_ORGAO NOT LIKE '%seção Judiciária%')tb
						JOIN SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA sc ON sc.SEQ_ORGAO = seq_corporativo
						join COMPARTILHADO.TRIBUNAL tri on tri.SEQ_ORGAO = tb.orgaoPai
						WHERE tribunal = '".$_SESSION['sig_tribunal']."'
						and ativo = 'S'
						group by seq_corporativo
						ORDER BY uf,DS_CIDADE,numOrdem,denominacao";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para trazer todas as secretarias do estado passado pela session.
	 */
	function retornaSecretariaFederal()
	{
		$sql = "SELECT  sc.SEQ_SECRETARIA_SERVENTIA,
						seq_corporativo,
						CASE numOrdem WHEN 0
						THEN denominacao
						ELSE CONCAT(CONVERT(numOrdem,CHAR),'º ',CONVERT(denominacao,CHAR)) END DSC_DENOM_SERVENTIA_JUDICIAL,
						sc.NOM_RESPONSAVEL_SECRETARIA,
						sc.IND_TIPO_RESPONSAVEL,
						sc.IND_TIPO_DENOMINACAO,
						sc.NUM_TELEFONE_OUTROS,
						sc.NUM_TELEFONE_SECRETARIA,
						sc.DSC_ENDERECO_SECRETARIA,
						sc.DSC_BAIRRO_SECRETARIA,
						sc.DSC_END_COMPLEMENTO_SECRETARIA,
						sc.NUM_END_SECRETARIA,
						sc.IND_UF,
						sc.COD_CIDADE,
						sc.COD_CEP,
						sc.DSC_EMAIL_SECRETARIA,
						sc.FLG_STATUS,
						DS_CIDADE
						FROM(SELECT    	   DISTINCT org.SEQ_ORGAO AS 'seq_corporativo',
								   TRIM(REPLACE(REPLACE(REPLACE(REPLACE(org1.DSC_ORGAO,'SUBSEÇÃO JUDICIÁRIA DE',''),'Subseção Judiciária de',''),'SUBSEÇÃO JUDICIÁRIA DO',''),'Subseção Judiciária do','')) AS 'DS_CIDADE',
								   org2.DSC_ORGAO,
								   org.FLG_ATIVO as 'ativo',
								   REPLACE(org3.DSC_ORGAO,'1º Grau - ','') AS 'tribunal',
								   IFNULL(org.INT_ORDEM_ORGAO,0) AS 'numOrdem',
								   TRIM(CASE org.DSC_ORGAO
										WHEN org.DSC_ORGAO NOT LIKE 'ª %' THEN SUBSTR(org.DSC_ORGAO,3)
										WHEN org.DSC_ORGAO NOT LIKE 'º %' THEN SUBSTR(org.DSC_ORGAO,3)
										ELSE org.DSC_ORGAO END) AS 'denominacao'
								    FROM corporativo.orgao org
								    JOIN corporativo.orgao org1 ON org1.SEQ_ORGAO = org.SEQ_ORGAO_PAI
								    JOIN corporativo.orgao org2 ON org2.SEQ_ORGAO = org1.SEQ_ORGAO_PAI
								    JOIN corporativo.orgao org3 ON org3.SEQ_ORGAO = org2.SEQ_ORGAO_PAI
								    WHERE org.TIP_ORGAO = 'VARAF')tb
				         JOIN SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA sc ON sc.SEQ_ORGAO = seq_corporativo
						 WHERE tribunal = '".$_SESSION['sig_tribunal']."'
						 and ativo = 'S'
						 ORDER BY DS_CIDADE";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método para verificar se já existe secretaria de serventia informada por parametro. 
	 * @param unknown $seqServentia
	 * @return unknown
	 */
	function retornaSecretariaServentia($seqServentia)
	{
		$sql 	= " SELECT SEQ_SECRETARIA_SERVENTIA
						FROM SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA
					WHERE SEQ_SERVENTIA_JUDICIAL = ".$seqServentia;
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}

	/**
	 * Método para inserir nova secretaria pelos parametros passados.
	 */
	function inserirSecretariaServentia($seqOrgao,$cpfResponsavel,$responsabilidade,$tipResponsavel,$tipDenominacao,$dddOutro,$telOutro,$dddTel,$telSec,$endSec,$dscBairro,$endComp,$numEnd,$uf,$cidade,$cep,$email)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA
			    (
						NUM_CPF_RESPONSAVEL_SECRETARIA,
						NOM_RESPONSAVEL_SECRETARIA,
						IND_TIPO_RESPONSAVEL,
					    IND_TIPO_DENOMINACAO,
						NUM_DDD_TELEFONE_OUTROS,
						NUM_TELEFONE_OUTROS,
						NUM_DDD_TELEFONE_SECRETARIA,
						NUM_TELEFONE_SECRETARIA,
						DSC_ENDERECO_SECRETARIA,
						DSC_BAIRRO_SECRETARIA,
						DSC_END_COMPLEMENTO_SECRETARIA,
						NUM_END_SECRETARIA,
						IND_UF,
						COD_CIDADE,
						COD_CEP,
						DSC_EMAIL_SECRETARIA,
						FLG_STATUS,
						SEQ_ORGAO,
						DAT_IMPORTACAO_XML
				)
				VALUES
				(
						'".$this->util->converterTelaBanco($cpfResponsavel)."',
						".$this->util->converterTelaBanco($responsabilidade).",
						".$this->util->converterTelaBanco($tipResponsavel).",
						".$this->util->converterTelaBanco($tipDenominacao).",
						".$this->util->converterTelaBanco($dddOutro).",
						".$this->util->converterTelaBanco($telOutro).",
						".$this->util->converterTelaBanco($dddTel).",
						".$this->util->converterTelaBanco($telSec).",
						".$this->util->converterTelaBanco($endSec).",
						".$this->util->converterTelaBanco($dscBairro).",
						".$this->util->converterTelaBanco($endComp).",
						".$this->util->converterTelaBanco($numEnd).",
						".$this->util->converterTelaBanco($uf).",
						".$this->util->converterTelaBanco($cidade).",
						".$this->util->converterTelaBanco($cep).",
						".$this->util->converterTelaBanco($email).",
						'1',
						".$this->util->converterTelaBanco($seqOrgao).",
						NOW()
				)";
		$q 	= $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}	
	}
	
	/**
	 * Método para atualizar as secretaria pelos parametros passados.
	 * @param $params
	 */
	function atualizarSecretariaServentia($seqOrgao,$seqSecretaria,$cpfResponsavel,$responsabilidade,$tipResponsavel,$tipDenominacao,$dddOutro,$telOutro,$dddTel,$telSec,$endSec,$dscBairro,$endComp,$numEnd,$uf,$cidade,$cep,$email)
	{
		$sql =	"UPDATE SERVENTIAS_PRM_GRAU.SECRETARIA_SERVENTIA
					SET NOM_RESPONSAVEL_SECRETARIA 	   = ".$this->util->converterTelaBanco($responsabilidade).",
						NUM_CPF_RESPONSAVEL_SECRETARIA = ".$this->util->converterTelaBanco($cpfResponsavel).",
						IND_TIPO_RESPONSAVEL		   = ".$this->util->converterTelaBanco($tipResponsavel).",
						IND_TIPO_DENOMINACAO	 	   = ".$this->util->converterTelaBanco($tipDenominacao).",						
						NUM_DDD_TELEFONE_OUTROS		   = ".$this->util->converterTelaBanco($dddOutro).",
						NUM_TELEFONE_OUTROS		 	   = ".$this->util->converterTelaBanco($telOutro).",
						NUM_DDD_TELEFONE_SECRETARIA	   = ".$this->util->converterTelaBanco($dddTel).",
						NUM_TELEFONE_SECRETARIA	 	   = ".$this->util->converterTelaBanco($telSec).",
						DSC_ENDERECO_SECRETARIA	 	   = ".$this->util->converterTelaBanco($endSec).",
						DSC_BAIRRO_SECRETARIA	 	   = ".$this->util->converterTelaBanco($dscBairro).",
						DSC_END_COMPLEMENTO_SECRETARIA = ".$this->util->converterTelaBanco($endComp).",
						NUM_END_SECRETARIA 			   = ".$this->util->converterTelaBanco($numEnd).",
						IND_UF			 			   = ".$this->util->converterTelaBanco($uf).",
						COD_CIDADE		 			   = ".$this->util->converterTelaBanco($cidade).",
						COD_CEP			 			   = ".$this->util->converterTelaBanco($cep).",
						DSC_EMAIL_SECRETARIA		   = ".$this->util->converterTelaBanco($email).",
						FLG_STATUS		   			   = '1',
						SEQ_ORGAO				       = ".$this->util->converterTelaBanco($seqOrgao).",
						DAT_IMPORTACAO_XML			   = NOW()		
					WHERE SEQ_SECRETARIA_SERVENTIA	 = ".$this->util->converterTelaBanco($seqSecretaria);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqSecretaria;
		}else{
			return NULL;
		}
	}
	
	/****************************************/
	/*		Mètodos do Quinto Passo			*/
	/****************************************/
	
	/**
	 * Método para trazer todas as perguntas dos magistrados.
	 */
	function listaPerguntaMagistradoPrmGrau()
	{
		$sql = "SELECT  perg.SEQ_PERGUNTA_SERVENTIA as 'seqPergunta',
						perg.DSC_SIGLA_PERGUNTA as 'sigla',
						perg.DSC_TIPO_PERGUNTA_SERVENTIA as 'descricao',
						perg.DSC_PERGUNTA as 'glossario',
						perg.NUM_ORDEM,
						'1' as 'grau'
				FROM  SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA perg
				join SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA_SERVENTIA = dtp.SEQ_PERGUNTA_SERVENTIA and dtp.SEQ_ORGAO = ".$_SESSION['seq_orgao_pai']."
				WHERE FLG_DESTINATARIO_PERGUNTA='M'
				AND FLG_ATIVO = '1'
				ORDER BY DSC_SIGLA_PERGUNTA";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna se existe lista de respostas da produtividade do magistrado pelo parametro passado. 
	 */
	function listaRespMag($seqMagi){
		$sql = "SELECT  SEQ_RESPOSTA_SERVENTIA
				FROM  SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				WHERE  SEQ_PRODUTIVIDADE_MAGISTRADO = ".$seqMagi;
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna se já tem resposta pelos parametros passados.
	 * @param unknown $seqProdMagi
	 * @param unknown $seqPergunta
	 * @return NULL
	 */
	function retornaSeqRespMagiPrmGrau($seqProdMagi,$seqPergunta)
	{
		$sql = "SELECT SEQ_RESPOSTA_SERVENTIA
				FROM  SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				WHERE SEQ_PRODUTIVIDADE_MAGISTRADO = ".$seqProdMagi."
				AND SEQ_PERGUNTA_SERVENTIA = ".$seqPergunta;
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['SEQ_RESPOSTA_SERVENTIA'];
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para retornar se já existe produtividade dos magistrados pelo parametro passado.
	 */
	function retornaProdutividadeMagistradoPrmGrau($seqMagistrado,$seqOrgao,$mes,$ano,$tipo){
		if(trim($seqMagistrado))
			$and = "AND SEQ_MAGISTRADO = ".$this->util->converterTelaBanco($seqMagistrado)."";
		else 
			$and = "AND SEQ_MAGISTRADO is NULL";
		
		if(!empty($tipo)){
			$andTipo = "and IND_TIPO_JUIZ = $tipo";
		}else{ 
			if(is_numeric($tipo) && $tipo == 0)
				$andTipo = "and IND_TIPO_JUIZ = $tipo";
			else
				$andTipo = "and IND_TIPO_JUIZ is NULL";
		}
		
		$sql = "SELECT 	SEQ_PRODUTIVIDADE_MAGISTRADO
				FROM SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO
				WHERE SEQ_ORGAO = ".$this->util->converterTelaBanco($seqOrgao)."
				$and
				AND MES_REFERENCIA = '".$this->util->qualMesNumero($mes)." / ".$ano."'
				AND FLG_STATUS = 1
				$andTipo";
		$q = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res[0]['SEQ_PRODUTIVIDADE_MAGISTRADO'];
		}else{ 
			return NULL;
		}
	}
	
	/**
	 * Método para salvar a produtividade dos magistrados pelos parametros passados.
	 * @param unknown $seqServentia
	 * @param unknown $seqMagistrado
	 * @param unknown $tipoJuiz
	 * @param unknown $obs
	 * @param unknown $mesReferencia
	 * @param unknown $ano
	 * @param unknown $dtInicio
	 * @param unknown $dtFim
	 * @return NULL
	 */
	function inserirMagistradoProdutividadePrmGrau($seqOrgao,$seqMagistrado,$tipoJuiz,$obs,$mesReferencia,$ano,$qtdDias)
	{
		$sql = "
				INSERT INTO SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO
				(
				   		SEQ_ORGAO,
				   		SEQ_MAGISTRADO,
						IND_TIPO_JUIZ,
						DSC_TEXTO_PRODUTIVIDADE,
						MES_REFERENCIA,
						QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES,
						DAT_INCLUSAO,
						FLG_STATUS,
						DAT_IMPORTACAO_XML,
						COD_USU_INCLUSAO
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqOrgao).",
						".$this->util->converterTelaBanco($seqMagistrado).",
						".$this->util->converterTelaBanco($tipoJuiz).",
						".$this->util->converterTelaBanco($obs).",
						'".$this->util->qualMesNumero($mesReferencia)." / ".$ano."',
						".$this->util->converterTelaBanco($qtdDias).",
						NOW(),
						1,
						NOW(),
						".$_SESSION['seq_usuario']."
				)";
		$q 		= $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}	
	}
	
	function alterarMagistradoProdutividadePrmGrau($seqProduvidade,$datInicio,$datFinal,$indTipo,$obs,$qtdDias)
	{
		$sql =	"UPDATE SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_MAGISTRADO
					SET DSC_TEXTO_PRODUTIVIDADE 				 = ".$this->util->converterTelaBanco($obs).",
						DAT_INICIAL		   						 = ".$this->util->converterDataParaBanco($datInicio).",
						DAT_FINAL	 	   						 = ".$this->util->converterDataParaBanco($datFinal).",
						QTD_DIAS_CORRIDOS_ATUACAO_MAGISTRADO_MES = ".$this->util->converterTelaBanco($qtdDias).",
						IND_TIPO_JUIZ							 = ".$this->util->converterTelaBanco($indTipo).",
						DAT_IMPORTACAO_XML						 = NOW()
					WHERE SEQ_PRODUTIVIDADE_MAGISTRADO	= ".$this->util->converterTelaBanco($seqProduvidade);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqProduvidade;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para salvar respostas da produtividade dos magistrados pelos parametros passados.
	 * @param $params
	 */
	function inserirRespMagistradoPrmGrau($seqPergunta,$seqProdutividade,$resposta)
	{
		$sql = "
				INSERT INTO SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
				(
				   		SEQ_PERGUNTA_SERVENTIA,
				   		SEQ_PRODUTIVIDADE_MAGISTRADO,
						VLR_RESPOSTA,
						DAT_INCLUSAO,
						COD_USU_INCLUSAO,
						FLG_STATUS
				)
				VALUES
				(
						".$this->util->converterTelaBanco($seqPergunta).",
						".$this->util->converterTelaBanco($seqProdutividade).",
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
	 * Método para atualizar as respostas das produtividades das serventias/magistrado pelos parametros passados.
	 * @param $params
	 */
	function atualizarRespServentiaPrmGrau($vlrResposta,$seqResposta)
	{
		$sql =	"UPDATE SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
					SET VLR_RESPOSTA		   		= ".$this->util->converterTelaBanco($vlrResposta).",
						DAT_ALTERACAO	 	   		= NOW(),
						COD_USU_ALTERACAO			= ".$_SESSION['seq_usuario'].",
						FLG_STATUS		   			= 1
					WHERE SEQ_RESPOSTA_SERVENTIA	= $seqResposta";
		$q = $this->db->execute($sql);
		if ($q){
			return $seqResposta;
		}else{
			return NULL;
		}
	}
	
	function listarDocumentoMeta18($seqProdutividade)
	{
		$sql = "SELECT  SEQ_DOCUMENTO_META18,
						NOM_ARQUIVO,
						TIP_ARQUIVO
				FROM SERVENTIAS_PRM_GRAU.DOCUMENTO_META18
				WHERE SEQ_PRODUTIVIDADE_MAGISTRADO = ".$this->util->converterTelaBanco($seqProdutividade);
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		if($res)
			return $res;
		else
			return NULL;
	}
	
	/**
	 * Método insere os documentos da META18 na produtividade.
	 * @param unknown $seqProdutividadeMagistrado
	 * @param unknown $nomeArq
	 * @param unknown $tipo
	 * @return NULL
	 */
	function inserirDocumentoMeta18($seqProdutividadeMagistrado,$nomeArq,$tipo)
	{
		$tipo = explode(".",$tipo);
	
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.DOCUMENTO_META18
				(
					SEQ_PRODUTIVIDADE_MAGISTRADO,
					NOM_ARQUIVO,
					TIP_ARQUIVO
				)
				VALUES
				(
					".$this->util->converterTelaBanco($seqProdutividadeMagistrado).",
					".$this->util->converterTelaBanco($nomeArq).",
					".$this->util->converterTelaBanco($tipo[1])."
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método para excluir os documentos da META18.
	 * @param unknown $param
	 * @return unknown|NULL
	 */
	function excluirDocumentoMeta18($seqProdutividade)
	{
		$sql = "DELETE FROM SERVENTIAS_PRM_GRAU.DOCUMENTO_META18
				WHERE SEQ_PRODUTIVIDADE_MAGISTRADO = ".$this->util->converterTelaBanco($seqProdutividade);
		$q = $this->db->execute($sql);
		if ($q){
			return $seqProdutividade;
		}else{
			return NULL;
		}
	}
	
	/************************************/
	/* 		Mètodos do Sexto Passo		*/
	/************************************/
	
	/**
	 * Método lista as competencias das serventias da infancia e juventude
	 * @return unknown
	 */
	function listarCompenteciaInfanciaJuventude()
	{
		$sql = "select 	aux.CODIGO,
						aux.DESCRICAO
				from COMPARTILHADO.AUXILIAR aux
				where aux.NOME_COLUNA = 'ind_competencia_infancia_juventude'
				and aux.ATIVO = 1";
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método lista as as perguntas do questionário de estrutura.
	 * @return unknown
	 */
	function listarTipoRemuneracao()
	{
		$sql = "select *
				from SERVENTIAS_PRM_GRAU.TIPO_FORMA_REMUNERACAO";
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método retorna informações do questionario de infancia e juventude.
	 * @return unknown
	 */
	function retornaInfanciaJuventudeQuestionario($orgao,$ano)
	{
		$sql = "select 	rac.*
		from SERVENTIAS_PRM_GRAU.ROTEIRO_ACESSO_COMARCA rac
		where rac.SEQ_ORGAO = '$orgao'
		and rac.NUM_ANO_REFERENCIA = '$ano'";
		
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		if($res){
			return $res[0];
		}else{
			return NULL;
		}
	
	}
	
	/**
	 * Método verifica se a serventia passada por parametro tem competencia de Infancia e Juventude vinculado.
	 * @return unknown
	 */
	function retornaCompetencia($seqOrgao)
	{
		$sql = "select cs.SEQ_COMPETENCIA_JUIZO 
				from SERVENTIAS_PRM_GRAU.COMPETENCIA_SERVENTIA cs
				where cs.SEQ_ORGAO = $seqOrgao
				group by cs.SEQ_COMPETENCIA_JUIZO";
		$q   = $this->db->execute($sql);
		if($q){
			$res = $q->getRows();
			return $res;
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método inserir o questionário do quinto passo.
	 * @param unknown_type $param
	 * @return NULL
	 */
	function inserirRoteiroAcessoComarca($seqOrgao,$ano,$formaTrajeto,$competencia,$cidade,$kmDistancia,$hrDistancia)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.ROTEIRO_ACESSO_COMARCA
				(
					NUM_ANO_REFERENCIA,
					TIP_FORMA_TRAJETO,
					NUM_QUILOMETRO_DISTANCIA,
					NUM_HORA_TRAJETO,
					SEQ_ORGAO,
					ID_CIDADE,
					TIP_COMPETENCIA_VARA,
					DAT_INCLUSAO,
					USU_INCLUSAO
				)
				VALUES
				(
					".$this->util->converterTelaBanco($ano).",
					".$this->util->converterTelaBanco($formaTrajeto).",
					".$this->util->converterTelaBanco($kmDistancia).",
					".$this->util->converterTelaBanco($hrDistancia).",
					".$this->util->converterTelaBanco($seqOrgao).",
					".$this->util->converterTelaBanco($cidade).",
					".$this->util->converterTelaBanco($competencia).",
					NOW(),
					".$this->util->converterTelaBanco($_SESSION['seq_usuario'])."
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método retorna o Seq_Pergunta pelos parametros passados.
	 * @param unknown $gurpo
	 * @param unknown $ordem
	 * @param string $descricao
	 */
	function retornaSeqPergunta($gurpo,$ordem,$descricao=NULL)
	{
		if($descricao)
			$and = "and pe.DSC_TITULO_GRUPO_PERGUNTA = '".$descricao."'";
		else
			$and = "";
			
		$sql = "select * 
				from SERVENTIAS_PRM_GRAU.PERGUNTA_ESTRUTURA pe
				where pe.FLG_ATIVO = '1'
				and pe.NUM_ORDEM_TITULO_GRUPO = $gurpo
				and pe.NUM_ORDEM_PERGUNTA = $ordem
				$and";
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0]['SEQ_PERGUNTA_ESTRUTURA'];
	}
	
	/**
	 * Método lista os Seq_Pergunta pelos parametros passados.
	 * @param unknown $gurpo
	 * @param unknown $descricao
	 * @return unknown
	 */
	function listaSeqPergunta($gurpo,$descricao)
	{
		$sql = "select *
				from SERVENTIAS_PRM_GRAU.PERGUNTA_ESTRUTURA pe
				where pe.FLG_ATIVO = '1'
				and pe.NUM_ORDEM_TITULO_GRUPO = $gurpo
				and pe.DSC_PERGUNTA = '".$descricao."'";
		$q   = $this->db->execute($sql);
		$res = $q->getRows();
		return $res;
	}
	
	/**
	 * Método inserir as respostas quesito da estrutura.
	 * @param unknown_type $param
	 * @return NULL
	 */
	function inserirRespostaQuesitoEstrutura($seqPergunta,$vlrResposta,$seqRoteiro)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.RESPOSTA_QUESITO_ESTRUTURA
				(
					SEQ_PERGUNTA_ESTRUTURA,
					VLR_RESPOSTA,
					SEQ_ROTEIRO_ACESSO_COMARCA
				)
				VALUES
				(
					".$this->util->converterTelaBanco($seqPergunta,1).",
					".$this->util->converterTelaBanco($vlrResposta,1).",
					".$this->util->converterTelaBanco($seqRoteiro,1)."
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método inserir a forma de remuneração equipe multidimensional.
	 * @param unknown_type $param
	 * @return NULL
	 */
	function inserirFormaRemuneracaoEquipeMulti($tipo,$seqRoteiro)
	{
		$sql = "INSERT INTO SERVENTIAS_PRM_GRAU.FORMA_REMUNERACAO_EQUIPE_MULTI
				(
					SEQ_TIPO_FORMA_REMUNERACAO,
					SEQ_ROTEIRO_ACESSO_COMARCA
				)
				VALUES
				(
					".$this->util->converterTelaBanco($tipo,1).",
					".$this->util->converterTelaBanco($seqRoteiro,1)."
				)";
		$q = $this->db->execute($sql);
		if ($q){
			return $this->db->Insert_ID();
		}else{
			return NULL;
		}
	}
}
?>
