<?php
class magistrado
{
	function magistrado($db, $smarty)
	{
        libxml_use_internal_errors(true);
		$this->db = $db;
		$this->smarty = $smarty;
		
		include_once("php/importarArquivos/importarArquivos.php");
		$this->importarArquivos = new importarArquivos($db, $smarty);

		include_once("sql/magistrado/bdmagistrado.php");
		$this->sql = new bdmagistrado($db);
		
		include_once("sql/importarArquivos/bdImportarArquivos.php");
		$this->sqlPrm = new bdImportarArquivos($db);
		
		include_once("sql/consulta/bdconsulta.php");
		$this->sqlCons = new bdconsulta($db);
		
		include_once("php/util.php");
		$this->util = new util($smarty);
		
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
	}
	
	/**
	 * Método monta exemplo xml do magistrado
	 */
	function exemploXmlMagistrado()
	{
		$dom = new DOMDocument("1.0", "ISO-8859-1");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		
		$root = $dom->createElement("CNJ_MAGISTRADO");
		$row = $dom->createElement("ROW");
		
		$cabecalho = $this->util->campoXml(2);
		$excplicacao = $this->util->explicacaoCampo(2,NULL,NULL,'xml');
		foreach ($cabecalho as $key => $valueCab) {
			$row->appendChild($dom->createElement($valueCab, utf8_encode($excplicacao[$key])));
		}
		$root->appendChild($row);
		$dom->appendChild($root);
		
		header("Content-Type: text/xml");
		print $dom->saveXML();exit;
	}
	
