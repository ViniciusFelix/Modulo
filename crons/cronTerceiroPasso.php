<?php
	include("excel_reader2.php");
	include("ConnectCron.php");
	
	mysqli_autocommit($strConexao, false);
	
	$sqlArquivoEmProcessamento= 
	 "select *
	  from COMPARTILHADO.ARQUIVO_MODULO_XML_CRON ac
	  where ac.TIP_SITUACAO_ARQUIVO = '1'
	  limit 1";
	$aqruivoProc = mysqli_query($strConexao, $sqlArquivoEmProcessamento);
	if(mysqli_num_rows($aqruivoProc) == 0){
		$sqlArquivo = "select * 
					  from COMPARTILHADO.ARQUIVO_MODULO_XML_CRON ac
					  where ac.TIP_ETAPA_SISTEMA = '3'
					  and   ac.TIP_SITUACAO_ARQUIVO = '0'
					  limit 1";
		$resultArq = mysqli_query($strConexao, $sqlArquivo);
		$arquivoUpload = mysqli_fetch_assoc($resultArq);
	}else{
		$arquivoUpload = NULL;
	}

	if($arquivoUpload){
		try {
			$sqlAlterarArquivoUpload =
			"UPDATE COMPARTILHADO.ARQUIVO_MODULO_XML_CRON
				SET
				TIP_SITUACAO_ARQUIVO = '1'
			WHERE SEQ_ARQUIVO_MODULO_XML_CRON = ".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON'];
			if(!mysqli_query($strConexao, $sqlAlterarArquivoUpload))
				throw new Exception("Erro ao alterar status para processando do arquivo");
			
			mysqli_commit($strConexao);
			
			$sqlSeqOrgaoPai =
			"SELECT  o.SEQ_ORGAO_PAI
			 FROM corporativo.orgao o
			 where o.SEQ_ORGAO = ".$arquivoUpload['SEQ_ORGAO'];
			$resultSeqOrgaoPai = mysqli_query($strConexao, $sqlSeqOrgaoPai);
			if(mysqli_num_rows($resultSeqOrgaoPai) > 0){
				$resultSeqOrgaoPai = mysqli_fetch_row($resultSeqOrgaoPai);
				if($resultSeqOrgaoPai[0] == 1){
					$seqSeqOrgaoPai = $arquivoUpload['SEQ_ORGAO'];
				}else{
					$seqSeqOrgaoPai = $resultSeqOrgaoPai[0];
				}
			}
			
			$sqlPerguntaPrmGrau = "SELECT  perg.SEQ_PERGUNTA_SERVENTIA as 'seqPergunta',
								perg.DSC_SIGLA_PERGUNTA as 'sigla',
								perg.DSC_TIPO_PERGUNTA_SERVENTIA as 'descricao',
								perg.DSC_PERGUNTA as 'glossario',
								perg.NUM_ORDEM,
								'1' as 'grau'
						FROM SERVENTIAS_PRM_GRAU.PERGUNTA_SERVENTIA perg
						join SERVENTIAS_PRM_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA_SERVENTIA = dtp.SEQ_PERGUNTA_SERVENTIA and dtp.SEQ_ORGAO = ".$seqSeqOrgaoPai."
						WHERE FLG_DESTINATARIO_PERGUNTA='S'
						AND FLG_ATIVO = '1'
						ORDER BY DSC_SIGLA_PERGUNTA";
			$resultPrm = mysqli_query($strConexao, $sqlPerguntaPrmGrau);
			while($fetchPrm = mysqli_fetch_assoc($resultPrm)){
				$listaPerguntaPrmGrau[] = $fetchPrm;
			}
			
			$sqlPerguntaSegGrau = "SELECT  perg.SEQ_PERGUNTA as 'seqPergunta',
									perg.DSC_SIGLA_PERGUNTA as 'sigla',
									perg.DSC_TITULO as 'descricao',
									perg.DSC_DESCRICAO as 'glossario',
									perg.NUM_ORDEM,
									'2' as 'grau'
							FROM SERVENTIAS_SEG_GRAU.PERGUNTA perg
							join SERVENTIAS_SEG_GRAU.DESTINATARIO_TRIBUNAL_PERGUNTA dtp on perg.SEQ_PERGUNTA = dtp.SEQ_PERGUNTA and dtp.SEQ_ORGAO = ".$seqSeqOrgaoPai."
							WHERE perg.FLG_DESTINATARIO_PERGUNTA='S'
							AND perg.FLG_STATUS = '1'
							ORDER BY DSC_SIGLA_PERGUNTA";
			$resultSeg = mysqli_query($strConexao, $sqlPerguntaSegGrau);
			while($fetchPrm = mysqli_fetch_assoc($resultSeg)){
				$listaPerguntaSegGrau[] = $fetchPrm;
			}

			if(isset($listaPerguntaPrmGrau) && isset($listaPerguntaSegGrau))
				$perguntas = array_merge($listaPerguntaPrmGrau, $listaPerguntaSegGrau);
			else if(isset($listaPerguntaPrmGrau))
				$perguntas = $listaPerguntaPrmGrau;
			else if(isset($listaPerguntaSegGrau))
				$perguntas = $listaPerguntaSegGrau;
			
			$new_array = array();
			$sortable_array = array();

			if (count($perguntas) > 0) {
				foreach ($perguntas as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $k2 => $v2) {
							if ($k2 == 'NUM_ORDEM') {
								$sortable_array[$k] = $v2;
							}
						}
					} else {
						$sortable_array[$k] = $v;
					}
				}
		
				asort($sortable_array);
		
				foreach ($sortable_array as $k => $v) {
					$listaPerguntaOrdenada[$k] = $perguntas[$k];
				}
			}

			$data = new Spreadsheet_Excel_Reader("/var/www/html/modulo_xml/php/importarArquivos/arq1grau/".$arquivoUpload['NOM_ARQUIVO']);
			$linhas = $data->rowcount();
			$colunas= $data->colcount();
			
			$ref[1] = "codServentia";
			$ref[2] = "mes";
			$ref[3] = "ano";
			$ref[4] = "obs";
			$ref[5] = "codUsuario";
			$i=count($ref);
			foreach ($listaPerguntaOrdenada as $valuePerg) {
				$ref[$i] = $valuePerg['sigla'];
				$i++;
			}
			for($i = 2; $i <= $linhas; $i++){
				for($j = 1; $j <= $colunas; $j++){
					$meuArray[$i]['codUsuario'] = $arquivoUpload['SEQ_ORGAO'];
					if(!empty($ref[$j])){
						$valor = $data->val($i,$j);
						if($j >= 5 && empty($valor) && !is_numeric($valor))
							$meuArray[$i][$ref[$j]] = 'NULL';
						else
							$meuArray[$i][$ref[$j]] = trim($data->val($i,$j));
					}else{
						$meuArray[$i][$j] = trim($data->val($i,$j));
					}
				}
			}
			
			foreach ($meuArray as $key => $valueArray) {
				if($key > 2 && empty($valueArray['codServentia'])){
					break;
				}else{
					switch ($valueArray['mes']){
						case 1:
							$mesDescricao = "JANEIRO";
							break;
						case 2:
							$mesDescricao = "FEVEREIRO";
							break;
						case 3:
							$mesDescricao = "MARÇO";
							break;
						case 4:
							$mesDescricao = "ABRIL";
							break;
						case 5:
							$mesDescricao = "MAIO";
							break;
						case 6:
							$mesDescricao = "JUNHO";
							break;
						case 7:
							$mesDescricao = "JULHO";
							break;
						case 8:
							$mesDescricao = "AGOSTO";
							break;
						case 9:
							$mesDescricao = "SETEMBRO";
							break;
						case 10:
							$mesDescricao = "OUTUBRO";
							break;
						case 11:
							$mesDescricao = "NOVEMBRO";
							break;
						case 12:
							$mesDescricao = "DEZEMBRO";
							break;
					}
					
					foreach ($listaPerguntaOrdenada as $pergServ) {
						if($pergServ['grau'] == 1){
							$seqProdutividadePrmGrau = NULL;
							$sqlProdServentiaPrmGrau =
							"SELECT SEQ_PRODUTIVIDADE_SERVENTIA
							 FROM  SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA
	   						 WHERE SEQ_ORGAO = ".$valueArray['codServentia']."
							 AND MES_REFERENCIA = '".$mesDescricao." / ".$valueArray['ano']."'";
							$resultSeqProdPrm = mysqli_query($strConexao, $sqlProdServentiaPrmGrau);
							if(mysqli_num_rows($resultSeqProdPrm) > 0){
								$resultSeq = mysqli_fetch_row($resultSeqProdPrm);
								$seqProdutividadePrmGrau = $resultSeq[0];
							}
							
							if(empty($seqProdutividadePrmGrau)){
								$sqlInserirProdServentiaPrmGrau =
								"INSERT INTO SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA
								(
										SEQ_ORGAO,
										DSC_TEXTO_PRODUTIVIDADE,
										MES_REFERENCIA,
										COD_USUARIO_INCLUSAO,
										DAT_INCLUSAO,
										FLG_STATUS,
										DAT_IMPORTACAO_XML,
										SEQ_ARQUIVO_MODULO_XML_CRON
								)
								VALUES
								(
										".$valueArray['codServentia'].",
										'".$valueArray['obs']."',
										'".$mesDescricao." / ".$valueArray['ano']."',
										".$arquivoUpload['SEQ_USUARIO'].",
										NOW(),
										1,
										NOW(),
										".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON']."
								)";
								if(mysqli_query($strConexao, $sqlInserirProdServentiaPrmGrau)){
									$seqProdutividadePrmGrau = mysqli_insert_id($strConexao);
								}else{
									throw new Exception("Erro ao inserir a produtividade do primeiro grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
								}
							}else{
								$sqlAlterarProdServentiaPrmGrau =
								"UPDATE SERVENTIAS_PRM_GRAU.PRODUTIVIDADE_SERVENTIA
									SET
									SEQ_ORGAO					= ".$valueArray['codServentia'].",
									DSC_TEXTO_PRODUTIVIDADE		= '".$valueArray['obs']."',
									MES_REFERENCIA	 	   	 	= '".$mesDescricao." / ".$valueArray['ano']."',
									FLG_STATUS					= 1,
									DAT_IMPORTACAO_XML			= NOW(),
									COD_USUARIO_INCLUSAO		= ".$arquivoUpload['SEQ_USUARIO'].",
									SEQ_ARQUIVO_MODULO_XML_CRON = ".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON']."
								WHERE SEQ_PRODUTIVIDADE_SERVENTIA	= ".$seqProdutividadePrmGrau;
								if(!mysqli_query($strConexao, $sqlAlterarProdServentiaPrmGrau))
									throw new Exception("Erro ao alterar a produtividade do primeiro grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
							}
							
							$seqRespostaPrmGrau = NULL;
							$sqlRespostaPrmGrau = 
							"SELECT SEQ_RESPOSTA_SERVENTIA
							 FROM  SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
							 WHERE SEQ_PRODUTIVIDADE_SERVENTIA = ".$seqProdutividadePrmGrau."
							 AND SEQ_PERGUNTA_SERVENTIA = ".$pergServ['seqPergunta']."";
							$resultSeqRespPrm = mysqli_query($strConexao, $sqlRespostaPrmGrau);
							if(mysqli_num_rows($resultSeqRespPrm) > 0){
								$resultRespPrmGrau = mysqli_fetch_row($resultSeqRespPrm);
								$seqRespostaPrmGrau = $resultRespPrmGrau[0];
							}
							if(empty($seqRespostaPrmGrau)){
								$respServentiaPrmGrau = 
								"INSERT INTO SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
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
										".$seqProdutividadePrmGrau.",
										".$pergServ['seqPergunta'].",
										".$valueArray[$pergServ['sigla']].",
										NOW(),
										".$arquivoUpload['SEQ_USUARIO'].",
										1
								)";
								if(mysqli_query($strConexao, $respServentiaPrmGrau)){
									$seqRespostaPrmGrau = mysqli_insert_id($strConexao);
								}else{
									throw new Exception("Erro ao inserir resposta da produtividade do primeiro grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
								}
							}else{
								$respServentiaPrmGrau =	
								"UPDATE SERVENTIAS_PRM_GRAU.RESPOSTA_SERVENTIA
								 SET VLR_RESPOSTA		   	  = ".$valueArray[$pergServ['sigla']].",
									 DAT_ALTERACAO	 	   	  = NOW(),
									 COD_USU_ALTERACAO		  = ".$arquivoUpload['SEQ_USUARIO'].",
									 FLG_STATUS		   		  = 1,
									 COD_USU_INCLUSAO		  = ".$arquivoUpload['SEQ_USUARIO']."
								 WHERE SEQ_RESPOSTA_SERVENTIA = $seqRespostaPrmGrau";
								if(!mysqli_query($strConexao, $respServentiaPrmGrau))
									throw new Exception("Erro ao alterar resposta da produtividade do primeiro grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
							}
						}else if($pergServ['grau'] == 2){
							$seqProdutividadeSegGrau = NULL;
							$sqlSeqProdutividade = "SELECT SEQ_PRODUTIVIDADE
												FROM SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
												WHERE SEQ_ORGAO 	   = ".$valueArray['codServentia']."
												AND NUM_MES_REFERENCIA = ".$valueArray['mes']."
												AND NUM_ANO_REFERENCIA = ".$valueArray['ano'];
							$resultSeqProdutividade = mysqli_query($strConexao, $sqlSeqProdutividade);
							if(mysqli_num_rows($resultSeqProdutividade) > 0){
								$resultSeqSeg = mysqli_fetch_row($resultSeqProdutividade);
								$seqProdutividadeSegGrau = $resultSeqSeg[0];
							}
							if(empty($seqProdutividadeSegGrau)){
								$sqlInsertProdutividadeSegGrau =
								"INSERT INTO SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
									(
									   		SEQ_ORGAO,
											DSC_MES_REFERENCIA,
											NUM_MES_REFERENCIA,
											NUM_ANO_REFERENCIA,
											DSC_TEXTO_PRODUTIVIDADE,
											COD_USU_INCLUSAO,
											DAT_INCLUSAO,
											FLG_STATUS,
											DAT_IMPORTACAO_XML,
											SEQ_ARQUIVO_MODULO_XML_CRON 
									)
									VALUES
									(
											".$valueArray['codServentia'].",
											'".$mesDescricao." / ".$valueArray['ano']."',
											".$valueArray['mes'].",
											".$valueArray['ano'].",
											'".$valueArray['obs']."',
											".$arquivoUpload['SEQ_USUARIO'].",
											NOW(),
											1,
											NOW(),
											".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON']."
									)";
								if(mysqli_query($strConexao, $sqlInsertProdutividadeSegGrau)){
									$seqProdutividadeSegGrau = mysqli_insert_id($strConexao);
								}else{
									throw new Exception("Erroao inserir produtividade do segundo grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
								}
							}else{
								$sqlUpdateProdutividadeSegGrau =
								"UPDATE SERVENTIAS_SEG_GRAU.PRODUTIVIDADE
									SET
										DSC_TEXTO_PRODUTIVIDADE 	= '".$valueArray['obs']."',
										DAT_IMPORTACAO_XML			= NOW(),
										COD_USU_INCLUSAO			= ".$arquivoUpload['SEQ_USUARIO'].",
										SEQ_ARQUIVO_MODULO_XML_CRON	= ".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON']."
									WHERE
										SEQ_PRODUTIVIDADE = ".$seqProdutividadeSegGrau;
								if(!mysqli_query($strConexao, $sqlUpdateProdutividadeSegGrau))
									throw new Exception("Erro ao alterar produtividade do segundo grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
							}
							
							$seqRepostaSegGrau = NULL;
							$sqlRespostaSegGrau = 
							"SELECT SEQ_RESPOSTA
							 FROM  SERVENTIAS_SEG_GRAU.RESPOSTA
							 WHERE SEQ_PRODUTIVIDADE = ".$seqProdutividadeSegGrau."
							 AND SEQ_PERGUNTA = ".$pergServ['seqPergunta'];
							$resultSeqResposta = mysqli_query($strConexao, $sqlRespostaSegGrau);
							if(mysqli_num_rows($resultSeqResposta) > 0){
								$resultSeqRespostaSegGrau = mysqli_fetch_row($resultSeqResposta);
								$seqRepostaSegGrau = $resultSeqRespostaSegGrau[0];
							}
							if(empty($seqRepostaSegGrau)){
								$sqlRespServentiaSegGrau =
								"INSERT INTO SERVENTIAS_SEG_GRAU.RESPOSTA
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
											".$seqProdutividadeSegGrau.",
											".$pergServ['seqPergunta'].",
											".$valueArray[$pergServ['sigla']].",
											NOW(),
											".$arquivoUpload['SEQ_USUARIO'].",
											1
									)";
								if(!mysqli_query($strConexao, $sqlRespServentiaSegGrau))
									throw new Exception("Erro ao inserir resposta da produtividade do segundo grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
							}else{
								$seqRespServentiaSegGrau =
								"UPDATE SERVENTIAS_SEG_GRAU.RESPOSTA
									SET VLR_RESPOSTA	  = ".$valueArray[$pergServ['sigla']].",
										DAT_ALTERACAO	  = NOW(),
										COD_USU_ALTERACAO = ".$arquivoUpload['SEQ_USUARIO'].",
										FLG_STATUS		  = 1
									WHERE SEQ_RESPOSTA	  = ".$seqRepostaSegGrau."";
								if(!mysqli_query($strConexao, $seqRespServentiaSegGrau))
									throw new Exception("Erro ao alterar resposta da produtividade do segundo grau de referencia ".$mesDescricao." / ".$valueArray['ano']." da serventia ".$valueArray['codServentia']);
							}
						}
					}
					mysqli_commit($strConexao);
				}
			}
			
			$sqlAlterarArquivoUploadProcessado =
			"UPDATE COMPARTILHADO.ARQUIVO_MODULO_XML_CRON
				SET
				TIP_SITUACAO_ARQUIVO = '2',
				TXT_OCORRENCIA = NULL
			WHERE SEQ_ARQUIVO_MODULO_XML_CRON = ".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON'];
			if(!mysqli_query($strConexao, $sqlAlterarArquivoUploadProcessado)){
				echo "Erro";exit;
			}else{
				mysqli_commit($strConexao);
			}
			
			unlink("/var/www/html/modulo_xml/php/importarArquivos/arq1grau/".$arquivoUpload['NOM_ARQUIVO']);
		} catch (Exception $e) {
			mysqli_rollback($strConexao);
			$sqlAlterarOcorrencia =
			"UPDATE COMPARTILHADO.ARQUIVO_MODULO_XML_CRON
				SET
				TXT_OCORRENCIA = '".$e->getMessage()."',
				TIP_SITUACAO_ARQUIVO = '0'
			WHERE SEQ_ARQUIVO_MODULO_XML_CRON = ".$arquivoUpload['SEQ_ARQUIVO_MODULO_XML_CRON'];
			if(!mysqli_query($strConexao, $sqlAlterarOcorrencia)){
				echo "Erro";exit;
			}else{
				mysqli_commit($strConexao);
			}
			
			echo "<pre>";print_r($e);exit;
		}
	}else{
		echo "<pre>";print_r("Sem arquivo!");exit;
	}
	echo "<pre>";print_r("Sucesso!");exit;
?>