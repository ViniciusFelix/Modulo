<?php
class consulta{
	function consulta($db, $smarty)
	{
		$this->db = $db;
		$this->smarty = $smarty;
		
		include_once("php/util.php");
		$this->util = new util($smarty);
	
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
	
		include_once("sql/consulta/bdconsulta.php");
		$this->sql = new bdconsulta($db);
		
		include_once("sql/importarArquivos/bdImportarArquivos.php");
		$this->sqlPrm = new bdImportarArquivos($db);
	
		include_once("php/importarArquivos/terceiroPasso.php");
		$this->terceiroPasso = new terceiroPasso($db,$smarty);
		
		include_once("php/importarArquivos/quartoPasso.php");
		$this->quartoPasso = new quartoPasso($db,$smarty);
	}
	
	/**
	 * Método para gerar os recibos de imporatação de todos os passo.
	 */
	function gerarComprovanteImportação()
	{
		if($_POST['passo'] == 1){
			$textoTop = $_SESSION['dsc_tribunal'].'<br />
						Dados importados do primeiro passo no ano de '.$_POST['ano'];
			$caminhoHTML = 'consulta/PDFreciboPrimeiroPasso.html';
			$conteudo = $this->sql->reciboPrimeiroPasso($_POST['ano']);
		}else if($_POST['passo'] == 2){
			$textoTop = $_SESSION['dsc_tribunal'].'<br />
						Dados importados do segundo passo no ano de '.$_POST['ano'];
			$caminhoHTML = 'consulta/PDFreciboSegundoPasso.html';
			$serventia = $this->sqlPrm->retornaServentia();
			$conteudo = $this->sql->reciboSegundoPasso($_POST['ano'],$serventia);
		}else if($_POST['passo'] == 3){
			$textoTop = $_SESSION['dsc_tribunal'].'<br />
						Dados importados do terceiro passo no mes '.$this->util->qualMesNumero(intval($_POST['mes'])).' do ano de '.$_POST['ano'];
			$caminhoHTML = 'consulta/PDFreciboTerceiroPasso.html';
			$serventia = $this->sqlPrm->retornaServentia();
			$conteudo = $this->sql->reciboTerceiroPasso($_POST['mes'],$_POST['ano'],$serventia);
		}else if($_POST['passo'] == 4){
			$textoTop = $_SESSION['dsc_tribunal'].'<br />
						Dados importados do quarto passo no mes '.$this->util->qualMesNumero(intval($_POST['mes'])).' do ano de '.$_POST['ano'];
			$caminhoHTML = 'consulta/PDFreciboQuartoPasso.html';
			$serventia = $this->sqlPrm->retornaServentia();
			$conteudo = $this->sql->reciboQuartoPasso($_POST['mes'],$_POST['ano'],$serventia);
		}
		$this->util->montaPdf($textoTop,$caminhoHTML,$conteudo);
	}
	
	/**
	 * Método monta a tela principal de produtividades.
	 */
	function formPrincipal()
	{
		permissao($_POST);
		if($_POST['passo'] == 1){
			$parametros['SEQ_ORGAO'] = $_POST['SEQ_ORGAO'];
			$parametros['a'] = 'consulta';
			$parametros['d'] = 'consulta';
			$parametros['f'] = 'primeiroPasso';
			$parametros['token'] = $_SESSION['token'];
			$this->token->redirect($parametros);
		}
	
		if($_POST['passo'] == 2)
			$this->segundoPasso();
	
			if($_POST['passo'] == 3){
				$parametros['SEQ_ORGAO'] = $_POST['SEQ_ORGAO'];
				$parametros['a'] = 'consulta';
				$parametros['d'] = 'consulta';
				$parametros['f'] = 'terceiroPasso';
				$parametros['token'] = $_SESSION['token'];
				$this->token->redirect($parametros);
			}
	
			if($_POST['passo'] == 4){
				$parametros['SEQ_ORGAO'] = $_POST['SEQ_ORGAO'];
				$parametros['a'] = 'consulta';
				$parametros['d'] = 'consulta';
				$parametros['f'] = 'quartoPasso';
				$parametros['token'] = $_SESSION['token'];
				$this->token->redirect($parametros);
			}
	}
	
