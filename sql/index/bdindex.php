<?php
class bdindex{
	
	function bdindex($db)
	{
        $this->db = $db;
	}
	
	/**
	 * Método retorna seq_tribunal do usuário logado.
	 * @param unknown $seqUsuario
	 */
	function retornaSeqTribunal($seqUsuario)
	{
		$sql 	= " SELECT SEQ_TRIBUNAL
						FROM COMPARTILHADO.USUARIO
					WHERE SEQ_USUARIO = $seqUsuario";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0]['SEQ_TRIBUNAL'];
	}
	
	/**
	 * Método para retornar a uf via sistema SCA.
	 * @param unknown $numCpf
	 */
	function retornaSeqTribunalUsu($seqOrg)
	{
		$sql 	= " select tri.seq_tribunal,
						   tri.ind_uf,
						   tri.sig_tribunal,
						   org.TIP_ORGAO,
						   org.SEQ_ORGAO_PAI
					from corporativo.orgao org
					join corporativo.sigla_orgao so on so.SEQ_ORGAO = org.SEQ_ORGAO
					join COMPARTILHADO.TRIBUNAL tri on tri.sig_tribunal = so.DSC_SIGLA
					where org.seq_orgao = $seqOrg";
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0];
	}
	
	/**
	 * Método retorna os dados do magistrado logado no sistema pelo SCA.
	 */
	function retornaUsuarioPeloSca($cpf)
	{
		$sql = "SELECT 	usu.NOM_USUARIO,
						usu.SEQ_USUARIO,
						usu.NUM_CPF,
						org.*,
						tel.NUM_TELEFONE,
						em.DSC_EMAIL,
						usu.SIG_UF,
						sig.DSC_SIGLA
				FROM corporativo.usuario usu
				join corporativo.orgao org on usu.seq_orgao = org.seq_orgao
				left join corporativo.sigla_orgao sig on org.seq_orgao = sig.seq_orgao
				join corporativo.usuario_telefone tel on tel.seq_usuario = usu.seq_usuario
				join corporativo.usuario_email em on em.seq_usuario = usu.seq_usuario
				left join corporativo.cidade cid on cid.SEQ_CIDADE = org.SEQ_CIDADE
				WHERE usu.num_cpf = $cpf";
		$q	 = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0];
	}
	
	/**
	 * Método retorna o tribunal
	 * @return unknown
	 */
	function retornaTribunal($seqOrgaoPai)
	{
		$sql = "SELECT o.SEQ_ORGAO, o.DSC_ORGAO, o.SEQ_ORGAO_PAI, o.TIP_ORGAO, so.DSC_SIGLA, o.COD_HIERARQUIA
				FROM corporativo.orgao o
				LEFT JOIN corporativo.sigla_orgao so on so.SEQ_ORGAO = o.SEQ_ORGAO
				WHERE o.SEQ_ORGAO = ".$seqOrgaoPai;
		$q = $this->db->execute($sql);
		$res = $q->getRows();
		return $res[0];
	}
}	
?>