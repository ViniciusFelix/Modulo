<?php
class segGrau{

	function segGrau($db, $smarty)
	{
		libxml_use_internal_errors(true);
        $this->db = $db;
		$this->smarty = $smarty;

		require_once ("bibliotecas/excel_reader2.php");
		
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
		
		include_once("sql/segGrau/bdSegGrau.php");
		$this->sql = new bdSegGrau($db);
		
		include_once("php/segGrau/montaArray.php");
		$this->array = new montaArray($db, $smarty);
		
		include_once("php/util.php");
		$this->util = new util();
	}
	
	/**
	 * Método para montar a página principal da importação da classe. 
	 */
	function form2Grau()
	{
		permissao($_POST);
		
		if(isset($_POST['limpaPost']) == 1){
			unset ($_POST['validoXml']);
		}
		
		$comboPresidencia = $this->sql->comboPresidencia();
		$this->smarty->assign('comboPresidencia',$comboPresidencia);
		
		$listaPeriodo = $this->sql->comboPeriodoPreenchimento();
		$this->smarty->assign('listaPeriodo',$listaPeriodo);
		
		for($i = 2009; $i <= date("Y"); $i++) {
			$comboAno[$i] = $i;
		} 
		$this->smarty->assign('comboAno',$comboAno);
		
		$arr_meses = array(
		      '01' => 'JANEIRO',
		      '02' => 'FEVEREIRO',
		      '03' => 'MARÇO',
		      '04' => 'ABRIL',
		      '05' => 'MAIO',
		      '06' => 'JUNHO',
		      '07' => 'JULHO',
		      '08' => 'AGOSTO',
		      '09' => 'SETEMBRO',
		      '10' => 'OUTUBRO',
		      '11' => 'NOVEMBRO',
		      '12' => 'DEZEMBRO'
        );
        $this->smarty->assign('comboMes',$arr_meses);
	}

