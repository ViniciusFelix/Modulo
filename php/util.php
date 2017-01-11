<?php
class util
{
	public function util($smarty)
	{
		require_once ("bibliotecas/PHPExcel/class-excel-xml.inc.php");
		$this->smarty = $smarty;
	}
	
	/**
	 * Caminho absoluto para os arquivos.
	 */
	var $caminho_absoluto1Grau 		 = 'php/importarArquivos/arq1grau/';
	var $caminho_absoluto2Grau 	 	 = 'php/segGrau/arq2grau/';
	var $caminho_absolutoMagistrado  = 'php/magistrado/arqMagistrado/';
	var $caminho_absolutoPergunta    = 'php/pergunta/arqPergunta/';
	
	function mesesAno(){
		$arr_meses = array(
				'01' => 'Janeiro',
				'02' => 'Fevereiro',
				'03' => 'Março',
				'04' => 'Abril',
				'05' => 'Maio',
				'06' => 'Junho',
				'07' => 'Julho',
				'08' => 'Agosto',
				'09' => 'Setembro',
				'10' => 'Outubro',
				'11' => 'Novembro',
				'12' => 'Dezembro'
		);
		return $arr_meses;
	}
	
	/**
	 * Método para gerar o xls.
	 * @param $res
	 * @param $cabecalho
	 */
	function gerarXLS($res, $cabecalho)
	{
		$xls = new Excel_XML;
		$xls->cabecalho($cabecalho);
		$xls->addArray($res);
		$xls->generateXML("relatorio");
		die();
	}
	
	/**
	 * Método monta dinamicamente o corpo do pdf.
	 * @param unknown $textoTop
	 * @param unknown $caminhoHTML
	 * @param unknown $conteudo
	 */
	public function montaPdf($textoTop,$caminhoHTML,$conteudo,$orientacaoPage=NULL)
	{
		$mpdf=ini_set("memory_limit","1024M");
		$mpdf=set_time_limit(0);
		$mpdf=new mPDF();
		
		$mpdf->progbar_altHTML = '
			 <div style="border-radius: 15px; height: 100px; margin-left: 35%; margin-top: 20em; text-align: center;  background:#AAAAAA; width: 30%;">
				<br />
				<table align="center">
					<tr>
						<td>
							<img style="vertical-align: middle;" src="html/img/loading.gif" width="50px" />
						</td>
						<td>
							<font style="font-family: Verdana; font-size: 25px; color: #FFFFFF;">Carregando...</font>
						</td>
					</tr>
				</table>
			 </div>';
		$mpdf->StartProgressBarOutput();
	
		if(!empty($orientacaoPage)){
			$dataHoraTop = "<div style='margin-left:870px;font-size:14px;margin-top:-250px;z-index:10;'>
			   				<span>Bras&iacute;lia {DATE j/m/Y}</span><br />
		    				</div>";
		}else{
			$dataHoraTop = "<div style='margin-left:550px;font-size:14px;margin-top:-145px;z-index:10;'>
			   				<span>Bras&iacute;lia {DATE j/m/Y}</span><br />
		    				</div>";
		}
	
		$header = " <table width='100%' style='background-color: #265C8A;'>
						<tr>
							<td>
								<h3><font style='color: #FFFFFF;'>M&oacute;dulo de Produtividade Mensal</font></h3>
							</td>
							<td width='1%'>
								<img src='html/img/marca-cnj.png' width='20%' style='margin-top: 18px;'/>
							</td>	
						</tr>
					</table>
					<table width='100%'>
						<tr>
							<td alingin='center'>
								<center><b>".utf8_encode($textoTop)."</b></center>
							</td>
						</tr>
					</table>
		$dataHoraTop";
		$footer = "<table align='center'><tr><td><i>Conselho Nacional de Justi&ccedil;a</i></td><td width='1%'>{PAGENO}</td></tr></table>";
		if(!empty($orientacaoPage)){
			$mpdf->SetMargins('3cm','2cm','45cm');
		}else{
			$mpdf->SetMargins('3cm','2cm','45cm');
		}
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->addPage($orientacaoPage);
		
		$this->smarty->assign("conteudo", $conteudo);

		$html = utf8_encode($this->smarty->fetch($caminhoHTML));

		$mpdf->WriteHTML($html);
		$mpdf->Output();
		exit;
	}
	
