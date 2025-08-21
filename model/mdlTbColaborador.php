<?php

class TbColaborador{
  private $idcolaboradorsala;
  private $nmcolaboradorsala;
  private $dsemail;
  private $dssetor;

  public function __construct(){
    $this->idcolaboradorsala = "";
    $this->nmcolaboradorsala = "";
    $this->dsemail = "";
    $this->dssetor = "";
  }

  public function Set($prpTbColaborador, $valTbColaborador){
    $this->$prpTbColaborador = $valTbColaborador;
  }

  public function Get($prpTbColaborador){
    return $this->$prpTbColaborador;
  }

  public function LoadObject($resSet){
    $objTbColaborador = new TbColaborador();

    $objTbColaborador->Set("idcolaboradorsala", $resSet["idcolaboradorsala"]);
    $objTbColaborador->Set("nmcolaboradorsala", $resSet["nmcolaboradorsala"]);
    $objTbColaborador->Set("dsemail", $resSet["dsemail"]);
    $objTbColaborador->Set("dssetor", $resSet["dssetor"]);

    return $objTbColaborador;
  }

  public function Insert($objTbColaborador){
    $dtbServer = new DtbServer();
    $fmt = new Format();

    $dsSql = "INSERT INTO
                shtreinamento.tbcolaboradorsala(
                  idcolaboradorsala,
                  nmcolaboradorsala,
                  dsemail,
                  dssetor
                )
                VALUES(
                (SELECT NEXTVAL('shtreinamento.sqidcolaboradorsala')),
                '".$fmt->escSqlQuotes($objTbColaborador->Get("nmcolaboradorsala"))."',
                '".$fmt->escSqlQuotes($objTbColaborador->Get("dsemail"))."',
                '".$fmt->escSqlQuotes($objTbColaborador->Get("dssetor"))."'
                );";
                
    if(!$dtbServer->Exec($dsSql)){
      $arrMsg = $dtbServer->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public function Update($objTbColaborador){
    $dtbServer = new DtbServer();
    $fmt = new Format();

    $dsSql = "UPDATE
                shtreinamento.tbcolaboradorsala
              SET
                idcolaboradorsala = ".$objTbColaborador->Get("idcolaboradorsala").",
                nmcolaboradorsala = '".$fmt->escSqlQuotes($objTbColaborador->Get("nmcolaboradorsala"))."',
                dsemail = '".$fmt->escSqlQuotes($objTbColaborador->Get("dsemail"))."',
                dssetor = '".$fmt->escSqlQuotes($objTbColaborador->Get("dssetor"))."'
              WHERE
                idcolaboradorsala = ".$objTbColaborador->Get("idcolaboradorsala"). ";";
                
    if(!$dtbServer->Exec($dsSql)){
      $arrMsg = $dtbServer->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public function Delete($objTbColaborador){
    $dtbServer = new DtbServer();

    $dsSql = "DELETE FROM
                shtreinamento.tbcolaboradorsala
              WHERE
                idcolaboradorsala = ".$objTbColaborador->Get("idcolaboradorsala").";";
              
    if(!$dtbServer->Exec($dsSql)){
      $arrMsg = $dtbServer->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public static function LoadByIdColaborador($idColaborador){
    $dtbServer = new DtbServer();
    $objTbColaborador = new TbColaborador();

    $dsSql = "SELECT FROM
                shtreinamento.tbcolaboradorsala
              WHERE
                idcolaboradorsala = ". $idColaborador .";";

    if(!$dtbServer->Query($dsSql)){
      return $dtbServer->getMessage()["dsMsg"];
    }else{
      $resSet = $dtbServer->FetchArray();
      $objTbColaborador = $objTbColaborador->LoadObject($resSet);
      return $objTbColaborador;
    }
  }

  public static function ListByCondicao($strCondicao, $strOrdenacao){
    $dtbServer = new DtbServer();
    $objTbColaborador = new TbColaborador();

    $dsSql = "SELECT 
                *
              FROM
                shtreinamento.tbcolaboradorsala
              WHERE
                1 = 1";
    
    if($strCondicao){
      $dsSql .= $strCondicao;
    }

    if($strOrdenacao){
      $dsSql .= " ORDER BY ". $strOrdenacao;
    }

    if(!$dtbServer->Query($dsSql)){
      return $dtbServer->getMessage()["dsMsg"];
    }else{
      while($resSet = $dtbServer->FetchArray()){
        $aroTbColaborador[] = $objTbColaborador->LoadObject($resSet);
      }
        return $aroTbColaborador;
    }

  }
  
}