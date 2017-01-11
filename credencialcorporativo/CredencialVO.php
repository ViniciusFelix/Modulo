<?php
class CredencialVO
{
    public $id;
    public $usuario;
    public $seqSistema;
    public $seqPerfil;
    public $funcionalidadesPerfil;
    public $datCriacao;
    public $timeToLive;
	
    public function setAtributo($atributo,$valor){
        $this->$atributo = $valor;
    }
    
    public function getDatCriacao(){
        return $this->datCriacao;
    }

    public function getFuncionalidadesPerfil(){
        return $this->funcionalidadesPerfil;
    }

    public function getId(){
        return $this->id;
    }

    public function getSeqPerfil(){
        return $this->seqPerfil;
    }
	
    public function getSeqSistema(){
        return $this->seqSistema;
    }

    public function getTimeToLive(){
        return $this->timeToLive;
    }


    public function getUsuario(){
        return $this->usuario;
    }

    public function setDatCriacao($datCriacao){
        $this->datCriacao = $datCriacao;
    }

    public function setFuncionalidadesPerfil($funcionalidadesPerfil){
        $this->funcionalidadesPerfil = $funcionalidadesPerfil;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setSeqPerfil($seqPerfil){
        $this->seqPerfil = $seqPerfil;
    }

    public function setSeqSistema($seqSistema){
        $this->seqSistema = $seqSistema;
    }

    public function setTimeToLive($timeToLive){
        $this->timeToLive = $timeToLive;
    }

    public function setUsuario($usuario){
        $this->usuario = $usuario;
    }
    
}

?>