	/**
	 * Método para validar CPF.
	 * @param unknown $cpf
	 * @return boolean
	 */
	function validaCPF($cpf = null) 
	{
		// Verifica se um número foi informado
		if(empty($cpf)) {
			return false;
		}
		// Elimina possivel mascara
		$cpf = ereg_replace('[^0-9]', '', $cpf);
		$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
		// Verifica se o numero de digitos informados é igual a 11
		if (strlen($cpf) != 11) {
			return false;
		}
		// Verifica se nenhuma das sequências invalidas abaixo
		// foi digitada. Caso afirmativo, retorna falso
		else if($cpf == '00000000000' ||
				$cpf == '11111111111' ||
				$cpf == '22222222222' ||
				$cpf == '33333333333' ||
				$cpf == '44444444444' ||
				$cpf == '55555555555' ||
				$cpf == '66666666666' ||
				$cpf == '77777777777' ||
				$cpf == '88888888888' ||
				$cpf == '99999999999') {
				return false;
				// Calcula os digitos verificadores para verificar se o
				// CPF é válido
		} else {
			for ($t = 9; $t < 11; $t++) {
				 
				for ($d = 0, $c = 0; $c < $t; $c++) {
					$d += $cpf{$c} * (($t + 1) - $c);
				}
				$d = ((10 * $d) % 11) % 10;
				if ($cpf{$c} != $d) {
					return false;
				}
			}
			return $cpf;
		}
	}
	
	/**
	 * Método tira os espaços caso tenha cido digitado erradamente.
	 * @param unknown_type $tela
	 * @param unknown_type $ehNumero
	 * @return string|unknown
	 */
	public function converterTelaBanco($tela) {
		if (trim($tela) == "") {
			return 'NULL';
		} else {
			if (is_numeric($tela)) {
				return $tela;
			} else {
				return "'".trim(addslashes($tela))."'";
			}
		}
	}
	
	/**
	 * Método converte o cpf que foi digitado para o sql
	 * @param unknown_type $tela
	 * @return string
	 */
	function converterCpfBanco($tela)
	{
		if($tela){
			$quebra=explode(".", $tela);
			$quebra2=explode("-", $quebra[2]);
			return $quebra[0].$quebra[1].$quebra2[0].$quebra2[1];
		}else{
			return 'NULL';
		}
	}
	
	/**
	 * Método converte o telefone que foi digitado para o sql
	 * @param unknown_type $tela
	 * @return string
	 */
	public function coverterTelefoneBanco($tela)
	{
		if($tela){
			$quebra=explode("(", $tela);
			$quebra2=explode(")", $quebra[1]);
			$quebra3=explode("-", $quebra2[1]);
			return $quebra2[0].$quebra3[0].$quebra3[1];
		}else{
			return 'NULL';
		}
	}
	
	/**
	 * Método verifica se a data é valida
	 * @param unknown_type $tela
	 */
	public function validaData($tela)
	{
		if($tela){
			$barra = strripos($tela, '/');
			if($barra === false){
				$data = explode("-",$tela);
				if(checkdate ( $data[1] , $data[2] , $data[0] ))
					return true;
				else
					return NULL;
			}else{
				$data = explode("/",$tela);
				if(checkdate ( $data[0] , $data[1] , $data[2] ))
					return true;
				else
					return NULL;
			}
		}else{
			return NULL;
		}
	}
	
	/**
	 * Verifica se string precisa de utf8
	 * @param unknown $tela
	 */
	public function precisaUtf8($tela)
	{
		$encoding = mb_detect_encoding($tela.'x', 'UTF-8, ISO-8859-1');
		if($encoding == 'UTF-8'){
			return utf8_decode($tela);
		}else{
			return trim($tela);
		}
	}
	
	public function temPontoOuVirgula($tela)
	{
		$ponto   = explode(".",$tela);
		$virgula = explode(",",$tela);
		if(count($ponto) > 1 || count($virgula) > 1){
			return NULL;
		}else{
			return true;
		}
	}
	
	public function validaDataXml($tela){
		if($tela){
			$data = explode("-",$tela);
			$res = checkdate($data[1],$data[2],$data[0]);
			if($res)
				return true;
			else
				return NULL;
		}else{
			return true;
		}
	}
	
	/**
	 * Método converte a data que foi digitada para o sql
	 * @param unknown_type $tela
	 */
	public function converterDataParaBancoXls($tela,$formato)
	{
		if($tela){
			$data = explode("/",$tela);
			if(isset($formato['format']) && $formato['format'] == 'm/d/Y'){
				$resFormatoXls 		= checkdate($data[0],$data[1],$data[2]);
				$resFormatoDigitado = checkdate($data[1],$data[0],$data[2]);
				if($resFormatoXls || $resFormatoDigitado)
					return trim($data[2])."-".trim($data[0])."-".trim($data[1]);
				else 
					return NULL;
			}else{
				$res = checkdate($data[1],$data[0],$data[2]);
				if($res)
					return trim($data[2])."-".trim($data[1])."-".trim($data[0]);
				else
					return NULL;
			}
		}else{
			return NULL;
		}
	}
	
