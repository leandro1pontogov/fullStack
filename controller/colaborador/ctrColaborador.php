<?php

require_once "../../lib/libUtils.php";
require_once "../../lib/libDatabase.php";
require_once "../../model/mdlTbColaborador.php";

$objTbColaborador = new TbColaborador();
$objMsg = new Message();

//-----------------------------------------------------------------------------------------//
//Ação de Abertura da Tela de Consulta
//-----------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "winConsulta"){
  $frmResult = "";
  if(isset($_GET["frmResult"]) && $_GET["frmResult"] != ""){
    $frmResult = "#".$_GET["frmResult"];
  }
  require_once "../../view/colaborador/viwConsultaColaborador.php";
}
//-----------------------------------------------------------------------------------------//

//-----------------------------------------------------------------------------------------//
//Ação de Inclusão de Registros
//-----------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "incluir"){
  require_once "../../view/colaborador/viwCadastroColaborador.php";
}
//-----------------------------------------------------------------------------------------//

//-----------------------------------------------------------------------------------------//
//Ação de Edição de Registros
//-----------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "editar"){

  $objTbColaborador = TbColaborador::LoadByIdColaborador($_GET["idColaborador"]);

  require_once "../../view/colaborador/viwCadastroColaborador.php";

}
//-----------------------------------------------------------------------------------------//

//-----------------------------------------------------------------------------------------//
//Ação de Consulta de Registros
//-----------------------------------------------------------------------------------------//
if(isset($_GET["action"]) && $_GET["action"] == "ListColaborador"){
  $objFilter = new Filter($_GET);
  global $_intTotalColaborador;

  $aroTbColaborador = TbColaborador::ListByCondicao($objFilter->GetWhere(), $objFilter->GetOrderBy());

  if(is_array($aroTbColaborador) && count($aroTbColaborador) > 0){
    $arrLinhas = [];
    $arrTempor = [];

    foreach($aroTbColaborador as $objTbColaborador){
      $arrTempor["idcolaboradorsala"] = utf8_encode($objTbColaborador->Get("idcolaboradorsala"));
      $arrTempor["nmcolaboradorsala"] = utf8_encode($objTbColaborador->Get("nmcolaboradorsala"));
      $arrTempor["dsemail"] = utf8_encode($objTbColaborador->Get("dsemail"));
      $arrTempor["dssetor"] = utf8_encode($objTbColaborador->Get("dssetor"));

      array_push($arrLinhas, $arrTempor);
    }
      echo '{"jsnColaborador":'.json_encode($arrLinhas).', "jsnTotal": '. $_intTotalColaborador .'}';
  }else if(!is_array($aroTbColaborador) && trim($aroTbColaborador) != ""){
      echo '{"error": '. $aroTbColaborador .'}'; 
  }else{
      echo '{"jsnColaborador":null}';
  }
  //-----------------------------------------------------------------------------------------//
}

//-----------------------------------------------------------------------------------------//
  //Ação para Gravação de Registros
  //-----------------------------------------------------------------------------------------//
  if(isset($_GET["action"]) && $_GET["action"] == "gravar"){
    
    $objTbColaborador->Set("idcolaboradorsala", utf8_decode($_POST["idColaborador"]));
    $objTbColaborador->Set("nmcolaboradorsala", utf8_decode($_POST["nmColaborador"]));
    $objTbColaborador->Set("dsemail", utf8_decode($_POST["dsEmail"]));
    $objTbColaborador->Set("dssetor", utf8_decode($_POST["dsSetor"]));

    $strMessage = "";

    if(empty($objTbColaborador->Get("nmcolaboradorsala"))){
      $strMessage .= "&raquo; O campo <strong>Nome</strong> é de preenchimento obrigatorio.<br>";
    }

    if(empty($objTbColaborador->Get("dsemail"))){
      $strMessage .= "&raquo; O campo <strong>Email</strong> é de preenchimento obrigatorio.<br>";
    }

    if(empty($objTbColaborador->Get("dssetor"))){
      $strMessage .= "&raquo; O campo <strong>Setor</strong> é de preenchimento obrigatorio.<br>";
    }

    if($strMessage != ""){
      $objMsg->Alert("dlg", $strMessage);
    }else{
      if($objTbColaborador->Get("idcolaboradorsala") != ""){
        $arrResult = $objTbColaborador->Update($objTbColaborador);

      if($arrResult["dsMsg"] == "ok"){
        $objMsg->Succes("ntf", "Registro atualizado com sucesso");
      }else{
        $objMsg->LoadMessage($arrResult);
        $objTbColaborador = new TbColaborador();
      }
      }else{
        $arrResult = $objTbColaborador->Insert($objTbColaborador);

      if($arrResult["dsMsg"] == "ok"){
        $objMsg->Succes("ntf", "Registro inserido com sucesso");
      }else{
        $objMsg->LoadMessage($arrResult);
        $objTbColaborador = new TbColaborador();
      }
     }
    }
  }
  //-----------------------------------------------------------------------------------------//

  //-----------------------------------------------------------------------------------------//
  //Ação para exclusão de registros
  //-----------------------------------------------------------------------------------------//
  if(isset($_GET["action"]) && $_GET["action"] == "excluir"){
    $objTbColaborador = TbColaborador::LoadByIdColaborador($_POST["idColaborador"]);
    $arrResult = $objTbColaborador->Delete($objTbColaborador);

    if($arrResult["dsMsg"] == "ok"){
    $objMsg->Succes("ntf", "Registro excluido com sucesso");
    }else{
    $objMsg->LoadMessage($arrResult);
    $objTbColaborador = new TbColaborador();
    }
  }
  //-----------------------------------------------------------------------------------------//




