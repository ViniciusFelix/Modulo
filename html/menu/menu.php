<?php
$importar = NULL;
$perg = NULL;

if($_POST['a'] == 'pergunta')
	$perg = 'active';
else
	$importar = 'active';

$menu[] = getMenu('Página inicial', '#', 'wiOpen(\'?d=importarArquivos&a=importarArquivos&f=formPrincipal\');', $importar, '', '');

if($_SESSION['sig_tribunal'] == 'CNJ')
	$menu[] = getMenu('Pergunta', '#', 'wiOpen(\'?d=pergunta&a=pergunta&f=formPrincipalPergunta\');', $perg, '', '');

function getMenu($titulo, $url, $onclick, $classe, $target, $submenu){
	$objMenu = array();
	$objMenu = (object) $objMenu;
	
	//Menu principal
	$objMenu->titulo = $titulo;
	$objMenu->url = $url;
	$objMenu->onclick = $onclick;
	$objMenu->class = $classe;
	$objMenu->target = $target;
	
	if($submenu){
		$objMenu->submenu = $submenu;		
	}else{
		$objMenu->submenu = NULL;
	}
	
	return $objMenu;
}

?>	