	/**
	 * Método monta a lista de serventias para consulta.
	 */
	function prmGrauProdutividade()
	{
		permissao($_POST);
		$this->smarty->assign('meses',$this->util->mesesAno());
		
		$ano = $this->sqlPrm->buscaAnos();
		$this->smarty->assign('ano',$ano);
		
		$serventia_estado = $this->sqlPrm->retornaServentia();
		$this->smarty->assign('listaServentia',$serventia_estado);
	}
	
	/***************************************************/
	/*******Metodos de consulta do primeiro passo*******/
	/***************************************************/
	/**
	 * Método monta os dados para o primeiro passo.
	 */
	function primeiroPasso()
	{
		$retornaDadosServentia = $this->sql->retornaConsultaServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('retornaDadosServentia',$retornaDadosServentia[0]);
		
		$municipio_estado = $this->sql->retornaMunicipiosEstado($_POST['SEQ_ORGAO']);
		$this->smarty->assign('listaMunicipios',$municipio_estado);
		
		$retornaCompetenciaServentia = $this->sql->retornaCompetenciaServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('retornaCompetenciaServentia',$retornaCompetenciaServentia);
		
		$respostasTrabPrmGrau = $this->sql->respostaProdutividadeTrabalhoPrmGrau($_POST['SEQ_ORGAO']);
		$respostasTrabSegGrau = $this->sql->respostaProdutividadeTrabalhoSegGrau($_POST['SEQ_ORGAO']);
		if($respostasTrabPrmGrau && $respostasTrabSegGrau){
			$respostaTrabalho = array_merge($respostasTrabPrmGrau,$respostasTrabSegGrau);
		}else if($respostasTrabPrmGrau){
			$respostaTrabalho = $respostasTrabPrmGrau;
		}else{
			$respostaTrabalho = $respostasTrabSegGrau;
		}
		$this->smarty->assign('respostaTrabalho',$respostaTrabalho);
	}
	
	/**
	 * Método monta os detalhes das serventias.
	 */
	function dadosServentia()
	{
		$dadosServentia = $this->sql->retornaDadosServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('dadosServentia',$dadosServentia);
	
		$magistado_serventia = $this->sql->retornaMagistradoServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('magistado_serventia',$magistado_serventia);
	
		$produtividadeServentia = $this->sql->retornaProdutividadesServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('produtividadeServentia',$produtividadeServentia);
	
		$produtividadesMagistrados = $this->sql->retornaProdutividadesMagistrados($_POST['SEQ_ORGAO']);
		$this->smarty->assign('produtividadesMagistrados',$produtividadesMagistrados);
	
		$dadosComplementares = $this->sql->retornaDadosComplementares($_POST['SEQ_ORGAO']);
		$this->smarty->assign('dadosComplementares',$dadosComplementares[0]);
	}
	
	/**************************************************/
	/*******Metodos de consulta do segundo passo*******/
	/**************************************************/
	/**
	 * Método mostra a lista de magistrados e monta o XLS.
	 */
	function dadosMagistrado()
	{
		$serventia = $this->sqlPrm->retornaServentia();
		if($serventia){
			foreach ($serventia as $key => $value) {
				$seqOrgao[] = $value['seq_corporativo'].',';
			}
			$seqOrgao[count($seqOrgao) - 1] = substr($seqOrgao[count($seqOrgao) - 1], 0, strlen($seqOrgao[count($seqOrgao) - 1])-1);
		}
	
		$listaMagistradoTribunal = $this->sql->retornaMagistradoServentia($seqOrgao,true);
		$this->smarty->assign('listaMagistrado',$listaMagistradoTribunal);
	}
	
