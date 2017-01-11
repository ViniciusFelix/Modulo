<?php
class primeiroPasso{

	function primeiroPasso($db, $smarty)
	{
		$this->db = $db;
		$this->smarty = $smarty;
		
		include_once("php/importarArquivos/importarArquivos.php");
		$this->importarArquivos = new importarArquivos($db, $smarty);

		include_once("sql/importarArquivos/bdImportarArquivos.php");
		$this->sql = new bdImportarArquivos($db);
		
		include_once("sql/consulta/bdconsulta.php");
		$this->sqlCons = new bdconsulta($db);
		
		include_once("sql/magistrado/bdmagistrado.php");
		$this->sqlMag = new bdmagistrado($db);
		
		include_once("sql/segGrau/bdSegGrau.php");
		$this->sqlSeg = new bdSegGrau($db);

		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}

	function gerarExemploXmlPrimeiroPasso()
	{
		$dom = new DOMDocument("1.0", "ISO-8859-1");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		$root = $dom->createElement("SERVENTIA");
		$row = $dom->createElement("ROW");
		
		$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
		$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
		$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
		$perguntaTrabalho = $this->util->array_sort($perguntas,'NUM_ORDEM');
		$cabecalho = $this->util->campoXml(1,$_SESSION['tipoArquivo'],$perguntaTrabalho);
		$excplicacao = $this->util->explicacaoCampo(1,$_SESSION['tipoArquivo'],$perguntaTrabalho,"xml");
		foreach ($cabecalho as $key => $valueCab) {
			if(count($valueCab) == 1){
				$row->appendChild($dom->createElement($valueCab, utf8_encode($excplicacao[$key])));
			}else if($key == 'RECURSOS_HUMANOS'){
				$pai = $dom->createElement($key);
				foreach ($valueCab as $keyPerg => $valuePerg) {
					$filho = $dom->createElement($valuePerg,utf8_encode($excplicacao[$keyPerg]));
					$pai->appendChild($filho);
					$dom->appendChild($pai);
				}
				$row->appendChild($pai);
			}else{
				$pai = $dom->createElement($valueCab[0]);
				$filho = $dom->createElement($valueCab[1],utf8_encode($excplicacao[$key]));
				$pai->appendChild($filho);
				$dom->appendChild($pai);
				$row->appendChild($pai);
			}
		}
		$root->appendChild($row);
		$dom->appendChild($root);
		
		header("Content-Type: text/xml");
		print $dom->saveXML();exit;
	}
	