	/**
	 * Método converte a data que foi digitada para o sql
	 * @param unknown_type $tela
	 */
	public function converterDataParaBanco($tela)
	{
		if($tela){
			$barra = strripos($tela, '/');
			if($barra === false){
				return $tela;
			}else{
				$data = explode("/",$tela);
				return $data[2]."-".$data[1]."-".$data[0];
			}
		}else{
			return 'NULL';
		}
	}
	
	/**
	 * Método converte o cep que foi digitada para o sql
	 * @param unknown_type $tela
	 */
	public function converterCepParaBanco($tela)
	{
		if($tela){
			$quebra1 = explode(".", $tela);
			$quebra2 = explode("-", $quebra1[1]);
			return "'".$quebra1[0].$quebra2[0].$quebra2[1]."'";
		}else{
			return 'NULL';
		}
	}
	
	/**
	 * Mètodo retorna o tipo do arquivo.
	 * @param unknown $nomeArquivo
	 * @return string
	 */
	public function retornarTipoDocumento($nomeArquivo)
	{
		$tipo = explode(".",$nomeArquivo);
		$tipo = $tipo[(sizeof($tipo)-1)];
		return $tipo;
	}
	
	/**
	 * Método para forçar a baixar os documentos.
	 */
	public function baixarDocumento($caminho,$nomeArquivo)
	{
		$arquivo = $caminho.$nomeArquivo;
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($arquivo));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($arquivo));
		ob_clean();
		readfile($arquivo);
		exit;
	}
	
	/**
	 * Método pega o número do mes e transforma no nome do mes informado.
	 */
	function qualMes($mes)
	{
		switch ($mes){
			case "JANEIRO":
				$numMes = '01';
				break;
			case "FEVEREIRO":
				$numMes = '02';
				break;
			case "MARÇO":
				$numMes = '03';
				break;
			case "ABRIL":
				$numMes = '04';
				break;
			case "MAIO":
				$numMes = '05';
				break;
			case "JUNHO":
				$numMes = '06';
				break;
			case "JULHO":
				$numMes = '07';
				break;
			case "AGOSTO":
				$numMes = '08';
				break;
			case "SETEMBRO":
				$numMes = '09';
				break;
			case "OUTUBRO":
				$numMes = '10';
				break;
			case "NOVEMBRO":
				$numMes = '11';
				break;
			case "DEZEMBRO":
				$numMes = '12';
				break;
		}
		return $numMes;
	}
	
	/**
	 * Método pega o número do mes e transforma no nome do mes informado.
	 */
	function qualMesNumero($mes)
	{
		switch ($mes){
			case 1:
				$mes = "JANEIRO";
				break;
			case 2:
				$mes = "FEVEREIRO";
				break;
			case 3:
				$mes = "MARÇO";
				break;
			case 4:
				$mes = "ABRIL";
				break;
			case 5:
				$mes = "MAIO";
				break;
			case 6:
				$mes = "JUNHO";
				break;
			case 7:
				$mes = "JULHO";
				break;
			case 8:
				$mes = "AGOSTO";
				break;
			case 9:
				$mes = "SETEMBRO";
				break;
			case 10:
				$mes = "OUTUBRO";
				break;
			case 11:
				$mes = "NOVEMBRO";
				break;
			case 12:
				$mes = "DEZEMBRO";
				break;
		}
		if(is_numeric($mes))
			return NULL;
		else
			return $mes;
	}	
	
	/**
	 * Método orderna de acordo com um item do array.
	 * @param unknown $array
	 * @param unknown $on
	 * @param string $order
	 * @return unknown[]
	 */
	function array_sort($array, $on, $order=SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();
	
		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}
	
			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}
	
			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}
	
		return $new_array;
	}
	
	/**
	 * Método retorna a referencia para importação dos arquivos xls.
	 * @param unknown $passo
	 * @param unknown $tipo
	 * @param unknown $perguntas
	 * @return string|unknown
	 */
	function referenciaImportacao($passo,$tipo,$perguntas)
	{
		if ($passo == 1){
			if($tipo == 1){
				$referencia[1] = "codServentia";
				$referencia[2] = "grau";
				$referencia[3] = "descricao";
				$referencia[4] = "uf";
				$referencia[5] = "internet";
				$referencia[6] = "municipio";
				$referencia[7] = "instalacao";
				$referencia[8] = "municipiosAbrangidos";
				$referencia[9] = "competencias";
				$referencia[10] = "latitude";
				$referencia[11] = "longitude";
				$referencia[12] = "status";
				if($_SESSION['tip_orgao'] == 'TRIBE')
					$referencia[13] = "entrancia";
			}else if($tipo == 2){
				$referencia[1] = "codServentia";
				$referencia[2] = "grau";
				$referencia[3] = "descricao";
				$referencia[4] = "uf";
				$referencia[5] = "internet";
				$referencia[6] = "municipio";
				$referencia[7] = "instalacao";
				$referencia[8] = "municipiosAbrangidos";
				$referencia[9] = "latitude";
				$referencia[10] = "longitude";
				$referencia[11] = "status";
			}else if($tipo == 3){
				$referencia[1] = "codServentia";
				$referencia[2] = "descricao";
				$referencia[3] = "status";
			}else if($tipo == 4){
				$referencia[1] = "codServentia";
				$referencia[2] = "descricao";
				$referencia[3] = "competencias";
				$referencia[4] = "status";
			}
		}else if($passo == 2){
			$referencia[1]  = "cpf";
			$referencia[2]  = "nome";
			$referencia[3]  = "matricula";
			$referencia[4]  = "uf";
			$referencia[5]  = "datNascimento";
			$referencia[6]  = "email";
			$referencia[7]  = "telefone";
			$referencia[8]  = "datPosse";
			$referencia[9]  = "sexo";
			$referencia[10] = "status";
		}else if($passo == 3){
			$referencia[1] = "codServentia";
			$referencia[2] = "mes";
			$referencia[3] = "ano";
			$referencia[4] = "obs";
		}else if($passo == 4){
			$referencia[1] = "codMag";
			$referencia[2] = "codServentia";
			$referencia[3] = "tipo";
			$referencia[4] = "mes";
			$referencia[5] = "ano";
			$referencia[6] = "qtdDias";
			$referencia[7] = "obs";
		}
		if($perguntas){
			$i=count($referencia)+1;
			foreach ($perguntas as $value) {
				$referencia[$i] = $value['sigla'];
				$i++;
			}
		}
		return $referencia;
	}
	
	/**
	 * Metodo para validar o cabeçalho dos xls.
	 * @param unknown $passo
	 * @param unknown $tipo
	 * @param unknown $posicao
	 * @param unknown $perguntas
	 * @return string|unknown
	 */
	function campoXls($passo,$tipo=NULL,$posicao=NULL,$perguntas=NULL)
	{
		if ($passo == 1){
			if($tipo == 1){
				$cabecalho[1] = "Código Serventia";
				$cabecalho[2] = "Grau";
				$cabecalho[3] = "Nome Serventia";
				$cabecalho[4] = "UF";
				$cabecalho[5] = "Internet";
				$cabecalho[6] = "Municipio";
				$cabecalho[7] = "Instalação";
				$cabecalho[8] = "Municipios Abrangidos";
				$cabecalho[9] = "Competencias";
				$cabecalho[10] = "Latitude";
				$cabecalho[11] = "Longitude";
				$cabecalho[12] = "Status";
				if($_SESSION['tip_orgao'] == 'TRIBE')
					$cabecalho[13] = "Entrância";
			}else if($tipo == 2){
				$cabecalho[1] = "Código Serventia";
				$cabecalho[2] = "Grau";
				$cabecalho[3] = "Nome Serventia";
				$cabecalho[4] = "UF";
				$cabecalho[5] = "Internet";
				$cabecalho[6] = "Municipio";
				$cabecalho[7] = "Instalação";
				$cabecalho[8] = "Municipios Abrangidos";
				$cabecalho[9] = "Latitude";
				$cabecalho[10] = "Longitude";
				$cabecalho[11] = "Status";
			}else if($tipo == 3){
				$cabecalho[1] = "Código Serventia";
				$cabecalho[2] = "Nome Serventia";
				$cabecalho[3] = "Status";
			}else if($tipo == 4){
				$cabecalho[1] = "Código Serventia";
				$cabecalho[2] = "Nome Serventia";
				$cabecalho[3] = "Competencias";
				$cabecalho[4] = "Status";
			}
			if($perguntas){
				$i=count($cabecalho)+1;
				foreach ($perguntas as $value) {
					$cabecalho[$i] = $value['sigla'];
					$i++;
				}
			}
		}else if($passo == 2){
			if($tipo == 'rel'){
				$cabecalho[1]  = "Codigo magistrado";
				$cabecalho[2]  = "CPF";
				$cabecalho[3]  = "Nome";
				$cabecalho[4]  = "Matricula";
				$cabecalho[5]  = "UF";
				$cabecalho[6]  = "Data Nascimento";
				$cabecalho[7]  = "Email";
				$cabecalho[8]  = "Telefone";
				$cabecalho[9]  = "Data posse";
				$cabecalho[10] = "Sexo";
				$cabecalho[11] = "Status";
			}else{
				$cabecalho[1]  = "CPF";
				$cabecalho[2]  = "Nome";
				$cabecalho[3]  = "Matricula";
				$cabecalho[4]  = "UF";
				$cabecalho[5]  = "Data Nascimento";
				$cabecalho[6]  = "Email";
				$cabecalho[7]  = "Telefone";
				$cabecalho[8]  = "Data posse";
				$cabecalho[9] = "Sexo";
				$cabecalho[10] = "Status";
			}
		}else if($passo == 3){
			$cabecalho[1] = "Código Serventia";
			$cabecalho[2] = "Mes";
			$cabecalho[3] = "Ano";
			$cabecalho[4] = "Observação";
			$perguntaOrdenadas = $this->array_sort($perguntas,'NUM_ORDEM');
			$i=count($cabecalho)+1;
			foreach ($perguntaOrdenadas as $key => $value) {
				$cabecalho[$i] = $value['sigla'];
				$i++;
			}
		}else if($passo == 4){
			$cabecalho[1] = "Código Magistrado";
			$cabecalho[2] = "Código Serventia";
			$cabecalho[3] = "Tipo Juiz";
			$cabecalho[4] = "Mes";
			$cabecalho[5] = "Ano";
			$cabecalho[6] = "Quantidade dias corridos";
			$cabecalho[7] = "Observação";
			$i=count($cabecalho)+1;
			$perguntaOrdenadas = $this->array_sort($perguntas,'NUM_ORDEM');
			foreach ($perguntaOrdenadas as $key => $value) {
				$cabecalho[$i] = $value['sigla'];
				$i++;
			}
		}
		if($posicao)
			return $cabecalho[$posicao];
		else 
			return $cabecalho;
	}
	
	function campoXml($passo,$tipo=NULL,$perguntas=NULL)
	{
		if ($passo == 1){
			if($tipo == 1){
				$cabecalho[1]    = "CODIGO_SERVENTIA";
				$cabecalho[2]    = "GRAU";
				$cabecalho[3]    = "DENOMINACAO";
				$cabecalho[4]    = "UF";
				$cabecalho[5]    = "INTERNET";
				$cabecalho[6]    = "MUNICIPIO";
				$cabecalho[7]    = "INSTALACAO";
				$cabecalho[8][0] = "MUNICIPIOS_ABRANGIDOS";
				$cabecalho[8][1] = "CODIGO_MUNICIPIO";
				$cabecalho[9][0] = "COMPETENCIA";
				$cabecalho[9][1] = "CODIGO";
				$cabecalho[10] 	 = "LATITUDE";
				$cabecalho[11] 	 = "LONGITUDE";
				$cabecalho[12] 	 = "STATUS";
				if($_SESSION['tip_orgao'] == 'TRIBE')
					$cabecalho[13] 	 = "ENTRANCIA";
			}else if($tipo == 2){
				$cabecalho[1] = "CODIGO_SERVENTIA";
				$cabecalho[2] = "GRAU";
				$cabecalho[3] = "DENOMINACAO";
				$cabecalho[4] = "UF";
				$cabecalho[5] = "INTERNET";
				$cabecalho[6] = "MUNICIPIO";
				$cabecalho[7] = "INSTALACAO";
				$cabecalho[8][0] = "MUNICIPIOS_ABRANGIDOS";
				$cabecalho[8][1] = "CODIGO_MUNICIPIO";
				$cabecalho[9]  = "LATITUDE";
				$cabecalho[10] = "LONGITUDE";
				$cabecalho[11] = "STATUS";
			}else if($tipo == 3){
				$cabecalho[1] = "CODIGO_SERVENTIA";
				$cabecalho[2] = "DENOMINACAO";
				$cabecalho[3] = "STATUS";
			}else if($tipo == 4){
				$cabecalho[1] = "CODIGO_SERVENTIA";
				$cabecalho[2] = "DENOMINACAO";
				$cabecalho[3][0] = "COMPETENCIA";
				$cabecalho[3][1] = "CODIGO";
				$cabecalho[4] = "STATUS";
			}
			if($perguntas){
				$i=count($cabecalho)+1;
				foreach ($perguntas as $value) {
					$cabecalho['RECURSOS_HUMANOS'][$i] = $value['sigla'];
					$i++;
				}
			}
		}else if ($passo == 2){
			$cabecalho[1]  = "NUM_CPF_MAGISTRADO";
			$cabecalho[2]  = "NOM_MAGISTRADO";
			$cabecalho[3]  = "NUM_MATRICULA";
			$cabecalho[4]  = "IND_UF";
			$cabecalho[5]  = "DAT_NASCIMENTO";
			$cabecalho[6]  = "DSC_EMAIL_INSTITUCIONAL";
			$cabecalho[7]  = "NUM_TELEFONE_GABINETE";
			$cabecalho[8]  = "DAT_INGRESSO_MAGISTRATURA";
			$cabecalho[9]  = "SEXO";
			$cabecalho[10] = "STATUS";
		}else if ($passo == 3){
			$cabecalho[1]  = "PERGUNTA";
			$cabecalho[2]  = "SERVENTIA";
			$cabecalho[3]  = "MES";
			$cabecalho[4]  = "ANO";
			$cabecalho[5]  = "RESPOSTA";
			$cabecalho[6]  = "OBSERVACAO";
		}else if ($passo == 4){
			$cabecalho[1] = "PERGUNTA";
			$cabecalho[2] = "MAGISTRADO";
			$cabecalho[3] = "SERVENTIA";
			$cabecalho[4] = "TIPO_MAGISTRADO";
			$cabecalho[5] = "MES";
			$cabecalho[6] = "ANO";
			$cabecalho[7] = "QUANTIDADE_DIAS_CORRIDOS";
			$cabecalho[8] = "RESPOSTA";
			$cabecalho[9] = "OBSERVACAO";
		}
		return $cabecalho;
	}
	
	/**
	 * Método monta a explicação dos campos dos exemplos.
	 * @param unknown $passo
	 * @param unknown $tipo
	 * @return string
	 */
	function explicacaoCampo($passo,$tipo=NULL,$perguntas=NULL,$arq=NULL)
	{
		if ($passo == 1){
			if($tipo == 1){
				$explicacao[1] = "Inserir o código da serventia junto ao CNJ (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").Observar que deve ser o mesmo código utilizado nos demais arquivos.";
				if($_SESSION['tip_orgao'] == 'TRIBE')
					$explicacao[2] = "Preencher com os códigos 1 (para 1º grau), 2 (para 2º grau), 3 (para Turma Recursal) ou 4 (para Juizado Especial).";
				else if($_SESSION['tip_orgao'] == 'TRIBF')
					$explicacao[2] = "Preencher com os códigos 1 (para 1º grau), 2 (para 2º grau), 3 (para Turma Recursal), 4 (para Juizado Especial) ou 5 (para Turma Regional de Uniformização).";
				if($_SESSION['tip_orgao'] == 'TRIBF')
					$explicacao[3] = "Colocar o nome da Serventia (por exemplo \"1ª Vara Federal\", no 1º grau ou \"Gabinete do Desembargador...\", no 2º grau)";
				else
					$explicacao[3] = "Colocar o nome da Serventia (por exemplo \"1ª Vara Cível\" no 1º grau ou \"Gabinete do Desembargador...\", no 2º grau).";
				$explicacao[4] = "Inserir a UF na qual a serventia está situada.";
				$explicacao[5] = "Se a serventia possui internet. Preencher S para sim e N para não";
				$explicacao[6] = "Inserir o código do município-sede junto ao CNJ (vide Lista na aba início: \"Listagem de municípios do seu tribunal\").";
				if($arq == 'xls')
					$explicacao[7] = "Inserir a data da instalação da serventia, no formato DD/MM/AAAA.";
				else 
					$explicacao[7] = "Data da instalação da serventia. Formatação: 0000-00-00 / Ano-Mes-Dia.";
				if($arq == 'xls'){
					$explicacao[8] = "Todos os códigos CNJ dos municípios abrangidos pela jurisdição da serventia, separados por virgula (vide Lista na aba início: \"Listagem de municípios do seu tribunal\").";
					$explicacao[9] = "Todos os códigos das competências da serventia, separados por virgula (vide Lista na aba início: \"Listagem de competências das serventias\").";
				}else{
					$explicacao[8] = "Todos os códigos CNJ dos municípios abrangidos pela jurisdição da serventia (vide Lista na aba início: \"Listagem de municípios do seu tribunalstado\").";
					$explicacao[9] = "Todos os códigos das competências da serventia (vide Lista na aba início: \"Listagem de competências das serventias\").";
				}
				$explicacao[10] = "Código da Latitude de localização da serventia.";
				$explicacao[11] = "Código da Longitude de localização da serventia.";
				$explicacao[12] = "Inserir o status da serventia: S ativo e N para inativa.";
				if($_SESSION['tip_orgao'] == 'TRIBE')
					$explicacao[13] = "Indicar a entrância da comarca (Opções: 1,2,3,4)
										a) No caso de tribunais com estrutura de quatro entrâncias, preencher com os códigos 1 a 4;
										b) No caso de tribunais com estrutura de três entrâncias, preencher com os códigos 1 a 3;
										c) no caso de tribunais com estrutura de duas entrâncias, preencher com os códigos 1 ou 2;
										d) no caso de tribunais com estrutura de apenas uma entrância, preencher com o código 1.";
			}else if($tipo == 2){
				$explicacao[1] = "Inserir o código da serventia junto ao CNJ (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").Observar que deve ser o mesmo código utilizado nos demais arquivos.";
				$explicacao[2] = "Preencher com os códigos 1 (para 1º grau) ou 2 (para 2º grau).";
				$explicacao[3] = "Colocar o nome da Serventia (por exemplo \"1º Cartório Eleitoral\" no 1º grau ou \"Gabinete do Desembargador...\", no 2º grau).";
				$explicacao[4] = "Inserir a UF na qual a serventia está situada.";
				$explicacao[5] = "Se a serventia possui internet. Preencher S para sim e N para não";
				$explicacao[6] = "Inserir o código do município-sede junto ao CNJ (vide Lista na aba início: \"Listagem de municípios do seu tribunal\").";
				if($arq == 'xls')
					$explicacao[7] = "Inserir a data da instalação da serventia, no formato DD/MM/AAAA.";
				else 
					$explicacao[7] = "Data da instalação da serventia. Formatação: 0000-00-00 / Ano-Mes-Dia.";
				$explicacao[8] = "Todos os códigos CNJ dos municípios abrangidos pela jurisdição da serventia, separados por virgula (vide Lista na aba início: \"Listagem de municípios do seu estado\").";
				$explicacao[9] = "Código da Latitude de localização da serventia.";
				$explicacao[10] = "Código da Longitude de localização da serventia.";
				$explicacao[11] = "Inserir o status da serventia: S ativo e N para inativa.";
			}else if($tipo == 3){
				$explicacao[1] = "Inserir o código da serventia junto ao CNJ (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").Observar que deve ser o mesmo código utilizado nos demais arquivos.";
				$explicacao[2] = "Colocar o nome da Serventia, sem o número (por exemplo \"Vara Cível\", no caso de \"2ª Vara Cível\")";
				$explicacao[3] = "Inserir o status da serventia: S ativo e N para inativa.";
			}else if($tipo == 4){
				$explicacao[1] = "Inserir o código da serventia junto ao CNJ (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").Observar que deve ser o mesmo código utilizado nos demais arquivos.";
				$explicacao[2] = "Colocar o nome da Serventia, sem o número (por exemplo \"Vara Cível\", no caso de \"2ª Vara Cível\")";
				$explicacao[3] = "Todos os códigos das competências da serventia, separados por virgula (vide Lista na aba início: \"Listagem de competências das serventias\").";
				$explicacao[4] = "Inserir o status da serventia: S ativo e N para inativa.";
			}
		}else if ($passo == 2){
			if($arq == 'xls'){
				$explicacao[1]  = "Inserir o número do CPF do Magistrado, com 11 dígitos, sem caracteres especiais (Ex: 81254444780).";
				$explicacao[2]  = "Inserir o nome completo do Magistrado.";
				$explicacao[3]  = "Inserir o número de matrícula do Magistrado.";
				$explicacao[4]  = "Inserir a UF de lotação do Magistrado.";
				$explicacao[5]  = "Inserir a data de nascimento do magistrado. Formato dd/mm/aaaa(Ex.: 15/06/1970).";
				$explicacao[6]  = "Inserir o e-mail institucional do Magistrado.";
				$explicacao[7]  = "Inserir o número do telefone do gabinete do Magistrado, com DDD, sem caracteres especiais.Ex: 6125127679.";
				$explicacao[8]  = "Inserir a data de ingresso na magistratura. Formato dd/mm/aaaa(Ex.: 19/04/1990).";
				$explicacao[9]  = "Inserir o sexo do Magistrado: M para masculino e F para feminino.";
				$explicacao[10] = "Inserir código Status do magistrado junto ao CNJ(vide Lista na aba início: \"Listagem status magistrados\")";
			}else{
				$explicacao[1]  = "Inserir o número do CPF do Magistrado, com 11 dígitos, sem caracteres especiais (Ex: 81254444780).";
				$explicacao[2]  = "Inserir o nome completo do Magistrado.";
				$explicacao[3]  = "Inserir o número de matrícula do Magistrado.";
				$explicacao[4]  = "Inserir a UF de lotação do Magistrado.";
				$explicacao[5]  = "Inserir a data de nascimento do magistrado. Formato aaaa-mm-dd(Ex.: 1970-06-15).";
				$explicacao[6]  = "Inserir o e-mail institucional do Magistrado.";
				$explicacao[7]  = "Inserir o número do telefone do gabinete do Magistrado, com DDD, sem caracteres especiais.Ex: 6125127679.";
				$explicacao[8]  = "Inserir a data de ingresso na magistratura. Formato aaaa-mm-dd(Ex.: 1970-06-15).";
				$explicacao[9]  = "Inserir o sexo do Magistrado: M para masculino e F para feminino.";
				$explicacao[10] = "Inserir código Status do magistrado junto ao CNJ(vide Lista na aba início: \"Listagem status magistrados\")";
			}
		}else if ($passo == 3){
			if($arq == 'xls'){
				$explicacao[1] = "Inserir o código da serventia junto ao CNJ. Deve ser o mesmo utilizado para o cadastro da serventia no primeiro passo (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").";
				$explicacao[2] = "Coloque o número do mês de referência das informações relativas à produtividade da seventia (1 a 12).";
				$explicacao[3] = "Coloque o número do ano de referência das informações relativas à produtividade da seventia. (Ex.: 2015, 2016, ...).";
				$explicacao[4] = "Alguma observação referenta a produtividade";
			}else{
				$explicacao[1] = "Código da pergunta junto ao CNJ(vide lista na aba início \"Listagem de perguntas referentes à produtividade da Serventia\")";
				$explicacao[2] = "Inserir o código da serventia junto ao CNJ. Deve ser o mesmo utilizado para o cadastro da serventia no primeiro passo (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").";
				$explicacao[3] = "Coloque o número do mês de referência das informações relativas à produtividade da seventia (1 a 12).";
				$explicacao[4] = "Coloque o número do ano de referência das informações relativas à produtividade da seventia. (Ex.: 2015, 2016, ...).";
				$explicacao[5] = "Coloque o valor resposta da pergunta, ou seja, o quantitativo correspondente à pergunta, para dado mês,ano e serventia";
				$explicacao[6] = "Alguma observação referenta a produtividade";
			}
		}else if ($passo == 4){
			if($arq == 'xls'){
				$explicacao[1] = "Inserir o código do magistrado junto ao CNJ. Deve ser o mesmo utilizado para o cadastro do magistrado no segundo passo (vide Lista na aba início: \"Listagem dos magistrados\").";
				$explicacao[2] = "Inserir o código da serventia junto ao CNJ. Deve ser o mesmo utilizado para o cadastro da serventia no primeiro passo (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").";
				$explicacao[3] = "Código do Tipo do Juiz junto ao CNJ (vide Lista na aba início: \"Listagem de tipos de magistrados\").";
				$explicacao[4] = "Coloque o número do mês de referência das informações relativas à produtividade da seventia.1 a 12).";
				$explicacao[5] = "Coloque o número do ano de referência das informações relativas à produtividade da seventia.(Ex.: 2015, 2016, ...).";
				$explicacao[6] = "Coloque a quantidade de dias corridos de atuação do magistrado no mês.";
				$explicacao[7] = "Coloque alguma observação atinente aos dados incluídos. Em caso de inexistência de observação deixar o campo em branco.";
			}else{
				$explicacao[1] = "Código da pergunta junto ao CNJ(Lista na aba início \"Listagem de perguntas destinadas aos magistrados\")";
				$explicacao[2] = "Inserir o código do magistrado junto ao CNJ. Deve ser o mesmo utilizado para o cadastro do magistrado no segundo passo (vide Lista na aba início: \"Listagem dos magistrados\")";
				$explicacao[3] = "Inserir o código da serventia junto ao CNJ. Deve ser o mesmo utilizado para o cadastro da serventia no primeiro passo (vide Lista na aba início: \"Listagem de serventias do seu tribunal\").";
				$explicacao[4] = "Código do Tipo do Juiz junto ao CNJ (vide Lista na aba início: \"Listagem de tipos de magistrados\").";
				$explicacao[5] = "Coloque o número do mês de referência das informações relativas à produtividade da seventia. (1 a 12).";
				$explicacao[6] = "Coloque o número do ano de referência das informações relativas à produtividade da seventia. (Ex.: 2015, 2016, ...).";
				$explicacao[7] = "Coloque a quantidade de dias corridos de atuação do magistrado no mês.";
				$explicacao[8] = "Coloque o valor resposta da pergunta, ou seja, o quantitativo correspondente à pergunta, para dado mês,ano,serventia e magistrado.";
				$explicacao[9] = "Alguma observação da produtividade do magistrado.";
			}
		}
		if($perguntas){
			$i=count($explicacao)+1;
			foreach ($perguntas as $value) {
				$explicacao[$i] = $value['descricao'];
				$i++;
			}
		}
		return $explicacao;
	}
	
	function retornaTipoOrgao($grau)
	{
		switch ($_SESSION['tip_orgao']){
			case "TRIBE":
				if($grau == 1){
					$tipoOrgao = 'VARAE';
					break;
				}else if($grau == 2){
					$tipoOrgao = 'GABIE';
					break;
				}
			case "TRIBF":
				if($grau == 1){
					$tipoOrgao = 'VARAF';
					break;
				}else if($grau == 2){
					$tipoOrgao = 'GABIF';
					break;
				}
			case "TRIBM":
				$tipoOrgao = 'VARAM';
				break;
			case "TRIBT":
				if($grau == 1){
					$tipoOrgao = 'GRA1T';
					break;
				}else if($grau == 2){
					$tipoOrgao = 'GRA2T';
					break;
				}
			case "TRIBL":
				$tipoOrgao = 'ZONA';
				break;
			case "TRIBS":
				$tipoOrgao = 'GABIM';
				break;
		}
		
		if($tipoOrgao)
			return $tipoOrgao;
		else 
			return NULL;
	}
}
?>