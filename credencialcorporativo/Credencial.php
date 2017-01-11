<?php
require 'CredencialVO.php';
require 'UsuarioCredencial.php';

class Credencial {
    
	protected $corporativo;

   /**
     * Construtor da classe para autenticação
     *
     * @param $strCorporativo string 
     */
    public function __construct($strCorporativo) {
		  $this->corporativo = $strCorporativo;
    }

    /**
     * Executa a autenticação com o webservice do CNJ
     */
    public function authenticate()
    {
        try {
		
            if($this->corporativo){
            	$objCredencial = $this->iniciarCredencialSistema( $this->corporativo );
            	return $objCredencial;

            } else {      
            	        
                  return "Usu�rio bloqueado! Favor entrar em contato com a equipe CNJ.";      
            }

        } catch (SoapFault $err) {
            //return $this->resultado(Zend_Auth_Result::FAILURE, NULL, array("Senha ou login inv�lidos."));
            //throw new Zend_Auth_Adapter_Exception($err->getMessage());
        }

    }
    
    /**
     * Verifica e inicializa a credencial enviada
     *
     * @param string $credencialEnviada
     * @return Credencial Credencial inicializada
     */
     protected function iniciarCredencialSistema($credencialEnviada){
        try{
        if (is_null($credencialEnviada) || $credencialEnviada == ""){
        	return  array("error"=>"Credencial inválida");
        }
        
        $credencialCodificada = substr($credencialEnviada,0,-32);
        $hashCredencial = substr($credencialEnviada,-32);
        
        $stringCredencial = $this->decodificarCredencial($credencialCodificada);
        $credencial = $this->montarCredencial($stringCredencial);
        
        $timeToLiveCredencial = $credencial->getTimeToLive();
        
        $timeAtual = time();
        
        
        if ($timeAtual > $timeToLiveCredencial){
        	return  array("error"=>"Sua Credencial expirou!");
        }
        
        $seqSistema = $credencial->getSeqSistema();
       /* $codSistema = Zend_Registry::get('config')->sistema->codSistema;

        if ( $seqSistema != $codSistema ){
        
        	return array("error"=>"Sua credencial n�o � v�lida para este sistema!");
        }*/

        return $credencial;

        } catch( Exception $ex ){
            return array("error"=>"Sua credencial n�o � v�lida para este sistema!");
        }
        
    }
    
    protected function decodificarCredencial($credencialCodificada){
  		
        return base64_decode($credencialCodificada);
        
    }
    
    /**
     * Retorna o objeto credencial
     *
     * @param string $credencialSerializada
     * @return Credencial Credencial
     */
    protected function montarCredencial($stringCredencial){
        try {    
        $credencial        = new CredencialVO();
        $usuarioCredencial = new UsuarioCredencial();
      
        $separadorCredencial = $this->getSeparadorCredencial(";");
        $atributosCredencial = explode($separadorCredencial,$stringCredencial);
        $qntCredencial = count($atributosCredencial);
        for ($i = 0;$i < $qntCredencial -2; $i+=2){
            
            $atributo = $this->decriptografarAtributo($atributosCredencial[$i]);
            $valor    = $this->decriptografarAtributo($atributosCredencial[$i+1]);
         
            $credencial->setAtributo($atributo,$valor);
        }
        
        $inicioUsuario = stripos($stringCredencial,"\n");
        $stringUsuario = substr($stringCredencial,$inicioUsuario);
  
        $separadorUsuario    = $this->getSeparadorCredencial("|");
        
        $atributosUsuario    = explode($separadorUsuario,$stringUsuario);
        $qntUsuario          = count($atributosUsuario);
    
        for ($i = 0;$i < $qntUsuario -2; $i+=2){
            
            $atributo = $this->decriptografarAtributo($atributosUsuario[$i]);
            $valor    = $this->decriptografarAtributo($atributosUsuario[$i+1]);
            
            $usuarioCredencial->setAtributo($atributo,$valor);
        }
        
        $credencial->setUsuario($usuarioCredencial);
        
        return $credencial;

        } catch( Exception $ex ) {
            echo $ex->getMessage();
        }
        
    }
    
    protected function decriptografarAtributo($atributo){
        return base64_decode($atributo);
    }
    
 	function getSeparadorCredencial($sufixo){
        
        $separadorDefault = 'SEPARADORCREDENCIALCNJ';
        $separador = $separadorDefault . $sufixo;
        return md5($separador);
        
    }
    
   
    
    protected function resultado($code, $identity, $mensagens) {
        if (!is_array($mensagens)) {
            $mensagens = array($mensagens);
        }
        
        return new Zend_Auth_Result($code, $identity, $mensagens);
    }

  
}