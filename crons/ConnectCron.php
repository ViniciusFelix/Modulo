<?php
$conDBHost = "pratad01.cnj.jus.br";
$conDBUser = "vinicius.silva";
$conDBPass = "#@Cnj12013";
// $conDBHost = "prata01.cnj.jus.br";
// $conDBUser = "modulo_xml";
// $conDBPass = "hFrdd34";
$strConexao = mysqli_connect($conDBHost, $conDBUser, $conDBPass);
return $strConexao;
?>