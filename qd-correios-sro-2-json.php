<?php
/**
 * Quatro Digital - Correios SRO to JSON
 *
 * Este script lê a página de rastreamento dos Correios com base em código de rastreio informado via GET ou POST
 * e a transforma em JSON ou JSONP
 *
 * @version 1.1
 * @author Carlos Vinicius
 * @license MIT <http://opensource.org/licenses/MIT>
 */

include("helpers/simple_html_dom.php");

$correiosSRO = file_get_html("http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_ITEMCODE=&P_LINGUA=001&P_TESTE=&P_TIPO=001&Z_ACTION=Search&P_COD_UNI=" . strtoupper(trim($_REQUEST["tracking"])));
$out = array();

function getSroArray($table) {
	foreach ($table->find("tr") as $k => $row) {
		// Removendo a linha de título/cabeçalho da tabela
		if($k == 0)
			continue;

		// DATA
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

		// DESCRIÇÃO
		// Armazenando a descrição do estado de rastreio
		$site = $row->find("td", $dateExists ? 1 : 0);
		$out[$k]["site"] = is_object($site) ? utf8_encode($site->plaintext) : false;

		// STATUS
		// Armazenando as informações de situação do pedido
		$status = $row->find("td", $dateExists ? 2 : 1);
		$statusValue = is_object($status) ? utf8_encode($status->plaintext) : false;
		$out[$k]["status"] = $statusValue;
		// Quando a descrição possui colspan eu copio a informação de status existente na linha anterior
		if($site->colspan > 1)
			$out[$k]["status"] = $out[$k - 1]["status"];

		// Informo se a linha é a origem da informação ou o destino
		$out[$k]["isOrigin"] = $statusValue === false? false: true;
	}

	return $out;
}

// Busco a tabela no conteúdo da requisição feita aos correios
$sroTable = $correiosSRO->find('table', 0);
// Caso a tabela exista, faço o processamento
if($sroTable)
	$arrayOut = getSroArray($sroTable);
// Caso não exista, retorno uma informação de dados não encontrados
else
	$arrayOut = array("emptyResult" => true);

// Defino os headers da requisção
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

// Saída na tela
if(isset($_REQUEST["callback"]))
	echo $_REQUEST["callback"] . "(" . json_encode($arrayOut) . ");";
else
	echo json_encode($arrayOut);