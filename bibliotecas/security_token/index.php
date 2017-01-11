<?php
class token {
	function token(){
		//Cria um token para validação de login na aplicação.
		$token 				= md5(uniqid(rand(), true));
		if(!isset($_SESSION['token'])){
			$_SESSION['token'] 	= $token;
		} 		
		return $token;
	}
	
	function redirect($parametros, $target=""){
		/*if($target){
			$_target=" target= '".$target."'";
		}*/
		$html  = "<html>\n";
		$html .= "<body>\n";
		$html .= "<form id='frm_redirect' name='frm_redirect' method='post'>\n";		
		
		foreach ($parametros as $key => $value) {
			$html .= "<input type=\"hidden\" name=\"".$key."\" id=\"".$key."\" value=\"".$value."\" />\n";
			
		}				
		$html .= "</form>\n";
		$html .= "<script>\n";
		$html .= "document.forms[0].submit();\n";
		$html .= "</script>\n";
		$html .= "</body>\n";
		$html .= "</html>";		

		echo $html;		
		exit;
	}
}
?>