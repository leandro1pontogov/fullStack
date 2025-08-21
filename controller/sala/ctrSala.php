<?php

require_once "../../lib/libUtils.php";
require_once "../../lib/libDatabase.php";
require_once "../../model/mdlTbSala.php";

$objTbSala = new TbSala();
$objMsg = new Message();

//------------------------------------------------------------------------------------------//
//A��o de Abertura da Tela de Consulta
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "winConsulta"){
  require_once "../../view/sala/viwConsultaSala.php";
}
//------------------------------------------------------------------------------------------//

//------------------------------------------------------------------------------------------//
//A��o de Inclus�o de Registros
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "incluir"){
  require_once "../../view/sala/viwCadastroSala.php";
}
//------------------------------------------------------------------------------------------//

//------------------------------------------------------------------------------------------//
//A��o de Edi��o de Registros
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "editar"){

  $objTbSala = TbSala::LoadByIdSala($_GET["idSala"]);

  require_once "../../view/sala/viwCadastroSala.php";
}
//------------------------------------------------------------------------------------------//

//------------------------------------------------------------------------------------------//
//A��o de Consulta de Registros
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "ListSala"){
  $objFilter = new Filter($_GET);
  global $_intTotalSala; 

  $aroTbSala = TbSala::ListByCondicao($objFilter->GetWhere(), $objFilter->GetOrderBy()); 

  if(is_array($aroTbSala) && count($aroTbSala) > 0){
    $arrLinhas = [];
    $arrTempor = [];

  foreach($aroTbSala as $objTbSala){
    $arrTempor["idsala"] = $objTbSala->Get("idsala");
    $arrTempor["nmsala"] = $objTbSala->Get("nmsala");
    $arrTempor["dslocalizacao"] = $objTbSala->Get("dslocalizacao");
    $arrTempor["nrcapacidade"] = $objTbSala->Get("nrcapacidade");
    $arrTempor["txrecursosdisponiveis"] = $objTbSala->Get("txrecursosdisponiveis");

    array_push($arrLinhas, $arrTempor);
  }
    echo '{"jsnSala":'.json_encode($arrLinhas).', "jsnTotal":'. $_intTotalSala .'}';
  }else if(!is_array($aroTbSala) && trim($aroTbSala) != ""){ //Sinal de erro na busca
    echo '{"error":'.$aroTbSala.'}';
  }else{ //Nenhum registro encontrado
    echo '{"jsnSala":null}';
  }
}
//------------------------------------------------------------------------------------------//

//------------------------------------------------------------------------------------------//
//A��o para Grava��o de Registros
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "gravar"){

  $objTbSala->Set("idsala", $_POST["idSala"]);
  $objTbSala->Set("nmsala", utf8_decode($_POST["nmSala"]));
  $objTbSala->Set("dslocalizacao", $_POST["dsLocalizacao"]);
  $objTbSala->Set("nrcapacidade", $_POST["nrCapacidade"]);
  $objTbSala->Set("txrecursosdisponiveis", $_POST["txRecursosDisponiveis"]);

  $strMessage = "";

  if(empty($objTbSala->Get("nmsala"))){
    $strMessage .= "&raquo; O campo <strong>Nome</strong> � de preenchimento obrigatorio.<br>";
  }

  if(empty($objTbSala->Get("dslocalizacao"))){
    $strMessage .= "&raquo; O campo <strong>Localizacao</strong> � de preenchimento obrigatorio.<br>";
  }

  if(empty($objTbSala->Get("nrcapacidade"))){
    $strMessage .= "&raquo; O campo <strong>Capacidade</strong> � de preenchimento obrigatorio.<br>";
  }

  if(empty($objTbSala->Get("txrecursosdisponiveis"))){
    $strMessage .= "&raquo; O campo <strong>Recursos Disponiveis</strong> � de preenchimento obrigatorio.<br>";
  }

  if($strMessage != ""){
    $objMsg->Alert("dlg", $strMessage);
  }else{
    if($objTbSala->Get("idsala") != ""){
      $arrResult = $objTbSala->Update($objTbSala);

      if($arrResult["dsMsg"] == "ok"){
        $objMsg->Succes("ntf", "Registro atualizado com sucesso");
      }else{
        $objMsg->LoadMessage($arrResult);
        $objTbSala = new TbSala();
      }
    }else{
      $arrResult = $objTbSala->Insert($objTbSala);

      if($arrResult["dsMsg"] == "ok"){
      $objMsg->Succes("ntf", "Registro inserido com sucesso");
      }else{
        $objMsg->LoadMessage($arrResult);
        $objTbSala = new TbSala();
      }
    }
  }
}
//------------------------------------------------------------------------------------------//

//------------------------------------------------------------------------------------------//
//A��o para Exclus�o de Registros
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "excluir"){

  $objTbSala = TbSala::LoadByIdSala($_POST["idSala"]);
  $arrResult = $objTbSala->Delete($objTbSala);

  if($arrResult["dsMsg"] == "ok"){
    $objMsg->Succes("ntf", "Registro excluido com sucesso");
  }else{
    $objMsg->LoadMessage($arrResult);
    $objTbSala = new TbSala();
  }
}
//------------------------------------------------------------------------------------------//

