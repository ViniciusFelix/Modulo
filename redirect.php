<?php
require_once("bibliotecas/security_token/index.php");
$token = new token();
?>
<html>
<head>
<title>
	Redirecionando
</title>
<script type="text/javascript" src="extras/page.js" charset="iso-8859-1"></script>
<script type="text/javascript" src="extras/md5.js" charset="iso-8859-1"></script>
</head>
<body>
	<input type="hidden" name="token" id="token" value="<?=$_SESSION['token']; ?>" />
	<script>
		document.location.href='http://titaniod01.cnj.jus.br/corporativo/';				
	</script>
</body>
</html>