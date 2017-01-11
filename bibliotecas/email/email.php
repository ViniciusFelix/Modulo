<?php
define('EMAIL_VERSION','0.07');
class email{
	private $DestinatariosEmail;	
	private $assunto;
	private $corpo;
	private $qtdDestinatarioEmail;	
	
	public function __construct(){		
	}
	
	public function enviaEmail($DestinatariosEmail=false, $assunto=false, $corpo=false){
		try{
			$this->DestinatariosEmail 	= $DestinatariosEmail;
			$this->assunto				= $assunto;
			$this->corpo				= $corpo;			
			
			if($this->DestinatariosEmail==false){
				throw new Exception('N�o foi poss�vel enviar o e-mail, n�o existe destinat�rios.');
				$assunto = "Sem destinat�rio";	
			}
			if($this->assunto==false){
				throw new Exception('N�o foi poss�vel enviar o e-mail, n�o existe assunto.');
				$assunto = "Sem assunto";	
			}			
			if($this->corpo==false){
				throw new Exception('N�o foi poss�vel enviar o e-mail, n�o existe o corpo do e-mail.');
				$assunto = "Sem corpo";	
			}			
			/* **************** Parte de envio de e-mail ************ */						
					
			//$assunto = "[Projeto Justi�a Plena] Solicita��o de acesso ao SAPRS";
			/*$corpo = '
						<html>
						<head>
						   <title>[Projeto Justi�a Plena] Cadastro</title>
						   <style>
						   body{
						   		font-size:11px;
								font-family:Verdana, Geneva, Arial, Helvetica, sans-serif;
						   }
						   </style>
						</head>
							<body>				
								<b>Prezado(a),</b><br />
								<br />
								Informamos que o seu cadastro no SAPRS -Acompanhamento de Processos de Relev�ncia social foi efetuado com sucesso.<br /> 
								Seus respectivos login e Senha de acesso ao sistema, inicialmente � seu pr�prio numero de CPF.<br />
								Segue abaixo link para acesso ao sistema.<br />
								<br />
								link: http://www.cnj.jus.br/corregedoria/saprs/<br /><br />
								D�vidas sobre o Projeto Justi�a Plena: 61 2326-4641.						
							</body>
						</html>';*/
						
			//para o envio em formato HTML
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html;
						charset=iso-8859-1\r\n";
						
			//endere�o do remetente
			$headers .= "From: Projeto Justi�a Plena <no-reply@cnj.jus.br>\r\n";				
			
			//endere�o de resposta, se for diferente do remetente
			$headers .= "Reply-To: no-reply@cnj.jus.br\r\n";					
						
			if(mail($this->DestinatariosEmail,$this->assunto,$this->corpo,$headers)){
				return true;	
			}else{
				throw new Exception('N�o foi poss�vel enviar o e-mail para os destinat�rios.');	
			}			
			
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}	
}
?>