	/**
	 * Método verifica se o xml está no formatação do padrão.
	 */
	function validarXml()
	{
		permissao($_POST);
		$caminhoXml = 'php/segGrau/arq2grau/'.$_FILES['arquivo_xml']['name'];

		try {
			if (! empty($_FILES['arquivo_xml']['name'])) {
				move_uploaded_file($_FILES['arquivo_xml']['tmp_name'], 'php/segGrau/arq2grau/'.$_FILES['arquivo_xml']['name']);
			}
				
			$xml = new DOMDocument();
			$xml->load('php/segGrau/arq2grau/'.$_FILES['arquivo_xml']['name']);
			
			if ($xml->schemaValidate('php/exemplos/validacaoXmlSegGrau.xsd')) {
			   	$parametros['validoXml']  = true;
			   	$parametros['caminhoXml'] = $caminhoXml;
			   	$parametros['nomeXml'] = $_FILES['arquivo_xml']['name'];
			}else {
			    $errors = libxml_get_errors();
			    throw new Exception("Ocorreu um erro ao validar os dados do xml na linha ".$errors[0]->line);
			}
		}catch (Exception $e) {
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		$parametros['a'] = 'segGrau';
		$parametros['d'] = 'segGrau';
		$parametros['f'] = 'form2Grau';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método salva os dados no xml informado.
	 */
	function salvarXml()
	{
		permissao($_POST);
		$xml = simplexml_load_file($_POST['caminhoXml']);
		try {
			$this->db->BeginTrans();
			foreach ($xml as $value) {
				$param['SEQ_MAGISTRADO'] 		    = $value->MAGISTRADO;
				$param['SEQ_PRESIDENCIA'] 	 		= $value->PRESIDENCIA;
				$param['NUM_MES_REFERENCIA'] 		= $value->MES;
				$param['NUM_ANO_REFERENCIA'] 		= $value->ANO;
				$param['DSC_TEXTO_PRODUTIVIDADE'] 	= $value->OBSERVACAO;
				$param['DSC_PERIODO_PREENCHIMENTO'] = $this->util->qualMesNumero($param['NUM_MES_REFERENCIA'])." / ".$param['NUM_ANO_REFERENCIA'];
				$param['IND_TIPO_PRODUTIVIDADE'] 	= $value->TIPOMAGISTRADO;
				$param['IND_TIPO_ATUACAO_MES'] 		= $value->TIPO_ATUACAO_MES;
				$param['IND_PERIODO_ATUACAO_MES'] 	= $value->PERIODO_ATUACAO_MES;
				
				$param['SEQ_PRODUTIVIDADE'] = $this->sql->retornaProdutividade($param);

				if(empty($param['SEQ_PRODUTIVIDADE'])){
					$param['SEQ_PRODUTIVIDADE'] = $this->sql->inserirProdutividade($param);
					
					if ($param['SEQ_PRODUTIVIDADE']){
						$produtividadeCompetencia = $this->sql->inserirProdutividadeCompetencia($value);
						if(empty($produtividadeCompetencia))
							throw new Exception("Ocorreu um erro ao salvar os dados do xml do magistrado ".$value->MAGISTRADO.".");
					}
				}else{
					$param['SEQ_PRODUTIVIDADE'] = $this->sql->alterarProdutividade($param);
				}
				
				if(!empty($param['SEQ_PRODUTIVIDADE'])){
					$seqResposta = $this->sql->retornaSeqResposta($param['SEQ_PRODUTIVIDADE'],$value->PERGUNTA);
					$param['SEQ_RESPOSTA'] = $seqResposta['SEQ_RESPOSTA'];
					$param['SEQ_PERGUNTA'] = $value->PERGUNTA;
					$param['VLR_RESPOSTA'] = $value->RESPOSTA;

					if($seqResposta){
						$resposta = $this->sql->atualizarResposta($param);
					}else{
						$resposta = $this->sql->inserirRespostaXml($param);
					}
					
					if(empty($resposta))
						throw new Exception("Ocorreu um erro ao salvar os dados do xml do magistrado ".$value->MAGISTRADO.".");
				}else{
					throw new Exception("Ocorreu um erro ao salvar os dados do xml do magistrado ".$value->MAGISTRADO.".");
				}
			}

			$this->db->CommitTrans();
			unlink($_POST['caminhoXml']);
			$parametros['msgSucesso'] = 'Dados do xml gravados com sucesso.';
		}
		catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		$parametros['a'] = 'segGrau';
		$parametros['d'] = 'segGrau';
		$parametros['f'] = 'form2Grau';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 *Método verifica se xls está na formatação do modelo.
	 */
	function validaXls()
	{
		permissao($_POST);
    	$caminhoXls = 'php/segGrau/arq2grau/'.$_FILES['arquivo_xls']['name'];
		
		if (! empty($_FILES['arquivo_xls']['name'])) {
			move_uploaded_file($_FILES['arquivo_xls']['tmp_name'], $caminhoXls);
        }
        
		$data = new Spreadsheet_Excel_Reader("php/segGrau/arq2grau/".$_FILES['arquivo_xls']['name']);

		$linhas = $data->rowcount();
		$colunas= $data->colcount();
		
		for($i = 2; $i <= 2; $i++){
		    for($j = 1; $j <= $colunas; $j++){
		    	if(trim($data->val($i,$j)) != '')
		        	$meuArray[$j] = $data->val($i,$j);
		    }
		}
		
		$periodo = $_POST['MES']." / ".$_POST['ANO'];
		$_POST['SEQ_PERIODO_PREENCHIMENTO'] = $this->sql->retornaSeqPeriodo($periodo);
		$arrayPerguntas = $this->sql->listaPerguntas($_POST);
		$countPergunta = count($arrayPerguntas);
		
		/*Verifica se xls está no formato certo*/
		if($_POST['FLG_DESTINATARIO_PERGUNTA'] == 1){
			$countXls = count($meuArray)-7;
			$ultimaPosicao = count($meuArray);
			if($meuArray[1] == "CPF" && $meuArray[2] == "Magistrado" && $meuArray[3] == "Atuação" &&
			   $meuArray[4] == "Competências" && $meuArray[5] == "Período de atuação em segundo grau" && 
			   $meuArray[6] == "Dias úteis sem exercício" && $meuArray[$ultimaPosicao] == "OBS" && $countPergunta == $countXls){
			   		$parametros['validoXls'] = true;
			   		$parametros['caminhoXls'] = $caminhoXls;
			   		$parametros['nomeXls'] = $_FILES['arquivo_xls']['name'];
			   		$parametros['SEQ_PRESIDENCIA'] = $_POST['SEQ_PRESIDENCIA'];
			   		$parametros['FLG_DESTINATARIO_PERGUNTA'] = $_POST['FLG_DESTINATARIO_PERGUNTA'];
			   		$parametros['SEQ_PRESIDENCIA'] = $_POST['SEQ_PRESIDENCIA'];
			   		$parametros['mes'] = $_POST['MES'];
			   		$parametros['ano'] = $_POST['ANO'];
			}else{
					unlink($caminhoXls);
					$parametros['msgErro'] = 'Dados do xls de formato inválido.';
			}
		}else if($_POST['FLG_DESTINATARIO_PERGUNTA'] == 3){
			$countXls = count($meuArray)-3;
			$ultimaPosicao = count($meuArray);
			if($meuArray[1] == "CPF" && $meuArray[2] == "Magistrado" && 
			   $countPergunta == $countXls && $meuArray[$ultimaPosicao] == "OBS"){
			   		$parametros['validoXls'] = true;
			   		$parametros['caminhoXls'] = $caminhoXls;
			   		$parametros['nomeXls'] = $_FILES['arquivo_xls']['name'];
			   		$parametros['SEQ_PRESIDENCIA'] = $_POST['SEQ_PRESIDENCIA'];
			   		$parametros['FLG_DESTINATARIO_PERGUNTA'] = $_POST['FLG_DESTINATARIO_PERGUNTA'];
			   		$parametros['SEQ_PRESIDENCIA'] = $_POST['SEQ_PRESIDENCIA'];
			   		$parametros['mes'] = $_POST['MES'];
			   		$parametros['ano'] = $_POST['ANO'];
			}else{
					unlink($caminhoXls);
					$parametros['msgErro'] = 'Dados do xls de formato inválido.';
			}
		}else if($_POST['FLG_DESTINATARIO_PERGUNTA'] == 4){
			$countXls = count($meuArray)-3;
			$ultimaPosicao = count($meuArray);
			if($meuArray[1] == "CPF" && $meuArray[2] == "Magistrado" && 
			   $countPergunta == $countXls && $meuArray[$ultimaPosicao] == "OBS"){
			   		$parametros['validoXls'] = true;
			   		$parametros['caminhoXls'] = $caminhoXls;
			   		$parametros['nomeXls'] = $_FILES['arquivo_xls']['name'];
			   		$parametros['SEQ_PRESIDENCIA'] = $_POST['SEQ_PRESIDENCIA'];
			   		$parametros['FLG_DESTINATARIO_PERGUNTA'] = $_POST['FLG_DESTINATARIO_PERGUNTA'];
			   		$parametros['SEQ_PRESIDENCIA'] = $_POST['SEQ_PRESIDENCIA'];
			   		$parametros['mes'] = $_POST['MES'];
			   		$parametros['ano'] = $_POST['ANO'];
			}else{
					unlink($caminhoXls);
					$parametros['msgErro'] = 'Dados do xls de formato inválido.';
			}
		}
		
		$parametros['aba'] = 2;
		$parametros['a'] = 'segGrau';
		$parametros['d'] = 'segGrau';
		$parametros['f'] = 'form2Grau';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método salva os dados no xls informado.
	 */
	function salvarXls()
	{
		permissao($_POST);
		$data = new Spreadsheet_Excel_Reader($_POST['caminhoXls']);

		$linhas = $data->rowcount();
		$colunas= $data->colcount();

		/*Monta array completo.*/	
		for($i = 3; $i <= $linhas; $i++){
		    for($j = 1; $j <= $colunas; $j++){
		    	if(trim($data->val($i,$j)) != '')
		       		$meuArray[$i][$j] = $data->val($i,$j);
		    }
		}
	
		$ultimaPosicao = count($meuArray[3]);
		
		try {
			$this->db->BeginTrans();
			foreach ($meuArray as $key=>$value) {
				$numMesRef = $this->util->qualMes($_POST['MES']);
				$value['DSC_PERIODO_PREENCHIMENTO'] = $_POST['MES']." / ".$_POST['ANO'];
				$value['NUM_MES_REFERENCIA'] 		= $numMesRef;
				$value['NUM_ANO_REFERENCIA'] 		= $_POST['ANO'];
				$value['SEQ_PRESIDENCIA'] 	 	    = $_POST['SEQ_PRESIDENCIA'];
				$value['IND_TIPO_PRODUTIVIDADE'] 	= $_POST['FLG_DESTINATARIO_PERGUNTA'];
				$value['DSC_TEXTO_PRODUTIVIDADE']	= $ultimaPosicao;
				$value['SEQ_MAGISTRADO'] = $this->sql->retornaMagistrado($value[1]);
				$value['SEQ_PRODUTIVIDADE'] = $this->sql->retornaProdutividade($value);
				$value['IND_TIPO_ATUACAO_MES'] = $this->sql->retornaTipo($value[3]);
				$value['IND_PERIODO_ATUACAO_MES'] = $this->sql->retornaAtuacao($value[5]);

				if($value['SEQ_MAGISTRADO']){
					if(empty($value['SEQ_PRODUTIVIDADE'])){
						$value['SEQ_PRODUTIVIDADE'] = $this->sql->inserirProdutividade($value);
						
						if(!empty($value['SEQ_PRODUTIVIDADE'])){
							$listaCompetencia = explode(",", $value[3]);
							foreach ($listaCompetencia as $key => $comp) {
								$value['IND_COMPETENCIA_PRODUTIVIDADE'] = $this->sql->retornaTipo($comp);
								$produtividadeCompetencia = $this->sql->inserirProdutividadeCompetencia($value);
								if(empty($produtividadeCompetencia))
									throw new Exception("Ocorreu um erro ao salvar as competencias.");
							}
						}
					}else{
						$value['SEQ_PRODUTIVIDADE'] = $this->sql->alterarProdutividade($value);
					}
					
					if(empty($value['SEQ_PRODUTIVIDADE'])){
						throw new Exception("Ocorreu um erro ao salvar os dados do xml do magistrado ".$value[1].".");
					}else{
						$value['FLG_DESTINATARIO_PERGUNTA'] = $_POST['FLG_DESTINATARIO_PERGUNTA'];
						$value['SEQ_PERIODO_PREENCHIMENTO'] = $_POST['SEQ_PERIODO_PREENCHIMENTO'];
						$value['SEQ_PERIODO_PREENCHIMENTO'] = $this->sql->retornaSeqPeriodo($value['DSC_PERIODO_PREENCHIMENTO']);
						$listaPerguntas = $this->sql->listaPerguntas($value);
						
						if($_POST['FLG_DESTINATARIO_PERGUNTA'] == 1){
							$arrayPerguntas = $this->array->arrayPerguntasFlgDesembargador($listaPerguntas,$value);
						}else if($_POST['FLG_DESTINATARIO_PERGUNTA'] == 3){
							$arrayPerguntas = $this->array->arrayPerguntasFlgCorreguedor($listaPerguntas,$value);
						}else if($_POST['FLG_DESTINATARIO_PERGUNTA'] == 4){
							$arrayPerguntas = $this->array->arrayPerguntasFlgPresidente($listaPerguntas,$value);
						}
						
						foreach ($arrayPerguntas as $key=>$resp){
							$seqResposta = $this->sql->retornaSeqResposta($value['SEQ_PRODUTIVIDADE'],$key);
							$value['SEQ_RESPOSTA'] = $seqResposta['SEQ_RESPOSTA'];
							$value['SEQ_PERGUNTA'] = $key;
							$value['VLR_RESPOSTA'] = $resp;
							
							if($seqResposta)
								$resposta = $this->sql->atualizarResposta($value);
							else
								$resposta = $this->sql->inserirRespostaXml($value);
							
							if(empty($resposta)) 
								throw new Exception("Ocorreu um erro ao salvar as respostas do(a) magistrado :".$value[2].".");
						}
					}
				}else{
					throw new Exception("Magistrado ".$value[2]." não está cadastrado ou está sem CPF na base de dados.");
				}
			}
			
			$this->db->CommitTrans();
			unlink($_POST['caminhoXls']);
			$parametros['msgSucesso'] = 'Dados do xls gravados com sucesso.';
		}
		catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}

		$parametros['aba'] = 2;
		$parametros['a'] = 'segGrau';
		$parametros['d'] = 'segGrau';
		$parametros['f'] = 'form2Grau';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
}
?>
