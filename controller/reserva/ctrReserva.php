<?php

require_once "../../lib/libUtils.php";
require_once "../../lib/libDatabase.php";
require_once "../../model/mdlTbReserva.php";
require_once "../../model/mdlTbSala.php";
require_once "../../model/mdlTbColaborador.php";

$objTbReserva = new TbReserva();
$objMsg = new Message();

if(isset($_GET["action"]) && $_GET["action"] == "winConsulta"){
  require_once "../../view/reserva/viwConsultaReserva.php";
}

if(isset($_GET["action"]) && $_GET["action"] == "incluir"){
  $objTbReserva->Set("idsala", $_GET["idSala"]);
  $objTbReserva->Set("idcolaboradorsaça", $_GET["idColaborador"]);

  $blSalaSelecionada = $_GET["idSala"] != "";
  $blColaboradorSelecionado = $_GET["idColaborador"] != "";

  require_once "../../view/reserva/viwCadastroReserva.php";
}

if(isset($_GET["action"]) && $_GET["action"] == "editar"){
  $objTbReserva = TbReserva::LoadByIdReserva($_GET["idReserva"]);
  require_once "../../view/reserva/viwCadastroReserva.php";
}

if(isset($_GET["action"]) && $_GET["action"] == "AutoComplete"){
  $strFiltro = " and upper(clear(nmsala)) like upper(clear('%".utf8_decode($_GET["filter"]["filters"][0]["value"])."%')) ";
  $strOrdenacao = " nmsala asc";

  $aroTbSala = TbSala::ListByCondicao($strFiltro, $strOrdenacao);

  if($aroTbSala && is_array($aroTbSala) == true){
    $arrTempor = array();
    $arrLinhas = array();

    foreach($aroTbSala as $key => $objTbSala){
      $arrTempor["idsala"] = utf8_encode($objTbSala->Get("idsala"));
      $arrTempor["nmsala"] = utf8_encode($objTbSala->Get("nmsala"));
      array_push($arrLinhas, $arrTempor);
    }
  }

  header("Content-type: application/json");
  echo "{\"data\":".json_encode($arrLinhas)."}";

}

if(isset($_GET["action"]) && $_GET["action"] == "AutoCompleteColaborador"){
  $strFiltro = " and upper(clear(nmcolaboradorsala)) like upper(clear('%".utf8_decode($_GET["filter"]["filters"][0]["value"])."%')) ";
  $strOrdenacao = " nmcolaboradorsala asc";

  $aroTbColaborador = TbColaborador::ListByCondicao($strFiltro, $strOrdenacao);

  if($aroTbColaborador && is_array($aroTbColaborador) == true){
    $arrTempor = array();
    $arrLinhas = array();

    foreach($aroTbColaborador as $key => $objTbSala){
      $arrTempor["idcolaboradorsala"] = utf8_encode($objTbSala->Get("idcolaboradorsala"));
      $arrTempor["nmcolaboradorsala"] = utf8_encode($objTbSala->Get("nmcolaboradorsala"));
      array_push($arrLinhas, $arrTempor);
    }
  }

  header("Content-type: application/json");
  echo "{\"data\":".json_encode($arrLinhas)."}";

}

//-----------------------------------------------------------------------------------------//
//Ação de Consulta de Registros
//-----------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "ListReserva"){
  $objFilter = new Filter($_GET);
  global $_intTotalReserva;

  $aroTbReserva = TbReserva::ListByCondicao($objFilter->GetWhere(), $objFilter->GetOrderBy());

  if(is_array($aroTbReserva) && count($aroTbReserva) > 0){
    $arrLinhas = [];
    $arrTempor = [];

    foreach($aroTbReserva as $objTbReserva){
      $arrTempor["idreserva"] = utf8_encode($objTbReserva->Get("idreserva"));
      $arrTempor["nmsala"] = utf8_encode($objTbReserva->GetObjTbSala()->Get("nmsala"));
      $arrTempor["nmcolaboradorsala"] = utf8_encode($objTbReserva->GetObjColaborador()->Get("nmcolaboradorsala"));
      $arrTempor["dtdata"] = utf8_encode($objTbReserva->Get("dtdata"));
      $arrTempor["hrinicio"] = utf8_encode($objTbReserva->Get("hrinicio"));
      $arrTempor["hrfim"] = utf8_encode($objTbReserva->Get("hrfim"));

      array_push($arrLinhas, $arrTempor);
    }
      echo '{"jsnReserva":'.json_encode($arrLinhas).', "jsnTotal": '. $_intTotalReserva .'}';
  }else if(!is_array($aroTbReserva) && trim($aroTbReserva) != ""){
      echo '{"error": '. $aroTbReserva .'}'; 
  }else{
      echo '{"jsnReserva":null}';
  }
  //-----------------------------------------------------------------------------------------//
}


if(isset($_GET["action"]) && $_GET["action"] == "gravar"){
    
    $objTbReserva->Set("idreserva", utf8_decode($_POST["idReserva"]));
    $objTbReserva->Set("idsala", utf8_decode($_POST["idSala"]));
    $objTbReserva->Set("idcolaboradorsala", utf8_decode($_POST["idColaborador"]));
    $objTbReserva->Set("dtdata", utf8_decode($_POST["dtData"]));
    $objTbReserva->Set("hrinicio", utf8_decode($_POST["hrInicio"]));
    $objTbReserva->Set("hrfim", utf8_decode($_POST["hrFim"]));
    
    $strMessage = "";

    if(empty($objTbReserva->Get("dtdata"))){
      $strMessage .= "&raquo; O campo <strong>Data</strong> é de preenchimento obrigatorio.<br>";
    }

    if(empty($objTbReserva->Get("hrinicio"))){
      $strMessage .= "&raquo; O campo <strong>Hora Inicio</strong> é de preenchimento obrigatorio.<br>";
    }

    if(empty($objTbReserva->Get("hrfim"))){
      $strMessage .= "&raquo; O campo <strong>Hora Fim</strong> é de preenchimento obrigatorio.<br>";
    }

    if($strMessage != ""){
      $objMsg->Alert("dlg", $strMessage);
    }else{
      if($objTbReserva->Get("idreserva") != ""){
        $arrResult = $objTbReserva->Update($objTbReserva);

      if($arrResult["dsMsg"] == "ok"){
        $objMsg->Succes("ntf", "Registro atualizado com sucesso");
      }else{
        $objMsg->LoadMessage($arrResult);
        $objTbReserva = new TbReserva();
      }
      }else{
        $arrResult = $objTbReserva->Insert($objTbReserva);

      if($arrResult["dsMsg"] == "ok"){
        $objMsg->Succes("ntf", "Registro inserido com sucesso");
      }else{
        $objMsg->LoadMessage($arrResult);
        $objTbReserva = new TbReserva();
      }
     }
    }
  }

  if(isset($_GET["action"]) && $_GET["action"] == "excluir"){
    $objTbReserva = TbReserva::LoadByIdReserva($_POST["idReserva"]);
    $arrResult = $objTbReserva->Delete($objTbReserva);

    if($arrResult["dsMsg"] == "ok"){
    $objMsg->Succes("ntf", "Registro excluido com sucesso");
    }else{
    $objMsg->LoadMessage($arrResult);
    $objTbReserva = new TbReserva();
    }
  }