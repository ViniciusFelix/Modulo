<?php
class xmlToArray{
	static function retornaArray(){
		require_once("xml.php");	
		$filename = "#Config_modXml.xml";
		
		$fp = fopen($filename,"r");
		$xml = fread ($fp, filesize ($filename));
		fclose($fp);
		
		$xml_parser = new xmlM();
		$xml_parser->parse($xml);
		$dom = $xml_parser->dom;

		if($dom['child_nodes'][0]['child_nodes'][0]['tag_name']=='db'){
			foreach ($dom['child_nodes'][0]['child_nodes'] as $chave => $valor){
				foreach($valor['child_nodes'] as $chave1 => $valor1){
					$array[$chave][$valor1['tag_name']] = $valor1['child_nodes'][0];	
				}
			}
		}		
		return $array;
	}
	
}
?>