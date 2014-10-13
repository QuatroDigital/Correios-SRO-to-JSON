<?php
/**
 * Quatro Digital - Correios SRO to JSON
 * 
 * Este script lê a página de rastreamento dos Correios com base em código de rastreio informado via GET ou POST 
 * e a transforma em JSON ou JSONP 
 *
 * @version 1.0
 * @author Carlos Vinicius
 * @license MIT <http://opensource.org/licenses/MIT>
 */

include("helpers/simple_html_dom.php");

header('Content-Type: application/json; charset=utf-8;');

$correiosSRO = file_get_html("http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=002&P_COD_LIS=" . $_REQUEST["tracking"]);
$out = array();
foreach ($correiosSRO->find('table', 0)->find("tr") as $k => $row) {
	// Removendo a linha de título
	if($k == 0)
		continue;

	// Armazenando a informação de data caso ela ainda não exista
	$dateExists = false;
	$date = $row->find("td", 0);
	if(!isset($out[$k]["date"])){
		$out[$k]["date"] = utf8_encode($date->plaintext);
		$dateExists = true;
	}
	// Quando a célula possui colspan, coloco a data para as demais linhas
	$rowSpan = $date->rowspan;
	if($rowSpan > 1){
		for($i = 1; $i < $rowSpan; $i++)
		$out[$k + $i]["date"] = $out[$k]["date"];
	}

	// Armazenando a descrição do estado de rastreio
	$site = $row->find("td", $dateExists ? 1 : 0);
	$out[$k]["site"] = is_object($site) ? utf8_encode($site->plaintext) : false;

	// Armazenando as informações de situação do pedido
	$status = $row->find("td", $dateExists ? 2 : 1);
	$out[$k]["status"] = is_object($status) ? utf8_encode($status->plaintext) : false;

}

// Saída na tela
if(isset($_REQUEST["callback"]))
	echo $_REQUEST["callback"] . "(" . json_encode($out) . ");";
else
	echo json_encode($out);