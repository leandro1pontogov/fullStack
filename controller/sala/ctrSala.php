<?php

require_once "../../lib/libUtils.php";
require_once "../../lib/libDatabase.php";
require_once "../../model/mdlTbSala.php";
require_once "../../model/mdlTbReserva.php";

$objTbSala = new TbSala();
$objMsg = new Message();
$objTbReserva = new TbReserva();

//------------------------------------------------------------------------------------------//
//A��o de Abertura da Tela de Consulta
//------------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "winConsulta"){
  $frmResult = "";
  if(isset($_GET["frmResult"]) && $_GET["frmResult"] != ""){
    $frmResult = "#".$_GET["frmResult"];
  }
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
    $arrTempor["idsala"] = utf8_encode($objTbSala->Get("idsala"));
    $arrTempor["nmsala"] = utf8_encode($objTbSala->Get("nmsala"));
    $arrTempor["dslocalizacao"] = utf8_encode($objTbSala->Get("dslocalizacao"));
    $arrTempor["nrcapacidade"] = utf8_encode($objTbSala->Get("nrcapacidade"));
    $arrTempor["txrecursosdisponiveis"] = utf8_encode($objTbSala->Get("txrecursosdisponiveis"));

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

  $objTbSala->Set("idsala", utf8_decode($_POST["idSala"]));
  $objTbSala->Set("nmsala", utf8_decode($_POST["nmSala"]));
  $objTbSala->Set("dslocalizacao", utf8_decode($_POST["dsLocalizacao"]));
  $objTbSala->Set("nrcapacidade", $_POST["nrCapacidade"]);
  $objTbSala->Set("txrecursosdisponiveis", utf8_decode($_POST["txRecursosDisponiveis"]));

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
  $aroTbReserva = TbReserva::ListByCondicao(" AND idsala=". $objTbSala->Get("idsala"), "");

  $dtbLink = new DtbServer();
  $dtbLink->Begin();

  if(is_array($aroTbReserva)){
    foreach($aroTbReserva as $key => $objTbReserva){
      $objTbReserva->SetDtbLink($dtbLink);

      $arrResult = $objTbReserva->Delete($objTbReserva);

      if($arrResult["dsMsg"] != "ok"){
        $dtbLink->Rollback();
        $objMsg->LoadMessage($arrResult);
        exit;
      }
    }
  }

  $objTbSala->SetDtbLink($dtbLink);
  $arrResult = $objTbSala->Delete($objTbSala);

  if($arrResult["dsMsg"] == "ok"){
    $objMsg->Succes("ntf", "Registro excluido com sucesso");
    $dtbLink->Commit();
  }else{
    $objMsg->LoadMessage($arrResult);
    $dtbLink->Rollback();
    $objTbSala = new TbSala();
  }
}
//------------------------------------------------------------------------------------------//