	/***************************************************/
	/*******Metodos de consulta do terceiro passo*******/
	/***************************************************/
	/**
	 * Método monta os dados para o quarto passo(Produtividade serventia).
	 */
	function terceiroPasso()
	{
		$dadosServentia = $this->sql->retornaDadosServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('dadosServentia',$dadosServentia[0]);
		
		$produtividadePrm = $this->sql->retornaProdutividadeServentiaPrmGrau($_POST['SEQ_ORGAO']);
		$produtividadeSeg = $this->sql->retornaProdutividadeServentiaSegGrau($_POST['SEQ_ORGAO']);
		if($produtividadeSeg){
			foreach ($produtividadeSeg as $key => $value) {
				$produtividadeSeg[$key]['mes'] = $this->util->qualMesNumero($value['NUM_MES_REFERENCIA']).' / '.$value['NUM_ANO_REFERENCIA'];
			}
		}

		if($produtividadePrm && $produtividadeSeg){
			$listaProdutividade = array_merge($produtividadePrm,$produtividadeSeg);
			if($listaProdutividade){
				foreach ($listaProdutividade as $key => $valueProd) {
					$produtividades[$valueProd['mes']] = $valueProd;
				}
			}
		}else if($produtividadePrm){
			foreach ($produtividadePrm as $key => $valueProd) {
				$produtividades[$valueProd['mes']] = $valueProd;
			}
		}else if($produtividadeSeg){
			foreach ($produtividadeSeg as $key => $valueProd) {
				$produtividades[$valueProd['mes']] = $valueProd;
			}
		}
		$this->smarty->assign('listaProdutividade',$produtividades);
		
	}
	
	/**
	 * Método monta a planilha de produtividade das serventias.
	 */
	function planilhaProdutividadeServentia()
	{
		permissao($_POST);
		$serventia = $this->sqlPrm->retornaServentia();
		
		$periodo = $_POST['mes'].' / '.$_POST['ano'];
		$xlsPrmGrau = $this->sql->produtividadeServentiaPrmGrauXls($periodo,$serventia);
		$xlsSegGrau = $this->sql->produtividadeServentiaSegGrauXls($periodo,$serventia);
		$respostaProdutividade = array_merge($xlsPrmGrau,$xlsSegGrau);
		
		if($respostaProdutividade){
			$this->terceiroPasso->gerarRelatorioXlsTerceiroPasso($respostaProdutividade);
		}else{
			$parametros['msgErro'] = ("Não há produtividade no período selecionado.");
			$parametros['passo'] = '3';
			$parametros['a'] = 'consulta';
			$parametros['d'] = 'consulta';
			$parametros['f'] = 'prmGrauProdutividade';
			$parametros['token'] = $_SESSION['token'];
			$this->token->redirect($parametros);
		}
	}
	
	/**
	 * Método monta os dados da produtividade da serventia.
	 */
	function produtividadeServentia()
	{
		$respostasPrmGrau = $this->sql->respostaProdutividadeServentiaPrmGrau($_POST['mes'],$_POST['seqOrgao']);
		
		$respostasSegGrau = $this->sql->respostaProdutividadeServentiaSegGrau($_POST['mes'],$_POST['seqOrgao']);
		
		$respostaProdutividade = array_merge($respostasPrmGrau,$respostasSegGrau);
		$this->smarty->assign('respostaProdutividade',$this->util->array_sort($respostaProdutividade, 'NUM_ORDEM'));
	}
	
