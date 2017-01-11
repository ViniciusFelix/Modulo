<?php
class tipoCampos{
	function __construct(){
		
	}
	
	function gerarCampo($parametros, $selecionado=""){		
		if($parametros['TIP_TIPO']=='nhu'){
			$retorno = $this->nhu($parametros);
		}
		
		if($parametros['TIP_TIPO']=='txt'){
			$retorno = $this->txt($parametros);
		}
		
		if($parametros['TIP_TIPO']=='txl'){
			$retorno = $this->txl($parametros);
		}

		if($parametros['TIP_TIPO']=='int' ){
			$retorno = $this->int($parametros);
		}
		
		if($parametros['TIP_TIPO']=='dec' ){
			$retorno = $this->dec($parametros);
		}
		
		if($parametros['TIP_TIPO']=='cmb' ){			
			$retorno = $this->cmb($parametros);
		}
		
		if($parametros['TIP_TIPO']=='chk' ){
			$retorno = $this->chk($parametros);
		}
		
		if($parametros['TIP_TIPO']=='rab' ){
			$retorno = $this->rab($parametros);
		}
		
		if($parametros['TIP_TIPO']=='mea' ){
			$retorno = $this->mea($parametros);
		}
		
		if($parametros['TIP_TIPO']=='dat' ){
			$retorno = $this->dat($parametros);
		}
		
		if($parametros['action']=='edit' && $selecionado==$parametros['SEQ_PERGUNTA']){
			$_SESSION['propriedades'] = $parametros;

		}
		return $retorno;
	}
	
	function nhu($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;";
		$html .= "\n		</td>";	
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function txt($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input type=\"text\" size=\"6\" name=\"txt".$parametros['SEQ_PERGUNTA']."\" id=\"txt".$parametros['SEQ_PERGUNTA']."\" value=\"texto\">";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";	
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function txl($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<textarea cols=\"6\" rows=\"1\" name=\"txl".$parametros['SEQ_PERGUNTA']."\" id=\"txl".$parametros['SEQ_PERGUNTA']."\">texto longo</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function int($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input type=\"text\" size=\"6\" name=\"int".$parametros['SEQ_PERGUNTA']."\" id=\"int".$parametros['SEQ_PERGUNTA']."\" value=\"inteiro\">";		
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function dec($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input type=\"text\" size=\"6\" name=\"dec".$parametros['SEQ_PERGUNTA']."\" id=\"dec".$parametros['SEQ_PERGUNTA']."\" value=\"decimal\">";		
		$html .= "\n		</td>";			
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function cmb($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<select name=\"cmb".$parametros['SEQ_PERGUNTA']."\" id=\"cmb".$parametros['SEQ_PERGUNTA']."\" ><option>combobox</option></select>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function chk($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input type=\"checkbox\" name=\"chk".$parametros['SEQ_PERGUNTA']."\" id=\"chk".$parametros['SEQ_PERGUNTA']."\" value=\"\">Checkbox";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function rab($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input type=\"radio\" name=\"rab".$parametros['SEQ_PERGUNTA']."\" id=\"rab".$parametros['SEQ_PERGUNTA']."\" value=\"\">Radio";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function mea($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input size=\"3\" type=\"text\" name=\"mes".$parametros['SEQ_PERGUNTA']."\" id=\"mes".$parametros['SEQ_PERGUNTA']."\" value=\"Mês\">&nbsp;e&nbsp;<input size=\"3\" type=\"text\" name=\"ano".$parametros['SEQ_PERGUNTA']."\" id=\"ano".$parametros['SEQ_PERGUNTA']."\" value=\"Ano\">";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";			
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
	
	function dat($parametros){		
		$html  = "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		$html .= "\n	<tr>";
		$html .= "\n		<td class=\"td_pergunta\">";
		$html .= "\n			<strong>".$parametros['NOM_NOME_PERGUNTA']."</strong>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_textarea\">";
		$html .= "\n			Descrição da pergunta<br />";
		$html .= "\n			<textarea rows=\"2\" cols=\"35\" name=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\" id=\"DSC_PERGUNTA".$parametros['SEQ_PERGUNTA']."\">".$parametros['DSC_PERGUNTA']."</textarea>";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_tipo\">";
		$html .= "\n			Tipo<br />";
		$html .= "\n			<br /><strong>".$parametros['TIP_TIPO']."</strong>&nbsp;<input size=\"12\" type=\"text\" name=\"dat".$parametros['SEQ_PERGUNTA']."\" id=\"dat".$parametros['SEQ_PERGUNTA']."\" value=\"dd/MM/yyyy\">&nbsp;Data";		
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_espaco\">";
		$html .= "\n			Espaços<br />";
		$html .= "\n			<br /><strong>".$parametros['NUM_ESPACO_PERGUNTA']." Espaço(s)</strong>&nbsp;";
		$html .= "\n		</td>";
		$html .= "\n		<td class=\"td_destaque\">";
		$html .= "\n			Destaque<br />";
		$html .= "\n			<br /><strong>".($parametros['FLG_PERGUNTA_DESTAQUE']==1?"SIM":"NÃO")." </strong>&nbsp;<a href=\"#\" onclick=\"editar('".$parametros['SEQ_PERGUNTA']."')\"><img src=\"./html/img/prop.gif\" border=\"0\"/></a>";
		$html .= "\n		</td>";
		$html .= "\n	</tr>";
		$html .= "\n</table>";
		return $html;	
	}
}

?>