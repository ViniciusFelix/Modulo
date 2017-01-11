<?php
class pergunta
{
	function pergunta($db, $smarty)
	{
        libxml_use_internal_errors(true);
		$this->db = $db;
		$this->smarty = $smarty;

		require_once ("bibliotecas/excel_reader2.php");
				
		include_once("sql/pergunta/bdpergunta.php");
		$this->sql = new bdpergunta($db);
		
		include_once("php/util.php");
		$this->util = new util($smarty);
		
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token($smarty);
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
	 * Método para montar a página principal da importação da classe. 
	 */
	function formPrincipalPergunta()
	{
		permissao($_POST);
		
		if(isset($_POST['limpaPost']) == 1){
			unset ($_POST['validoXml']);
			
			if(!empty($_POST['nomeDocumento'])){
				unlink($this->util->caminho_absolutoPergunta.$_POST['nomeDocumento']);
			}
		}
	}
	
	/**
	 * Monta lista de tribunais para planilha
	 */
	function planilhaTribunal()
	{
		$listaTribunal = $this->sql->listaTribunal();
		
		if(isset($_POST['salvar'])){
			$this->gerarXLS($listaTribunal, "SEQ_TRIBUNAL,DSC_TRIBUNAL");
		}else{
			$this->smarty->assign('listaTribunal',$listaTribunal);
		
			echo $this->smarty->fetch("pergunta/planilhaTribunal.html");
			die;
		}
	}
	
	function planilhaPerguntas()
	{
		$listaPergunta = $this->sql->listaPergunta();
		if(isset($_POST['salvar'])){
			$this->gerarXLS($listaPergunta, "SIGLA,DSC_PERGUNTA,GLOSSARIO");
		}else{
			$this->smarty->assign('listaPergunta',$listaPergunta);
		
			echo $this->smarty->fetch("pergunta/planilhaPergunta.html");
			die;
		}
	}
	
	/**
	 *Método verifica se xls está na formatação do modelo.
	 */
	function validaXls()
	{
		permissao($_POST);
    	$caminhoXls = $this->util->caminho_absolutoPergunta.$_FILES['arquivo_xls']['name'];
		
		if (! empty($_FILES['arquivo_xls']['name'])) {
			move_uploaded_file($_FILES['arquivo_xls']['tmp_name'], $caminhoXls);
        }
		$data = new Spreadsheet_Excel_Reader($this->util->caminho_absolutoPergunta.$_FILES['arquivo_xls']['name']);
		
		$linhas = $data->rowcount();
		$colunas= $data->colcount();
			 
		for($i = 1; $i <= $linhas; $i++){
		    for($j = 1; $j <= $colunas; $j++){
		    	if(trim($data->val($i,$j)) != '')
		        	$meuArray[$i][$j] = $data->val($i,$j);
		    }
		}

		if($meuArray[1][1] == "Seq Pergunta" && $meuArray[1][2] == "Tribunal" && $meuArray[1][3] == "Grau Tribunal" &&
		   $meuArray[1][4] == "Sigla Pergunta" && $meuArray[1][5] == "Descrição Pergunta" && 
		   $meuArray[1][6] == "Glossário Pergunta" && $meuArray[1][7] == "Número Ordem" && $meuArray[1][8] == "Destinatário"){
			$xls = 1;
		   	$parametros['validoXls'] = true;
		}else{
			$xls = 2;
			$parametros['msgErro'] = 'Dados do xls de formato inválido.';
		}

		$parametros['aba'] = 2;
		$parametros['caminhoXls'] = $caminhoXls;
		$parametros['nomeXls'] = $_FILES['arquivo_xls']['name'];
		$parametros['a'] = 'pergunta';
		$parametros['d'] = 'pergunta';
		$parametros['f'] = 'formPrincipalPergunta';
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
			 
		for($i = 2; $i <= $linhas; $i++){
		    for($j = 1; $j <= $colunas; $j++){
		        $meuArray[$i][$j] = $data->val($i,$j);
		    }
		}

		try {
			$this->db->BeginTrans();
			foreach ($meuArray as $key=>$value){
				if($value[5] == ""){
					break;
				}else{
					$value[6] =  ucfirst ( $value[6] );
					
					if($value[3] == 1 || $value[3] == NULL){
						if(!empty($value[1])){
							$pergunta = $this->sql->alterarPerguntaPrmGrau($value);
							if(!$pergunta)
								throw new Exception("Ocorreu um erro ao salvar os dados da pergunta ".$value[4]." na linha ".$key.".");
							
							if($value[2]){
								$vinculo = $this->sql->excluirVinculosPerguntaPrmGrau($value[1]);
								if(!$vinculo)
									throw new Exception("Ocorreu um erro ao salvar os dados da pergunta ".$value[4]." na linha ".$key.".");
							}
						}else{
							$pergunta = $this->sql->inserirPerguntaPrmGrau($value);
							if(!$pergunta)
								throw new Exception("Ocorreu um erro ao salvar os dados da pergunta ".$value[4]." na linha ".$key.".");
						}

						if($value[2]){
							if(count(explode('.', $value[2])) > 1)
								$tribunal = explode('.', $value[2]);
							else
								$tribunal = explode(',', $value[2]);
							if(count($tribunal) > 1){
								foreach ($tribunal as $keyTri => $tri) {
									$perguntaTribunal = $this->sql->vinculoPerguntaTribunalPrmGrau($tri,$pergunta);
									
									if(!$perguntaTribunal)
										throw new Exception("Ocorreu um erro ao vincular pergunta primeiro grau ".$value[4]." na linha ".$key.".");
								}
							}else{
								$perguntaTribunal = $this->sql->vinculoPerguntaTribunalPrmGrau($value[2],$pergunta);
							
								if(!$perguntaTribunal)
									throw new Exception("Ocorreu um erro ao vincular pergunta primeiro grau ".$value[4]." na linha ".$key.".");
							}
						}
					}else if($value[3] == 2){
						if(!empty($value[1])){
							$pergunta = $this->sql->alterarPerguntaSegGrau($value);
							if(!$pergunta)
								throw new Exception("Ocorreu um erro ao alterar os dados da pergunta ".$value[4]." na linha ".$key.".");
							
							if($value[2]){
								$vinculo = $this->sql->excluirVinculosPerguntaSegGrau($value[1]);
								if(!$vinculo)
									throw new Exception("Ocorreu um erro ao salvar os dados da pergunta ".$value[4].".");
							}
						}else{
							$pergunta = $this->sql->inserirPerguntaSegGrau($value);
							if(!$pergunta)
								throw new Exception("Ocorreu um erro ao salvar os dados da pergunta ".$value[4]." na linha ".$key.".");
						}
						
						if($value[2]){
							$tribunal = explode(',' , $value[2]);
							if(count($tribunal) > 1){
								foreach ($tribunal as $keyTri => $tri) {
									$perguntaTribunal = $this->sql->vinculoPerguntaTribunalSegGrau($tri,$pergunta);
									if(!$perguntaTribunal)
										throw new Exception("Ocorreu um erro ao vincular pergunta segundo grau ".$value[4]." na linha ".$key.".");
								}
							}else{
								$perguntaTribunal = $this->sql->vinculoPerguntaTribunalSegGrau($value[2],$pergunta);
								if(!$perguntaTribunal)
									throw new Exception("Ocorreu um erro ao vincular pergunta segundo grau ".$value[4]." na linha ".$key.".");
							}
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
		$parametros['a'] = 'pergunta';
		$parametros['d'] = 'pergunta';
		$parametros['f'] = 'formPrincipalPergunta';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	/**
	 * Método para baixa o arquivo.
	 */
	function baixaArquivo()
	{
		$this->util->baixarDocumento($this->util->caminho_absolutoPergunta,$_POST['nomeDocumento']);
	}
	
	/**
	 * Método para excluir o arquivo
	 */
	function excluirArquivo()
	{
		unlink($this->util->caminho_absolutoPergunta.$_POST['nomeDocumento']);
		
		$parametros['aba'] = 2;
		$parametros['a'] = 'pergunta';
		$parametros['d'] = 'pergunta';
		$parametros['f'] = 'formPrincipalPergunta';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
}
?>
