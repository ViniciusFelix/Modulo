<?php
class terceiroPasso{

	function terceiroPasso($db, $smarty)
	{
		$this->db = $db;
		$this->smarty = $smarty;
		
		include_once("php/importarArquivos/importarArquivos.php");
		$this->importarArquivos = new importarArquivos($db, $smarty);
		
		include_once("sql/importarArquivos/bdImportarArquivos.php");
		$this->sql = new bdImportarArquivos($db);
		
		include_once("sql/segGrau/bdSegGrau.php");
		$this->sqlSeg = new bdSegGrau($db);

		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}
	
	/**
	 * Método monta o xml exemplo do terceiro passo.
	 */
	function gerarExemploXmlTerceiroPasso()
	{
		$dom = new DOMDocument("1.0", "ISO-8859-1");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
	
		$root = $dom->createElement("PRODUTIVIDADE");
		$row = $dom->createElement("ROW");
	
		$cabecalho = $this->util->campoXml(3);
		$excplicacao = $this->util->explicacaoCampo(3,NULL,NULL,'xml');
		foreach ($cabecalho as $key => $valueCab) {
			$row->appendChild($dom->createElement($valueCab, utf8_encode($excplicacao[$key])));
		}
		$root->appendChild($row);
		$dom->appendChild($root);
	
		header("Content-Type: text/xml");
		print $dom->saveXML();exit;
	}
	
