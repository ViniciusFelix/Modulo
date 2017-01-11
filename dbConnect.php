<?php
include('bibliotecas/adodb5/adodb.inc.php');

// dizendo qual o sgbd sera utilizado
$db =  ADONewConnection($confDB["sgbd"]);

// realizando a conexao
$db->NConnect($confDB["servidor"], $confDB["usuario"], $confDB["senha"], $confDB["banco"]);

// setando o modo como os dados sao carregado para associar o nome_do_campo da tabela ao seu valor
$db->setFetchMode(ADODB_FETCH_ASSOC);

?>