	function gerarExemploXlsPrimeiroPasso()
	{
		$objPHPExcel = new PHPExcel();
		$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
		$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
		$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
		$perguntaTrabalho = $this->util->array_sort($perguntas,'NUM_ORDEM');
		$cabecalho = $this->util->campoXls(1,$_SESSION['tipoArquivo'],NULL,$perguntaTrabalho);
		foreach ($cabecalho as $key => $valueCab) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(64+$key).'1', utf8_encode($valueCab));
		}
		$excplicacao = $this->util->explicacaoCampo(1,$_SESSION['tipoArquivo'],null,"xls");
		foreach ($excplicacao as $key => $valueExplicacao) {
			$objPHPExcel->getActiveSheet()->getColumnDimension(chr(64+$key))->setWidth(90);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key-1, 2, utf8_encode($valueExplicacao));
		}
		if($_SESSION['tipoArquivo'] == 1){
			if($_SESSION['tip_orgao'] == 'TRIBE')
				$tamanho = 13;
			else 
				$tamanho = 12;
		}else if ($_SESSION['tipoArquivo'] == 2){
			$tamanho = 11;
		}else if($_SESSION['tipoArquivo'] == 3){
			$tamanho = 3;
		}else if($_SESSION['tipoArquivo'] == 4){
			$tamanho = 4;
		}
				
		foreach ($perguntaTrabalho as $valuePergTrab) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(65+$tamanho).'1', $valuePergTrab['sigla']);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($tamanho, 2, utf8_encode($valuePergTrab['descricao']));
			$objPHPExcel->getActiveSheet()->getColumnDimension(chr(65+$tamanho))->setWidth(50);
			$tamanho++;
		}
		
		$ultimaColuna = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
		
		$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(80);
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
		header('Content-Disposition: attachment;filename="exemploPrimeiroPasso.xls"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	/**
	 * Método para gerar o xls para serventias do estado do primeiro passo.
	 */
	function planilhaServentiaEstado()
	{ 
		$serventia = $this->sql->retornaServentia();
		if(isset($_POST['salvar'])){
			$arrayDividido = array_chunk($serventia,500);
			$arq = 0;
			foreach ($arrayDividido as $keyDiv => $valueDiv) {
				$objPHPExcel = new PHPExcel();
				$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
				$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
				if($listaPerguntaTrabalhoPrmGrau && $listaPerguntaTrabalhoSegGrau){
					$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
					$perguntasTrabalho = $this->util->array_sort($perguntas,'NUM_ORDEM');
				}else if($listaPerguntaTrabalhoPrmGrau){
					$perguntasTrabalho = $this->util->array_sort($listaPerguntaTrabalhoPrmGrau,'NUM_ORDEM');
				}else{
					$perguntasTrabalho = $this->util->array_sort($listaPerguntaTrabalhoSegGrau,'NUM_ORDEM');
				}
					
				$cabecalho = $this->util->campoXls(1,$_SESSION['tipoArquivo'],NULL,$perguntasTrabalho);
				foreach ($cabecalho as $key => $valueCab) {
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(64+$key).'1', utf8_encode($valueCab));
				}
				if($_SESSION['tipoArquivo'] == 1){
					$tamanho = 13;
				}else if ($_SESSION['tipoArquivo'] == 2){
					$tamanho = 11;
				}else if($_SESSION['tipoArquivo'] == 3){
					$tamanho = 3;
				}else if($_SESSION['tipoArquivo'] == 4){
					$tamanho = 4;
				}
				
				$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
				$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
				if($listaPerguntaTrabalhoPrmGrau && $listaPerguntaTrabalhoSegGrau){
					$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
					$perguntasTrabalho = $this->util->array_sort($perguntas,'NUM_ORDEM');
				}else if($listaPerguntaTrabalhoPrmGrau){
					$perguntasTrabalho = $this->util->array_sort($listaPerguntaTrabalhoPrmGrau,'NUM_ORDEM');
				}else{
					$perguntasTrabalho = $this->util->array_sort($listaPerguntaTrabalhoSegGrau,'NUM_ORDEM');
				}
					
				$i = 2;
				foreach ($valueDiv as $value) {
					if($_SESSION['tipoArquivo'] == 1 || $_SESSION['tipoArquivo'] == 2){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $value['seq_corporativo']);
						if($value['grauNovo'] == '0')
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $value['grau']);
						else 
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $value['grauNovo']);
						
						if($value['numOrdem'] != 0 && $value['numOrdem'] != ''){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, $value['numOrdem'].utf8_encode($value['DSC_ORGAO']));
						}else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, utf8_encode($value['DSC_ORGAO']));
						}
						
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $i, $value['uf']);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $i, $value['FLG_ACESSO_INTERNET']);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $i, $value['seqCidade']);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $i, date('d/m/Y',strtotime($value['DAT_INSTALACAO_SERVENTIA'])));
						$municipiosAbrangidos = NULL;
						$muniXls = NULL;
						$municipiosAbrangidos = $this->sql->municipiosAbrangidosServentia($value['seq_corporativo']);
						if($municipiosAbrangidos){
							foreach ($municipiosAbrangidos as $muni) {
								if($muni['COD_CIDADE'] != $value['seqCidade'])
									$muniXls .= $muni['COD_CIDADE'].',';
							}
							$muniXls = substr($muniXls, 0, strlen($muniXls)-1);
						}
						if(!empty($muniXls)){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $i, $value['seqCidade'].','.$muniXls);
						}else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $i, $value['seqCidade']);
						}
						if($_SESSION['tipoArquivo'] == 1){
							$competencias = $this->sql->retornaCompetencia($value['seq_corporativo']);
							$compXls = NULL;
							if($competencias){
								foreach ($competencias as $comp) {
									$compXls .= $comp['SEQ_COMPETENCIA_JUIZO'].',';
								}
								$compXls = substr($compXls, 0, strlen($compXls)-1);
							}
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $i, $compXls);
							$dadosComplementares = $this->sqlCons->retornaConsultaServentia($value['seq_corporativo']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $i, utf8_encode($dadosComplementares[0]['LATITUDE']));
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $i, utf8_encode($dadosComplementares[0]['LONGITUDE']));
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $i, $value['FLG_ATIVO']);
							if($dadosComplementares[0]['TIP_CLASSIFICACAO_ENTRANCIA'] != 0){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $i, $dadosComplementares[0]['TIP_CLASSIFICACAO_ENTRANCIA']);
							}else{
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $i, '');
							}
						}else{
							$dadosComplementares = $this->sqlCons->retornaConsultaServentia($value['seq_corporativo']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $i, utf8_encode($dadosComplementares[0]['LATITUDE']));
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $i, utf8_encode($dadosComplementares[0]['LONGITUDE']));
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $i, $value['FLG_ATIVO']);
						}
					}else if($_SESSION['tipoArquivo'] == 3){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $value['seq_corporativo']);
						if($value['numOrdem'] != 0 && $value['numOrdem'] != ''){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $value['numOrdem'].utf8_encode($value['DSC_ORGAO']));
						}else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, utf8_encode($value['DSC_ORGAO']));
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, $value['FLG_ATIVO']);
						}
					}else if($_SESSION['tipoArquivo'] == 4){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $value['seq_corporativo']);
						if($value['numOrdem'] != 0 && $value['numOrdem'] != ''){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $value['numOrdem'].utf8_encode($value['DSC_ORGAO']));
						}else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, utf8_encode($value['DSC_ORGAO']));
							$competencias = $this->sql->retornaCompetencia($value['seq_corporativo']);
							if($competencias){
								$compXls = NULL;
								foreach ($competencias as $comp) {
									$compXls .= $comp['SEQ_COMPETENCIA_JUIZO'].',';
								}
								$compXls = substr($compXls, 0, strlen($compXls)-1);
							}else{
								$compXls = NULL;
							}
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, $compXls);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $i, $value['FLG_ATIVO']);
						}
					}
					$respostaTrabalho = $this->sqlCons->respostaProdutividadeTrabalhoPrmGrau($value['seq_corporativo']);
				
					$inicioPergunta = count($cabecalho)-count($perguntasTrabalho);
					if($respostaTrabalho){
						foreach ($respostaTrabalho as $key => $valueRespTrab) {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($inicioPergunta, $i, $valueRespTrab['valor']);
							$inicioPergunta++;
						}
					}
				
					$i++;
				}
				
				$ultimaColuna = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
				
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.$ultimaColuna.$i)
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
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$arquivosZip[$arq] = 'relatorioPrimeiroPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
				$nomeArquivo = 'relatorioPrimeiroPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
				$salvar = $this->util->caminho_absoluto1Grau.$nomeArquivo;
				$objWriter->save($salvar);
				$arq++;
			}
			
			$zip = new ZipArchive();
			$nomeArquivoZip = "arquivosRelatorioPrimeiroPasso".$_SESSION['sig_tribunal'].".zip";
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
		}else{
			$this->smarty->assign('listaServentia',$serventia);
			
			echo $this->smarty->fetch("importarArquivos/planilhaServentiaEstado.html");
			die;
		}
	}
	
	/**
	 * Método para gerar o xls dos municipios do estado do primeiro passo.
	 */
	function planilhaMunicipiosEstado()
	{
		$municipio_estado = $this->sql->retornaMunicipiosEstado();
		if(isset($_POST['salvar'])){
			$this->importarArquivos->gerarXLS($municipio_estado, "Id_Cidade,UF,Cidade,Codigo IBGE");
		}else{
			$this->smarty->assign('listaMunicipio',$municipio_estado);
	
			echo $this->smarty->fetch("importarArquivos/planilhaMunicipiosEstado.html");
			die;
		}
	}
	
	/**
	 * Método para gerar o xls das competências das serventias.
	 */
	function planilhaCompetencia()
	{
		$listaCompetencia = $this->sql->listaCompetencia();
		if(isset($_POST['salvar'])){
			$this->importarArquivos->gerarXLS($listaCompetencia, "Id_Competencia,Descricao da competencia");
		}else{
			$this->smarty->assign('listaCompetencia',$listaCompetencia);
	
			echo $this->smarty->fetch("importarArquivos/planilhaCompetencia.html");
			die;
		}
	}
	
	/**
	 * Método salva os dados do xml do primeiro passo.
	 */
	function salvarXmlPrimeiroPasso()
	{
		permissao($_POST);
		$xml = simplexml_load_file($_POST['caminhoXml']);
		try {
			$this->db->BeginTrans();
			foreach ($xml as $value) {
				$value->DENOMINACAO = $this->util->precisaUtf8($value->DENOMINACAO);
				/***********************Validações************************/
				$tipOrgaoValida = $this->sql->retornaTipoOrgao(trim($value->GRAU));
				if(!$tipOrgaoValida)
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." grau não é válido.");
				
				$campos = $this->util->campoXml(1,$_SESSION['tipoArquivo']);
				foreach ($campos as $cap) {
					if($cap == "INSTALACAO"){
						if(trim($value->GRAU) != 2 && trim($value->GRAU) != 3){
							$valor = trim($value->$cap);
							if(empty($valor))
								throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." o campo ".$cap." não pode ser vazio.");
						}
					}else if($cap[0] == "MUNICIPIOS_ABRANGIDOS"){
						if(trim($value->GRAU) != 2 && trim($value->GRAU) != 3){
							if(empty($value->MUNICIPIOS_ABRANGIDOS->CODIGO_MUNICIPIO))
								throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." o campo MUNICIPIOS_ABRANGIDOS não pode ser vazio.");
						}
					}else if($cap[0] == "COMPETENCIA"){
						$valor = trim($value->COMPETENCIA->CODIGO);
						if(empty($valor))
							throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." COMPETENCIA não foi preenchido.");
					}else{
						$valor = trim($value->$cap);
						if(empty($valor) && $valor != '0'){
							throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." ".$cap." não foi preenchido.");
						}
					}
				}					
				
				if(is_numeric(trim($value->GRAU)) != 1)
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." grau não é número ou está vazio.");
				
				if(strtotime($value->INSTALACAO) > strtotime(date('Y-m-d')))
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." data instalação maior que a data atual.");

				if(trim($value->INTERNET) != 'S' && trim($value->INTERNET) != 'N')
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." informação digitada em INTERNET não pode ser diferente de 'S' ou 'N'.");
				
				$ufValida = $this->sqlMag->retornaUf($value->UF);
				if(!$ufValida)
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." UF informada é inválida.");
				
				if($value->GRAU != 2 && $value->GRAU != 3){
					if(empty($value->INSTALACAO)){
						throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." data Instalação não foi preenchida.");
					}
						
					if(empty($value->MUNICIPIOS_ABRANGIDOS->CODIGO_MUNICIPIO)){
						throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." Municipios Abrangidos não foi preenchida.");
					}
				}	
				
				$municipio = $this->sql->retornaMunicipio($value->MUNICIPIO);
				if(!$municipio)
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." codigo municipio não existente.");
				
				if($_SESSION['tipoArquivo'] == 1 && $_SESSION['tip_orgao'] == 'TRIBE'){
					if($value->GRAU == 1 || $value->GRAU == 4){
						if($value->ENTRANCIA == null)
							throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." entrância não pode ser o valor vazia ou zero.");
					}
				}
				
				/*Valida se a data de instalação é valida*/
				$dataInstalacao = NULL;
				if(isset($value->INSTALACAO) && !empty($value->INSTALACAO)){
					$dataInstalacao = trim($value->INSTALACAO);
					$temTraco = explode("-",$dataInstalacao);
					if(count($temTraco) == 1)
						throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." data de instação em formato inválido.");
				}
				/**********************************************************/	
				
				$value->NUMERO = NULL;
				$primeiraPosicao = substr(trim($value->DENOMINACAO), 0, 1);
				if(is_numeric($primeiraPosicao)){
					$posNum1 = strpos(trim($value->DENOMINACAO), 'º');
					$posNum2 = strpos(trim($value->DENOMINACAO), 'ª');
					$posNum3 = strpos(trim($value->DENOMINACAO), '°');
					if($posNum1 || $posNum2 || $posNum3){
						if($posNum1){
							$value->NUMERO = substr(trim($value->DENOMINACAO), 0, $posNum1);
							$value->DENOMINACAO = substr(trim($value->DENOMINACAO), $posNum1, strlen(trim($value->DENOMINACAO)) - $posNum1);
						}else if($posNum2){
							$value->NUMERO = substr(trim($value->DENOMINACAO), 0, $posNum2);
							$value->DENOMINACAO = substr(trim($value->DENOMINACAO), $posNum2, strlen(trim($value->DENOMINACAO)) - $posNum2);
						}else if($posNum3){
							$value->NUMERO = substr(trim($value->DENOMINACAO), 0, $posNum3);
							$value->DENOMINACAO = substr(trim($value->DENOMINACAO), $posNum3, strlen(trim($value->DENOMINACAO)) - $posNum3);
						}
					}else{
						throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->DENOMINACAO." existe número no inicio do nome da serventia mas sem o caracter 'º' ou 'ª'.");
					}
				}
				
				$tipOrgao = NULL;
				$tipOrgao = $this->util->retornaTipoOrgao($value->GRAU);
				if(empty($tipOrgao)){
					throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." grau informado não existente.");
				}
				
				if($value->CODIGO_SERVENTIA == 0){
					$seqOrgaoPai = NULL;
					$codHeranca  = NULL;
					if($_SESSION['tip_orgao'] == 'TRIBE'){
						if($value->GRAU == 1 || $value->GRAU == 4 || $value->GRAU == 3){
							$herancaTipo = $this->sql->retornaCodHerancaTipoGrau($value->GRAU,'E');
							$herancaMunicipio = $this->sql->retornaCodHerancaEstadual($municipio,$value->MUNICIPIO);
							if($herancaMunicipio){
								$seqOrgaoPai = $herancaMunicipio['SEQ_ORGAO'];
								$codHeranca = ':'.$herancaMunicipio['SEQ_ORGAO'].':,'.$herancaMunicipio['COD_HIERARQUIA'];
							}else{
								$seqOrgaoPai = $herancaTipo['SEQ_ORGAO'];
								$codHeranca = ':'.$herancaTipo['SEQ_ORGAO'].':,'.$herancaTipo['COD_HIERARQUIA'];
								$novaComarca = $this->sql->salvarNovaComarca($municipio,$codHeranca,$value->MUNICIPIO,$seqOrgaoPai);
								if($novaComarca){
									$herancaMunicipio = $this->sql->retornaCodHerancaEstadual($municipio,$value->MUNICIPIO);
										
									$seqOrgaoPai = $herancaMunicipio['SEQ_ORGAO'];
									$codHeranca = ':'.$herancaMunicipio['SEQ_ORGAO'].':,'.$herancaMunicipio['COD_HIERARQUIA'];
								}
							}
						}else if($value->GRAU == 2){
							$herancaTipo = $this->sql->retornaCodHerancaTipoGrau($value->GRAU,'E');
							$seqOrgaoPai = $herancaTipo['SEQ_ORGAO'];
							$codHeranca = ':'.$herancaTipo['SEQ_ORGAO'].':,'.$herancaTipo['COD_HIERARQUIA'];
						}
					}else if($_SESSION['tip_orgao'] == 'TRIBF'){
						$excecao = NULL;
						if($value->MUNICIPIO == 4795){
							if(strstr($value->DENOMINACAO,'criminal')){
								$excecao = 'criminal';
							}else if(strstr($value->DENOMINACAO,'civel') || strstr($value->DENOMINACAO,'cível')){
								$excecao = 'civel';
							}else if(strstr($value->DENOMINACAO,'fiscal') || strstr($value->DENOMINACAO,'fiscais')){
								$excecao = 'fiscal';
							}else if(strstr($value->DENOMINACAO,'previdenciária') || strstr($value->DENOMINACAO,'previdenciaria')){
								$excecao = 'previdenciário';
							}else if(strstr($value->DENOMINACAO,'jef') || strstr($value->DENOMINACAO,'juizado especial federal')){
								$excecao = 'juizado especial';
							}
						}
							
						$herancaTipo = $this->sql->retornaCodHerancaTipoGrauFederal($value->GRAU,$value->MUNICIPIO,$excecao);
						if($herancaTipo){
							$seqOrgaoPai = $herancaTipo['SEQ_ORGAO'];
							$codHeranca = ':'.$herancaTipo['SEQ_ORGAO'].':,'.$herancaTipo['COD_HIERARQUIA'];
						}else{
							$herancaUf = $this->sql->retornaCodHerancaFederalUf($value['uf']);
							$seqOrgaoPai = $herancaUf['SEQ_ORGAO'];
							$codHeranca = ':'.$herancaUf['SEQ_ORGAO'].':,'.$herancaUf['COD_HIERARQUIA'];
						}
					}else{
						$heranca = $this->sql->retornaCodHeranca();
						$seqOrgaoPai = $_SESSION['seq_orgao'];
						$codHeranca = ':'.$_SESSION['seq_orgao'].':,:'.$heranca['SEQ_ORGAO'].':,'.$heranca['COD_HIERARQUIA'];
					}
					
					/*Verifica se a serventia com a mesma denominação e no mesmo municipo*/
					$serventiaRepetida = $this->sql->retornaServentiaRepetida($value->DENOMINACAO,$value->MUNICIPIO,$value->NUMERO,$codHeranca);
					if($serventiaRepetida)
						throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." foi verificado que existe uma serventia cadastrada
									com as mesmas informações com o código ".$serventiaRepetida.", favor verificar e atualizar a coluna Código Serventia.");
					
					if($codHeranca){
						$orgao = $this->sql->inserirOrgaoCorporativo(trim($value->GRAU),$value->NUMERO,utf8_decode($value->DENOMINACAO),$codHeranca,$value->MUNICIPIO,$seqOrgaoPai,$tipOrgao,$value->STATUS);
						if(!$orgao)
							throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
					}else{
						throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
					}
				}else{
					$orgao = $this->sql->retornaServentia($value->CODIGO_SERVENTIA);
					if($orgao){
						$seqOrgaoPai = $orgao[0]['SEQ_ORGAO_PAI'];
						$codHeranca  = $orgao[0]['COD_HIERARQUIA'];
						$orgao = $this->sql->atualizarOrgao(trim($value->GRAU),$value->NUMERO,$value->DENOMINACAO,$value->MUNICIPIO,$value->CODIGO_SERVENTIA,$tipOrgao,$value->STATUS,$codHeranca,$seqOrgaoPai,$orgao[0]['TIP_ORGAO']);
						if(!$orgao)
							throw new Exception("Erro ao atualizar a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$xml->DENOMINACA.".");
					}else{
						throw new Exception("Serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->ROW->DENOMINACAO." não cadastrada.");
					}
				}
				
				/*Inserir as informações sobre internet da serventia*/
				$temDadosInternet = $this->sql->temDadosInternet($orgao);
				if($temDadosInternet){
					$serventiaNet = $this->sql->atualizarFlgInternetServentia($orgao,$value->INTERNET);
				}else{
					$serventiaNet = $this->sql->inserirFlgInternetServentia($orgao,$value->INTERNET);
				}
				if(!$serventiaNet)
					throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
				
				/*Inserir dados complementares da serventia*/
				$temDadosComplementares = $this->sql->retornaDadosComplementares($orgao);
				if($temDadosComplementares){
					$excluiDadosComplementeres = $this->sql->excluirDadosComplementares($orgao);
					if(!$excluiDadosComplementeres){
						throw new Exception("Erro ao inserir dados de alguma das colunas (data instalação ou latitude ou da longitude) da serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$xml->DENOMINACAO.".");
					}
				}
				$inserirDadosComplementares = $this->sql->inserirDadosComplementares($orgao,$value->INSTALACAO,$value->LONGITUDE,$value->LATITUDE,$value->ENTRANCIA);
				if(!$inserirDadosComplementares)
					throw new Exception("Erro ao inserir os dados complementares da serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
					
				if($value->GRAU != 2 && $value->GRAU != 3){
					/*Inserir dados dos municipio(s) abrangido(s) da serventia*/
					$excluirMunicipioAbrangido = $this->sql->excluirMunicipioAbrangido($orgao);
					if($value->MUNICIPIOS_ABRANGIDOS->CODIGO_MUNICIPIO){
						$listaMunicipios = NULL;
						foreach ($value->MUNICIPIOS_ABRANGIDOS->CODIGO_MUNICIPIO as $muni) {
							$municipio = $this->sql->retornaMunicipio($muni);
							if(!$municipio)
								throw new Exception("Erro ao inserir a serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." codigo municipio abrangido não existente.");
							if(trim($muni) != trim($value->MUNICIPIO))
								$listaMunicipios[] = trim($muni);
						}
						
						$listaMunicipios[] = trim($value->MUNICIPIO);
						foreach ($listaMunicipios as $valueMuni) {
							$inserirCompetencia = $this->sql->inserirMunicipioAbrangido($orgao,$valueMuni);
							if(!$inserirCompetencia)
								throw new Exception("Erro ao inserir os municípios abrangidos da serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
						}
					}
				}
				
				/*Inserir dados complementares da serventia*/
				$excluirCompetencia = $this->sql->excluirCompetencia($orgao);
				if($value->COMPETENCIA->CODIGO){
					foreach ($value->COMPETENCIA->CODIGO as $comp) {
						$inserirCompetencia = $this->sql->inserirCompetencia($orgao,$comp);
						if(!$inserirCompetencia)
							throw new Exception("Erro ao inserir as competencias da serventia ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
					}
				}
				
				/*Inserir os dados da força de trabalho da serventia*/
				$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
				$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
				$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
				$perguntaTrabalho = $this->util->array_sort($perguntas,'NUM_ORDEM');
				$i = count($meuArray[2]) - count($perguntaTrabalho);
				$mes = $this->util->qualMesNumero(date('m'));
				$ultimoDia = date('Y')."-".date('m')."-".date("t", mktime(0,0,0,date('m'),'01',date('Y')));
				$valueResposta = get_object_vars($value->RECURSOS_HUMANOS);
				foreach ($perguntaTrabalho as $valuePergTrab) {
					if($valuePergTrab['grau'] == 1){
						if( count(explode(",", $valueResposta[$valuePergTrab['sigla']])) > 1 || count(explode(".", $valueResposta[$valuePergTrab['sigla']])) > 1 || $valueResposta[$valuePergTrab['sigla']] < 0)
							throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." o valor não pode ser negativo ou fracionário na pergunta ".$valuePergTrab['sigla'].".");
						
						$prodServentia = $this->sql->retornaProdServentiaPrmGrau($orgao,$mes,date('Y'));
						if(empty($prodServentia)){
							$prodServentia = $this->sql->inserirProdServentiaPrmGrau($orgao,NULL,date('Y'),date('m'),$ultimoDia);
						}else{
							$prodServentia = $this->sql->atualizarProdServentiaPrmGrau($orgao,NULL,date('Y'),date('m'),date('m'),$ultimoDia,$prodServentia);
						}
				
						$seqRespostaPrmGrau = $this->sql->retornaSeqResposta($prodServentia,$valuePergTrab['seqPergunta']);
						if(!empty($seqRespostaPrmGrau)){
							$respServentia = $this->sql->atualizarRespServentiaPrmGrau($valueResposta[$valuePergTrab['sigla']],$seqRespostaPrmGrau);
						}else{
							$respServentia = $this->sql->inserirRespServentiaPrmGrau($prodServentia,$valuePergTrab['seqPergunta'],$valueResposta[$valuePergTrab['sigla']]);
						}
						if(empty($respServentia))
							throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value[1]." - ".$value->NUMERO.$value->DENOMINACAO.".");
					}else{
						if( count(explode(",", $valueResposta[$valuePergTrab['sigla']])) > 1 || count(explode(".", $valueResposta[$valuePergTrab['sigla']])) > 1 || $valueResposta[$valuePergTrab['sigla']] < 0)
							throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO." o valor não pode ser negativo ou fracionário na pergunta ".$valuePergTrab['sigla'].".");
						
						$seqProdutividadeSegGrau = $this->sqlSeg->retornaProdutividadeServentiaSegGrau($orgao,$mes,date('Y'));
						if(empty($seqProdutividadeSegGrau)){
							$seqProdutividadeSegGrau = $this->sqlSeg->inserirProdutividadeServentiaSegGrau($orgao,$mes,date('Y'),date('m'),NULL);
						}else{
							$seqProdutividadeSegGrau = $this->sqlSeg->alterarProdutividadeServentiaSegGrau($seqProdutividadeSegGrau,NULL);
						}
						if(empty($seqProdutividadeSegGrau)){
							throw new Exception("Ocorreu um erro ao salvar os dados do xls da força de trabalho da serventia de código ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
						}
							
						$seqRespostaSegGrau = $this->sqlSeg->retornaSeqResposta($seqProdutividadeSegGrau,$pergServ['seqPergunta']);
						if(!empty($seqRespostaSegGrau)){
							$respServentia = $this->sqlSeg->atualizarRespostaSegGrau($valueResposta[$valuePergTrab['sigla']],$seqRespostaSegGrau);
						}else{
							$respServentia = $this->sqlSeg->inserirRespostaSegGrau($seqProdutividadeSegGrau,$valuePergTrab['seqPergunta'],$valueResposta[$valuePergTrab['sigla']]);
						}
						if(empty($respServentia))
							throw new Exception("Ocorreu um erro ao salvar as respostas da produtividade da serventia de código ".$value->CODIGO_SERVENTIA." - ".$value->NUMERO.$value->DENOMINACAO.".");
					}
					$i++;
				}
			}
			
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'Dados do xml do Primeiro Passo gravados com sucesso.';
	
		}
		catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
	
		unlink($_POST['caminhoXml']);
		$parametros['aba']  = 1;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método para salvar os dados do primeiro passo do xls.
	 */
	function salvarXlsPrimeiro()
	{
		permissao($_POST);
		$data = new Spreadsheet_Excel_Reader("php/importarArquivos/arq1grau/".$_POST['nomeArq']);
		
		$linhas = $data->rowcount();
		$colunas= $data->colcount();
		
		for($i = 1; $i <= 1; $i++){
			for($j = 1; $j <= $colunas; $j++){
				$cabecalho[$i][$j] = $data->val($i,$j);
			}
		}
		
		$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
		$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
		$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
		$refImport = $this->util->referenciaImportacao(1,$_SESSION['tipoArquivo'],$perguntas);
		for($i = 2; $i <= $linhas; $i++){
			for($j = 1; $j <= count($refImport); $j++){
				$meuArray[$i][$refImport[$j]] = $data->val($i,$j);
			}
		}
		
		try {
			$this->db->BeginTrans();
			if(empty($meuArray))
				throw new Exception("Ocorreu um erro ao salvar os dados do xls, arquivo vazio.");
			
			$ref = 2;
			foreach ($meuArray as $value){
				if($value['descricao'] == ""){
					break;
				}else{
					$numero = NULL;
					$value['descricao'] = $this->util->precisaUtf8($value['descricao']);
					$primeiraPosicao = substr(trim($value['descricao']), 0, 1);
					if(is_numeric($primeiraPosicao)){
						$posNum1 = strpos(trim($value['descricao']), 'º');
						$posNum2 = strpos(trim($value['descricao']), 'ª');
						$posNum3 = strpos(trim($value['descricao']), '°');
						if($posNum1 || $posNum2 || $posNum3){
							if($posNum1){
								$numero = substr(trim($value['descricao']), 0, $posNum1);
								$descricao = substr(trim($value['descricao']), $posNum1, strlen(trim($value['descricao'])) - $posNum1);
							}else if($posNum2){
								$numero = substr(trim($value['descricao']), 0, $posNum2);
								$descricao = substr(trim($value['descricao']), $posNum2, strlen(trim($value['descricao'])) - $posNum2);
							}else if($posNum3){
								$numero = substr(trim($value['descricao']), 0, $posNum3);
								$descricao = substr(trim($value['descricao']), $posNum3, strlen(trim($value['descricao'])) - $posNum3);
							}else{
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." existe número no inicio do nome da serventia mas sem o caracter 'º' ou 'ª'.");
							}
						}else{
							$descricao = $value['descricao'];
						}
					}else{
						$descricao = $value['descricao'];
					}

					/*****************Validações******************/
					/*Valida se o campo grau é valido*/
					if($_SESSION['tip_orgao'] == 'TRIBF' || $_SESSION['tip_orgao'] == 'TRIBE'){
						$tipOrgaoValida = $this->sql->retornaTipoOrgao($value['grau']);
						if(!$tipOrgaoValida)
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." grau informado não existente.");
					}
					
					/*Valida os campos vazios do arquivo menos do TJ*/
					if($_SESSION['tip_orgao'] == 'TRIBE'){
						if($value['grau'] != 2 && $value['grau'] != 3 & $value['grau'] != 4){
							if(empty($value['instalacao']))
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." o campo intalação não pode ser vazio.");
								
							if(empty($value['municipiosAbrangidos']))
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." o campo municipios abrangidos não pode ser vazio.");
							
							$k=1;
							foreach ($value as $key => $validacao) {
								if($key != 'codServentia'){
									if(empty($value[$key]))
										throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." o campo ".$cabecalho[1][$k]." não pode ser vazio.");
								}
								if($k == count($refImport) - count($perguntas))
									break;
									$k++;
							}
						}
					}
					
					/*Validar o grau para eleitoral não pode ser diferente de 1 ou 2*/
					if($_SESSION['tip_orgao'] == 'TRIBL'){
						if($value['grau'] != 1 && $value['grau'] != 2)
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." grau informado não é válido.");
					}
					
					/*Válida se o campo Internet foi preenchido com S ou N*/
					if(isset($value['internet'])){
						$internet = trim($value['internet']);
						if(empty($internet) || strtoupper(trim($value['internet'])) != 'S' && strtoupper(trim($value['internet'])) != 'N')
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." informação digitada em INTERNET não pode ser diferente de 'S' ou 'N'.");
					}
					
					/*Válida se o campo Código Serventia é número*/
					if(is_numeric($value['codServentia']) != 1)
						throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." identificação não é número.");
						
					/*Valida se a data de instalação é valida*/
					$dataInstalacao = NULL;
					if(isset($value['instalacao']) && !empty($value['instalacao'])){
						$dataInstalacao = $this->util->converterDataParaBancoXls($value['instalacao'],$data->sheets[0]['cellsInfo'][$ref][7]);
						if(empty($dataInstalacao))
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." data instalação não é válida ou não foi preenchida.");
					}	
					
					/*Valida o código do municipio*/
					if($value['municipio']){
						$municipioValido = $this->sql->retornaMunicipio($value['municipio']);
						if(empty($municipioValido))
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." código municipio informado é inválido(Código ".$value['municipio'].".");
					}
					
					/*Válida se o grau for direfente de 2 ou 3 os campos Instalação e Municipios Abrangidos não podem ser vazios*/
					if($_SESSION['tip_orgao'] == 'TRIBF' || $_SESSION['tip_orgao'] == 'TRIBE'){
						if($value['grau'] != 2 && $value['grau'] != 3){
							/*Válida se há municipios abrangidos*/
							if(empty($value['municipiosAbrangidos'])){
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." Municipios Abrangidos não foi preenchida.");
							}
						}
						
						/*Válida se a data instalação é maior que a data de hoje.*/
						if($dataInstalacao){
							if(strtotime($dataInstalacao) > strtotime(date('Y-m-d')))
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." data instalação maior que a data atual.");
						}
						
						/*Válida se a Uf preenchida*/
						$ufValida = $this->sqlMag->retornaUf($value['uf']);
						if(!$ufValida)
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." UF informada é inválida.");
										
						/*Válida o municipio preenchido*/
						$municipio = $this->sql->retornaMunicipio($value['municipio']);
						if(!$municipio)
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." codigo municipio não existente.");
					}
							
					if($_SESSION['tipoArquivo'] == 1 && $_SESSION['tip_orgao'] == 'TRIBE'){
						/*Válida se o grau for 1 ou 4 o campo entrancia é obrigatório*/
						if($value['grau'] == 1 || $value['grau'] == 4){
							if($value['entrancia'] == null)
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." entrância não pode ser o valor vazia ou zero.");
						}
									
						/*Válida a entrancia só pode ser NULL,0,1,2,3 ou 4.*/
						if($value['entrancia'] != 1 && $value['entrancia'] != 2 && $value['entrancia'] != 3 && $value['entrancia'] != 4 && $value['entrancia'] != null)
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$numero.$value[3]." entrância não pode ser o valor ".$value[13].".");
					}
					/*********************************************/	

					$tipOrgao = NULL;
					$tipOrgao = $this->util->retornaTipoOrgao($value['grau']);
					if(!$tipOrgao)
						throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." grau informado não existente.");
					
					if($value['codServentia'] == 0){
						/******Monta o código herança e verifica o seq_orgao_pai para cadastro do orgao******/
						$seqOrgaoPai = NULL;
						$codHeranca  = NULL;
						$herancaTipo = NULL;
						if($_SESSION['tip_orgao'] == 'TRIBE'){
							if($value['grau'] == 1 || $value['grau'] == 4 || $value['grau'] == 3){
								$herancaMunicipio = $this->sql->retornaCodHerancaEstadual($municipio,$value['municipio']);
								if($herancaMunicipio){
									$seqOrgaoPai = $herancaMunicipio['SEQ_ORGAO'];
									$codHeranca = ':'.$herancaMunicipio['SEQ_ORGAO'].':,'.$herancaMunicipio['COD_HIERARQUIA'];
								}else{
									$herancaTipo = $this->sql->retornaCodHerancaTipoGrau($value['grau'],'E');
									$seqOrgaoPai = $herancaTipo['SEQ_ORGAO'];
									$codHeranca = ':'.$herancaTipo['SEQ_ORGAO'].':,'.$herancaTipo['COD_HIERARQUIA'];
									$novaComarca = $this->sql->salvarNovaComarca($municipio,$codHeranca,$value['municipio'],$seqOrgaoPai);
									if($novaComarca){
										$herancaMunicipio = $this->sql->retornaCodHerancaEstadual($municipio,$value['municipio']);
											
										$seqOrgaoPai = $herancaMunicipio['SEQ_ORGAO'];
										$codHeranca = ':'.$herancaMunicipio['SEQ_ORGAO'].':,'.$herancaMunicipio['COD_HIERARQUIA'];
									}
								}
							}else if($value['grau'] == 2){
								$herancaTipo = $this->sql->retornaCodHerancaTipoGrau($value['grau'],'E');
								$seqOrgaoPai = $herancaTipo['SEQ_ORGAO'];
								$codHeranca = ':'.$herancaTipo['SEQ_ORGAO'].':,'.$herancaTipo['COD_HIERARQUIA'];
							}
						}else if($_SESSION['tip_orgao'] == 'TRIBF'){
							$excecao = NULL;
							if($value['municipio'] == 4795){
								if(strstr($value['descricao'],'criminal')){
									$excecao = 'criminal';
								}else if(strstr($value['descricao'],'civel') || strstr($value['descricao'],'cível')){
									$excecao = 'civel';
								}else if(strstr($value['descricao'],'fiscal') || strstr($value['descricao'],'fiscais')){
									$excecao = 'fiscal';
								}else if(strstr($value['descricao'],'previdenciária') || strstr($value['descricao'],'previdenciaria')){
									$excecao = 'previdenciário';
								}else if(strstr($value['descricao'],'jef') || strstr($value['descricao'],'juizado especial federal')){
									$excecao = 'juizado especial';
								}
							}
							
							$herancaTipo = $this->sql->retornaCodHerancaTipoGrauFederal($value['grau'],$value['municipio'],$excecao);
							if($herancaTipo){
								$seqOrgaoPai = $herancaTipo['SEQ_ORGAO'];
								$codHeranca = ':'.$herancaTipo['SEQ_ORGAO'].':,'.$herancaTipo['COD_HIERARQUIA'];
							}else{
								$herancaUf = $this->sql->retornaCodHerancaFederalUf($value['uf']);
								$seqOrgaoPai = $herancaUf['SEQ_ORGAO'];
								$codHeranca = ':'.$herancaUf['SEQ_ORGAO'].':,'.$herancaUf['COD_HIERARQUIA'];
							}
						}else{
							$heranca = $this->sql->retornaCodHeranca();
							$seqOrgaoPai = $_SESSION['seq_orgao'];
							$codHeranca = ':'.$_SESSION['seq_orgao'].':,:'.$heranca['SEQ_ORGAO'].':,'.$heranca['COD_HIERARQUIA'];
						}
						/**************************************************************************************/
						/*Verifica se a serventia com a mesma denominação e no mesmo municipo*/
						$serventiaRepetida = $this->sql->retornaServentiaRepetida($value['descricao'],$value['municipio'],$numero,$codHeranca);
						if($serventiaRepetida)
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." foi verificado que existe uma serventia cadastrada
								com as mesmas informações com o código ".$serventiaRepetida.", favor verificar e atualizar a coluna Código Serventia.");
						
						if($codHeranca){
							$orgao = $this->sql->inserirOrgaoCorporativo($value['grau'],$numero,$descricao,$codHeranca,$value['municipio'],$seqOrgaoPai,$tipOrgao,$value['status']);
							if(!$orgao)
								throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao'].".");
						}else{
							throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao'].".");
						}
					}else{
						$orgao = $this->sql->retornaServentia($value['codServentia']);
						if($orgao){
							$seqOrgaoPai = $orgao[0]['SEQ_ORGAO_PAI'];
							$codHeranca  = $orgao[0]['COD_HIERARQUIA'];
							$orgao = $this->sql->atualizarOrgao($value['grau'],$numero,$descricao,$value['municipio'],$value['codServentia'],$tipOrgao,$value['status'],$codHeranca,$seqOrgaoPai,$orgao[0]['TIP_ORGAO']);
							if(!$orgao)
								throw new Exception("Erro ao atualizar a serventia ".$value['codServentia']." - ".$value['descricao'].".");
						}else{
							throw new Exception("Serventia ".$value['codServentia']." - ".$value['descricao']." não está cadastrada ao seu tribunal.");
						}
					}
					
					/*Inserir as informações sobre internet da serventia*/
					if(!isset($value['internet']))
						$value['internet'] = NULL;
					
					$temDadosInternet = $this->sql->temDadosInternet($value['codServentia']);
					if($temDadosInternet){
						$serventiaNet = $this->sql->atualizarFlgInternetServentia($orgao,$value['internet']);
					}else{
						$serventiaNet = $this->sql->inserirFlgInternetServentia($orgao,$value['internet']);
					}
					if(!$serventiaNet)
						throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao'].".");
					
					if(!isset($value['entrancia']))
						$value['entrancia'] = NULL;
					
					/*Inserir dados complementares da serventia*/
					$temDadosComplementares = $this->sql->retornaDadosComplementares($value['codServentia']);
					if($temDadosComplementares){
						$excluiDadosComplementeres = $this->sql->excluirDadosComplementares($value['codServentia']);
						if(!$excluiDadosComplementeres){
							throw new Exception("Erro ao inserir dados de alguma das colunas (data instalação ou latitude ou da longitude) da serventia ".$value['codServentia']." - ".$value['descricao'].".");
						}
					}
					$inserirDadosComplementeres = $this->sql->inserirDadosComplementares($orgao,$dataInstalacao,$value['longitude'],$value['latitude'],$value['entrancia']);
					if(empty($inserirDadosComplementeres)){
						throw new Exception("Erro ao inserir dados de alguma das colunas (data instalação ou latitude ou da longitude) da serventia ".$value['codServentia']." - ".$value['descricao'].".");
					}
					
					/*Inserir dados dos municipio(s) abrangido(s) da serventia*/
					$trimMunicipios = trim($value['municipiosAbrangidos']);
					if(!empty($trimMunicipios)){
						$excluirMunicipioAbrangido = $this->sql->excluirMunicipioAbrangido($value['codServentia']);
						if(count(explode(',', $value['municipiosAbrangidos'])) > 1 || count(explode('.', $value['municipiosAbrangidos'])) > 1){
							$municipiosSeparadas = NULL;
							if(count(explode('.', $value['municipiosAbrangidos'])) > 1)
								$municipiosSeparadas = explode('.', $value['municipiosAbrangidos']);
							else
								$municipiosSeparadas = explode(',', $value['municipiosAbrangidos']);
							
							$listaMunicipios = NULL;
							$listaMunicipios[] = $value['municipio'];
							foreach ($municipiosSeparadas as $muni) {
								if($muni != $value['municipio']){
									$listaMunicipios[] = $muni;
								}
							}
							
							foreach ($listaMunicipios as $valueMuni) {
								$municipioValido = $this->sql->retornaMunicipio($valueMuni);
								if(empty($municipioValido))
									throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." código município abrangido informado é inválido(Código ".$valueMuni.").");
								
								$inserirMunicipioAbrangido = $this->sql->inserirMunicipioAbrangido($orgao,$valueMuni);
								if(!$inserirMunicipioAbrangido)
									throw new Exception("Erro ao inserir os municípios abrangidos da serventia ".$value['codServentia']." - ".$value['descricao']."(Código ".$valueMuni.").");
							}
						}else{
							$listaMunicipios = NULL;
							if(!empty($value['municipiosAbrangidos'])){
								$listaMunicipios[] = $value['municipiosAbrangidos'];
							}
							if($value['municipiosAbrangidos'] != $value['municipio'])
								$listaMunicipios[] = $value['municipio'];
	
							if($listaMunicipios){
								foreach ($listaMunicipios as $listMuni) {
									$municipioValido = $this->sql->retornaMunicipio($listMuni);
									if(empty($municipioValido))
										throw new Exception("Erro ao inserir a serventia ".$value['codServentia']." - ".$value['descricao']." código município abrangido informado é inválido.");
									
									$inserirMunicipioAbrangido = $this->sql->inserirMunicipioAbrangido($orgao,$listMuni);
									if(!$inserirMunicipioAbrangido)
										throw new Exception("Erro ao inserir os municípios abrangidos da serventia ".$value['codServentia']." - ".$value['descricao'].".");
								}
							}
						}
					}
		
					/*Inserir os dados da(s) competencia(s) da serventia*/
					if(isset($value['competencias'])){
						$trimCompetencia = trim($value['competencias']);
						if(!empty($trimCompetencia)){
							$excluirCompetencia = $this->sql->excluirCompetencia($value['codServentia']);
							if(count(explode(',', $value['competencias'])) > 1 || count(explode('.', $value['competencias'])) > 1){
								$competenciaSeparadas = NULL;
								if(count(explode('.', $value['competencias'])) > 1)
									$competenciaSeparadas = explode('.', $value['competencias']);
								else 
									$competenciaSeparadas = explode(',', $value['competencias']);
								
								foreach ($competenciaSeparadas as $comp) {
									$competenciaValida = $this->sql->listaCompetencia($comp);
									if(!$competenciaValida)
										throw new Exception("Erro ao inserir as competencias da serventia ".$value['codServentia']." - ".$value['descricao']." a competencia informada não é válida.");
									
									$inserirCompetencia = $this->sql->inserirCompetencia($orgao,$comp);
									if(!$inserirCompetencia)
										throw new Exception("Erro ao inserir as competencias da serventia ".$value['codServentia']." - ".$value['descricao'].".");
								}
							}else{
								if(!empty($value['competencias'])){
									$competenciaValida = $this->sql->listaCompetencia($value['competencias']);
									if(!$competenciaValida)
										throw new Exception("Erro ao inserir as competencias da serventia ".$value['codServentia']." - ".$value['descricao']." a competencia informada não é válida.");
									
									$inserirCompetencia = $this->sql->inserirCompetencia($orgao,$value['competencias']);
									if(!$inserirCompetencia)
										throw new Exception("Erro ao inserir as competencias da serventia ".$value['codServentia']." - ".$value['descricao'].".");
								}
							}
						}
					}
					
					/*Inserir os dados da força de trabalho da serventia*/
					$i = count($meuArray[2]) - count($perguntas);
					$mes = $this->util->qualMesNumero(date('m'));
					$ultimoDia = date('Y')."-".date('m')."-".date("t", mktime(0,0,0,date('m'),'01',date('Y')));
					foreach ($perguntas as $valuePergTrab) {
						if($valuePergTrab['grau'] == 1){
							if(count(explode(",", $value[$valuePergTrab['sigla']])) > 1 || count(explode(".", $value[$valuePergTrab['sigla']])) > 1 || $value[$valuePergTrab['sigla']] < 0 )
								throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value['codServentia']." - ".$value['descricao']." o valor não pode ser negativo ou fracionário.");
							
							if(!empty($value[$valuePergTrab['sigla']]) && !is_numeric($value[$valuePergTrab['sigla']]))
								throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value['codServentia']." - ".$value['descricao']." o valor não pode ser negativo ou fracionário.");
								
							$prodServentia = $this->sql->retornaProdServentiaPrmGrau($orgao,$mes,date('Y'));
							if(empty($prodServentia)){
								$prodServentia = $this->sql->inserirProdServentiaPrmGrau($orgao,NULL,date('Y'),date('m'),$ultimoDia);
							}else{
								$prodServentia = $this->sql->atualizarProdServentiaPrmGrau($orgao,NULL,date('Y'),date('m'),date('m'),$ultimoDia,$prodServentia);
							}
								
							$seqRespostaPrmGrau = $this->sql->retornaSeqResposta($prodServentia,$valuePergTrab['seqPergunta']);
							if(!empty($seqRespostaPrmGrau)){
								$respServentia = $this->sql->atualizarRespServentiaPrmGrau($value[$valuePergTrab['sigla']],$seqRespostaPrmGrau);
							}else{
								$respServentia = $this->sql->inserirRespServentiaPrmGrau($prodServentia,$valuePergTrab['seqPergunta'],$value[$valuePergTrab['sigla']]);
							}
							if(!$respServentia)
								throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value['codServentia']." - ".$value['descricao'].".");
						}else{
							if( count(explode(",", $value[$valuePergTrab['sigla']])) > 1 || count(explode(".", $value[$valuePergTrab['sigla']])) > 1 || $value[$valuePergTrab['sigla']] < 0)
								throw new Exception("Ocorreu um erro ao salvar as respostas da força de trabalho da serventia de código ".$value['codServentia']." - ".$numero.$descricao." o valor não pode ser negativo ou fracionário.");
							
							$seqProdutividadeSegGrau = $this->sqlSeg->retornaProdutividadeServentiaSegGrau($orgao,$mes,date('Y'));
							if(empty($seqProdutividadeSegGrau)){
								$seqProdutividadeSegGrau = $this->sqlSeg->inserirProdutividadeServentiaSegGrau($orgao,$mes,date('Y'),date('m'),NULL);
							}else{
								$seqProdutividadeSegGrau = $this->sqlSeg->alterarProdutividadeServentiaSegGrau($seqProdutividadeSegGrau,NULL);
							}
							if(empty($seqProdutividadeSegGrau)){
								throw new Exception("Ocorreu um erro ao salvar os dados do xls da força de trabalho da serventia de código ".$value['codServentia']." - ".$value['descricao'].".");
							}
							
							$seqRespostaSegGrau = $this->sqlSeg->retornaSeqResposta($seqProdutividadeSegGrau,$pergServ['seqPergunta']);
							if(!empty($seqRespostaSegGrau)){
								$respServentia = $this->sqlSeg->atualizarRespostaSegGrau($value[$valuePergTrab['sigla']],$seqRespostaSegGrau);
							}else{
								$respServentia = $this->sqlSeg->inserirRespostaSegGrau($seqProdutividadeSegGrau,$valuePergTrab['seqPergunta'],$value[$valuePergTrab['sigla']]);
							}
							if(!$respServentia)
								throw new Exception("Ocorreu um erro ao salvar as respostas da produtividade da serventia de código ".$value['codServentia']." - ".$value['descricao'].".");
						}
						$i++;
					}
				}
				$ref++;
			}
			
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'Dados do xls do Primeiro Passo gravados com sucesso.';
	
		}catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		unlink($_POST['caminhoXls']);
		$parametros['aba'] = 1;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
}
?>