	/**
	 * Método para montar o exemplo do xls dinamicamente.
	 */
	function gerarExemploXlsTerceiroPasso($db,$smarty,$resposta=NULL)
	{
		$objPHPExcel = new PHPExcel();
		$listaPerguntaServentiaPrmGrau = $this->sql->listaPerguntaServentiaPrmGrau();
		$listaPerguntaServentiaSegGrau = $this->sqlSeg->listaPerguntaServentiaSegGrau();
		$pergunta = array_merge($listaPerguntaServentiaPrmGrau, $listaPerguntaServentiaSegGrau);
		$perguntaServentia = $this->util->array_sort($pergunta,'NUM_ORDEM');
		$lopCab = 1;
		$letraCab = 65;
		$cabecalho = $this->util->campoXls(3,NULL,NULL,$perguntaServentia);
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
		$excplicacao = $this->util->explicacaoCampo(3,null,$perguntaServentia,"xls");
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
				)
				);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="exemploTerceiroPasso.xls"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	/**
	 * Método para montar o exemplo do xls dinamicamente.
	 */
	function gerarRelatorioXlsTerceiroPasso($resposta)
	{
		permissao($_POST);
		foreach ($resposta as $value) {
			$listaServentiasResp[$value['SEQ_ORGAO']]['SEQ_ORGAO'] = $value['SEQ_ORGAO'];
			$listaServentiasResp[$value['SEQ_ORGAO']]['observacao'] = $value['observacao'];
			$listaServentiasResp[$value['SEQ_ORGAO']][$value['sigla']] = $value['valor'];
		}
		
		$arrayDividido = array_chunk($listaServentiasResp,500);
		$arq = 0;
		foreach ($arrayDividido as $keyDiv => $valueDiv) {
			$objPHPExcel = new PHPExcel();
			$listaPerguntaServentiaPrmGrau = $this->sql->listaPerguntaServentiaPrmGrau();
			$listaPerguntaServentiaSegGrau = $this->sqlSeg->listaPerguntaServentiaSegGrau();
			$perguntas = array_merge($listaPerguntaServentiaPrmGrau, $listaPerguntaServentiaSegGrau);
			$perguntaServentia = $this->util->array_sort($perguntas,'NUM_ORDEM');
			$lopCab = 1;
			$letraCab = 65;
			$cabecalho = $this->util->campoXls(3,NULL,NULL,$perguntaServentia);
			foreach ($cabecalho as $key => $valueCab) {
				if($lopCab == 1){
					$ref = chr($letraCab);
				}else{
					$proxRef=63+$lopCab;
					$ref = chr($proxRef).chr($letraCab);
				}
					
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($ref.'1', utf8_encode($valueCab));
				$objPHPExcel->getActiveSheet()->getColumnDimension($ref)->setWidth(30);
				$letraCab++;
				if($letraCab == 91){
					$letraCab = 65;
					$lopCab++;
				}
			}
			
			$letra=69;
			$lop=1;
			foreach ($perguntaServentia as $key => $value) {
				if($lop == 1){
					$ref = chr($letra).'1';
				}else{
					$proxRef=63+$lop;
			
					$ref = chr($proxRef).chr($letra).'1';
				}
				$coluna[$key]['seqPergunta'] = $value['seqPergunta'];
				$coluna[$key]['ref'] = $ref;
				$coluna[$key]['nomeColuna'] = utf8_encode($value['sigla']);
				$coluna[$key]['descricao'] = utf8_encode($value['descricao']);
				$letra++;
				if($letra == 91){
					$letra = 65;
					$lop++;
				}
			}
			
			$c = 4;
			foreach ($coluna as $value) {
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($value['ref'], $value['nomeColuna'] );
			
				$i=2;
				foreach ($valueDiv as $keyResp => $valueResp) {
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $valueResp['SEQ_ORGAO']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $this->util->qualMes(strtoupper($_POST['mes'])));
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, $_POST['ano']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $i, utf8_encode($valueResp['observacao']));
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c, $i, $valueResp[utf8_decode($value['nomeColuna'])]);
					$i++;
				}
				$c++;
			}
			
			$ultimaRef = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
			$objPHPExcel->getActiveSheet()->getStyle('A2:'.substr($value['ref'], -3, -1).'2')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A2:'.substr($value['ref'], -3, -1).'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$styleArray = array(
					'font'  => array(
							'bold'  => true,
							'color' => array('rgb' => 'FFFFFF'),
							'name'  => 'Arial',
							'text-align' => 'center'
					));
			$objPHPExcel->getActiveSheet()->getStyle('A1:'.substr($value['ref'], -3, -1).'1')->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A1:'.substr($value['ref'], -3, -1).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaRef.'1')->applyFromArray(
					array('fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => '192591')
					),
					)
					);
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$arquivosZip[$arq] = 'relatorioTerceiroPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
			$nomeArquivo = 'relatorioTerceiroPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
			$salvar = $this->util->caminho_absoluto1Grau.$nomeArquivo;
			$objWriter->save($salvar);
			$arq++;
		}
		
		$zip = new ZipArchive();
		$nomeArquivoZip = "arquivosRelatorioTerceiroPasso".$_SESSION['sig_tribunal'].".zip";
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
	 * Método monta a planilha de todas as perguntas das serventias com seus respectivos indices.
	 */
	function planilhaPerguntasServentia()
	{
		$listaPerguntaServentiaPrmGrau = $this->sql->listaPerguntaServentiaPrmGrau();
		
		$listaPerguntaServentiaSegGrau = $this->sqlSeg->listaPerguntaServentiaSegGrau();

		$pergunta = array_merge($listaPerguntaServentiaPrmGrau, $listaPerguntaServentiaSegGrau);
		$perguntaServentia = $this->util->array_sort($pergunta,'NUM_ORDEM');
		if(isset($_POST['salvar'])){
			foreach ($perguntaServentia as $value) {
				$perguntaXls[$value['seqPergunta']]['SEQ_PERGUNTA'] = $value['seqPergunta'];
				$perguntaXls[$value['seqPergunta']]['SIGLA'] 		= $value['sigla'];
				$perguntaXls[$value['seqPergunta']]['DESCRICAO'] 	= $value['descricao'];
				$perguntaXls[$value['seqPergunta']]['GLOSSARIO'] 	= $value['glossario'];
			}
			$this->importarArquivos->gerarXLS($perguntaXls, "SEQ_PERGUNTA,SIGLA,DESCRICAO,GLOSSARIO");
		}else{
			$this->smarty->assign('listaPergServ',$perguntaServentia);
	
			echo $this->smarty->fetch("importarArquivos/planilhaPerguntaServentia.html");
			die;
		}
	}
	
	/**
	 * Método salva os dados do xml do segundo passo.
	 */
	function salvarTerXml()
	{
		permissao($_POST);
		$xml = simplexml_load_file($_POST['caminhoXml']);
		try {
			$this->db->BeginTrans();
			foreach ($xml as $value) {
				if(trim($value->RESPOSTA) == NULL)
					$eNumero = 1;
				else
					$eNumero = $this->util->temPontoOuVirgula(trim($value->RESPOSTA));
				
				$resposta = trim($value->RESPOSTA);
				if($resposta < 0 || !$eNumero || !is_numeric($resposta))
					throw new Exception("Dados do xls de formato inválido na pergunta ".$value->PERGUNTA." do seq_serventia ".$value->SERVENTIA." mes ".$value->MES." e ano ".$value->ANO." deve ser inteiro positivo, zero ou nulo.");
					
				if(trim($value->ANO) < '2015')
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value->SERVENTIA." ano informado não pode ser menor que 2015.");
				
				if(trim($value->ANO) > date("Y"))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value->SERVENTIA." ano informado maior que o ano corrente.");
				
				$destinatario = $this->sql->destinatarioPergunta($value->PERGUNTA,'S');
				if(empty($destinatario['primeiro']) && empty($destinatario['segundo']))
					throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xml da serventia ".$value->SERVENTIA." a pergunta ".$value->PERGUNTA." não está vinculado a esse tribunal");
					
				$ultimoDia = $value->ANO."-".$value->MES."-".date("t", mktime(0,0,0,"$value->MES",'01',"$value->ANO"));
				$mes = $this->util->qualMesNumero($value->MES);
				if(empty($mes))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value->SERVENTIA." o campo mês deve estar entre 1 e 12.");
				
				$orgao = $this->sql->retornaServentia($value->SERVENTIA);
				if($orgao){
					if(!empty($destinatario['primeiro']))
						$temProdutividade = $this->sql->retornaProdServentiaPrmGrau($value->SERVENTIA,$mes,$value->ANO);
					else
						$temProdutividade = $this->sqlSeg->retornaProdutividadeServentiaSegGrau($value->SERVENTIA,$value->MES,$value->ANO);
					
					if(empty($temProdutividade)){
						if(!empty($destinatario['primeiro'])){
							$prodServentia = $this->sql->inserirProdServentiaPrmGrau($value->SERVENTIA,utf8_decode(utf8_decode($value->OBSERVACAO)),$value->ANO,$value->MES,$ultimoDia);
						}else{ 
							$prodServentia = $this->sqlSeg->inserirProdutividadeServentiaSegGrau($value->SERVENTIA,$mes,$value->ANO,$value->MES,utf8_decode(utf8_decode($value->OBSERVACAO)));
						}
						if(!$prodServentia)
							throw new Exception("Ocorreu um erro ao salvar os dados da produtividade da serventia da serventia ".$value->SERVENTIA);
					}else{
						if(!empty($destinatario['primeiro'])){
							$prodServentia = $this->sql->atualizarProdServentiaPrmGrau($value->SERVENTIA,utf8_decode(utf8_decode($value->OBSERVACAO)),$value->ANO,$value->MES,$mes,$ultimoDia,$temProdutividade);
						}else{
							$prodServentia = $this->sqlSeg->alterarProdutividadeServentiaSegGrau($temProdutividade,utf8_decode(utf8_decode($value->OBSERVACAO)));
						}
						if(!$prodServentia)
							throw new Exception("Ocorreu um erro ao atualizar os dados da produtividade da serventia da serventia ".$value->SERVENTIA);
					}
		
					if(!empty($destinatario['primeiro'])){
						$seqResposta = $this->sql->retornaSeqResposta($prodServentia,$value->PERGUNTA);
					}else{
						$seqResposta = $this->sqlSeg->retornaSeqResposta($prodServentia,$value->PERGUNTA);
					}
					
					if($seqResposta){
						if(!empty($destinatario['primeiro'])){
							$respServentia = $this->sql->atualizarRespServentiaPrmGrau($value->RESPOSTA,$seqResposta);
						}else{
							$respServentia = $this->sqlSeg->atualizarRespostaSegGrau($value->RESPOSTA,$seqResposta);
						}
							
					}else{
						if(!empty($destinatario['primeiro'])){
							$respServentia = $this->sql->inserirRespServentiaPrmGrau($prodServentia,$value->PERGUNTA,$value->RESPOSTA);
						}else{
							$respServentia = $this->sqlSeg->inserirRespostaSegGrau($prodServentia,$value->PERGUNTA,$value->RESPOSTA);
						}
					}
						
					if(!$respServentia)
						throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xml da serventia ".$value->SERVENTIA." da pergunta ".$value->PERGUNTA);
				}else{
					throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xml da serventia ".$value->SERVENTIA." favor verificar se o código da serventia está correto.");
				}
			}
				
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'Dados do xml do Terceiro Passo gravados com sucesso.';
		}
		catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}

		unlink($_POST['caminhoXml']);
		$parametros['aba'] = 3;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método para salvar os dados do xls do segundo passo.
	 */
	function salvarTerXls()
	{
		permissao($_POST);
		try {
			$this->db->BeginTrans();
			
			$referenciaArquivo = $this->sql->inserirFilaGravacaoCron($_POST['nomeArquivo'],3);
			if(empty($referenciaArquivo))
				throw new Exception("Ocorreu um erro ao salvar as referencias do arquivo do terceiro passo");
			
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'A referência do arquivo foi salvo com sucesso. Em instantes os dados da produtividade estarão salvos na base de dados. Para visualizar o andamento da gravação na base de dados, favor clicar no botão “Arquivos para gravação terceiro passo“';
		}catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
	
		$parametros['aba'] = 3;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
}
?>