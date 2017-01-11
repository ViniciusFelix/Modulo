<?php
class index{
   	
	function index($db, $smarty){	
        $this->db = $db;
		$this->smarty = $smarty;
		
		include_once("bibliotecas/security_token/index.php");
		$this->token = new token();
		
		include_once("sql/index/bdindex.php");
		$this->sql = new bdindex($db);		
	}
	
	/**
	 * Método retorna o tribunal do orgão.
	 * @param unknown $row
	 * @return multitype:
	 */
	function retornaDenominacao($row) {
		if (substr($row['TIP_ORGAO'], 0, 4) != "TRIB") {
			do {
				$row2 = $this->sql->retornaTribunal($row['SEQ_ORGAO_PAI']);
				$row['SEQ_ORGAO_PAI'] = $row2['SEQ_ORGAO_PAI'];
			} while (substr($row2['TIP_ORGAO'], 0, 4) != "TRIB");
		}else{
			$row2 = $this->sql->retornaTribunal($row['SEQ_ORGAO']);
		}
		return $row2;
	}
		
	function logado()
	{		
		if(empty($_POST['Credencial'])){
			limpaSessao();
			require_once('redirect.php');
			exit;
		}
		
		if(!empty($_GET['c'])){
			if(!empty($_POST['Credencial']->erro)){
				session_unset();
				session_destroy();
				die ("<script>document.location.href='http://www.cnj.jus.br/corporativo/'</script>");
			}

			$_SESSION['corporativo'] = true;
			$_SESSION['seq_usuario'] = $_POST['Credencial']->usuario->getSeqUsuario();
			$_SESSION['nomUsuario']  = $_POST['Credencial']->usuario->getNomUsuario();
			$_SESSION['cpfUsuario']  = $_POST['Credencial']->usuario->getNumCpf();
			$_SESSION['sigUsuario']  = $_POST['Credencial']->usuario->getSigUsuario();
			
			$usuario = $this->sql->retornaUsuarioPeloSca($_POST['Credencial']->usuario->getNumCpf());
			
			if($usuario['COD_HIERARQUIA'] == ':1:' || $usuario['TIP_ORGAO'] == 'CNJ'){
				$_SESSION['dsc_tribunal']  = $usuario['DSC_ORGAO'];
				$_SESSION['sig_tribunal']  = $usuario['DSC_SIGLA'];
				$_SESSION['tip_orgao']     = $usuario['TIP_ORGAO'];
				$_SESSION['seq_orgao']	   = $usuario['SEQ_ORGAO'];
				$_SESSION['seq_orgao_pai'] = $usuario['SEQ_ORGAO'];
				$_SESSION['cod_hierarquia']= $usuario['COD_HIERARQUIA'];
			}else{
				$tribunal = $this->retornaDenominacao($usuario);
				
				$_SESSION['dsc_tribunal']  = $tribunal['DSC_ORGAO'];
				$_SESSION['sig_tribunal']  = $tribunal['DSC_SIGLA'];
				$_SESSION['tip_orgao']     = $tribunal['TIP_ORGAO'];
				$_SESSION['seq_orgao']	   = $tribunal['SEQ_ORGAO'];
				$_SESSION['seq_orgao_pai'] = $tribunal['SEQ_ORGAO_PAI'];
				$_SESSION['cod_hierarquia']= $tribunal['COD_HIERARQUIA'];
			}
			
			if($_SESSION['seq_orgao_pai'] == 12728){
				$_SESSION['tipoArquivo'] = 4;
			}else{
				if($_SESSION['tip_orgao'] == 'TRIBE' || $_SESSION['tip_orgao'] == 'TRIBF' || $_SESSION['tip_orgao'] == 'CONSE'){
					$_SESSION['tipoArquivo'] = 1;
				}else if($_SESSION['tip_orgao'] == 'TRIBL' || $_SESSION['tip_orgao'] == 'TRIBM' || $_SESSION['sig_tribunal'] == 'STM' || $_SESSION['tip_orgao'] == 'TRIBT'){
					$_SESSION['tipoArquivo'] = 2;
				}else if($_SESSION['tip_orgao'] == 'TRIBS'){
					$_SESSION['tipoArquivo'] = 3;
				}
			}
		}
		
		$parametros['a'] = 'index';
		$parametros['d'] = 'index';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_SESSION['token'];
		$this->token->redirect($parametros);
	}
	
	function formPrincipal()
	{
		permissao($_POST);
		$parametros['a'] = 'importarArquivos';
		$parametros['d'] = 'importarArquivos';
		$parametros['f'] = 'formPrincipal';
		$parametros['token'] = $_POST['token'];
		$this->token->redirect($parametros);
	}
	
	function sair()
	{
		session_unset();
		session_destroy();
		die ("<script>document.location.href='//titaniod01.cnj.jus.br/corporativo/index.php'</script>");
	}
}
?>