	/**
	 * Método monta exemplo do xls do magistrado.
	 */
	function exemploXlsMagistrado()
	{
		$objPHPExcel = new PHPExcel();
		$cabecalho = $this->util->campoXls(2);
		foreach ($cabecalho as $key => $valueCab) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(64+$key).'1', utf8_encode($valueCab));
		}
		$excplicacao = $this->util->explicacaoCampo(2,NULL,NULL,'xls');
		foreach ($excplicacao as $key => $valueExplicacao) {
			$objPHPExcel->getActiveSheet()->getColumnDimension(chr(64+$key))->setWidth(40);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($key-1, 2, utf8_encode($valueExplicacao));
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
		header('Content-Disposition: attachment;filename="exemploSegundoPasso.xls"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
	/**
	 * Método monta a planilha de todos os magistrados do seu estado.
	 */
	function planilhaMagistradoEstado()
	{
		$serventia = $this->sqlPrm->retornaServentia();
		if($serventia){
			foreach ($serventia as $key => $value) {
				$seqOrgao[] = $value['seq_corporativo'].',';
			}
			$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		}
		
		$listaMagistradoTribunal = $this->sqlCons->retornaMagistradoServentia($seqOrgao,true);
		if(isset($_POST['salvar'])){
			$arrayDividido = array_chunk($listaMagistradoTribunal,500);
			$arq = 0;
			foreach ($arrayDividido as $keyDiv => $valueDiv) {
				$objPHPExcel = new PHPExcel();
				$cabecalho = $this->util->campoXls(2,'rel');
				foreach ($cabecalho as $key => $valueCab) {
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(64+$key).'1', utf8_encode($valueCab));
				}
				$i = 2;
				foreach ($valueDiv as $mag) {
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $mag['SEQ_MAGISTRADO']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $mag['NUM_CPF_MAGISTRADO']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $i, utf8_encode($mag['Nome']));
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $i, $mag['NUM_MATRICULA']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $i, $mag['IND_UF']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $i, date('d/m/Y', strtotime($mag['DAT_NASCIMENTO'])));
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $i, $mag['DSC_EMAIL_JUIZ']);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $i, $mag['NUM_TELEFONE_FIXO_JUIZ']);
					if($mag['DAT_INGRESSO_MAGISTRATURA'] != '0000-00-00'){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $i, date('d/m/Y', strtotime($mag['DAT_INGRESSO_MAGISTRATURA'])));
					}else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $i, '');
					}
					if(is_numeric($mag['IND_SEXO'])){
						if($mag['IND_SEXO'] == 0){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $i, 'M');
						}else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $i, 'F');
						}
					}else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $i, $mag['IND_SEXO']);
					}
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $i, $mag['FLG_STATUS']);
					$i++;
				}
					
				$objPHPExcel->getActiveSheet()->getStyle('E1:E'.$i)
							->getNumberFormat()
							->setFormatCode('General');
				$objPHPExcel->getActiveSheet()->getStyle('H1:H'.$i)
							->getNumberFormat()
							->setFormatCode('General');
					
				$styleArray = array(
						'font'  => array(
								'bold'  => true,
								'color' => array('rgb' => 'FFFFFF'),
								'name'  => 'Arial',
								'text-align' => 'center'
						));
				
				$ultimaColuna = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
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
				$arquivosZip[$arq] = 'relatorioSegundoPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
				$nomeArquivo = 'relatorioSegundoPasso'.$_SESSION['sig_tribunal'].$arq.'.xls';
				$salvar = $this->util->caminho_absoluto2Grau.$nomeArquivo;
				$objWriter->save($salvar);
				$arq++;
			}
			
			$zip = new ZipArchive();
			$nomeArquivoZip = "arquivosRelatorioSegundoPasso".$_SESSION['sig_tribunal'].".zip";
			if( $zip->open( 'php/zips/'.$nomeArquivoZip , ZipArchive::CREATE  )  === true){
				foreach ($arquivosZip as $arqZip) {
					$zip->addFile(  $this->util->caminho_absoluto2Grau.$arqZip , $arqZip );
				}
				$zip->close();
				
				header('Content-type: application/zip');
				header('Content-disposition: attachment; filename='.$nomeArquivoZip);
				readfile('php/zips/'.$nomeArquivoZip);
				
				foreach ($arquivosZip as $arqZip) {
					unlink($this->util->caminho_absoluto2Grau.$arqZip);
				}
				 
				unlink('php/zips/'.$nomeArquivoZip);
				die;
			}
		}else{
			$this->smarty->assign('listaMagistrado',$listaMagistradoTribunal);
			echo $this->smarty->fetch("importarArquivos/planilhaMagistrado.html");
			die;
		}
	}
	
	/**
	 * Mètodo para montar a planilha dos status do magistrado.
	 */
	function planilhaStatusMagistrado()
	{
		$statusMagistrado = $this->sqlPrm->retornaStatusMagistrado();
		if(isset($_POST['salvar'])){
			$this->importarArquivos->gerarXLS($statusMagistrado, "IDENTIFICACAO,DESCRICAO,DESCRICAO COMPLETA");
		}else{
			$this->smarty->assign('statusMagistrado',$statusMagistrado);
	
			echo $this->smarty->fetch("importarArquivos/planilhaStatusMagistrado.html");
			die;
		}
	}
	
	/**
	 * Método verifica se o xml está no formatação do padrão.
	 */
	function validarXml()
	{
		permissao($_POST);
		if(!$_FILES['arquivo_xml_mag']['name']){
			$parametros['aba'] = 2;
			$parametros['msgErro'] = 'Nenhum arquivo foi selecionado.';
			$parametros['a'] = 'importarArquivos';
			$parametros['d'] = 'importarArquivos';
			$parametros['f'] = 'formPrincipal';
			$parametros['token'] = $_POST['token'];
			$this->token->redirect($parametros);
		}
		$caminhoXml = $this->util->caminho_absolutoMagistrado.$_FILES['arquivo_xml_mag']['name'];
		
		if (! empty($_FILES['arquivo_xml_mag']['name'])) {
			move_uploaded_file($_FILES['arquivo_xml_mag']['tmp_name'], $this->util->caminho_absolutoMagistrado.$_FILES['arquivo_xml_mag']['name']);
		}
			
		$xml = new DOMDocument();
		$xml->load($this->util->caminho_absolutoMagistrado.$_FILES['arquivo_xml_mag']['name']);
		
		if ($xml->schemaValidate('php/validador/validacaoXmlMagistrado.xsd')) {
		   	$parametros['validoXml']  = 1;
		   	$parametros['caminhoXml'] = $caminhoXml;
		   	$parametros['nomeXml'] = $_FILES['arquivo_xml_mag']['name'];
		}else{
		   $errors = libxml_get_errors();
		   $parametros['msgErro'] = 'Formatação do xml invalida, erro na linha '.$errors[0]->line;
		   unlink($caminhoXml);
		}
		
		$parametros['aba'] = 2;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
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
			foreach ($xml as $key => $value) {
				if(empty($value->NUM_CPF_MAGISTRADO)){
					if($key == 1)
						throw new Exception("Ocorreu um erro ao salvar os dados do xls, arquivo vazio.");
					else
						break;
				}else{
					if(strtotime($value->DAT_NASCIMENTO) > strtotime($value->DAT_INGRESSO_MAGISTRATURA))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".$value[3]." Data Nascimento é maior que a Data posse.");
					
					if(strtotime($value->DAT_NASCIMENTO) > strtotime(date('Y-m-d')))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." DAT_NASCIMENTO é maior que a data atual.");
						
					if(strtotime($value->DAT_INGRESSO_MAGISTRATURA) > strtotime(date('Y-m-d')))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." DAT_INGRESSO_MAGISTRATURA é maior que a data atual.");
					
					$cpfValido = $this->util->validaCPF($value->NUM_CPF_MAGISTRADO);
					if(empty($cpfValido))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." CPF informado é inválido.");
					
					if(!is_numeric(trim($value->NUM_CPF_MAGISTRADO)))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." número de CPF com letras.");
					
					if(trim($value->SEXO) != "M" && trim($value->SEXO) != "F")
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." sexo informado é inválido.");
						
					$ufValida = $this->sql->retornaUf($value->IND_UF);
					if(empty($ufValida))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." UF informada é inválida.");
					
					if(!is_numeric(trim($value->NUM_TELEFONE_GABINETE)) || trim($value->NUM_TELEFONE_GABINETE) == '')
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." telefone informado contém letras nem caractéres especiais.");
						
					$statusValido = $this->sqlPrm->retornaStatusMagistrado(trim($value->STATUS));
					if(empty($statusValido))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO)." status informada é inválida.");
						
					$param['cpf'] 			= trim($value->NUM_CPF_MAGISTRADO);
					$param['nome'] 			= $this->util->precisaUtf8($value->NOM_MAGISTRADO);
					$param['matricula'] 	= trim($value->NUM_MATRICULA);
					$param['uf'] 			= trim($value->IND_UF);
					$param['datNascimento'] = trim($value->DAT_NASCIMENTO);
					$param['email'] 		= trim($value->DSC_EMAIL_INSTITUCIONAL);
					$param['telefone'] 		= trim($value->NUM_TELEFONE_GABINETE);
					$param['datPosse'] 		= trim($value->DAT_INGRESSO_MAGISTRATURA);
					$param['sexo'] 			= trim($value->SEXO);
					$param['status'] 		= trim($value->STATUS);
					
					$param['datNascimento'] = $this->util->converterDataParaBanco($param['datNascimento']);
					$param['datPosse'] = $this->util->converterDataParaBanco($param['datPosse']);
					$param['SEQ_MAGISTRADO'] = $this->sql->buscarMagistrado($param['cpf']);
					
					if($param['SEQ_MAGISTRADO']){
						$alterarMagistrado = $this->sql->alterarMagistrado($param);
						if(!$alterarMagistrado)
							throw new Exception("Ocorreu um erro ao alterar os dados do magistrado ".utf8_decode($value->NOM_MAGISTRADO).".");
						
						$magistradoVinculadoOrgao = $this->sql->magistradoVinculadoOrgao($param['SEQ_MAGISTRADO']);
						if(empty($magistradoVinculadoOrgao)){
							$vincularOrgao = $this->sql->vincularOrgao($alterarMagistrado);
							if(!$vincularOrgao)
								throw new Exception("Ocorreu um erro ao alterar os dados do magistrado ".utf8_decode($value->NOM_MAGISTRADO).".");
						}
					}else{
						$inserirMagistrado = $this->sql->inserirMagistrado($param);
						if(!$inserirMagistrado)
							throw new Exception("Ocorreu um erro ao salvar os dados do xls ".utf8_decode($value->NOM_MAGISTRADO).".");
						
						$magistradoVinculadoOrgao = $this->sql->magistradoVinculadoOrgao($param['SEQ_MAGISTRADO']);
						if(empty($magistradoVinculadoOrgao)){
							$vincularOrgao = $this->sql->vincularOrgao($inserirMagistrado);
							if(!$vincularOrgao)
								throw new Exception("Ocorreu um erro ao alterar os dados do magistrado ".utf8_decode($value->NOM_MAGISTRADO).".");
						}
					}
				}
			}
			
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'Dados do xml gravados com sucesso.';
		}catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		$parametros['aba'] = 2;
		unlink($_POST['caminhoXml']);
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 *Método verifica se xls está na formatação do modelo.
	 */
	function validaXls()
	{
		permissao($_POST);
		if(!$_FILES['arquivo_xls_mag']['name']){
			$parametros['aba'] = 2;
			$parametros['msgErro'] = 'Nenhum arquivo foi selecionado.';
			$parametros['a'] = 'importarArquivos';
			$parametros['d'] = 'importarArquivos';
			$parametros['f'] = 'formPrincipal';
			$parametros['token'] = $_POST['token'];
			$this->token->redirect($parametros);
		}
    	$caminhoXls = $this->util->caminho_absolutoMagistrado.$_FILES['arquivo_xls_mag']['name'];
		
		if (! empty($_FILES['arquivo_xls_mag']['name'])) {
			move_uploaded_file($_FILES['arquivo_xls_mag']['tmp_name'], $caminhoXls);
        }
		$data = new Spreadsheet_Excel_Reader($this->util->caminho_absolutoMagistrado.$_FILES['arquivo_xls_mag']['name']);
		
		$linhas = $data->rowcount();
		$colunas= $data->colcount();
		
		for($i = 1; $i <= $linhas; $i++){
		    for($j = 1; $j <= $colunas; $j++){
		    	if(trim($data->val($i,$j)) != '')
		        	$meuArray[$i][$j] = $data->val($i,$j);
		    }
		}
		
		$parametros['aba'] = 2;
		$tamanhoCabecalho = $this->util->campoXls(2,NULL,NULL,NULL);
		for ($i = 1; $i <= count($tamanhoCabecalho); $i++) {
			$cabecalhoModelo = $this->util->campoXls(2,NULL,$i,NULL);
			if($cabecalhoModelo != 'Codigo Magistrado'){
				if($cabecalhoModelo != $meuArray[1][$i]){
					$parametros['validoXls'] = null;
					unlink($caminhoXls);
					$parametros['msgErro'] = 'Dados do xls de formato inválido na coluna '.$cabecalhoModelo.' escrita errada ou não existe.';
					break;
				}else{
					$parametros['validoXls'] = true;
					$parametros['caminhoXls'] = $caminhoXls;
					$parametros['nomeXls'] = $_FILES['arquivo_xls_mag']['name'];
				}
			}
		}
		
		$parametros['aba'] = 2;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método salvar os dados no xls informado.
	 */
	function salvarXls()
	{
		permissao($_POST);
		$data = new Spreadsheet_Excel_Reader($_POST['caminhoXls']);

		$linhas = $data->rowcount();
		$colunas= $data->colcount();
		
		$ref = $this->util->referenciaImportacao(2,NULL,NULL);
		for($i = 2; $i <= $linhas; $i++){
		    for($j = 1; $j <= count($ref); $j++){
		        $meuArray[$i][$ref[$j]] = $data->val($i,$j);
		    }
		}

		try {
			$this->db->BeginTrans();
			foreach ($meuArray as $key => $value){
				if(empty($value['cpf'])){
					if($key == 1)
						throw new Exception("Ocorreu um erro ao salvar os dados do xls, arquivo vazio.");
				}else{
					$cpf = trim($value['cpf']);
					if(empty($cpf))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." CPF não preenchido.");
					
					$nome = trim($value['nome']);
					if(empty($nome))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." Nome não preenchido.");
					
					$dataNascimentoValida = $this->util->converterDataParaBancoXls($value['datNascimento'],$data->sheets[0]['cellsInfo'][$key][5]);
					if(empty($dataNascimentoValida))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." Data Nascimento é inválida.");
					
					$dataPosseValida = $this->util->converterDataParaBancoXls($value['datPosse'],$data->sheets[0]['cellsInfo'][$key][8]);
					if(empty($dataPosseValida))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." Data Posse é inválida.");
					
					if(strtotime($dataNascimentoValida) > strtotime($dataPosseValida))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." Data Nascimento é maior que a Data posse.");

					if(strtotime($dataNascimentoValida) > strtotime(date('Y-m-d')))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." Data Nascimento é maior que a data atual.");
					
					if(strtotime($dataPosseValida) > strtotime(date('Y-m-d')))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." Data posse é maior que a data atual.");
						
					$cpfValido = $this->util->validaCPF($value['cpf']);
					if(empty($cpfValido))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls ".trim($value['nome'])." CPF informado é inválido.");
					
					if(!is_numeric($value['telefone']))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome'])." número do telefone não pode ter caractéres especiais.");
						
					if(!is_numeric($value['cpf']))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome'])." número de CPF com letras.");
					
					if(strtoupper($value['sexo']) != "M" && strtoupper($value['sexo']) != "F")
						throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome'])." sexo informado é inválido.");
							
					$ufValida = $this->sql->retornaUf($value['uf']);
					if(empty($ufValida))
						throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome'])." UF informada é inválida.");
								
					if(!is_numeric($value['telefone']) && $value['telefone'] == '')
						throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome'])." telefone informado contém letras ou esta vazio.");
					
					if($value['status'] != 0){
						$statusValido = $this->sqlPrm->retornaStatusMagistrado($value['status']);
						if(empty($statusValido) || empty($value['status']))
							throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome'])." status informada é inválida.");
					}
						
					$value['cpf']			= $cpfValido;
					$value['datNascimento'] = $dataNascimentoValida;
					$value['datPosse']		= $dataPosseValida;
					$value['SEQ_MAGISTRADO'] = $this->sql->buscarMagistrado($value['cpf']);
					
					if($value['SEQ_MAGISTRADO']){
						$alterarMagistrado = $this->sql->alterarMagistrado($value);
						if(!$alterarMagistrado)
							throw new Exception("Ocorreu um erro ao alterar os dados do magistrado ".trim($value['nome']).".");
						
						$magistradoVinculadoOrgao = $this->sql->magistradoVinculadoOrgao($value['SEQ_MAGISTRADO']);
						if(empty($magistradoVinculadoOrgao)){
							$vincularOrgao = $this->sql->vincularOrgao($alterarMagistrado);
							if(!$vincularOrgao)
								throw new Exception("Ocorreu um erro ao alterar os dados do magistrado ".trim($value['nome']).".");
						}
					}else{
						$inserirMagistrado = $this->sql->inserirMagistrado($value);
						if(empty($inserirMagistrado))
							throw new Exception("Ocorreu um erro ao salvar os dados do xls do magistrado ".trim($value['nome']).".");
						
						$magistradoVinculadoOrgao = $this->sql->magistradoVinculadoOrgao($value['SEQ_MAGISTRADO']);
						if(empty($magistradoVinculadoOrgao)){
							$vincularOrgao = $this->sql->vincularOrgao($inserirMagistrado);
							if(!$vincularOrgao)
								throw new Exception("Ocorreu um erro ao alterar os dados do magistrado ".trim($value['nome']).".");
						}
					}
				}
			}
			$this->db->CommitTrans();
			$parametros['msgSucesso'] = 'Dados do xls gravados com sucesso.';
		}
		catch (Exception $e) {
			$this->db->RollbackTrans();
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}
		
		unlink($_POST['caminhoXls']);
		$parametros['aba'] = 2;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método para baixa o arquivo.
	 */
	function baixaArquivo()
	{
		$this->util->baixarDocumento($this->util->caminho_absolutoMagistrado,$_POST['nomeDocumento']);
	}
	
	/**
	 * Método para excluir o arquivo
	 */
	function excluirArquivo()
	{
		unlink($this->util->caminho_absolutoMagistrado.$_POST['nomeDocumento']);
		
		$parametros['aba'] = 2;
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['security_token'];
		$this->token->redirect($parametros);
	}
}
?>