	/*************************************************/
	/*******Metodos de consulta do quarto passo*******/
	/*************************************************/
	/**
	 * Método monta os dados para o segundo passo.
	 */
	function quartoPasso()
	{
		$dadosServentia = $this->sql->retornaDadosServentia($_POST['SEQ_ORGAO']);
		$this->smarty->assign('dadosServentia',$dadosServentia[0]);
		
		$produtividadeMagistradoPrmGrau = $this->sql->listaProdutividadeMagistradoPrmGrau($_POST['SEQ_ORGAO']);
		$produtividadeMagistradoSegGrau = $this->sql->listaProdutividadeMagistradoSegGrau($_POST['SEQ_ORGAO']);

		if($produtividadeMagistradoPrmGrau && $produtividadeMagistradoSegGrau){
			$produtividadeMagistrado = array_merge($produtividadeMagistradoPrmGrau,$produtividadeMagistradoSegGrau);
			$this->smarty->assign('produtividadeMagistrado',$produtividadeMagistrado[0]);
		}else if($produtividadeMagistradoPrmGrau){
			$produtividadeMagistrado = $produtividadeMagistradoPrmGrau;
			$this->smarty->assign('produtividadeMagistrado',$produtividadeMagistrado[0]);
		}else if($produtividadeMagistradoSegGrau){
			$produtividadeMagistrado = $produtividadeMagistradoSegGrau;
			$this->smarty->assign('produtividadeMagistrado',$produtividadeMagistrado[0]);
		}
		
		if($produtividadeMagistrado){
			foreach ($produtividadeMagistrado as $key => $valueProd) {
				$mes = explode('/',$valueProd['mes']);
				$ordenar = $this->util->qualMes(trim($mes[0]));
				$valueProd['ordenacao'] = trim($mes[1]).'-'.$ordenar;
				if(!empty($valueProd['SEQ_MAGISTRADO']))
					$produtividades[$valueProd['ordenacao'].$valueProd['SEQ_MAGISTRADO'].$valueProd['tipo']] = $valueProd;
					else
						$produtividades[$valueProd['ordenacao'].$valueProd['SEQ_ORGAO']] = $valueProd;
			}
		}
		$this->smarty->assign('listaProdutividadeMagistradoServentia',$this->util->array_sort($produtividades,'ordenacao'));
	}
	
	/**
	 * Método monta o xls das produtividades do(s) magistrado(s).
	 */
	function planilhaProdutividadeMagistrado()
	{
		permissao($_POST);
		$serventia = $this->sqlPrm->retornaServentia();
		
		$periodo = $_POST['mes'].' / '.$_POST['ano'];
		$xlsPrmGrau = $this->sql->produtividadeMagistradoPrmGrauXls($periodo,$serventia);
		$xlsSegGrau = $this->sql->produtividadeMagistradoSegGrauXls($periodo,$serventia);
		$respostaProdutividade = array_merge($xlsPrmGrau,$xlsSegGrau);
		$respostaProdutividade = $this->util->array_sort($respostaProdutividade,'NUM_ORDEM');
		if($respostaProdutividade){
			$this->quartoPasso->gerarRelatorioXlsQuartoPasso($respostaProdutividade);
		}else{
			$parametros['msgErro'] = ("Não há produtividade no período selecionado.");
			$parametros['passo'] = 4;
			$parametros['a'] = 'consulta';
			$parametros['d'] = 'consulta';
			$parametros['f'] = 'prmGrauProdutividade';
			$parametros['token'] = $_SESSION['token'];
			$this->token->redirect($parametros);
		}
	}
	
	/**
	 * Método monta o pdf das produtividades dos magistrados.
	 */
	function produtividadeMagistrado()
	{
		$respostasMagistradoPrmGrau = $this->sql->respostaProdutividadeMagistradoPrmGrau($_POST['seqOrgao'],$_POST['mes'],$_POST['seqMagistrado'],$_POST['tipoMagistrado']);
		
		$respostasMagistradoSegGrau = $this->sql->respostaProdutividadeMagistradoSegGrau($_POST['seqOrgao'],$_POST['mes'],$_POST['seqMagistrado'],$_POST['tipoMagistrado']);
		
		$respostasMagistrado = array_merge($respostasMagistradoPrmGrau,$respostasMagistradoSegGrau);
		$this->smarty->assign('respostasMagistrado',$this->util->array_sort($respostasMagistrado, 'NUM_ORDEM'));
	}
	
	/*******************************************************/
	/*********Arquivos para gravação da CRON****************/
	/*******************************************************/
	
	/**
	 * Método monta a tela da listagem de arquivos para a gravação pela CRON do terceiro e quarto passo.
	 */
	function arquivosParaGravacao()
	{
		$listaArquvoParaGravacao = $this->sql->listaArquivoParaGravacao($_POST['passo']);
		$this->smarty->assign('listaArquvoParaGravacao',$listaArquvoParaGravacao);
	}
}