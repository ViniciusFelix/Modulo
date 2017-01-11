<?php
class credencial
{
	function credencial($credencial){
		$credencialCodificada = substr($credencial,0,-32);
        $hashCredencial = substr($credencial,-32);
		$cdecodificada = $this->decode($credencialCodificada);
		if (md5($cdecodificada) != $hashCredencial){
            echo "A Credencial informada é inválida! Favor tente novamente";die;
        }
		
		$this->cred = $this->montarCredencial($cdecodificada);
		/*
		echo "<pre>";
		print_r($c);
		die;
		
		
		
		
		
		echo "ok";
		die;
		*/
			
	}
	
	function retonaCredencial(){
		return $this->cred;
	}
	
	function decode($credencial){
    	return base64_decode($credencial);
	}
	
	function montarCredencial($stringCredencial){
        $separadorCredencial = $this->getSeparadorCredencial(";");
        $atributosCredencial = explode($separadorCredencial,$stringCredencial);
		
        $qntCredencial = count($atributosCredencial);
        for ($i = 0;$i < $qntCredencial -2; $i+=2){
            
            $atributo = $this->decriptografarAtributo($atributosCredencial[$i]);
            $valor    = $this->decriptografarAtributo($atributosCredencial[$i+1]);
            
            $credencial[$atributo]=$valor;
        }
        
        $inicioUsuario = stripos($stringCredencial,"\n");
        $stringUsuario = substr($stringCredencial,$inicioUsuario);
        
        $separadorUsuario    = $this->getSeparadorCredencial("|");
        $atributosUsuario    = explode($separadorUsuario,$stringUsuario);
        $qntUsuario          = count($atributosUsuario);
        
        for ($i = 0;$i < $qntUsuario -2; $i+=2){
            
            $atributo = $this->decriptografarAtributo($atributosUsuario[$i]);
            $valor    = $this->decriptografarAtributo($atributosUsuario[$i+1]);
            $usuarioCredencial[$atributo]=$valor;
        }
        
        $credencial['usuario'] = $usuarioCredencial;
        
        return $credencial;
        
    }
	
	function getSeparadorCredencial($sufixo){
        
        $separadorDefault = 'SEPARADORCREDENCIALCNJ';
        $separador = $separadorDefault . $sufixo;
        return md5($separador);
        
    }
	
	function decriptografarAtributo($atributo){
        return base64_decode($atributo);
    }
}
?>
