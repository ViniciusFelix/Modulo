<?php
class quartoPasso{
	
	function quartoPasso($db, $smarty)
	{
		$this->db = $db;
		$this->smarty = $smarty;
		
		include_once("php/importarArquivos/importarArquivos.php");
		$this->importarArquivos = new importarArquivos($db, $smarty);

		include_once("sql/importarArquivos/bdImportarArquivos.php");
		$this->sql = new bdImportarArquivos($db);
		
		include_once("sql/segGrau/bdSegGrau.php");
		$this->sqlSeg = new bdSegGrau($db);
		
		include_once("sql/consulta/bdconsulta.php");
		$this->sqlCons = new bdconsulta($db);
		
		include_once("sql/magistrado/bdmagistrado.php");
		$this->sqlMag = new bdmagistrado($db);
	
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}
	
	/**
	 * Método para montar a planilha das perguntas para magistrado
	 */
 	function planilhaPerguntasMagistrado() 
 	{
        $listaPerguntaMagistradoPrmGrau = $this->sql->listaPerguntaMagistradoPrmGrau();
        
        $listaPerguntaMagistradoSegGrau = $this->sqlSeg->listaPerguntaMagistradoSegGrau();
        
        $perguntaMagistrado = array_merge($listaPerguntaMagistradoPrmGrau, $listaPerguntaMagistradoSegGrau);
        $perguntaMagistrado = $this->util->array_sort($perguntaMagistrado,'NUM_ORDEM');
		if(isset($_POST['salvar'])){
			foreach ($perguntaMagistrado as $value) {
				$lista[$value['seqPergunta']]['seqPergunta'] = $value['seqPergunta'];
				$lista[$value['seqPergunta']]['sigla'] 		 = $value['sigla'];
				$lista[$value['seqPergunta']]['descricao']   = $value['descricao'];
				$lista[$value['seqPergunta']]['glossario']   = $value['glossario'];
			}
        	$this->importarArquivos->gerarXLS($lista, "SEQ_PERGUNTA,SIGLA,DESCRICAO PERGUNTA,GLOSSARIO");
        }else{
	        $this->smarty->assign('listaPergMagi',$perguntaMagistrado);
	        
	        echo $this->smarty->fetch("importarArquivos/planilhaPerguntaMagistrado.html");
	        die;
        }
    }
    
    /**
     * Mètodo para montar a planilha dos tipos dos magistrados.
     */
    function planilhaTipoMagistrado()
    {
    	$tipoMagistrado = $this->sql->retornaTipoMagistrado();
    	if(isset($_POST['salvar'])){
    		$this->importarArquivos->gerarXLS($tipoMagistrado, "IDENTIFICACAO,DESCRICAO,DESCRICAO COMPLETA");
    	}else{
    		$this->smarty->assign('listaTipoMagi',$tipoMagistrado);
    
    		echo $this->smarty->fetch("importarArquivos/planilhaTipoMagistrado.html");
    		die;
    	}
    }
    
    /**
     * Método monta o xml exemplo do quarto passo.
     */
    function gerarExemploXmlQuartoPasso()
    {
    	$dom = new DOMDocument("1.0", "ISO-8859-1");
    	$dom->preserveWhiteSpace = false;
    	$dom->formatOutput = true;
    
    	$root = $dom->createElement("PRODUTIVIDADEMAGISTRADO");
    	$row = $dom->createElement("ROW");
    
    	$cabecalho = $this->util->campoXml(4);
    	$excplicacao = $this->util->explicacaoCampo(4,NULL,NULL,'xml');
    	foreach ($cabecalho as $key => $valueCab) {
    		$row->appendChild($dom->createElement($valueCab, utf8_encode($excplicacao[$key])));
    	}
    	$root->appendChild($row);
    	$dom->appendChild($root);
    
    	header("Content-Type: text/xml");
    	print $dom->saveXML();exit;
    }
    
