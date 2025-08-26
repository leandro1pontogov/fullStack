<?php

class TbSala{
  private $idsala;
  private $nmsala;
  private $dslocalizacao;
  private $nrcapacidade;
  private $txrecursosdisponiveis;
  private $dtbLink;

  public function __construct(){
    $this->idsala = "";
    $this->nmsala = "";
    $this->dslocalizacao = "";
    $this->nrcapacidade = "";
    $this->txrecursosdisponiveis = "";
  }

  public function Set($prpTbSala, $valTbSala){
    $this->$prpTbSala = $valTbSala;
  }

  public function SetDtbLink($dtbLink){
    $this->dtbLink = $dtbLink;
  }

  public function Get($prpTbSala){
    return $this->$prpTbSala;
  }

  public function LoadObject($resSet){
    $objTbSala = new TbSala();

    $objTbSala->Set("idsala", $resSet["idsala"]);
    $objTbSala->Set("nmsala", $resSet["nmsala"]);
    $objTbSala->Set("dslocalizacao", $resSet["dslocalizacao"]);
    $objTbSala->Set("nrcapacidade", $resSet["nrcapacidade"]);
    $objTbSala->Set("txrecursosdisponiveis", $resSet["txrecursosdisponiveis"]);

    if(!isset($GLOBALS["_intTotalSala"])){
      $GLOBALS["_intTotalSala"] = $resSet["_inttotal"];
    }

    return $objTbSala;
  }

  public function Insert($objTbSala){
    if($this->dtbLink == null){
      $this->dtbLink = new DtbServer();
    }
    $fmt = new Format();

    $dsSql = "INSERT INTO
                shtreinamento.tbsala(
                idsala,
                nmsala,
                dslocalizacao,
                nrcapacidade,
                txrecursosdisponiveis
                )
                VALUES(
                (SELECT NEXTVAL('shtreinamento.sqidsala')),
                '".$fmt->escSqlQuotes($objTbSala->Get("nmsala")) ."',
                '".$fmt->escSqlQuotes($objTbSala->Get("dslocalizacao")) ."',
                '".$objTbSala->Get("nrcapacidade") ."',
                ".$fmt->NullString($fmt->escSqlQuotes($objTbSala->Get("txrecursosdisponiveis"))) ."
                );";

    if(!$this->dtbLink->Exec($dsSql)){
      $arrMsg = $this->dtbLink->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public function Update($objTbSala){
    if($this->dtbLink == null){
      $this->dtbLink = new DtbServer();
    }
    $fmt = new Format();

    $dsSql = "UPDATE 
                shtreinamento.tbsala
                SET
                  idsala = ".$objTbSala->Get("idsala") .",
                  nmsala = '".$fmt->escSqlQuotes($objTbSala->Get("nmsala")) ."', 
                  dslocalizacao = '".$fmt->escSqlQuotes($objTbSala->Get("dslocalizacao")) ."', 
                  nrcapacidade = ".$objTbSala->Get("nrcapacidade") .", 
                  txrecursosdisponiveis = ".$fmt->NullString($fmt->escSqlQuotes($objTbSala->Get("txrecursosdisponiveis"))) ."
                WHERE
                  idsala = ".$objTbSala->Get("idsala") .";";
                  
  if(!$this->dtbLink->Exec($dsSql)){
      $arrMsg = $this->dtbLink->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public function Delete($objTbSala){
    if($this->dtbLink == null){
      $this->dtbLink = new DtbServer();
    }

    $dsSql = "DELETE FROM 
                shtreinamento.tbsala
              WHERE
                idsala = " . $objTbSala->Get("idsala") . ";";
    
  if(!$this->dtbLink->Exec($dsSql)){
      $arrMsg = $this->dtbLink->getMessage();
    }else{
      $arrMsg = ["dsMsg"=>"ok"];
    }
    return $arrMsg;
  }

  public static function LoadByIdSala($idSala){
    $dtbServer = new DtbServer();
    $objTbSala = new TbSala();

    $dsSql = "SELECT * FROM
                shtreinamento.tbsala
              WHERE idsala = " . $idSala . " ";

    if(!$dtbServer->Query($dsSql)){
      return $dtbServer->getMessage()["dsMsg"];
    }else{
      $resSet = $dtbServer->FetchArray();
      $objTbSala = $objTbSala->LoadObject($resSet);
      return $objTbSala;
    }
  }

  public static function ListByCondicao($strCondicao, $strOrdenacao){
    $dtbServer = new DtbServer();
    $objTbSala = new TbSala();

    $dsSql = "SELECT
               *,
               COUNT(*) OVER() _inttotal 
              FROM
                shtreinamento.tbsala
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
        $aroTbSala[] = $objTbSala->LoadObject($resSet);
      }
        return $aroTbSala;
    }
  }
}