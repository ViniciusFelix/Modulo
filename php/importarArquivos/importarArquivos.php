<?php
class importarArquivos{

	function importarArquivos($db, $smarty)
	{
		libxml_use_internal_errors(true);
		$this->db = $db;
		$this->smarty = $smarty;
		
		include_once("sql/importarArquivos/bdImportarArquivos.php");
		$this->sql = new bdImportarArquivos($db);
		
		include_once("sql/segGrau/bdSegGrau.php");
		$this->sqlSeg = new bdSegGrau($db);
		
		include_once("sql/magistrado/bdmagistrado.php");
		$this->sqlMag = new bdmagistrado($db);
		
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	}
	
	/**
	 * Método construtor do html da pagina inicio.
	 */
	function Inicio()
	{
		return true;
	}

	/**
	 * Método para gerar o xls.
	 * @param $res
	 * @param $cabecalho
	 */
	function gerarXLS($res, $cabecalho) 
	{
        $xls = new Excel_XML;
        $xls->cabecalho($cabecalho);
        $xls->addArray($res);
        $xls->generateXML("relatorio");
        die();
    }
	
	/**
	 * Método construtor do html da pagina principal.
	 */
	function formPrincipal()
	{
		permissao($_POST);
		if(isset($_POST['limpaPost']) == 1){
			if(!empty($_POST['nomeDocumento'])){
				unlink($this->util->caminho_absoluto1Grau.$_POST['nomeDocumento']);
			}
		}
		
		$this->smarty->assign('meses',$this->util->mesesAno());
		
		$ano = $this->sql->buscaAnos();
		$this->smarty->assign('ano',$ano);
	}
	
