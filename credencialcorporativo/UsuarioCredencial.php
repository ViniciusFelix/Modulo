<?php

class UsuarioCredencial{
    
    private $seqUsuario;
    private $sigUsuario;
    private $nomUsuario;
    private $seqOrgao;
    private $dscEmail;
    private $seqGrauJurisdicao; 
    private $tipOrgaoUsuario; 
    private $hierarquiaOrgaoUsuario; 
    private $seqOrgaoHierarquia;
    private $numCpf;
    
    
    /**
	 * @return the $numCpf
	 */
	public function getNumCpf() {
		return $this->numCpf;
	}

	/**
	 * @param field_type $numCpf
	 */
	public function setNumCpf($numCpf) {
		$this->numCpf = $numCpf;
	}

	public function setAtributo($atributo,$valor){
        $this->$atributo = $valor;
    }

    public function getSeqOrgaoHierarquia(){
    	return $this->seqOrgaoHierarquia;
    }
    
    public function setSeqOrgaoHierarquia($seqOrgaoHierarquia) {
    	$this->seqOrgaoHierarquia = $seqOrgaoHierarquia;
    }
    
	public function getHierarquiaOrgaoUsuario(){
        return $this->hierarquiaOrgaoUsuario;
    }

    public function setHierarquiaOrgaoUsuario($hierarquiaOrgaoUsuario){
        $this->hierarquiaOrgaoUsuario = $hierarquiaOrgaoUsuario;
    }
    
 	public function getTipOrgaoUsuario(){
        return $this->tipOrgaoUsuario;
    }

    public function setTipOrgaoUsuario($tipOrgaoUsuario){
        $this->tipOrgaoUsuario = $tipOrgaoUsuario;
    }
    
    public function getSeqUsuario(){
        return $this->seqUsuario;
    }

    public function setSeqUsuario($seqUsuario){
        $this->seqUsuario = $seqUsuario;
    }
    public function getDscEmail(){
        return $this->dscEmail;
    }

    public function getNomUsuario(){
        return $this->nomUsuario;
    }

    public function getSeqOrgao(){
        return $this->seqOrgao;
    }

    public function getSigUsuario(){
        return $this->sigUsuario;
    }

    public function setDscEmail($dscEmail){
        $this->dscEmail = $dscEmail;
    }

    public function setNomUsuario($nomUsuario){
        $this->nomUsuario = $nomUsuario;
    }

    public function setSeqOrgao($seqOrgao){
        $this->seqOrgao = $seqOrgao;
    }

    public function setSigUsuario($sigUsuario){
        $this->sigUsuario = $sigUsuario;
    }

    public function getSeqGrauJurisdicao(){
        return $this->seqGrauJurisdicao;
    }

    public function setSeqGrauJurisdicao($seqGrauJurisdicao){
        $this->seqGrauJurisdicao = $seqGrauJurisdicao;
    }

    
}

?>