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
				throw new Exception('Não foi possível enviar o e-mail, não existe destinatários.');
				$assunto = "Sem destinatário";	
			}
			if($this->assunto==false){
				throw new Exception('Não foi possível enviar o e-mail, não existe assunto.');
				$assunto = "Sem assunto";	
			}			
			if($this->corpo==false){
				throw new Exception('Não foi possível enviar o e-mail, não existe o corpo do e-mail.');
				$assunto = "Sem corpo";	
			}			
			/* **************** Parte de envio de e-mail ************ */						
					
			//$assunto = "[Projeto Justiça Plena] Solicitação de acesso ao SAPRS";
			/*$corpo = '
						<html>
						<head>
						   <title>[Projeto Justiça Plena] Cadastro</title>
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
								Informamos que o seu cadastro no SAPRS -Acompanhamento de Processos de Relevância social foi efetuado com sucesso.<br /> 
								Seus respectivos login e Senha de acesso ao sistema, inicialmente é seu próprio numero de CPF.<br />
								Segue abaixo link para acesso ao sistema.<br />
								<br />
								link: http://www.cnj.jus.br/corregedoria/saprs/<br /><br />
								Dúvidas sobre o Projeto Justiça Plena: 61 2326-4641.						
							</body>
						</html>';*/
						
			//para o envio em formato HTML
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html;
						charset=iso-8859-1\r\n";
						
			//endereço do remetente
			$headers .= "From: Projeto Justiça Plena <no-reply@cnj.jus.br>\r\n";				
			
			//endereço de resposta, se for diferente do remetente
			$headers .= "Reply-To: no-reply@cnj.jus.br\r\n";					
						
			if(mail($this->DestinatariosEmail,$this->assunto,$this->corpo,$headers)){
				return true;	
			}else{
				throw new Exception('Não foi possível enviar o e-mail para os destinatários.');	
			}			
			
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}	
}
?>