    /**
     * Método monta o xls exepmlo do quarto passo.
     */
    function gerarExemploXlsQuartoPasso()
    {
    	$objPHPExcel = new PHPExcel();
    	$listaPerguntaMagistradoPrmGrau = $this->sql->listaPerguntaMagistradoPrmGrau();
    	$listaPerguntaMagistradoSegGrau = $this->sqlSeg->listaPerguntaMagistradoSegGrau();
    	$perguntas = array_merge($listaPerguntaMagistradoPrmGrau, $listaPerguntaMagistradoSegGrau);
    	$perguntaMagistrado = $this->util->array_sort($perguntas,'NUM_ORDEM');
    	$lopCab = 1;
    	$letraCab = 65;
    	$cabecalho = $this->util->campoXls(4,NULL,NULL,$perguntaMagistrado);
    	foreach ($cabecalho as $key => $valueCab) {
    		if($lopCab == 1){
    			$ref = chr($letraCab);
    		}else{
    			$proxRef=63+$lopCab;
    			$ref = chr($proxRef).chr($letraCab);
    		}
    
    		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($ref.'1', utf8_encode($valueCab));
    		$objPHPExcel->getActiveSheet()->getColumnDimension($ref)->setWidth(50);
    		$letraCab++;
    		if($letraCab == 91){
    			$letraCab = 65;
    			$lopCab++;
    		}
    	}
    
    	$lopExp = 1;
    	$letraExp = 65;
    	$excplicacao = $this->util->explicacaoCampo(4,null,$perguntaMagistrado,"xls");
    	foreach ($excplicacao as $key => $valueExp) {
    		if($lopExp == 1){
    			$ref = chr($letraExp);
    		}else{
    			$proxRef=63+$lopExp;
    			$ref = chr($proxRef).chr($letraExp);
    		}
    
    		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($ref.'2', utf8_encode($valueExp));
    		$letraExp++;
    		if($letraExp == 91){
    			$letraExp = 65;
    			$lopExp++;
    		}
    	}
    
    	$ultimaColuna = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    	$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(70);
    	$objPHPExcel->getActiveSheet()->getStyle('A2:'.$ultimaColuna.'2')->getAlignment()->setWrapText(true);
    	$objPHPExcel->getActiveSheet()->getStyle('A2:'.$ultimaColuna.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    
    	$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaColuna.'1')
			    	->getNumberFormat()
			    	->setFormatCode('General');
    	$styleArray = array(
    			'font'  => array(
    					'bold'  => true,
    					'color' => array('rgb' => 'FFFFFF'),
    					'name'  => 'Arial',
    					'text-align' => 'center'
    			));
    	$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaColuna.'1')->applyFromArray($styleArray);
    	$objPHPExcel->setActiveSheetIndex(0);
    	$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaColuna.'1')->applyFromArray(
    		array('fill' => array(
    				'type' => PHPExcel_Style_Fill::FILL_SOLID,
    				'color' => array('rgb' => '192591')
    			),
    		));
    	header('Content-Type: application/vnd.ms-excel');
    	header('Content-Disposition: attachment;filename="exemploQuartoPasso.xls"');
    	header('Cache-Control: max-age=0');
    	header('Cache-Control: max-age=1');
    	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    	$objWriter->save('php://output');
    	exit;
    }
    
    /**
     * Metodo gera relatório das produtividades em xls.
     * @param unknown $resposta
     */
    function gerarRelatorioXlsQuartoPasso($resposta)
    {
    	foreach ($resposta as $value) {
    		$listaMagistradoResp[$value['SEQ_MAGISTRADO'].$value['SEQ_ORGAO'].$value['tipo']]['SEQ_MAGISTRADO'] = $value['SEQ_MAGISTRADO'];
    		$listaMagistradoResp[$value['SEQ_MAGISTRADO'].$value['SEQ_ORGAO'].$value['tipo']]['SEQ_ORGAO'] 	 = $value['SEQ_ORGAO'];
    		$listaMagistradoResp[$value['SEQ_MAGISTRADO'].$value['SEQ_ORGAO'].$value['tipo']]['tipo'] 			 = $value['tipo'];
    		$listaMagistradoResp[$value['SEQ_MAGISTRADO'].$value['SEQ_ORGAO'].$value['tipo']]['qtdDias'] 	 	 = $value['qtdDias'];
    		$listaMagistradoResp[$value['SEQ_MAGISTRADO'].$value['SEQ_ORGAO'].$value['tipo']]['observacao'] 	 = $value['observacao'];
    		$listaMagistradoResp[$value['SEQ_MAGISTRADO'].$value['SEQ_ORGAO'].$value['tipo']][$value['sigla']]  = $value['resposta'];
    	}
    	
    	$arrayDividido = array_chunk($listaMagistradoResp,500);
    	$arq = 0;
    	foreach ($arrayDividido as $keyDiv => $valueDiv) {
    		$objPHPExcel = new PHPExcel();
    		$listaPerguntaMagistradoPrmGrau = $this->sql->listaPerguntaMagistradoPrmGrau();
    		$listaPerguntaMagistradoSegGrau = $this->sqlSeg->listaPerguntaMagistradoSegGrau();
    		$perguntas = array_merge($listaPerguntaMagistradoPrmGrau, $listaPerguntaMagistradoSegGrau);
    		$perguntaMagistrado = $this->util->array_sort($perguntas,'NUM_ORDEM');
    		$lopCab = 1;
    		$letraCab = 65;
    		$cabecalho = $this->util->campoXls(4,NULL,NULL,$perguntaMagistrado);
    		foreach ($cabecalho as $key => $valueCab) {
    			if($lopCab == 1){
    				$ref = chr($letraCab);
    			}else{
    				$proxRef=63+$lopCab;
    				$ref = chr($proxRef).chr($letraCab);
    			}
    		
    			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($ref.'1', utf8_encode($valueCab));
    			$objPHPExcel->getActiveSheet()->getColumnDimension($ref)->setWidth(50);
    			$letraCab++;
    			if($letraCab == 91){
    				$letraCab = 65;
    				$lopCab++;
    			}
    		}
    		 
    		$letra=72;
    		$lop=1;
    		foreach ($perguntaMagistrado as $key => $value) {
    			if($lop == 1){
    				$ref = chr($letra).'1';
    			}else{
    				$proxRef=63+$lop;
    		
    				$ref = chr($proxRef).chr($letra).'1';
    			}
    		
    			$coluna[$key]['ref'] = $ref;
    			$coluna[$key]['nomeColuna'] = utf8_encode($value['sigla']);
    			$coluna[$key]['descricao'] = utf8_encode($value['descricao']);
    			$letra++;
    			if($letra == 91){
    				$letra = 65;
    				$lop++;
    			}
    		}
    		
    		$c = 7;
    		foreach ($coluna as $value) {
    			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($value['ref'], $value['nomeColuna'] );
    			$i=2;
    			foreach ($valueDiv as $keyResp => $valueResp) {
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $valueResp['SEQ_MAGISTRADO']);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $valueResp['SEQ_ORGAO']);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, $valueResp['tipo']);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $i, $this->util->qualMes(strtoupper($_POST['mes'])));
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $i, $_POST['ano']);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $i, $valueResp['qtdDias']);
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $i, utf8_encode($valueResp['observacao']));
    				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c, $i, $valueResp[utf8_decode($value['nomeColuna'])]);
    				$i++;
    			}
    			$objPHPExcel->getActiveSheet()->getStyle('E1:E'.$i)
    			->getNumberFormat()
    			->setFormatCode('General');
    			$objPHPExcel->getActiveSheet()->getStyle('F1:F'.$i)
    			->getNumberFormat()
    			->setFormatCode('General');
    			$c++;
    		}
    		 
    		$ultimaRef = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
    		$objPHPExcel->getActiveSheet()->getStyle('A2:'.$ultimaRef.'2')->getAlignment()->setWrapText(true);
    		
    		$styleArray = array(
    				'font'  => array(
    						'bold'  => true,
    						'color' => array('rgb' => 'FFFFFF'),
    						'name'  => 'Arial',
    						'text-align' => 'center'
    				));
    		$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaRef.'1')->applyFromArray($styleArray);
    		$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaRef.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    		
    		$objPHPExcel->setActiveSheetIndex(0);
    		$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaRef.'1')->applyFromArray(
    				array('fill' => array(
    						'type' => PHPExcel_Style_Fill::FILL_SOLID,
    						'color' => array('rgb' => '192591')
    				),
    				));
    		
    		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$arquivosZip[$arq] = 'relatorioQuartoPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
			$nomeArquivo = 'relatorioQuartoPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
			$salvar = $this->util->caminho_absoluto1Grau.$nomeArquivo;
			$objWriter->save($salvar);
			$arq++;
    	}
    	
    	$zip = new ZipArchive();
    	$nomeArquivoZip = "arquivosRelatorioQuartoPasso".$_SESSION['sig_tribunal'].".zip";
    	if( $zip->open( 'php/zips/'.$nomeArquivoZip , ZipArchive::CREATE  )  === true){
    		foreach ($arquivosZip as $arqZip) {
    			$zip->addFile(  $this->util->caminho_absoluto1Grau.$arqZip , $arqZip );
    		}
    		$zip->close();
    	
    		header('Content-type: application/zip');
    		header('Content-disposition: attachment; filename='.$nomeArquivoZip);
    		readfile('php/zips/'.$nomeArquivoZip);
    	
    		foreach ($arquivosZip as $arqZip) {
    			unlink($this->util->caminho_absoluto1Grau.$arqZip);
    		}
    	
    		unlink('php/zips/'.$nomeArquivoZip);
    	}
		exit;    	
    }
    
	/**
	 * Método salva os dados do xml do quinto passo.
	 */
	function salvarXmlQuarPasso()
	{
		permissao($_POST);
		$xml = simplexml_load_file($_POST['caminhoXml']);
		try {
			$this->db->BeginTrans();
			foreach ($xml as $value) {
				$eNumero = null;
				/*Verifica se a resposta é inteiro ou null*/
				if(trim($value->RESPOSTA) == NULL)
					$eNumero = 1;
				else
					$eNumero = $this->util->temPontoOuVirgula(trim($value->RESPOSTA));
				if(trim($value->RESPOSTA) < 0 || empty($eNumero) || !is_numeric(trim($value->RESPOSTA)))
					throw new Exception("Na pergunta ".$value->PERGUNTA." do magistrado ".$value->MAGISTRADO." na serventia de código ".$value->SERVENTIA." deve ser inteiro positivo, zero ou nulo.");
					
				/*Verifica se o mes é valido*/
				$mesValido = $this->util->qualMesNumero(trim($value->MES));
				if(!$mesValido)
					throw new Exception("Ocorreu um erro ao salvar os dados do xls no magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA." mês informado é invalido.");
				
				/*Verifica se o orgao está correto*/
				$orgao = $this->sql->retornaServentia($value->SERVENTIA);
				if(empty($orgao))
					throw new Exception("Ocorreu um erro ao salvar os dados da serventia ".$value->SERVENTIA." não encontrada, favor verificar o código.");
			
					/*Verifica se o magistrado está correto*/
				if(trim($value->MAGISTRADO)){
					$magistrado = $this->sqlMag->buscarMagistradoPeloSeq($value->MAGISTRADO);
					if(empty($magistrado))
						throw new Exception("Ocorreu um erro ao salvar os dados do magistrado ".$value->MAGISTRADO." não encontrada, favor verificar o código.");
				}
				
				/*Verifica se a pergunta é para o tribunal de acesso*/
				$destinatario = $this->sql->destinatarioPergunta($value->PERGUNTA,'M');
				if(empty($destinatario['primeiro']) && empty($destinatario['segundo']))
					throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xml da no magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA." a pergunta ".$value->PERGUNTA." não está vinculado a esse tribunal");
				
				
				/*Verifica se o tipo do magistrado informado é valido*/
				if(trim($value->TIPO_MAGISTRADO)){
					$tipoValido = $this->sql->retornaTipoMagistrado($value->TIPO_MAGISTRADO);
					if(empty($tipoValido))
						throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xml da no magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA." a pergunta ".$value->PERGUNTA." tipo magistrado não é válida");
				}
				
				/*Verifica se o quantidade dias corridos é número*/
				if(trim($value->QUANTIDADE_DIAS_CORRIDOS) != "" && !is_numeric(trim($value->QUANTIDADE_DIAS_CORRIDOS)))
					throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xml da no magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA." quantidade de dias corridos deve ser numérico inteiro.");
				
				if(!empty($destinatario['primeiro'])){
					$seqProdutividade = $this->sql->retornaProdutividadeMagistradoPrmGrau($value->MAGISTRADO,$value->SERVENTIA,$value->MES,$value->ANO,$value->TIPO_MAGISTRADO);
					if(empty($seqProdutividade)){
						$seqProdutividade = $this->sql->inserirMagistradoProdutividadePrmGrau($value->SERVENTIA,$value->MAGISTRADO,$value->TIPO_MAGISTRADO,utf8_decode(utf8_decode($value->OBSERVACAO)),$value->MES,$value->ANO,$value->DATAINICIO,$value->DATAFIM,$value->QUANTIDADE_DIAS_CORRIDOS);
					}else{
						$seqProdutividade = $this->sql->alterarMagistradoProdutividadePrmGrau($seqProdutividade,$value->DATAINICIO,$value->DATAFIM,$value->TIPO_MAGISTRADO,utf8_decode(utf8_decode($value->OBSERVACAO)),trim($value->QUANTIDADE_DIAS_CORRIDOS));
					}
				}else if(!empty($destinatario['segundo'])){
					$seqProdutividade = $this->sqlSeg->retornaProdutividadeMagistradoSegGrau($value->MAGISTRADO,$value->SERVENTIA,$value->MES,$value->ANO,$value->DATAINICIO,$value->DATAFIM,$value->TIPO_MAGISTRADO);
					if(empty($seqProdutividade)){
						$seqProdutividade = $this->sqlSeg->inserirProdutividadeMagistradoSegGrau($value->SERVENTIA,$value->MAGISTRADO,$value->MES,$value->ANO,utf8_decode(utf8_decode($value->OBSERVACAO)),$value->DATAINICIO,$value->DATAFIM,$value->TIPO_MAGISTRADO,trim($value->QUANTIDADE_DIAS_CORRIDOS));
					}else{ 
						$seqProdutividade = $this->sqlSeg->alterarProdutividadeServentiaSegGrau($seqProdutividade,$value->DATAINICIO,$value->DATAFIM,utf8_decode(utf8_decode($value->OBSERVACAO)),trim($value->QUANTIDADE_DIAS_CORRIDOS));
					}
				}
				if(!$seqProdutividade)
					throw new Exception("Ocorreu um erro ao salvar os dados do xml no magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA." da pergunta ".$value->PERGUNTA);
				
				if(!empty($destinatario['primeiro'])){
					$seqResposta = $this->sql->retornaSeqRespMagiPrmGrau($seqProdutividade,$value->PERGUNTA);
					if(!empty($seqResposta))
						$respMagistradoProdutividade = $this->sql->atualizarRespServentiaPrmGrau($value->RESPOSTA,$seqResposta);
					else
						$respMagistradoProdutividade = $this->sql->inserirRespMagistradoPrmGrau($value->PERGUNTA,$seqProdutividade,$value->RESPOSTA);
					
				}else if(!empty($destinatario['segundo'])){
					$seqResposta = $this->sqlSeg->retornaSeqResposta($seqProdutividade,$value->PERGUNTA);
					if(!empty($seqResposta))
						$respMagistradoProdutividade = $this->sqlSeg->atualizarRespostaSegGrau($value->RESPOSTA,$seqResposta);
					else 
						$respMagistradoProdutividade = $this->sqlSeg->inserirRespostaSegGrau($seqProdutividade,$value->PERGUNTA,$value->RESPOSTA);
				}
				if(!$respMagistradoProdutividade)
					throw new Exception("Ocorreu um erro ao salvar os dados do xml no magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA." da pergunta ".$value->PERGUNTA);
				
				if(trim($value->MAGISTRADO)){
					$tipoAtualMagistrado = $this->sql->retornaTipoAtualMagistrado($value->SERVENTIA,$value->MAGISTRADO,$value->TIPO_MAGISTRADO);
					if($tipoAtualMagistrado['FLG_STATUS'] == 1 && $tipoAtualMagistrado['IND_TIPO_JUIZ'] == $value->TIPO_MAGISTRADO){
						$magistradoServentia = $this->sql->alterarMagistradoServentia($value->SERVENTIA,$value->MAGISTRADO,$value->TIPO_MAGISTRADO);
						if(!$magistradoServentia)
							throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA);
					}else{
						if($tipoAtualMagistrado['FLG_STATUS'] == 1){
							$tipoAnterior = $this->sql->inativarTipoAnterior($value->SERVENTIA,$value->MAGISTRADO);
							if(!$tipoAnterior)
								throw new Exception("Ocorreu um erro ao inativar o tipo anterior do magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA);
						}
						
						if(empty($tipoAtualMagistrado) || $tipoAtualMagistrado['FLG_STATUS'] == 0){
							$magistradoServentia = $this->sql->inserirMagistradoServentia($value->SERVENTIA,$value->MAGISTRADO,$value->TIPO_MAGISTRADO);
							if(!$magistradoServentia)
								throw new Exception("Ocorreu um erro ao vincular o magistrado ".$value->MAGISTRADO." na serventia ".$value->SERVENTIA);
						}
					}
				}
			}

			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'Dados do xml do Quarto Passo gravados com sucesso.';
		}
		catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		unlink($_POST['caminhoXml']);
		$parametros['aba'] = 4;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	function salvarQuartoXls()
	{
		permissao($_POST);
		try {
			$this->db->BeginTrans();

			$referenciaArquivo = $this->sql->inserirFilaGravacaoCron($_POST['nomeArquivo'],4);
			if(empty($referenciaArquivo))
				throw new Exception("Ocorreu um erro ao salvar as referencias do arquivo do quarto passo");
					
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'A referência do arquivo foi salvo com sucesso. Em instantes os dados da produtividade estarão salvos na base de dados. Para visualizar o andamento da gravação na base de dados, favor clicar no botão “Arquivos para gravação quarto passo“."';
		}catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		$parametros['aba'] = 4;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
}
?>