	/**
	 * Método valida o xml de todos os passos.
	 */
	function validarXml()
	{
		permissao($_POST);
		if(!$_FILES['arquivo_xml']['name']){
			$parametros['aba'] = $_POST['passo'];
			$parametros['msgErro'] = 'Nenhum arquivo foi selecionado.';
			$parametros['a'] = 'importarArquivos';
			$parametros['d'] = 'importarArquivos';
			$parametros['f'] = 'formPrincipal';
			$parametros['token'] = $_POST['token'];
			$this->token->redirect($parametros);
		}
		
		$caminhoXml = $this->util->caminho_absoluto1Grau.$_FILES['arquivo_xml']['name'];
		if (! empty($_FILES['arquivo_xml']['name'])) {
			move_uploaded_file($_FILES['arquivo_xml']['tmp_name'], $this->util->caminho_absoluto1Grau.$_FILES['arquivo_xml']['name']);
		}
		
		$xml = new DOMDocument();
		$xml->load($this->util->caminho_absoluto1Grau.$_FILES['arquivo_xml']['name']);
		
		/*Switch para os passos da importação do xml.*/
		switch ($_POST['passo']){
			case 1:
				$caminhoValidacao = "php/validador/validacaoXmlPrm".$_SESSION['tipoArquivo'].".xsd";
				if ($xml->schemaValidate($caminhoValidacao)) {
				   	$parametros['validoXmlPrimeiro'] = true;
					$parametros['caminhoXml'] = $caminhoXml;
				   	$parametros['nomeXml'] = $_FILES['arquivo_xml']['name'];
				}
				else {
					unlink($caminhoXml);
					$errors = libxml_get_errors();
				   	$parametros['msgErro'] = 'Formatação do xml invalida, erro na linha '.$errors[0]->line;
				}
				break;
			case 3:
				if ($xml->schemaValidate('php/validador/validacaoXmlTerceiroPasso.xsd')) {
					$parametros['validoTerXml'] = true;
				   	$parametros['caminhoXml'] = $caminhoXml;
				   	$parametros['nomeXml'] = $_FILES['arquivo_xml']['name'];
				}
				else {
					unlink($caminhoXml);
				   	$errors = libxml_get_errors();
				   	$parametros['msgErro'] = 'Formatação do xml invalida, erro na linha '.$errors[0]->line;
				}
				break;
			case 4:
				if ($xml->schemaValidate('php/validador/validacaoXmlQuartoPasso.xsd')) {
					$parametros['validoQuarXml']  = true;
				   	$parametros['caminhoXml'] = $caminhoXml;
				   	$parametros['nomeXml'] = $_FILES['arquivo_xml']['name'];
				}
				else {
					unlink($caminhoXml);
				   	$errors = libxml_get_errors();
				   	$parametros['msgErro'] = 'Formatação do xml invalida, erro na linha '.$errors[0]->line;
				}
				break;
		}	
		
		$parametros['aba'] = $_POST['passo'];
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Mètodo para válidar dados do xls.
	 */
	function validaXls()
	{
		permissao($_POST);
		try {
			if(!$_FILES['arquivo_xls']['name']){
				$parametros['aba'] = $_POST['passo'];
				$parametros['msgErro'] = 'Nenhum arquivo foi selecionado.';
				$parametros['a'] = 'importarArquivos';
				$parametros['d'] = 'importarArquivos';
				$parametros['f'] = 'formPrincipal';
				$parametros['token'] = $_POST['token'];
				$this->token->redirect($parametros);
			}
			
			if($_POST['passo'] == 3){
				$nomeArquivo = "terceiroPasso_".$_SESSION['sig_tribunal']."_".date('d').date('m').date('Y').date('H').date('m').date('s').".xls";
				$caminhoXls = $this->util->caminho_absoluto1Grau.$nomeArquivo;
			}else if($_POST['passo'] == 4){	
				$nomeArquivo = "quartoPasso_".$_SESSION['sig_tribunal']."_".date('d').date('m').date('Y').date('H').date('m').date('s').".xls";
				$caminhoXls = $this->util->caminho_absoluto1Grau.$nomeArquivo;
			}else{
				$nomeArquivo = $_FILES['arquivo_xls']['name'];
				$caminhoXls = $this->util->caminho_absoluto1Grau.$_FILES['arquivo_xls']['name'];
			}
			
			if (! empty($_FILES['arquivo_xls']['name'])) {
				move_uploaded_file($_FILES['arquivo_xls']['tmp_name'], $caminhoXls);
			}
			$data = new Spreadsheet_Excel_Reader($caminhoXls);
			
			$linhas = $data->rowcount();
			$colunas= $data->colcount();
			
			for($i = 1; $i == 1; $i++){
				for($j = 1; $j <= $colunas; $j++){
					if(trim($data->val($i,$j)) != '')
						$meuArray[$j] = $data->val(1,$j);
				}
			}
			
			if($_POST['passo'] == 1){
				$listaPerguntaTrabalhoPrmGrau = $this->sql->listaPerguntaServentiaTrabalhoPrmGrau();
				$listaPerguntaTrabalhoSegGrau = $this->sqlSeg->listaPerguntaServentiaTrabalhoSegGrau();
				$perguntas = array_merge($listaPerguntaTrabalhoPrmGrau, $listaPerguntaTrabalhoSegGrau);
				$pergunta = $this->util->array_sort($perguntas,'NUM_ORDEM');
				$countPergunta = count($pergunta);
				if($_SESSION['tipoArquivo'] == 1){
					$tamanho = 12+$countPergunta;
				}else if($_SESSION['tipoArquivo'] == 2){
					$tamanho = 11+$countPergunta;
				}else if($_SESSION['tipoArquivo'] == 3){
					$tamanho = 3+$countPergunta;
				}else if($_SESSION['tipoArquivo'] == 4){
					$tamanho = 4+$countPergunta;
				}
				$passoHtml = "validoXlsPrimeiro";
			}else if($_POST['passo'] == 3){
				$listaPerguntaServentiaPrmGrau = $this->sql->listaPerguntaServentiaPrmGrau();
				$listaPerguntaServentiaSegGrau = $this->sqlSeg->listaPerguntaServentiaSegGrau();
				$pergunta = array_merge($listaPerguntaServentiaPrmGrau, $listaPerguntaServentiaSegGrau);
				$countPergunta = count($pergunta);
				$tamanho = 4+$countPergunta;
				$passoHtml = "validoXlsTerceiro";
			}else if($_POST['passo'] == 4){
				$listaPerguntaMagistradoPrmGrau = $this->sql->listaPerguntaMagistradoPrmGrau();
				$listaPerguntaMagistradoSegGrau = $this->sqlSeg->listaPerguntaMagistradoSegGrau();
				$pergunta = array_merge($listaPerguntaMagistradoPrmGrau, $listaPerguntaMagistradoSegGrau);
				$countPergunta = count($pergunta);
				$tamanho = 7+$countPergunta;
				$passoHtml = "validoQuarXls";
			}
			
			$parametros['aba'] = $_POST['passo'];
			for ($i = 1; $i <= $tamanho; $i++) {
				$cabecalhoModelo = $this->util->campoXls($_POST['passo'],$_SESSION['tipoArquivo'],$i,$pergunta);
				if(utf8_decode($cabecalhoModelo) == utf8_decode($this->util->precisaUtf8($meuArray[$i]))){
					$parametros[$passoHtml] = true;
					$parametros['caminhoXls'] = $caminhoXls;
					$parametros['nomeXls'] = $nomeArquivo;
				}else{
					throw new Exception("Dados do xls de formato inválido na coluna '.$cabecalhoModelo.' escrita errada ou não existe.");
				}
			}
			
			if($_POST['passo'] == 3){
				$this->validacaoTerceiroPassoXls($caminhoXls);
			}
			
			if($_POST['passo'] == 4){
				$this->validacaoQuartoPassoXls($caminhoXls);
			}
		}catch (Exception $e) {
			$parametros[$passoHtml] = null;
			unlink($caminhoXls);
			$parametros['msgErro'] = ( $e->getMessage(). "\n");
		}

		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Valida os dados do arquivo do terceiro passo.
	 * @throws Exception
	 */
	function validacaoTerceiroPassoXls($caminhoXls)
	{
		$data = new Spreadsheet_Excel_Reader($caminhoXls);
		$linhas = $data->rowcount();
		$colunas= $data->colcount();
		$listaPerguntaServentiaPrmGrau = $this->sql->listaPerguntaServentiaPrmGrau();
		$listaPerguntaServentiaSegGrau = $this->sqlSeg->listaPerguntaServentiaSegGrau();
		$pergunta = array_merge($listaPerguntaServentiaPrmGrau, $listaPerguntaServentiaSegGrau);
		$perguntaServentia = $this->util->array_sort($pergunta,'NUM_ORDEM');
		$ref = $this->util->referenciaImportacao(3,null,$perguntaServentia);
		for($i = 2; $i <= $linhas; $i++){
			for($j = 1; $j <= $colunas; $j++){
				$meuArray[$i][$ref[$j]] = $data->val($i,$j);
			}
		}
		
		foreach ($meuArray as $key => $value) {
			if($key > 2 && !is_numeric($value['codServentia']) && empty($value['codServentia'])){
				break;
			}else{
				/*Ano informado não pode ser menor que 2015*/
				if($value['ano'] < '2015')
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value['codServentia']." na linha ".$key++." ano informado não pode ser menor que 2015.");
					
				/*Ano informado não pode ser maior que o atual*/
				if($value['ano'] > date("Y"))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value['codServentia']." na linha ".$key++." ano informado maior que o ano corrente.");
						
				/*Verifica se o mes é válido*/
				$mes = $this->util->qualMesNumero($value['mes']);
				if(empty($mes))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value['codServentia']." na linha ".$key++." o campo mês deve estar entre 1 e 12.");
			
				/*Verifica se orgão é do tribunal logado*/
				$orgao = $this->sql->retornaServentia($value['codServentia']);
				if(empty($orgao))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value['codServentia']." na linha ".$key++." serventia não pertence a esse tribunal, favor verificar se o código está correto.");
				
				/*Verifica se as respostas aos indicativos são validas*/
				foreach ($perguntaServentia as $pergServ) {
					if($value[$pergServ['sigla']] != NULL){
						$eNumero = $this->util->temPontoOuVirgula($value[$pergServ['sigla']]);
						if($value[$pergServ['sigla']] < 0 || !$eNumero || !is_numeric($value[$pergServ['sigla']]))
							throw new Exception("Dados do xls de formato inválido na coluna ".$pergServ['sigla']." do seq_serventia ".$value['codServentia']." mes ".$value['mes']." e ano ".$value['ano']." na linha ".$key++." a resposta deve deve ser inteiro positivo, zero ou nulo.");
					}
				}
			}
		}
	}
	
	/**
	 * Valida os dados do arquivo do quarto passo.
	 * @throws Exception
	 */
	function validacaoQuartoPassoXls($caminhoXls)
	{
		$data = new Spreadsheet_Excel_Reader($caminhoXls);
		$linhas = $data->rowcount();
		$colunas= $data->colcount();
		$listaPerguntaMagistradoPrmGrau = $this->sql->listaPerguntaMagistradoPrmGrau();
		$listaPerguntaMagistradoSegGrau = $this->sqlSeg->listaPerguntaMagistradoSegGrau();
		$perguntas = array_merge($listaPerguntaMagistradoPrmGrau, $listaPerguntaMagistradoSegGrau);
		$perguntaMagistrado = $this->util->array_sort($perguntas,'NUM_ORDEM');
		$ref = $this->util->referenciaImportacao(4,NULL,$perguntaMagistrado);
		for($i = 2; $i <= $linhas; $i++){
			for($j = 1; $j <= $colunas; $j++){
				$meuArray[$i][$ref[$j]] = $data->val($i,$j);
			}
		}
		
		foreach ($meuArray as $key => $value) {
			if($key > 2 && !is_numeric($value['codServentia']) && empty($value['codServentia'])){
				break;
			}else{
				/*Ano informado não pode ser menor que 2015*/
				if($value['ano'] < '2015')
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value['codServentia']." na linha ".$key++." ano informado não pode ser menor que 2015.");
				
				/*Ano informado não pode ser maior que o atual*/
				if($value['ano'] > date("Y"))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia de código ".$value['codServentia']." na linha ".$key++." ano informado maior que o ano corrente.");
					
				/*Verifica se o orgão é válido*/
				$orgao = $this->sql->retornaServentia($value['codServentia']);
				if(empty($orgao) || empty($value['codServentia']))
					throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia ".$value['codServentia']." do magistrado ".$value['codMag']." na linha ".$key++.", orgão não encontrado favor verificar o código.");
				
				/*Verifica se o mes é válido*/
				$mesValido = $this->util->qualMesNumero($value['mes']);
				if(empty($mesValido))
					throw new Exception("Ocorreu um erro ao salvar os dados do xls no magistrado ".$value['codMag']." na serventia ".$value['codServentia']." na linha ".$key++." mês informado é invalido.");
				
				/*Verifica se o tipo informado é válido*/
				if(!empty($value['codMag'])){
					/*Verifica se o magistrado é válido*/
					$magistrado = $this->sqlMag->buscarMagistradoPeloSeq($value['codMag']);
					if(empty($magistrado))
						throw new Exception("Ocorreu um erro ao salvar a produtividade da serventia ".$value['codServentia']." do magistrado ".$value['codMag']." na linha ".$key++.", magistrado não encontrado favor verificar o código.");
					
					if(is_numeric($value['tipo'])){
						$tipoValido = $this->sql->retornaTipoMagistrado($value['tipo']);
						if(empty($tipoValido))
							throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xls da no magistrado ".$value['codMag']." na serventia ".$value['codServentia']." na linha ".$key++." tipo magistrado não é válida");
					}else{
						throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xls da no magistrado ".$value['codMag']." na serventia ".$value['codServentia']." na linha ".$key++." tipo magistrado não é válida");
					}
				}
				
				/*Verifica se o quantidade dias corridos é número*/
				$qtdDias = trim($value['qtdDias']);
				if($qtdDias != "" && !is_numeric($qtdDias))
					throw new Exception("Ocorreu um erro ao salvar os dados das resposta do xls da no magistrado ".$value['codMag']." na serventia ".$value['codServentia']." na linha ".$key++." quantidade de dias corridos deve ser numérico inteiro.");
			
				/*Verifica se a resposta do indicador é inteiro, nula ou diferente de negativo*/
				$eNumero = null;
				foreach ($perguntaMagistrado as $pergMag) {
					if($value[$pergMag['sigla']] != NULL){
						$eNumero = $this->util->temPontoOuVirgula($value[$pergMag['sigla']]);
						if($value[$pergMag['sigla']] < 0 || !$eNumero || !is_numeric($value[$pergMag['sigla']]))
							throw new Exception("O indicador ".$pergMag['sigla']." da serventia ".$value['codServentia']." do magistrado ".$value['codMag']." deve ser inteiro positivo, zero ou nulo.");
					}
				}
			}
		}
	}
	
	/**
	 * Método para baixa o arquivo.
	 */
	function baixaArquivo()
	{
		$this->util->baixarDocumento($this->util->caminho_absoluto1Grau,$_POST['nomeDocumento']);
	}
	
	/**
	 * Método para excluir o arquivo
	 */
	function excluirArquivo()
	{
		unlink($this->util->caminho_absoluto1Grau.$_POST['nomeDocumento']);

		$parametros['aba']  = $_POST['aba'];
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_SESSION['token'];
 		$this->token->redirect($parametros);
	}
}
?>
