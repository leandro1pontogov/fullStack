<?php
  /*******************************************************************************************************************/
  /*Biblioteca com funções utilitárias para os sistemas da Pontogov                                                  */
  /*Programadores: Jonny Gubler                                                                                      */
  /*             :                                                                                                   */
  /*Ultima Atualização: 19/07/2016                                                                                   */
  /*******************************************************************************************************************/

  /*Classe Responsável por Formatações Diversas*/
  class Format{

    //Método que Retira quebras de linha em uma string
    public function RemoveQuebraLinha($strString){
      $strString = str_replace("\"", "",$strString);
      $strString = str_replace("\'", "",$strString);
      $strString = str_replace("  ", ' ', $strString);
      $strString = preg_replace("/\r?\n/","", $strString);
      return $strString;
    }

    //Método que Retira caracteres Especiais do Número de Telefone
    public function LimpaTelefone($strNumeroTelefone){
      $strNumeroTelefone = str_replace(" ", "",$strNumeroTelefone);
      $strNumeroTelefone = str_replace("(", "",$strNumeroTelefone);
      $strNumeroTelefone = str_replace(")", "",$strNumeroTelefone);
      $strNumeroTelefone = str_replace("-", "",$strNumeroTelefone);
      return $strNumeroTelefone;
    }

    /**
    * Método que retorna Script Sql Formatado
    * @param type $strSql -> Script SQL
    * @return string
    */
    public function ScriptFormatCode($strSql) {
      // Remove espaços duplicados e quebras
      $strSql = str_replace(array("\r", "\n"), ' ', $strSql);
      $strSql = preg_replace('/\s+/', ' ', $strSql);
      $strSql = trim($strSql);

      // Divide instruções por ponto e vírgula
      $arrComandos = preg_split('/;\s*/', $strSql);
      $formatado = [];

      foreach ($arrComandos as $comando) {
        $comando = trim($comando);
        if ($comando === '') continue;

        // INSERT
        if (preg_match('/^\s*INSERT\s+INTO\b/i', $comando)) {
          $comando = preg_replace('/\bINSERT\s+INTO\s+([^\s\(]+)\s*\(/i', "INSERT INTO $1 (\n", $comando);
          $comando = preg_replace('/\)\s*VALUES\s*\(/i', "\n)\nVALUES(\n", $comando);
          $comando = preg_replace('/\)\s*$/i', "\n);", $comando);

          $comando = preg_replace_callback('/INSERT INTO\s+([^\s\(]+)\s+\(\n(.*?)\n\)\nVALUES\(\n(.*?)\n\);?/is', function ($matches) {
            $tabela = $matches[1];
            $campos = explode(',', trim($matches[2]));
            $valores = preg_split("/,(?=(?:[^']*'[^']*')*[^']*$)/", trim($matches[3]));

            $camposFormatados = array_map(function ($campo) {
              return ' ' . trim($campo) . ',';
            }, $campos);
            $valoresFormatados = array_map(function ($valor) {
              return ' ' . trim($valor) . ',';
            }, $valores);

            $camposFormatados[count($camposFormatados) - 1] = rtrim($camposFormatados[count($camposFormatados) - 1], ',');
            $valoresFormatados[count($valoresFormatados) - 1] = rtrim($valoresFormatados[count($valoresFormatados) - 1], ',');

            return "INSERT INTO $tabela (\n" . implode("\n", $camposFormatados) . "\n)\nVALUES(\n" . implode("\n", $valoresFormatados) . "\n);";
          }, $comando);
        }

        // UPDATE
        elseif (preg_match('/^\s*UPDATE\b/i', $comando)) {
          $comando = preg_replace('/\bUPDATE\b/i', "UPDATE\n", $comando);
          $comando = preg_replace('/\bSET\b/i', "SET", $comando);
          $comando = preg_replace('/\bWHERE\b/i', "\nWHERE\n", $comando);

          $comando = preg_replace_callback('/SET(.*?)WHERE/is', function ($matches) {
            $set = trim($matches[1]);
            $linhas = preg_split("/,(?=(?:[^']*'[^']*')*[^']*$)/", $set);
            $formatado = array_map(function ($linha) {
              return ' ' . trim($linha) . ',';
            }, $linhas);
            $formatado[count($formatado) - 1] = rtrim($formatado[count($formatado) - 1], ',');
            return "\nSET\n" . implode("\n", $formatado) . "\nWHERE";
          }, $comando);
        }

        // DELETE
        elseif (preg_match('/^\s*DELETE\s+FROM\b/i', $comando)) {
          $comando = preg_replace('/\bDELETE\s+FROM\b/i', "DELETE FROM\n", $comando);
          $comando = preg_replace('/\bWHERE\b/i', "\nWHERE\n", $comando);
        }

        // SELECT
        elseif (preg_match('/^\s*SELECT\b/i', $comando)) {
          $comando = preg_replace_callback(
            '/SELECT\s+(.*?)\s+FROM\s+(.+?)$/is',
            function ($matches) {
              $campos = trim($matches[1]);
              $from = trim($matches[2]);

              $camposArray = preg_split("/,(?=(?:[^']*'[^']*')*[^']*$)/", $campos);
              $camposFormatados = array_map(function ($campo) {
                return '  ' . trim($campo) . ',';
              }, $camposArray);
              $camposFormatados[count($camposFormatados) - 1] = rtrim($camposFormatados[count($camposFormatados) - 1], ',');

              // Corrige fechamento do FROM com função
              if (preg_match('/\(.+\)$/', $from) && !preg_match('/\)\s*$/', $from)) {
                $from .= ' )';
              }

              return "SELECT\n" . implode("\n", $camposFormatados) . "\nFROM\n  " . $from . ";";
            },
            $comando
          );
        }

        $formatado[] = $comando;
      }

      return utf8_encode(implode("\n\n", $formatado));
    }

    /**
    * Método que substitui partes de string por algum caractér preservando espaços em branco
    * @param $texto -> Texto a ser substituído
    * @param $caracter -> Carácter utilizado para substituição
    * @param $digitos -> Dígitos que serão mantidos
    * @example 
    *   @method mascararTextoParcial('PONTOGOV SISTEMAS LTDA', '*', 3)
    *   @return Pon***** ******** *TDA
    *
    * @return string
    */
    function mascararTextoParcial($texto, $caracter, $digitos) {
      // Captura as 3 primeiras e 3 últimas letras
      $inicio = substr($texto, 0, $digitos);
      $fim = substr($texto, -$digitos);
      
      // Cria a parte do meio com os asteriscos, considerando os espaços
      $meio = substr($texto, $digitos, -$digitos);
      $meioMascarado = preg_replace('/[^\s]/', $caracter, $meio);
      
      // Retorna o texto final
      return $inicio.$meioMascarado.$fim;
    }

    /**
    * Método que retorna o caracter "0" a frente de um numero
    * @param type $val, $qtd
    * @return string
    */
    public function lpad($valor,$comprimento){
      if (trim($valor) != ""){
        $zeros = "";

        $x = $comprimento - strlen(trim($valor));

        for ($i=1;$i<=$x;$i++) {
          $zeros .= "0";
        }
        return $zeros.$valor;
      }
      else{
        return "";
      }
    }

    /**
    * Método que retorna um valor monetários retirando os pontos e substituindo a vírgula por ponto
    * @param $valor -> Valor a ser formatado
    * @param $decimals -> Quantidade de casas após a virgula
    * @return string
    */
    public function valor_bd($valor, $decimals = null, $flConsideraZero = 'N'){

      //Se $valor vier em branco irá retornar null
      //Se valor vier diferente de branco e com vírgula troca a vírgula pelo ponto
      //Se valor vier diferente de branco e sem vírgula retorna como entrou

      $decimals = ( is_null($decimals) ? 2 : $decimals );

      if($valor != '' || ($flConsideraZero == 'S' && (is_numeric($valor) && $valor == 0))){
        $pos= strpos($valor,',');

        if($pos === false){
          $valor = number_format((float)$valor, $decimals, '.', '');
          return $valor;
        }
        else{
          $valor = str_replace('.', '', $valor);
          $valor = str_replace(',', '.', $valor);
          $valor = number_format((float)$valor, $decimals, '.', '');
          return $valor;
        }
      }
      else{
        return 'null';
      }
    }

    /**
    * Método que retorna o mes extenso
    * @param type $valor
    * @return string
    */
    public function mesExtenso($referencia = NULL){

      switch (intval($referencia)){
        case 1: $mes = "Janeiro"; break;
        case 2: $mes = "Fevereiro"; break;
        case 3: $mes = "Março"; break;
        case 4: $mes = "Abril"; break;
        case 5: $mes = "Maio"; break;
        case 6: $mes = "Junho"; break;
        case 7: $mes = "Julho"; break;
        case 8: $mes = "Agosto"; break;
        case 9: $mes = "Setembro"; break;
        case 10: $mes = "Outubro"; break;
        case 11: $mes = "Novembro"; break;
        case 12: $mes = "Dezembro"; break;
        default: $mes = " de _______________ de ";
      }
      return $mes;
    }
    
    /**
    * Método que transforma o mês na forma extensa para a forma numérica (sem zero à esquerda)
    * @param type $dsMes
    * @return int
    */
    public function mesNumerico($dsMes){

      switch ($dsMes){
      case "Janeiro": $mes = 1; break;
      case "Fevereiro": $mes = 2; break;
      case "Março": $mes = 3; break;
      case "Abril": $mes = 4; break;
      case "Maio": $mes = 5; break;
      case "Junho": $mes = 6; break;
      case "Julho": $mes = 7; break;
      case "Agosto": $mes = 8; break;
      case "Setembro": $mes = 9; break;
      case "Outubro": $mes = 10; break;
      case "Novembro": $mes = 11; break;
      case "Dezembro": $mes = 12; break;
    }
      return $mes;
    }

    /**
    * Método que retorna o Mês Abreviado
    * @param type $referencia -> Referência do Mês
    * @return string
    * @author Marcos Frare
    */
    public function mesAbreviado($referencia = NULL){
      switch (intval($referencia)){
        case 1: $mes = "Jan"; break;
        case 2: $mes = "Fev"; break;
        case 3: $mes = "Mar"; break;
        case 4: $mes = "Abr"; break;
        case 5: $mes = "Mai"; break;
        case 6: $mes = "Jun"; break;
        case 7: $mes = "Jul"; break;
        case 8: $mes = "Ago"; break;
        case 9: $mes = "Set"; break;
        case 10: $mes = "Out"; break;
        case 11: $mes = "Nov"; break;
        case 12: $mes = "Dez"; break;
        default: $mes = " Ano ";
      }
      return $mes;
    }

    /**
    * Método que retorna uma data por Extenso independente do Formato de Entrdada
    * @param type $strData
    * @return string
    */
    public function dataExtenso($strData){

      $strDataExtenco = "";

      //Verificando o formato de entrada
      if (substr($strData,2,1) == '/'){ // Entrada->01/05/2013
        $strDataExtenco = substr($strData,0,2)." de ".$this->mesExtenso(substr($strData,3,2))." de ".substr($strData,6,4);
      }
      else{ // Entrada->2013-05-01
        $strDataExtenco = substr($strData,8,2)." de ".$this->mesExtenso(substr($strData,5,2))." de ".substr($strData,0,4);
      }
      return $strDataExtenco;

    }

    public function numeroExtenso($numero) {
        $numeros = [
            0 => 'zero', 1 => 'uma', 2 => 'dois', 3 => 'três', 4 => 'quatro',
            5 => 'cinco', 6 => 'seis', 7 => 'sete', 8 => 'oito', 9 => 'nove',
            10 => 'dez', 11 => 'onze', 12 => 'doze', 13 => 'treze', 14 => 'quatorze',
            15 => 'quinze', 16 => 'dezesseis', 17 => 'dezessete', 18 => 'dezoito', 19 => 'dezenove',
            20 => 'vinte', 30 => 'trinta', 40 => 'quarenta', 50 => 'cinquenta',
            60 => 'sessenta', 70 => 'setenta', 80 => 'oitenta', 90 => 'noventa', 100 => 'cem'
        ];

        if (isset($numeros[$numero])) {
            return $numeros[$numero];
        }

        if ($numero > 20 && $numero < 100) {
            $dezena = floor($numero / 10) * 10;
            $unidade = $numero % 10;
            return $numeros[$dezena] . ' e ' . $numeros[$unidade];
        }

        return 'fora do limite';
    }

    /**
    * Método para calcular um periodo em anos
    * @param type $data
    * @param type $periodo
    * @return string
    **/
    public function calculaPeriodo($data,$periodo){
      
      $dataReferencia = new DateTime($data);
      $dataReferencia->add(new DateInterval("P{$periodo}Y")); // Soma os anos
  
      $dataAtual = new DateTime();
  
      return $dataAtual >= $dataReferencia;
    }

    /**
    * Método que retorna null ou o valor passado como parametro
    * @param type $valor
    * @return string
    */
    public function nullVal($valor){

      if (trim($valor) == "")
        return null;
      else
        return $valor;
    }

    /**
    * Método que retorna a conta contábil formatada com pontos(Apenas para contas com 15 Posições).
    * @param type $conta
    * @return string
    */
    public function contaContabilPonto($conta){
      if(trim($conta) != ""){
        $cdConta = substr($conta,0,1).'.';
        $cdConta .= substr($conta,1,2).'.';
        $cdConta .= substr($conta,3,2).'.';
        $cdConta .= substr($conta,5,2).'.';
        $cdConta .= substr($conta,7,2).'.';
        $cdConta .= substr($conta,9,2).'.';
        $cdConta .= substr($conta,11,2).'.';
        $cdConta .= substr($conta,13,2);

        if(strlen($conta) > 15){
          $cdConta .= '.'.substr($conta,15,2).'.';
          $cdConta .= substr($conta,17,2);
        }
        return $cdConta;
      }
      else{
        return null;
      }
    }

    /**
    * Método que retorna a conta de Despesa formatada com pontos.
    * @param type $conta
    * @return string
    * @author Marcos Frare
    */
    public function mascaraDespesa($conta){
      if(trim($conta) != ""){
        $cdConta = substr($conta,0,1).'.';
        $cdConta .= substr($conta,1,2).'.';
        $cdConta .= substr($conta,3,2).'.';
        $cdConta .= substr($conta,5,2).'.';
        $cdConta .= substr($conta,7,2).'.';
        $cdConta .= substr($conta,9,2).'.';
        $cdConta .= substr($conta,11,2).'.';
        $cdConta .= substr($conta,13,2);
        if(strlen($conta) > 15){
          $cdConta .= '.'.substr($conta,15,2).'.';
          $cdConta .= substr($conta,17,2);
        }
        return $cdConta;
      }
      else{
        return null;
      }
    }

    /**
    * Método que retorna a conta de Receita formatada com pontos.
    * @param type $conta
    * @return string
    * @author Marcos Frare
    */
    public function mascaraReceita($conta){
      if(trim($conta) != ""){
        //Validar o Ano de Exercício
        if($_SESSION['cdAnoExercicio'] >= 2018){
          //Máscara (0.0.0.0.0.00.0.0.00.00.00)
          $cdConta = substr($conta,0,1).'.';
          $cdConta .= substr($conta,1,1).'.';
          $cdConta .= substr($conta,2,1).'.';
          $cdConta .= substr($conta,3,1).'.';
          $cdConta .= substr($conta,4,1).'.';
          $cdConta .= substr($conta,5,2).'.';
          $cdConta .= substr($conta,7,1).'.';
          $cdConta .= substr($conta,8,1).'.';
          $cdConta .= substr($conta,9,2).'.';
          $cdConta .= substr($conta,11,2).'.';
          $cdConta .= substr($conta,13,2);
          if(strlen($conta) > 15){
            $cdConta .= '.'.substr($conta,15,2).'.';
            $cdConta .= substr($conta,17,2);
          }
        }else{
          //Máscara (0.0.0.0.0.00.00.00.00.00)
          $cdConta = substr($conta,0,1).'.';
          $cdConta .= substr($conta,1,1).'.';
          $cdConta .= substr($conta,2,1).'.';
          $cdConta .= substr($conta,3,1).'.';
          $cdConta .= substr($conta,4,1).'.';
          $cdConta .= substr($conta,5,2).'.';
          $cdConta .= substr($conta,7,2).'.';
          $cdConta .= substr($conta,9,2).'.';
          $cdConta .= substr($conta,11,2).'.';
          $cdConta .= substr($conta,13,2);
          if(strlen($conta) > 15){
            $cdConta .= '.'.substr($conta,15,2).'.';
            $cdConta .= substr($conta,17,2);
          }
        }

        return $cdConta;
      }
      else{
        return null;
      }
    }

    /**
    * Método que retorna um Vínculo de Recurso Padrão Formatado com Pontos
    * @param type $intCdVinculoPadrao
    * @return string
    * @author Jonny Gubler
    */
    public function MaskVinculoPadrao($intCdVinculoPadrao){
      if(trim($intCdVinculoPadrao) != ""){
        $strCdVinculoPadrao = substr($intCdVinculoPadrao,0,1).'.';
        $strCdVinculoPadrao .= substr($intCdVinculoPadrao,1,3).'.';
        $strCdVinculoPadrao .= substr($intCdVinculoPadrao,4,4).'.';
        $strCdVinculoPadrao .= substr($intCdVinculoPadrao,8,4);
        return $strCdVinculoPadrao;
      }
      else{
        return null;
      }
    }

    /**
    * Método que retorna uma String sem Caracteres Especiais
    * @param type $strParam
    * @return string
    * @author Jonny Gubler
    */
    public function Clear($strParam){
      if(trim($strParam) != ""){
        $strParam = str_replace(" ", "",$strParam);
        $strParam = str_replace("(", "",$strParam);
        $strParam = str_replace(")", "",$strParam);
        $strParam = str_replace("-", "",$strParam);
        $strParam = str_replace(".", "",$strParam);
        return $strParam;
      }
      else{
        return null;
      }
    }

    /**
    * Método que retorna o argumento com as primeiras letras em maiúsculo
    * @param type $term
    * @return string
    */
    public function ucWord($term){
      if (trim($term) != ""){
        //Comentado por Jonny pois foi substituído a função maluca abaixo pela mb_convert_case
        //$palavra = strtr(ucwords(strtolower($term)),"ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏ?ÑÒÓÔÕÖ×ØÙÜÚ?ß","àáâãäåæçèéêëìíîï?ñòóôõö÷øùüú?ÿ");
        $palavra = mb_convert_case($term,MB_CASE_TITLE,"ISO-8859-1");
        $palavra = str_replace(" Da "," da ",$palavra);
        $palavra = str_replace(" Do "," do ",$palavra);
        $palavra = str_replace(" A "," a ",$palavra);
        $palavra = str_replace(" E "," e ",$palavra);
        $palavra = str_replace(" I "," i ",$palavra);
        $palavra = str_replace(" O "," o ",$palavra);
        $palavra = str_replace(" U "," u ",$palavra);
        $palavra = str_replace(" De "," de ",$palavra);
        $palavra = str_replace(" Um "," um ",$palavra);
        $palavra = str_replace(" Das "," das ",$palavra);
        $palavra = str_replace(" Dos "," dos ",$palavra);
        $palavra = str_replace(" Nº "," nº ",$palavra);
        return $palavra;
      }
      else{
        return "";
      }
    }

    /**
    * Método que retorna o argumento com as primeiras letras em maiúsculo
    * @param type $term
    * @return string
    * @author André Machado
    */
      public function Upper($strString){
        return mb_strtoupper($strString,'LATIN1');
      }

    /**
    * Método que retorna uma data independente do formato de entrada com o formato inverso da mesma
    * sem aspas ou em branco em caso de vazio
    * @param type $arg
    * @return string
    */
    public function data($data){
      if(trim($data) != ""){

        //Verificando o formato de entrada
        if (substr($data,2,1) == '/'){ // Entrada->01/05/2013 Saída->'2013-05-01'
          return substr($data,6,4)."-".substr($data,3,2)."-".substr($data,0,2);
        }
        else{ // Entrada->2013-05-01 Saída->'01/05/2013'
          return substr($data,8,2)."/".substr($data,5,2)."/".substr($data,0,4);
        }
      }
      else{
        return "";
      }
    }

    /**
    * Método para Escape de aspas simples (apóstrofo) em valores de campos de texto
    * em Inserts e Updates, bem como em valores informados em filtros de busca 
    * 
    * @param string $strTexto
    * @return string
    */
    public function escSqlQuotes($strTexto){
      return str_replace("'","''",$strTexto);
    }

    /**
    * Método que retorna um Numero Inteiro em Formato de Versão
    * Ex.: Entrada: 109 Saída 1.0.9
    * @param $nrVersao -> Versão a Ser Formatada
    * @return string
    */
    public function versao($nrVersao){
      $intTamanho = strlen($nrVersao);

      $strFormat = '';

      for ($i = 0; $i <= $intTamanho-1; $i++) {
        if ($i == 0)
          $strFormat = substr($nrVersao,$i,1);
        else
          $strFormat = $strFormat.".".substr($nrVersao,$i,1);
      }

      return $strFormat;

    }

    /**
    * Método para Formatação de Datas para Gravação em Banco de Dados
    * Obs O formato de entrada deverá ser obrigatóriamente de forma inversa
    * Entrada: 2015-05-01
    * Saída: '2015-05-01'
    * Entrada:
    * Saída: null
    * @param type $arg
    * @return string
    */
    public function DataBd($data){
      if (trim($data) != ""){
        return "'".$data."'";
      }
      else{
        return "null";
      }
    }

    /**
    * Método para Formatação de Time para Gravação em Banco de Dados
    * Obs O formato de entrada deverá ser obrigatóriamente de forma inversa
    * Entrada: 00:00
    * Saída: '00:00:00'
    * Entrada:
    * Saída: null
    * @param type $arg
    * @return string
    * @author André Machado
    */
    public function TimeBd($time){
      if (trim($time) != ""){
        return "'".$time."'";
      }
      else{
        return "null";
      }
    }

    /**
    * Método para Formatação de Valores Inteiros para Gravação no Banco de Dados
    * Entrada: 123245
    * Saída: 123245
    * Entrada:
    * Saída: null
    * @param type $intValor
    * @return string
    */
    public function NullBd($intValor){
      if (trim($intValor) != ""){
        return $intValor;
      }
      else{
        return "null";
      }
    }

    /**
    * Método para Formatação de Valores String para Gravação no Banco de Dados
    * Entrada: string
    * Saída: 'string'
    * Entrada: vazio
    * Saída: null
    * Entrada: null
    * Saída: null
    * @param type $strString
    * @return string
    */
    public function NullString($strString){
      if (trim($strString) != ""){

        if (trim($strString) == "null"){
           return "null";
        }
        else if (trim($strString) != ""){
           return "'".$strString."'";
        }

      }
      else{
        return "null";
      }
    }

    /**
    * Método que retorna o numero de espaços para os Tabs
    * Ex.: 0 Tab é igual a 0 Espaços
    * Ex.: 1 Tab é igual a 4 Espaços
    * @param type $intTabs
    * @return String
    */
    public function EchoTabs($intTabs){

      $x = 0;
      $strTabs = '';

      if($intTabs != 0){
        $intTot = 1 * $intTabs;

        for ($x = 1; $x <= $intTot; $x++){
          $strTabs .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        }

      }

      return $strTabs;
    }

    /**
    * Método que passa uma string para lowercase
    * @param  $strString -> String a ser processada
    * @return $strString -> String devidamente Processada
    **/
    public function LowerCase($strString){
      $strString = mb_strtolower($strString,'LATIN1');
      return $strString;
    }

    /**
    * Método que passar uma string com escapes de aspas e caracteres especiais
    * #Função usada para uso no Postgresql (pg_escape_string) pode se adaptada para outros bancos de dados
    * @param  $strString -> String a ser processada
    * @return $strString -> String devidamente Processada
    * @author André Machado
    **/
    public function StrScape($strString){
      return pg_escape_string($strString);
    }

    /**
    * Método para passar uma string e dela pegar a primeira expressão depois do espaço
    * @param  $strString -> String a ser processada
    * @return $strString -> String devidamente Processada
    * @author André Machado
    **/
    public function FirstWord($strString){
      return explode(' ', $strString)[0];
    }

    /**
    * Método que formata um valor colocando ponto para milhar e vírgula para decimal
    * @param $strValor -> Valor a ser formatado
    * @param $decimals -> Quantidade de casas após a virgula
    * @param mixed $strValor
    */
    public function Currency($strValor, $decimals = null) {

      $decimals = ( is_null($decimals) ? 2 : $decimals );

      if((string)$strValor != ''){
        if (floatval($strValor) == 0 || $strValor == '0')
          return '0,00';
        else
          return number_format($strValor, $decimals, ',', '.');
      }
      else{
        return '';
      }
    }

    /**
     * Função que formata qualquer valor de entrada numérico em um `float`.
     * Ex.: 10.5 => 10.5
     * Ex.: 10.000,50 => 10000.5
     * Ex.: 10,000.50 => 10000.5
     * Ex.: 1.234,56 => 1234.56
     * Ex.: 1,234.56 => 1234.56
     * Ex.: 1 234,56 => 1234.56
     * Ex.: valores não numéricos => 0
     * @param mixed $value
     * @return float
     */
    public function Float($value) {
      if (is_numeric($value)) {
        return (float) $value;
      }

      //Normalização do valor
      $value = trim(str_replace([" ", "?", "'"], "", $value));

      //VArificando se possui "." e ","
      if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        //Verifica se a "," é o separador de casas decimais
        if (strrpos($value, ',') > strrpos($value, '.')) {
          $value = str_replace('.', '', $value);
          $value = str_replace(',', '.', $value);
        }
        else { //Quando o "." for o separador de casas decimais
          $value = str_replace(',', '', $value);
        }
      }
      //Verificando se possui apenas ","
      else if (strpos($value, ',') !== false) {
        $value = str_replace(',', '.', $value);
      }

      //Transformando valor em float
      return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
    * Método para retornar vazio no caso do número for zero
    * @param $valor -> Valor a ser manipulado
    * @return $valor -> Valor atualizado
    * @author Marcos Frare
    */
    public function zeroEmBranco($valor){
      if((is_numeric($valor) || is_int($valor)) && $valor == 0){
        $valor = "";
      }
      return $valor;
    }

    /**
    * Método que retorna a Descrição do Período de acordo com o Tipo e o Código Passado como parâmetro
    * @author Jonny Gubler
    * @param mixed $flTipo
    * @param mixed $cdPeriodo
    */
    public function GetPeriodoByTipo($flTipo,$cdPeriodo){

      $strPeriodo = "";

      switch ($flTipo) {
        case "M": //Mensal
          $strPeriodo = $this->mesExtenso($this->lpad($cdPeriodo,2));
          break;
        case "B": //Bimestral
          $strPeriodo = $cdPeriodo.'º Bimestre';
          break;
        case "T": //Trimestral
          $strPeriodo = $cdPeriodo.'º Trimestre';
          break;
        case "Q": //Quadrimestral
          $strPeriodo = $cdPeriodo.'º Quadrimestre';
          break;
        case "S": //Semestral
          $strPeriodo = $cdPeriodo.'º Semestre';
          break;
        case "A": //Anual
          $strPeriodo = 'Anual';
          break;

      }

      return $strPeriodo;

    }

    /**
    * Método que retorna o Mes Final de Acordo com o Tipo do Período
    * @author Jonny Gubler
    * @param mixed $flTipo
    * @param mixed $cdPeriodo
    */
    public function GetMesReferenciaByTipoAndPeriodo($flTipo,$cdPeriodo){

      $cdMesReferencia = "";

      switch ($flTipo) {
        case "M": //Mensal
          $cdMesReferencia = $cdPeriodo;
          break;
        case "B": //Bimestral
          $cdMesReferencia = $cdPeriodo*2;
          break;
        case "T": //Trimestral
          $cdMesReferencia = $cdPeriodo*3;
          break;
        case "Q": //Quadrimestral
          $cdMesReferencia = $cdPeriodo*4;
          break;
        case "S": //Semestral
          $cdMesReferencia = $cdPeriodo*6;
          break;
        case "A": //Anual
          $cdMesReferencia = 12;
          break;

      }

      return $cdMesReferencia;

    }

    /**
    * Método que gera e Retorna uma string com Nome Único pela data e hora atual
    * Geralmente utilizada para nomear arquivos e ou imagens evitando a duplicidade dos mesmos
    * @author Jonny Gubler
    */
    public function GetNomeUnico(){
      $flMicro = microtime(true);
      $cdMicroTime = sprintf("%06d",($flMicro - floor($flMicro)) * 1000000);
      $objTime = new DateTime( date('H:i:s.'.$cdMicroTime, $flMicro) );

      //Gerando um Nome Unico para o arquivo
      $strFileName = date("Ymd").$objTime->format("Hisu");

      return $strFileName;
    }

    /**
    * Método que gera e retorna um identificador universal único com base na RFC 4122 - Seção 4.4
    * Geralmente utilizada para nomear arquivos e ou imagens evitando a duplicidade dos mesmos
    * Para versões PHP > 7, usar random_bytes no lugar de openssl_random
    */
    public function GetIdUnico() {
    
      //Gera uma sequência aleatória de bytes
      $data = openssl_random_pseudo_bytes(16, $cstrong);
   
      assert(strlen($data) == 16 && $data !== false && $cstrong);

      // seta a versão para 0100 (UUID v4)
      $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
      
      // Seta os bits 6-7 para 10
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

      //Retorna uma string UUID de 36 caracteres.
      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
    
    
    /**
    * Método que retorna um Número Ordinal por Exenso Ex.: Entrada: 7 Retorno: sétimo
    * @author Jonny Gubler
    * @param $strValor -> Valor Inteiro a ser convertido para extenso
    * @param $strGenero -> Gêndero do Valor [o] [a] Ex.: sétim[o][a]
    */
    public function GetOrdinalExtenso($strValor, $strGenero){

      $strValor = $this->lpad($strValor, 20);

      $elementos[1] = Array("", "primeir", "segund", "terceir", "quart", "quint", "sext", "sétim", "oitav", "non");
      $elementos[2] = Array("", "décim", "vigésim", "trigésim", "quadragésim", "quinquagésim", "sexagésim", "septuagésim", "octogésim", "nonagésim");
      $elementos[3] = Array("", "centésim", "ducentésim", "trecentésim", "quadringentésim", "quingentésim", "seiscentésim", "septingentésim", "octingentésim", "nongentésim");
      $elementos[4] = "milésim";
      $elementos[7] = "milhonésim";
      $elementos[10] = "bilhonésim";
      $elementos[13] = "trilhonésim";

      $controle = 3;
      $ordinal = "";
      $soma = 0;

      for ($c = 5; $c <= 19; $c++){
        $num = substr($strValor, $c, 1);
        settype($num, "integer");

        if ($num <> 0 && ($num > 1 || $c > 16)){
          $temp_ord = $elementos[$controle][$num];

          $ordinal = $ordinal." ".$temp_ord.$strGenero;

          $soma+= $num*10^$controle;
        }
        else if ($num <> 0){
          $soma+= $num*10^$controle;
        }

        $controle--;

        if ($controle == 0 && $c < 19){
          if ($soma > 1 && isset($elementos[20-$c])){
            $temp_ord = $elementos[20-$c];

            if ($maiusculas)
              $temp_ord = strtoupper(substr($temp_ord,0,1)).substr($temp_ord,1,strlen($temp_ord)-1);

            $ordinal = $ordinal." ".$temp_ord.$strGenero;
          }

          $controle = 3;
          $soma = 0;
        }
     }
     return $ordinal;
    }

    /**
    * Método que retorna um Número por Exenso Ex.: Entrada: 7 Retorno: sete
    * @author Samuel Koepsel
    * @param $strValor -> Valor Inteiro a ser convertido para extenso
    */
    public function GetVlExtenso($valor=0) {

      $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
      $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");

      $c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
      $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
      $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
      $u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");

      $z=0;

      $valor = number_format($valor, 2, ".", ".");
      $inteiro = explode(".", $valor);
      for($i=0;$i<count($inteiro);$i++)
        for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
          $inteiro[$i] = "0".$inteiro[$i];

      // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
      $fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
      for ($i=0;$i<count($inteiro);$i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
        $t = count($inteiro)-1-$i;
        $r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000")$z++; elseif ($z > 0) $z--;
        if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
        if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
      }

      return($rt ? $rt : "zero");
    }

    /**
    * Método que retorna o dia da semana por extenso a partir de um numero inteiro onde 1 é igual a domingo
    * @author Jonny Gubler
    * @param $intDiaSemana -> Inteiro que identifica o dia da semana
    * @return mixed
    */
    public function GetDiaSemanaExtenso($intDiaSemana){
      $strDiaSemanaExtenso = "";
      switch ($intDiaSemana) {
        case 1: $strDiaSemanaExtenso = "Domingo";break;
        case 2: $strDiaSemanaExtenso = "Segunda Feira";break;
        case 3: $strDiaSemanaExtenso = "Terça Feira";break;
        case 4: $strDiaSemanaExtenso = "Quarta Feira";break;
        case 5: $strDiaSemanaExtenso = "Quinta Feira";break;
        case 6: $strDiaSemanaExtenso = "Sexta Feira";break;
        case 7: $strDiaSemanaExtenso = "Sábado";break;
      }
      return $strDiaSemanaExtenso;
    }

    /**
    * Método que retorna uma string com máscara aplicada conforme parámetro
    *
    * @param $strMask   -> String contendo a máscara, onde o hash (#) é substituído pelo mixed passado
    * @param $strTexto  -> Texto a ser convertido
    *
    * @return string
    *
    * @author André Vigarani
    */
    public function ApplyHashMask($strMask,$strTexto){
      if($strTexto != ""){
        
        //Remove espaços
        $strTexto = str_replace(" ","",$strTexto);
        $strHash = preg_replace('([^#])', '', $strMask);
        
        for($i=0; ($i<strlen($strTexto) && $i<strlen($strHash)); $i++){
          $strMask[strpos($strMask,"#")] = $strTexto[$i];
        }

        return $strMask;
      }
      else{
        return $strTexto;
      }
    }
    
    /**
    * Método responsável por aplicar a máscara que esconde o CPF parcialmente com asteriscos.
    *
    * @param $cpf -> CPF a ser anonimizado.
    *
    * @return string
    *
    * @author Igor Ceola
    */
    public function cpfAnonimo($cpf){
      $cpf = substr_replace($cpf, '***', 4, 3);
      $cpf = substr_replace($cpf, '***', 8, 3);
      $cpf = substr_replace($cpf, '***', 12, 3);
      
      return $cpf;
    }

    /**
    * Método responsável por converter número inteiro em número romano
    * @param $int -> Número inteiro
    * @return string
    * @author Davi Gabriel
    */
    public function intToRoman($int){
      $result = '';
      
      //Cria um array com todos os numerais em romano e suas equivalências
      $arrayRomano = array('M' => 1000,
      'CM' => 900,
      'D' => 500,
      'CD' => 400,
      'C' => 100,
      'XC' => 90,
      'L' => 50,
      'XL' => 40,
      'X' => 10,
      'IX' => 9,
      'V' => 5,
      'IV' => 4,
      'I' => 1);
      
      foreach($arrayRomano as $romano => $num){
        //Número de ocorrencias
        $ocorrencias = intval($int/$num);
      
        //Adiciona o mesmo número de letras na string
        $result .= str_repeat($romano,$ocorrencias);
      
        //Seta o número int com o resto da divisão entre o inteiro e o num
        $int = $int % $num;
      }
      
      return $result;
    }

    /**
    * Método responsável por converter número inteiro em número romano
    * @param $strRomano -> Número romano
    * @return int
    * @author Davi Gabriel
    */
    public function romanToInt($strRomano){
      $arrayRomanos = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
      ];

      $roman = $strRomano;
      $int = 0;

      foreach ($arrayRomanos as $strRomano => $value) {
        while (strpos($roman, $strRomano) === 0) {
          //Decompõe número
          $int += $value;
          $roman = substr($roman, strlen($strRomano));
        }
      }

      return $int;
    }

    /**
     * Função para converter uma palavra do plural para singular
     * @param string -> Palavra no plural
     * @return string -> Palavra em singular
     * @author Davi Gabriel
     */
    public function pluralToSingular($palavra) {
      //Plural -> Singular,
      //Somente para excessões que não entram nas expressões regulares
      //Caso uma palavra irregular for encontrada é necessário colocar dentro do array
      $dicionarioIrregular = [
          'mãos' => 'mão',
          'pães' => 'pão',
          'cães' => 'cão',
          'alemães' => 'alemão',
          'irmãos' => 'irmão',
          'répteis' => 'réptil',
      ];

      if (array_key_exists($palavra, $dicionarioIrregular)) {
          return $dicionarioIrregular[$palavra];
      }

      $regras = [
          '/(ões)$/i' => 'ão',      // Ex: Leões -> Leão
          '/(ães)$/i' => 'ão',      // Ex: Pães -> Pão
          '/(ais|éis|óis|uis)$/i' => 'al', // Ex: Animais -> Animal
          '/(is)$/i' => 'il',       // Ex: Répteis -> Réptil
          '/(ns)$/i' => 'm',        // Ex: Sons -> Som
          '/(res|zes)$/i' => '\1',  // Ex: Flores -> Flor, Cruzes -> Cruz
          '/(s)$/i' => '',          // Ex: Casas -> Casa
      ];

      foreach ($regras as $regex => $substituicao) {
          if (preg_match($regex, $palavra)) {
              return preg_replace($regex, $substituicao, $palavra);
          }
      }

      return $palavra;
    }

    /**
     * Função para converter uma palavra em singular para plural
     * @param string -> Palavra em singular
     * @return string -> Palavra no plural
     * @author Davi Gabriel
     */
    public function singularToPlural($palavra) {
      //Singular -> Plural,
      //Somente para excessões que não entram nas expressões regulares
      //Caso uma palavra irregular for encontrada é necessário colocar dentro do array
      $dicionarioIrregular = [
          'mão' => 'mãos',
          'pão' => 'pães',
          'cão' => 'cães',
          'alemão' => 'alemães',
          'irmão' => 'irmãos',
          'reptil' => 'répteis',
      ];
  
      if (array_key_exists(LowerCase($palavra), $dicionarioIrregular)) {
          return $dicionarioIrregular[$palavra];
      }
  
      $regras = [
          '/(r|z)$/i' => '\1es',          // Ex: Flor -> Flores, Cruz -> Cruzes
          '/(al|el|ol|ul)$/i' => '\1is',  // Ex: Animal -> Animais
          '/il$/i' => 'eis',               // Ex: Útil -> Uteis
          '/m$/i' => 'ns',                // Ex: Som -> Sons
          '/ão$/i' => 'ões',              // Ex: Excessão -> Excessões
          '/s$/i' => 'ses',               // Ex: País -> Países
          '/(x|ch|sh)$/i' => '\1es',      // Ex: Peixe -> Peixes
          '/^(.*)$/i' => '\1s'            // Ex: Casa -> Casas (padrão geral)
      ];
  
      foreach ($regras as $regex => $substituicao) {
          if (preg_match($regex, $palavra)) {
              return preg_replace($regex, $substituicao, $palavra);
          }
      }
  
      return $palavra . 's';
    }
    
  }


  /*Classe Responsável pelo processamento de Menságens diverssas no sistema*/
  class Message{
    public function Alert($flDisplay,$strMessage){
      $arrMessage = array();
      $arrMessage['flDisplay'] = utf8_encode($flDisplay);
      $arrMessage['flTipo'] = utf8_encode('A');
      $arrMessage['dsMsg'] = utf8_encode($strMessage);
      header("Content-type: application/json");
      echo json_encode($arrMessage);
    }

    public function Succes($flDisplay,$strMessage,$intIdRegistro=null){
      $arrMessage = array();
      $arrMessage['flDisplay'] = utf8_encode($flDisplay);
      $arrMessage['flTipo'] = utf8_encode('S');
      $arrMessage['dsMsg'] = utf8_encode($strMessage);
      $arrMessage['idRegistro'] = utf8_encode($intIdRegistro);
      header("Content-type: application/json");
      echo json_encode($arrMessage);
    }

    public function Error($flDisplay,$strMessage){
      $arrMessage = array();
      $arrMessage['flDisplay'] = utf8_encode($flDisplay);
      $arrMessage['flTipo'] = utf8_encode('E');
      $arrMessage['dsMsg'] = utf8_encode($strMessage);
      header("Content-type: application/json");
      echo json_encode($arrMessage);
    }

    public function DlgError($strMessage){
      echo "<script>";
      echo "   DlgError('".$strMessage."')";
      echo "</script>";
    }

    /**
    * Método para tratar as mensagens de validações e Erros
    * @param mixed $arrResult -> Array com mensagem e o tipo da Mensagem
    * @author Marcos Frare
    */
    public function LoadMessage($arrResult){
      if($arrResult['flTipo'] == 'E'){
        $this->Error('dlg',$arrResult['dsMsg']."<br>");
      }else{
        $this->Alert('dlg',$arrResult['dsMsg']."<br>");
      }
    }
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------//
  //Classe responsável pela montagem das condições de Pesquisa
  //----------------------------------------------------------------------------------------------------------------------------------------------//
  class Filtro{

    //Propriedades Abstratas
    private $dsArgumento1;
    private $dsArgumento2;
    private $nmFiltro;
    private $flComparacao;

    public function __construct(){
      $this->dsArgumento1 = '';
      $this->dsArgumento2 = '';
      $this->nmFiltro = '';
      $this->flComparacao = '';
    }

    public function Set($prop, $val){
      $this->$prop = $val;
    }

    public function Get($prop){
      return $this->$prop;
    }

    public function GetFiltro(){
      $fmt = new Format();

      //Retirando o tipo do campo
      $this->nmFiltro = substr($this->nmFiltro,4,strlen($this->nmFiltro));

      //Verificando o tipo da pesquisa
      if(!(strpos($this->flComparacao, "&") === false)){ //Pesquisa por Texto
        if ($this->flComparacao == '&&'){ //Contém
          $condicao .= "AND UPPER(shglobal.CLEAR(".$this->nmFiltro.")) LIKE UPPER(shglobal.CLEAR('%".$this->dsArgumento1."%')) ";
        }else if ($this->flComparacao == '%&'){ //Inicia Com
          $condicao .= "AND UPPER(shglobal.CLEAR(".$this->nmFiltro.")) LIKE UPPER(shglobal.CLEAR('".$this->dsArgumento1."%')) ";
        }else if ($this->flComparacao == '&%'){ //Termina Com
          $condicao .= "AND UPPER(shglobal.CLEAR(".$this->nmFiltro.")) LIKE UPPER(shglobal.CLEAR('%".$this->dsArgumento1."')) ";
        }else if ($this->flComparacao == 'N&'){ //Não Contém
          $condicao .= "AND UPPER(shglobal.CLEAR(".$this->nmFiltro.")) NOT LIKE UPPER(shglobal.CLEAR('%".$this->dsArgumento1."%')) ";
        }else if ($this->flComparacao == '!&'){ //Diferente de
          $condicao .= "AND UPPER(shglobal.CLEAR(".$this->nmFiltro.")) <> UPPER(shglobal.CLEAR('".$this->dsArgumento1."')) ";
        }else if ($this->flComparacao == '=&'){ //Igual a
          $condicao .= "AND UPPER(shglobal.CLEAR(".$this->nmFiltro.")) = UPPER(shglobal.CLEAR('".$this->dsArgumento1."')) ";
        }
      }

      if(!(strpos($this->flComparacao, "#") === false)){ //Pesquisa por Inteiros
        if ($this->flComparacao == '##'){ //Contém
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('%".$this->dsArgumento1."%')) ";
        }else if ($this->flComparacao == '%#'){ //Inicia Com
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('".$this->dsArgumento1."%')) ";
        }else if ($this->flComparacao == '#%'){ //Termina Com
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('%".$this->dsArgumento1."')) ";
        }else if ($this->flComparacao == 'N#'){ //Não Contém
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) NOT LIKE UPPER(shglobal.CLEAR('%".$this->dsArgumento1."%')) ";
        }else if ($this->flComparacao == '!#'){ //Diferente de
          $condicao .= "AND ".$this->nmFiltro." <> ".$this->dsArgumento1." ";
        }else if ($this->flComparacao == '=#'){ //Igual a
          $condicao .= "AND ".$this->nmFiltro." = ".$this->dsArgumento1." ";
        }else if ($this->flComparacao == '>#'){ //Maior que
          $condicao .= "AND ".$this->nmFiltro." > ".$this->dsArgumento1." ";
        }else if ($this->flComparacao == '<#'){ //Maior que
          $condicao .= "AND ".$this->nmFiltro." < ".$this->dsArgumento1." ";
        }else if ($this->flComparacao == '*#'){ //Entre
          $condicao .= "AND ".$this->nmFiltro." >= ".$this->dsArgumento1." AND ".$this->nmFiltro." <= ".$this->dsArgumento2;
        }
      }

      if(!(strpos($this->flComparacao, "$") === false)){ //Pesquisa por Valores Monetários
        if ($this->flComparacao == '$$'){ //Contém
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('%".$fmt->valor_bd($this->dsArgumento1)."%')) ";
        }else if ($this->flComparacao == '%$'){ //Inicia Com
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('".$fmt->valor_bd($this->dsArgumento1)."%')) ";
        }else if ($this->flComparacao == '$%'){ //Termina Com
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('%".$fmt->valor_bd($this->dsArgumento1)."')) ";
        }else if ($this->flComparacao == 'N$'){ //Não Contém
          $condicao .= "AND CAST(".$this->nmFiltro." AS VARCHAR) NOT LIKE UPPER(shglobal.CLEAR('%".$fmt->valor_bd($this->dsArgumento1)."%')) ";
        }else if ($this->flComparacao == '!$'){ //Diferente de
          $condicao .= "AND ".$this->nmFiltro." <> ".$fmt->valor_bd($this->dsArgumento1)." ";
        }else if ($this->flComparacao == '=$'){ //Igual a
          $condicao .= "AND ".$this->nmFiltro." = ".$fmt->valor_bd($this->dsArgumento1)." ";
        }else if ($this->flComparacao == '>$'){ //Maior que
          $condicao .= "AND ".$this->nmFiltro." > ".$fmt->valor_bd($this->dsArgumento1)." ";
        }else if ($this->flComparacao == '<$'){ //Maior que
          $condicao .= "AND ".$this->nmFiltro." < ".$fmt->valor_bd($this->dsArgumento1)." ";
        }else if ($this->flComparacao == '*$'){ //Entre
          $condicao .= "AND ".$this->nmFiltro." >= ".$fmt->valor_bd($this->dsArgumento1)." AND ".$this->nmFiltro." <= ".$fmt->valor_bd($this->dsArgumento2);
        }
      }

      if(!(strpos($this->flComparacao, "D") === false)){ //Pesquisa por Datas
        if ($this->flComparacao == '=D'){ //Igual a
          $condicao .= "AND ".$this->nmFiltro." = '".$fmt->data($this->dsArgumento1)."' ";
        }else if ($this->flComparacao == '>D'){ //Maior que
          $condicao .= "AND ".$this->nmFiltro." > '".$fmt->data($this->dsArgumento1)."' ";
        }else if ($this->flComparacao == '<D'){ //Menor que
          $condicao .= "AND ".$this->nmFiltro." < '".$fmt->data($this->dsArgumento1)."' ";
        }else if ($this->flComparacao == '*D'){ //Entre
          $condicao .= "AND ".$this->nmFiltro." >= '".$fmt->data($this->dsArgumento1)."' AND  ".$this->nmFiltro." <= '".$fmt->data($this->dsArgumento2)."'";
        }
      }

      return $condicao;
    }
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------//
  //Classe responsavel pela manupulação de Relatórios
  //----------------------------------------------------------------------------------------------------------------------------------------------//
  class Report{
    //Propriedades Abstratas
    private $name;
    private $template;
    private $format;
    private $params;
    private $dsurl;
    private $dsurlhelp;

    public function __construct(){
      $this->name = '';
      $this->template = '';
      $this->format = '';
      $this->params = '';
      $this->dsurl = '';
      $this->dsurlhelp = '';
    }

    public function set($prop, $val){
      $this->$prop = $val;
    }

    public function get($prop){
      return $this->$prop;
    }
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------//
  //Classe com métodos Diversos
  //----------------------------------------------------------------------------------------------------------------------------------------------//
  class Utils{

    /**
    * Método que retorna o nome da imagem de acordo com o Estatus da Obrigação
    * Utilizado na consulta de Agenda de Obrigações
    * @return string
    */


    public function CheckEnvioEmail($strCurrentEmail){
      
      $bolEnvio = true;
      
      if(!isset($_SESSION['flAmbiente'])) {

        switch(getenv('SISGOV_ENV_MODE')){
          case 'LOC': $_SESSION["flAmbiente"] = "L"; break;
          case 'TES': $_SESSION["flAmbiente"] = "H"; break; //Igual a Homologação
          case 'HOM': $_SESSION["flAmbiente"] = "H"; break;
          case 'PRO': $_SESSION["flAmbiente"] = "W"; break;
        }
        
      }
      
      if($strCurrentEmail != ""){
        // Se for Local ou Homologação e se o E-mail não conter o domínio da PontoGOV, é desconsiderado
        if(($_SESSION['flAmbiente'] == 'L' || $_SESSION['flAmbiente'] == 'H') && strrpos($strCurrentEmail, '@pontogovsistemas.com.br') === false) {
          $bolEnvio = false;
        }
      }
      else{
        $bolEnvio = false;  
      }
      
      return $bolEnvio;  
    }



    public function GetStatusImage($strStatus){
      switch ($strStatus) {
        case "N":
          return 'icn_status_disabled';
          break;
        case "P":
          return 'icn_status_error';
          break;
        case "S":
          return 'icn_status_orange';
          break;
        case "G":
          return 'icn_status_blue';
          break;
        case "T":
          return 'icn_status_alert';
          break;
        case "T":
          return 'icn_status_succes';
          break;
      }
    }

    /**
    * Método que extrai o ano de uma data independente do formato de entrada
    * Entrada: 01/05/2019 ou 2019-05-01
    * Saída: 2019
    * @param mixed $strDate
    */
    public function GetYearFromDate($strDate){

      $intYear = "";

      //Verificando o formato de entrada
      if (substr($strDate,2,1) == '/'){ // Entrada->01/05/2013
        $intYear = substr($strDate,6,4);
      }
      else{ // Entrada->2013-05-01
        $intYear = substr($strDate,0,4);
      }

      return $intYear;

    }

    /**
    * Método que extrai o Mes de uma data independente do formato de entrada
    * Entrada: 01/05/2019 ou 2019-05-01
    * Saída: 05
    * @param mixed $strDate
    */
    public function GetMonthFromDate($strDate){

      $intMonth = "";

      //Verificando o formato de entrada
      if (substr($strDate,2,1) == '/'){ // Entrada->01/05/2013
        $intMonth = substr($strDate,3,2);
      }
      else{ // Entrada->2013-05-01
        $intMonth = substr($strDate,5,2);
      }

      return $intMonth;

    }

    /**
    * Gera uma saudação de acordo com o Horário
    */
    public function GetSaudacao(){
      $hr = date("H");
      $hr = intval($hr);
      if($hr >= 12 && $hr<18) {
        $resp = "Boa tarde!";
      }
      else if ($hr >= 0 && $hr <12 ){
        $resp = "Bom dia!";
      }
      else {
        $resp = "Boa noite!";
      }
      return $resp;
    }

    /**
    * Método que retorna uma senha alfanumérica de seis casas
    * Utilizado para gerar senhas automaticamente para os usuários
    * @return string
    */
    public function GeraSenhaAleatoria(){
      //Determina os caracteres que conterão a senha
      $strCaracteresAlfMai = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

      //Embaralha os caracteres e pega apenas os 6 primeiros e usando apenas Alfanúmericos Maiúsculas
      $strGerasenhaAlfMai = substr(str_shuffle($strCaracteresAlfMai),0,6);

      return $strGerasenhaAlfMai;
    }

    /**
    * Método que retorna o dia da semana de uma data, onde 1 = Domingo, 2 = Segunda Feira...
    * Obs.: o Formato de Entrada pode ser tanto 2018-01-01 como 01/01/2018
    * @author Jonny Gubler
    * @param $strData -> Data a ser verificada
    * @return $intDiaSemana -> Dia da semana em numero interio
    */
    public function GetDiaSemanaByData($strData){

      //Verificando o Formato de Entrada
      if (substr($strData,2,1) == '/'){ //Entrada->01/05/2013
        $strDia = substr($strData,0,2);
        $strMes = substr($strData,3,2);
        $strAno = substr($strData,6,4);
      }
      else{ //Entrada->2013-05-01
        $strDia = substr($strData,8,2);
        $strMes = substr($strData,5,2);
        $strAno = substr($strData,0,4);
      }

      //Begando o Dia da Semana
      $intDiaSemana = date("w", mktime(0,0,0,$strMes,$strDia,$strAno));

      if ($intDiaSemana == 0)
        $intDiaSemana = 1;
      else if ($intDiaSemana == 6)
        $intDiaSemana = 7;
      else
        $intDiaSemana = $intDiaSemana + 1;

      return $intDiaSemana;
    }

    /**
    * Método que verifica se a data informada é válida
    * Obs.: o Formato de Entrada pode ser tanto Y-m-d
    * @author Samuel Koepsel
    * @param $strData -> Data a ser verificada
    * @return 1 ou 0 -> 1 para verdadeiro e 0 para falso
    */
    public function VerificaData($strData){
      if($strData != ""){
        $data = explode("-",$strData);
        $y = $data[0];
        $m = $data[1];
        $d = $data[2];

        if(strstr($y, "_") || strstr($m, "_") || strstr($d, "_")){
          return 0;
        }else{
          $result = checkdate($m,$d,$y);
          if($result == 1){
            return 1;
          }
          else{
            return 0;
          }
        }
      }
      else{
        return 1;
      }
    }

    /**
     * Método que verifica se a hora informada é válida
     * Obs.: o Formato de Entrada pode ser H:i:s, H:i, H
     * Ex.: 25:00:00, 15:62:69 -> false
     * @author Davi Gabriel Scottini Adriano
     * @param string $strHora
     * @return bool
     */
    public function VerificaHora($strHora){
      $arrTempo = explode(":",$strHora);
      $arrVerificacao = [24,60,60];

      foreach($arrTempo as $key => $tempo){
        if($tempo > $arrVerificacao[$key] || $tempo < 0 || strstr($tempo, "_")){
          return false;
        }
      }
      return true;
    }

    /**
    * Método que valida um número de CPF ou CNPJ
    * Obs.: o Formato de Entrada deve ser com todos os caracteres
    * Referências: https://gist.github.com/rafael-neri/ab3e58803a08cb4def059fce4e3c0e40
    * Referências: https://gist.github.com/guisehn/3276302
    * @author Murilo Wippel
    * @param $strCpfCnpj -> CPF ou CNPJ a ser validado
    * @return true ou false -> de acordo com a validação
    * @return true ou false -> de acordo com a validação
    */
    public function ValidaCpfCnpj($strCpfCnpj){

      //Carregando a quantidade de caracteres
      $qtd = strlen($strCpfCnpj);

      //Verificando se é CPF ou CNPJ
      if($qtd <= 14){

        // Validando CPF

        // Extrai somente os números
        $strCpfCnpj = preg_replace('/[^0-9]/is', '', $strCpfCnpj);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($strCpfCnpj) != 11) {
          return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $strCpfCnpj)) {
          return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
          for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $strCpfCnpj[$c] * (($t + 1) - $c);
          }
          $d = ((10 * $d) % 11) % 10;
          if ($strCpfCnpj[$c] != $d) {
            return false;
          }
        }

        return true;

      }
      else{

        //Validando CNPJ

        $strCpfCnpj = preg_replace('/[^0-9]/', '', (string) $strCpfCnpj);

        // Valida tamanho
        if (strlen($strCpfCnpj) != 14)
          return false;

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $strCpfCnpj))
          return false;

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
          $soma += $strCpfCnpj[$i] * $j;
          $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($strCpfCnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
          return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
          $soma += $strCpfCnpj[$i] * $j;
          $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $strCpfCnpj[13] == ($resto < 2 ? 0 : 11 - $resto);

      }

    }

    /**
    * Método que retorna o Content-Type de uma Extensão
    * @author Jonny Gubler
    * @param $strExtensao -> Extensão do Arquivo
    * @return $strContentType -> String com o Content Type
    */
    public function GetContentType($strExtensao){

      switch($strExtensao){
        case "x3d" : $strContentType = "application/vnd.hzn-3d-crossword"; break;
        case "3gp" : $strContentType = "video/3gpp"; break;
        case "3g2" : $strContentType = "video/3gpp2"; break;
        case "mseq" : $strContentType = "application/vnd.mseq"; break;
        case "pwn" : $strContentType = "application/vnd.3m.post-it-notes"; break;
        case "plb" : $strContentType = "application/vnd.3gpp.pic-bw-large"; break;
        case "psb" : $strContentType = "application/vnd.3gpp.pic-bw-small"; break;
        case "pvb" : $strContentType = "application/vnd.3gpp.pic-bw-var"; break;
        case "tcap" : $strContentType = "application/vnd.3gpp2.tcap"; break;
        case "7z" : $strContentType = "application/x-7z-compressed"; break;
        case "abw" : $strContentType = "application/x-abiword"; break;
        case "ace" : $strContentType = "application/x-ace-compressed"; break;
        case "acc" : $strContentType = "application/vnd.americandynamics.acc"; break;
        case "acu" : $strContentType = "application/vnd.acucobol"; break;
        case "atc" : $strContentType = "application/vnd.acucorp"; break;
        case "adp" : $strContentType = "audio/adpcm"; break;
        case "aab" : $strContentType = "application/x-authorware-bin"; break;
        case "aam" : $strContentType = "application/x-authorware-map"; break;
        case "aas" : $strContentType = "application/x-authorware-seg"; break;
        case "air" : $strContentType = "application/vnd.adobe.air-application-installer-package+zip"; break;
        case "swf" : $strContentType = "application/x-shockwave-flash"; break;
        case "fxp" : $strContentType = "application/vnd.adobe.fxp"; break;
        case "pdf" : $strContentType = "application/pdf"; break;
        case "ppd" : $strContentType = "application/vnd.cups-ppd"; break;
        case "dir" : $strContentType = "application/x-director"; break;
        case "xdp" : $strContentType = "application/vnd.adobe.xdp+xml"; break;
        case "xfdf" : $strContentType = "application/vnd.adobe.xfdf"; break;
        case "aac" : $strContentType = "audio/x-aac"; break;
        case "ahead" : $strContentType = "application/vnd.ahead.space"; break;
        case "azf" : $strContentType = "application/vnd.airzip.filesecure.azf"; break;
        case "azs" : $strContentType = "application/vnd.airzip.filesecure.azs"; break;
        case "azw" : $strContentType = "application/vnd.amazon.ebook"; break;
        case "ami" : $strContentType = "application/vnd.amiga.ami"; break;
        case "apk" : $strContentType = "application/vnd.android.package-archive"; break;
        case "cii" : $strContentType = "application/vnd.anser-web-certificate-issue-initiation"; break;
        case "fti" : $strContentType = "application/vnd.anser-web-funds-transfer-initiation"; break;
        case "atx" : $strContentType = "application/vnd.antix.game-component"; break;
        case "dmg" : $strContentType = "application/x-apple-diskimage"; break;
        case "mpkg" : $strContentType = "application/vnd.apple.installer+xml"; break;
        case "aw" : $strContentType = "application/applixware"; break;
        case "les" : $strContentType = "application/vnd.hhe.lesson-player"; break;
        case "swi" : $strContentType = "application/vnd.aristanetworks.swi"; break;
        case "s" : $strContentType = "text/x-asm"; break;
        case "atomcat" : $strContentType = "application/atomcat+xml"; break;
        case "atomsvc" : $strContentType = "application/atomsvc+xml"; break;
        case "atom" : $strContentType = "application/atom+xml"; break;
        case "ac" : $strContentType = "application/pkix-attr-cert"; break;
        case "aif" : $strContentType = "audio/x-aiff"; break;
        case "avi" : $strContentType = "video/x-msvideo"; break;
        case "aep" : $strContentType = "application/vnd.audiograph"; break;
        case "dxf" : $strContentType = "image/vnd.dxf"; break;
        case "dwf" : $strContentType = "model/vnd.dwf"; break;
        case "par" : $strContentType = "text/plain-bas"; break;
        case "bcpio" : $strContentType = "application/x-bcpio"; break;
        case "bin" : $strContentType = "application/octet-stream"; break;
        case "bmp" : $strContentType = "image/bmp"; break;
        case "torrent" : $strContentType = "application/x-bittorrent"; break;
        case "cod" : $strContentType = "application/vnd.rim.cod"; break;
        case "mpm" : $strContentType = "application/vnd.blueice.multipass"; break;
        case "bmi" : $strContentType = "application/vnd.bmi"; break;
        case "sh" : $strContentType = "application/x-sh"; break;
        case "btif" : $strContentType = "image/prs.btif"; break;
        case "rep" : $strContentType = "application/vnd.businessobjects"; break;
        case "bz" : $strContentType = "application/x-bzip"; break;
        case "bz2" : $strContentType = "application/x-bzip2"; break;
        case "csh" : $strContentType = "application/x-csh"; break;
        case "c" : $strContentType = "text/x-c"; break;
        case "cdxml" : $strContentType = "application/vnd.chemdraw+xml"; break;
        case "css" : $strContentType = "text/css"; break;
        case "cdx" : $strContentType = "chemical/x-cdx"; break;
        case "cml" : $strContentType = "chemical/x-cml"; break;
        case "csml" : $strContentType = "chemical/x-csml"; break;
        case "cdbcmsg" : $strContentType = "application/vnd.contact.cmsg"; break;
        case "cla" : $strContentType = "application/vnd.claymore"; break;
        case "c4g" : $strContentType = "application/vnd.clonk.c4group"; break;
        case "sub" : $strContentType = "image/vnd.dvb.subtitle"; break;
        case "cdmia" : $strContentType = "application/cdmi-capability"; break;
        case "cdmic" : $strContentType = "application/cdmi-container"; break;
        case "cdmid" : $strContentType = "application/cdmi-domain"; break;
        case "cdmio" : $strContentType = "application/cdmi-object"; break;
        case "cdmiq" : $strContentType = "application/cdmi-queue"; break;
        case "c11amc" : $strContentType = "application/vnd.cluetrust.cartomobile-config"; break;
        case "c11amz" : $strContentType = "application/vnd.cluetrust.cartomobile-config-pkg"; break;
        case "ras" : $strContentType = "image/x-cmu-raster"; break;
        case "dae" : $strContentType = "model/vnd.collada+xml"; break;
        case "csv" : $strContentType = "text/csv"; break;
        case "cpt" : $strContentType = "application/mac-compactpro"; break;
        case "wmlc" : $strContentType = "application/vnd.wap.wmlc"; break;
        case "cgm" : $strContentType = "image/cgm"; break;
        case "ice" : $strContentType = "x-conference/x-cooltalk"; break;
        case "cmx" : $strContentType = "image/x-cmx"; break;
        case "xar" : $strContentType = "application/vnd.xara"; break;
        case "cmc" : $strContentType = "application/vnd.cosmocaller"; break;
        case "cpio" : $strContentType = "application/x-cpio"; break;
        case "clkx" : $strContentType = "application/vnd.crick.clicker"; break;
        case "clkk" : $strContentType = "application/vnd.crick.clicker.keyboard"; break;
        case "clkp" : $strContentType = "application/vnd.crick.clicker.palette"; break;
        case "clkt" : $strContentType = "application/vnd.crick.clicker.template"; break;
        case "clkw" : $strContentType = "application/vnd.crick.clicker.wordbank"; break;
        case "wbs" : $strContentType = "application/vnd.criticaltools.wbs+xml"; break;
        case "cryptonote" : $strContentType = "application/vnd.rig.cryptonote"; break;
        case "cif" : $strContentType = "chemical/x-cif"; break;
        case "cmdf" : $strContentType = "chemical/x-cmdf"; break;
        case "cu" : $strContentType = "application/cu-seeme"; break;
        case "cww" : $strContentType = "application/prs.cww"; break;
        case "curl" : $strContentType = "text/vnd.curl"; break;
        case "dcurl" : $strContentType = "text/vnd.curl.dcurl"; break;
        case "mcurl" : $strContentType = "text/vnd.curl.mcurl"; break;
        case "scurl" : $strContentType = "text/vnd.curl.scurl"; break;
        case "car" : $strContentType = "application/vnd.curl.car"; break;
        case "pcurl" : $strContentType = "application/vnd.curl.pcurl"; break;
        case "cmp" : $strContentType = "application/vnd.yellowriver-custom-menu"; break;
        case "dssc" : $strContentType = "application/dssc+der"; break;
        case "xdssc" : $strContentType = "application/dssc+xml"; break;
        case "deb" : $strContentType = "application/x-debian-package"; break;
        case "uva" : $strContentType = "audio/vnd.dece.audio"; break;
        case "uvi" : $strContentType = "image/vnd.dece.graphic"; break;
        case "uvh" : $strContentType = "video/vnd.dece.hd"; break;
        case "uvm" : $strContentType = "video/vnd.dece.mobile"; break;
        case "uvu" : $strContentType = "video/vnd.uvvu.mp4"; break;
        case "uvp" : $strContentType = "video/vnd.dece.pd"; break;
        case "uvs" : $strContentType = "video/vnd.dece.sd"; break;
        case "uvv" : $strContentType = "video/vnd.dece.video"; break;
        case "dvi" : $strContentType = "application/x-dvi"; break;
        case "seed" : $strContentType = "application/vnd.fdsn.seed"; break;
        case "dtb" : $strContentType = "application/x-dtbook+xml"; break;
        case "res" : $strContentType = "application/x-dtbresource+xml"; break;
        case "ait" : $strContentType = "application/vnd.dvb.ait"; break;
        case "svc" : $strContentType = "application/vnd.dvb.service"; break;
        case "eol" : $strContentType = "audio/vnd.digital-winds"; break;
        case "djvu" : $strContentType = "image/vnd.djvu"; break;
        case "dtd" : $strContentType = "application/xml-dtd"; break;
        case "mlp" : $strContentType = "application/vnd.dolby.mlp"; break;
        case "wad" : $strContentType = "application/x-doom"; break;
        case "dpg" : $strContentType = "application/vnd.dpgraph"; break;
        case "dra" : $strContentType = "audio/vnd.dra"; break;
        case "dfac" : $strContentType = "application/vnd.dreamfactory"; break;
        case "dts" : $strContentType = "audio/vnd.dts"; break;
        case "dtshd" : $strContentType = "audio/vnd.dts.hd"; break;
        case "dwg" : $strContentType = "image/vnd.dwg"; break;
        case "geo" : $strContentType = "application/vnd.dynageo"; break;
        case "es" : $strContentType = "application/ecmascript"; break;
        case "mag" : $strContentType = "application/vnd.ecowin.chart"; break;
        case "mmr" : $strContentType = "image/vnd.fujixerox.edmics-mmr"; break;
        case "rlc" : $strContentType = "image/vnd.fujixerox.edmics-rlc"; break;
        case "exi" : $strContentType = "application/exi"; break;
        case "mgz" : $strContentType = "application/vnd.proteus.magazine"; break;
        case "epub" : $strContentType = "application/epub+zip"; break;
        case "eml" : $strContentType = "message/rfc822"; break;
        case "nml" : $strContentType = "application/vnd.enliven"; break;
        case "xpr" : $strContentType = "application/vnd.is-xpr"; break;
        case "xif" : $strContentType = "image/vnd.xiff"; break;
        case "xfdl" : $strContentType = "application/vnd.xfdl"; break;
        case "emma" : $strContentType = "application/emma+xml"; break;
        case "ez2" : $strContentType = "application/vnd.ezpix-album"; break;
        case "ez3" : $strContentType = "application/vnd.ezpix-package"; break;
        case "fst" : $strContentType = "image/vnd.fst"; break;
        case "fvt" : $strContentType = "video/vnd.fvt"; break;
        case "fbs" : $strContentType = "image/vnd.fastbidsheet"; break;
        case "fe_launch" : $strContentType = "application/vnd.denovo.fcselayout-link"; break;
        case "f4v" : $strContentType = "video/x-f4v"; break;
        case "flv" : $strContentType = "video/x-flv"; break;
        case "fpx" : $strContentType = "image/vnd.fpx"; break;
        case "npx" : $strContentType = "image/vnd.net-fpx"; break;
        case "flx" : $strContentType = "text/vnd.fmi.flexstor"; break;
        case "fli" : $strContentType = "video/x-fli"; break;
        case "ftc" : $strContentType = "application/vnd.fluxtime.clip"; break;
        case "fdf" : $strContentType = "application/vnd.fdf"; break;
        case "f" : $strContentType = "text/x-fortran"; break;
        case "mif" : $strContentType = "application/vnd.mif"; break;
        case "fm" : $strContentType = "application/vnd.framemaker"; break;
        case "fh" : $strContentType = "image/x-freehand"; break;
        case "fsc" : $strContentType = "application/vnd.fsc.weblaunch"; break;
        case "fnc" : $strContentType = "application/vnd.frogans.fnc"; break;
        case "ltf" : $strContentType = "application/vnd.frogans.ltf"; break;
        case "ddd" : $strContentType = "application/vnd.fujixerox.ddd"; break;
        case "xdw" : $strContentType = "application/vnd.fujixerox.docuworks"; break;
        case "xbd" : $strContentType = "application/vnd.fujixerox.docuworks.binder"; break;
        case "oas" : $strContentType = "application/vnd.fujitsu.oasys"; break;
        case "oa2" : $strContentType = "application/vnd.fujitsu.oasys2"; break;
        case "oa3" : $strContentType = "application/vnd.fujitsu.oasys3"; break;
        case "fg5" : $strContentType = "application/vnd.fujitsu.oasysgp"; break;
        case "bh2" : $strContentType = "application/vnd.fujitsu.oasysprs"; break;
        case "spl" : $strContentType = "application/x-futuresplash"; break;
        case "fzs" : $strContentType = "application/vnd.fuzzysheet"; break;
        case "g3" : $strContentType = "image/g3fax"; break;
        case "gmx" : $strContentType = "application/vnd.gmx"; break;
        case "gtw" : $strContentType = "model/vnd.gtw"; break;
        case "txd" : $strContentType = "application/vnd.genomatix.tuxedo"; break;
        case "ggb" : $strContentType = "application/vnd.geogebra.file"; break;
        case "ggt" : $strContentType = "application/vnd.geogebra.tool"; break;
        case "gdl" : $strContentType = "model/vnd.gdl"; break;
        case "gex" : $strContentType = "application/vnd.geometry-explorer"; break;
        case "gxt" : $strContentType = "application/vnd.geonext"; break;
        case "g2w" : $strContentType = "application/vnd.geoplan"; break;
        case "g3w" : $strContentType = "application/vnd.geospace"; break;
        case "gsf" : $strContentType = "application/x-font-ghostscript"; break;
        case "bdf" : $strContentType = "application/x-font-bdf"; break;
        case "gtar" : $strContentType = "application/x-gtar"; break;
        case "texinfo" : $strContentType = "application/x-texinfo"; break;
        case "gnumeric" : $strContentType = "application/x-gnumeric"; break;
        case "kml" : $strContentType = "application/vnd.google-earth.kml+xml"; break;
        case "kmz" : $strContentType = "application/vnd.google-earth.kmz"; break;
        case "gqf" : $strContentType = "application/vnd.grafeq"; break;
        case "gif" : $strContentType = "image/gif"; break;
        case "gv" : $strContentType = "text/vnd.graphviz"; break;
        case "gac" : $strContentType = "application/vnd.groove-account"; break;
        case "ghf" : $strContentType = "application/vnd.groove-help"; break;
        case "gim" : $strContentType = "application/vnd.groove-identity-message"; break;
        case "grv" : $strContentType = "application/vnd.groove-injector"; break;
        case "gtm" : $strContentType = "application/vnd.groove-tool-message"; break;
        case "tpl" : $strContentType = "application/vnd.groove-tool-template"; break;
        case "vcg" : $strContentType = "application/vnd.groove-vcard"; break;
        case "h261" : $strContentType = "video/h261"; break;
        case "h263" : $strContentType = "video/h263"; break;
        case "h264" : $strContentType = "video/h264"; break;
        case "hpid" : $strContentType = "application/vnd.hp-hpid"; break;
        case "hps" : $strContentType = "application/vnd.hp-hps"; break;
        case "hdf" : $strContentType = "application/x-hdf"; break;
        case "rip" : $strContentType = "audio/vnd.rip"; break;
        case "hbci" : $strContentType = "application/vnd.hbci"; break;
        case "jlt" : $strContentType = "application/vnd.hp-jlyt"; break;
        case "pcl" : $strContentType = "application/vnd.hp-pcl"; break;
        case "hpgl" : $strContentType = "application/vnd.hp-hpgl"; break;
        case "hvs" : $strContentType = "application/vnd.yamaha.hv-script"; break;
        case "hvd" : $strContentType = "application/vnd.yamaha.hv-dic"; break;
        case "hvp" : $strContentType = "application/vnd.yamaha.hv-voice"; break;
        case "sfd-hdstx" : $strContentType = "application/vnd.hydrostatix.sof-data"; break;
        case "stk" : $strContentType = "application/hyperstudio"; break;
        case "hal" : $strContentType = "application/vnd.hal+xml"; break;
        case "html" : $strContentType = "text/html"; break;
        case "irm" : $strContentType = "application/vnd.ibm.rights-management"; break;
        case "sc" : $strContentType = "application/vnd.ibm.secure-container"; break;
        case "ics" : $strContentType = "text/calendar"; break;
        case "icc" : $strContentType = "application/vnd.iccprofile"; break;
        case "ico" : $strContentType = "image/x-icon"; break;
        case "igl" : $strContentType = "application/vnd.igloader"; break;
        case "ief" : $strContentType = "image/ief"; break;
        case "ivp" : $strContentType = "application/vnd.immervision-ivp"; break;
        case "ivu" : $strContentType = "application/vnd.immervision-ivu"; break;
        case "rif" : $strContentType = "application/reginfo+xml"; break;
        case "3dml" : $strContentType = "text/vnd.in3d.3dml"; break;
        case "spot" : $strContentType = "text/vnd.in3d.spot"; break;
        case "igs" : $strContentType = "model/iges"; break;
        case "i2g" : $strContentType = "application/vnd.intergeo"; break;
        case "cdy" : $strContentType = "application/vnd.cinderella"; break;
        case "xpw" : $strContentType = "application/vnd.intercon.formnet"; break;
        case "fcs" : $strContentType = "application/vnd.isac.fcs"; break;
        case "ipfix" : $strContentType = "application/ipfix"; break;
        case "cer" : $strContentType = "application/pkix-cert"; break;
        case "pki" : $strContentType = "application/pkixcmp"; break;
        case "crl" : $strContentType = "application/pkix-crl"; break;
        case "pkipath" : $strContentType = "application/pkix-pkipath"; break;
        case "igm" : $strContentType = "application/vnd.insors.igm"; break;
        case "rcprofile" : $strContentType = "application/vnd.ipunplugged.rcprofile"; break;
        case "irp" : $strContentType = "application/vnd.irepository.package+xml"; break;
        case "jad" : $strContentType = "text/vnd.sun.j2me.app-descriptor"; break;
        case "jar" : $strContentType = "application/java-archive"; break;
        case "class" : $strContentType = "application/java-vm"; break;
        case "jnlp" : $strContentType = "application/x-java-jnlp-file"; break;
        case "ser" : $strContentType = "application/java-serialized-object"; break;
        case "java" : $strContentType = "text/x-java-source,java"; break;
        case "js" : $strContentType = "application/javascript"; break;
        case "json" : $strContentType = "application/json"; break;
        case "joda" : $strContentType = "application/vnd.joost.joda-archive"; break;
        case "jpm" : $strContentType = "video/jpm"; break;
        case "jpeg" : $strContentType = "image/jpeg"; break;
        case "jpg" : $strContentType = "image/jpeg"; break;
        case "pjpeg" : $strContentType = "image/pjpeg"; break;
        case "jpgv" : $strContentType = "video/jpeg"; break;
        case "ktz" : $strContentType = "application/vnd.kahootz"; break;
        case "mmd" : $strContentType = "application/vnd.chipnuts.karaoke-mmd"; break;
        case "karbon" : $strContentType = "application/vnd.kde.karbon"; break;
        case "chrt" : $strContentType = "application/vnd.kde.kchart"; break;
        case "kfo" : $strContentType = "application/vnd.kde.kformula"; break;
        case "flw" : $strContentType = "application/vnd.kde.kivio"; break;
        case "kon" : $strContentType = "application/vnd.kde.kontour"; break;
        case "kpr" : $strContentType = "application/vnd.kde.kpresenter"; break;
        case "ksp" : $strContentType = "application/vnd.kde.kspread"; break;
        case "kwd" : $strContentType = "application/vnd.kde.kword"; break;
        case "htke" : $strContentType = "application/vnd.kenameaapp"; break;
        case "kia" : $strContentType = "application/vnd.kidspiration"; break;
        case "kne" : $strContentType = "application/vnd.kinar"; break;
        case "sse" : $strContentType = "application/vnd.kodak-descriptor"; break;
        case "lasxml" : $strContentType = "application/vnd.las.las+xml"; break;
        case "latex" : $strContentType = "application/x-latex"; break;
        case "lbd" : $strContentType = "application/vnd.llamagraphics.life-balance.desktop"; break;
        case "lbe" : $strContentType = "application/vnd.llamagraphics.life-balance.exchange+xml"; break;
        case "jam" : $strContentType = "application/vnd.jam"; break;
        case "123" : $strContentType = "application/vnd.lotus-1-2-3"; break;
        case "apr" : $strContentType = "application/vnd.lotus-approach"; break;
        case "pre" : $strContentType = "application/vnd.lotus-freelance"; break;
        case "nsf" : $strContentType = "application/vnd.lotus-notes"; break;
        case "org" : $strContentType = "application/vnd.lotus-organizer"; break;
        case "scm" : $strContentType = "application/vnd.lotus-screencam"; break;
        case "lwp" : $strContentType = "application/vnd.lotus-wordpro"; break;
        case "lvp" : $strContentType = "audio/vnd.lucent.voice"; break;
        case "m3u" : $strContentType = "audio/x-mpegurl"; break;
        case "m4v" : $strContentType = "video/x-m4v"; break;
        case "hqx" : $strContentType = "application/mac-binhex40"; break;
        case "portpkg" : $strContentType = "application/vnd.macports.portpkg"; break;
        case "mgp" : $strContentType = "application/vnd.osgeo.mapguide.package"; break;
        case "mrc" : $strContentType = "application/marc"; break;
        case "mrcx" : $strContentType = "application/marcxml+xml"; break;
        case "mxf" : $strContentType = "application/mxf"; break;
        case "nbp" : $strContentType = "application/vnd.wolfram.player"; break;
        case "ma" : $strContentType = "application/mathematica"; break;
        case "mathml" : $strContentType = "application/mathml+xml"; break;
        case "mbox" : $strContentType = "application/mbox"; break;
        case "mc1" : $strContentType = "application/vnd.medcalcdata"; break;
        case "mscml" : $strContentType = "application/mediaservercontrol+xml"; break;
        case "cdkey" : $strContentType = "application/vnd.mediastation.cdkey"; break;
        case "mwf" : $strContentType = "application/vnd.mfer"; break;
        case "mfm" : $strContentType = "application/vnd.mfmp"; break;
        case "msh" : $strContentType = "model/mesh"; break;
        case "mads" : $strContentType = "application/mads+xml"; break;
        case "mets" : $strContentType = "application/mets+xml"; break;
        case "mods" : $strContentType = "application/mods+xml"; break;
        case "meta4" : $strContentType = "application/metalink4+xml"; break;
        case "mcd" : $strContentType = "application/vnd.mcd"; break;
        case "flo" : $strContentType = "application/vnd.micrografx.flo"; break;
        case "igx" : $strContentType = "application/vnd.micrografx.igx"; break;
        case "es3" : $strContentType = "application/vnd.eszigno3+xml"; break;
        case "mdb" : $strContentType = "application/x-msaccess"; break;
        case "asf" : $strContentType = "video/x-ms-asf"; break;
        case "exe" : $strContentType = "application/x-msdownload"; break;
        case "cil" : $strContentType = "application/vnd.ms-artgalry"; break;
        case "cab" : $strContentType = "application/vnd.ms-cab-compressed"; break;
        case "ims" : $strContentType = "application/vnd.ms-ims"; break;
        case "application" : $strContentType = "application/x-ms-application"; break;
        case "clp" : $strContentType = "application/x-msclip"; break;
        case "mdi" : $strContentType = "image/vnd.ms-modi"; break;
        case "eot" : $strContentType = "application/vnd.ms-fontobject"; break;
        case "xls" : $strContentType = "application/vnd.ms-excel"; break;
        case "xlam" : $strContentType = "application/vnd.ms-excel.addin.macroenabled.12"; break;
        case "xlsb" : $strContentType = "application/vnd.ms-excel.sheet.binary.macroenabled.12"; break;
        case "xltm" : $strContentType = "application/vnd.ms-excel.template.macroenabled.12"; break;
        case "xlsm" : $strContentType = "application/vnd.ms-excel.sheet.macroenabled.12"; break;
        case "chm" : $strContentType = "application/vnd.ms-htmlhelp"; break;
        case "crd" : $strContentType = "application/x-mscardfile"; break;
        case "lrm" : $strContentType = "application/vnd.ms-lrm"; break;
        case "mvb" : $strContentType = "application/x-msmediaview"; break;
        case "mny" : $strContentType = "application/x-msmoney"; break;
        case "pptx" : $strContentType = "application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
        case "sldx" : $strContentType = "application/vnd.openxmlformats-officedocument.presentationml.slide"; break;
        case "ppsx" : $strContentType = "application/vnd.openxmlformats-officedocument.presentationml.slideshow"; break;
        case "potx" : $strContentType = "application/vnd.openxmlformats-officedocument.presentationml.template"; break;
        case "xlsx" : $strContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
        case "xltx" : $strContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.template"; break;
        case "docx" : $strContentType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
        case "dotx" : $strContentType = "application/vnd.openxmlformats-officedocument.wordprocessingml.template"; break;
        case "obd" : $strContentType = "application/x-msbinder"; break;
        case "thmx" : $strContentType = "application/vnd.ms-officetheme"; break;
        case "onetoc" : $strContentType = "application/onenote"; break;
        case "pya" : $strContentType = "audio/vnd.ms-playready.media.pya"; break;
        case "pyv" : $strContentType = "video/vnd.ms-playready.media.pyv"; break;
        case "ppt" : $strContentType = "application/vnd.ms-powerpoint"; break;
        case "ppam" : $strContentType = "application/vnd.ms-powerpoint.addin.macroenabled.12"; break;
        case "sldm" : $strContentType = "application/vnd.ms-powerpoint.slide.macroenabled.12"; break;
        case "pptm" : $strContentType = "application/vnd.ms-powerpoint.presentation.macroenabled.12"; break;
        case "ppsm" : $strContentType = "application/vnd.ms-powerpoint.slideshow.macroenabled.12"; break;
        case "potm" : $strContentType = "application/vnd.ms-powerpoint.template.macroenabled.12"; break;
        case "mpp" : $strContentType = "application/vnd.ms-project"; break;
        case "pub" : $strContentType = "application/x-mspublisher"; break;
        case "scd" : $strContentType = "application/x-msschedule"; break;
        case "xap" : $strContentType = "application/x-silverlight-app"; break;
        case "stl" : $strContentType = "application/vnd.ms-pki.stl"; break;
        case "cat" : $strContentType = "application/vnd.ms-pki.seccat"; break;
        case "vsd" : $strContentType = "application/vnd.visio"; break;
        case "vsdx" : $strContentType = "application/vnd.visio2013"; break;
        case "wm" : $strContentType = "video/x-ms-wm"; break;
        case "wma" : $strContentType = "audio/x-ms-wma"; break;
        case "wax" : $strContentType = "audio/x-ms-wax"; break;
        case "wmx" : $strContentType = "video/x-ms-wmx"; break;
        case "wmd" : $strContentType = "application/x-ms-wmd"; break;
        case "wpl" : $strContentType = "application/vnd.ms-wpl"; break;
        case "wmz" : $strContentType = "application/x-ms-wmz"; break;
        case "wmv" : $strContentType = "video/x-ms-wmv"; break;
        case "wvx" : $strContentType = "video/x-ms-wvx"; break;
        case "wmf" : $strContentType = "application/x-msmetafile"; break;
        case "trm" : $strContentType = "application/x-msterminal"; break;
        case "doc" : $strContentType = "application/msword"; break;
        case "docm" : $strContentType = "application/vnd.ms-word.document.macroenabled.12"; break;
        case "dotm" : $strContentType = "application/vnd.ms-word.template.macroenabled.12"; break;
        case "wri" : $strContentType = "application/x-mswrite"; break;
        case "wps" : $strContentType = "application/vnd.ms-works"; break;
        case "xbap" : $strContentType = "application/x-ms-xbap"; break;
        case "xps" : $strContentType = "application/vnd.ms-xpsdocument"; break;
        case "mid" : $strContentType = "audio/midi"; break;
        case "mpy" : $strContentType = "application/vnd.ibm.minipay"; break;
        case "afp" : $strContentType = "application/vnd.ibm.modcap"; break;
        case "rms" : $strContentType = "application/vnd.jcp.javame.midlet-rms"; break;
        case "tmo" : $strContentType = "application/vnd.tmobile-livetv"; break;
        case "prc" : $strContentType = "application/x-mobipocket-ebook"; break;
        case "mbk" : $strContentType = "application/vnd.mobius.mbk"; break;
        case "dis" : $strContentType = "application/vnd.mobius.dis"; break;
        case "plc" : $strContentType = "application/vnd.mobius.plc"; break;
        case "mqy" : $strContentType = "application/vnd.mobius.mqy"; break;
        case "msl" : $strContentType = "application/vnd.mobius.msl"; break;
        case "txf" : $strContentType = "application/vnd.mobius.txf"; break;
        case "daf" : $strContentType = "application/vnd.mobius.daf"; break;
        case "fly" : $strContentType = "text/vnd.fly"; break;
        case "mpc" : $strContentType = "application/vnd.mophun.certificate"; break;
        case "mpn" : $strContentType = "application/vnd.mophun.application"; break;
        case "mj2" : $strContentType = "video/mj2"; break;
        case "mpga" : $strContentType = "audio/mpeg"; break;
        case "mxu" : $strContentType = "video/vnd.mpegurl"; break;
        case "mpeg" : $strContentType = "video/mpeg"; break;
        case "m21" : $strContentType = "application/mp21"; break;
        case "mp4a" : $strContentType = "audio/mp4"; break;
        case "mp4" : $strContentType = "video/mp4"; break;
        case "mp4" : $strContentType = "application/mp4"; break;
        case "m3u8" : $strContentType = "application/vnd.apple.mpegurl"; break;
        case "mus" : $strContentType = "application/vnd.musician"; break;
        case "msty" : $strContentType = "application/vnd.muvee.style"; break;
        case "mxml" : $strContentType = "application/xv+xml"; break;
        case "ngdat" : $strContentType = "application/vnd.nokia.n-gage.data"; break;
        case "n-gage" : $strContentType = "application/vnd.nokia.n-gage.symbian.install"; break;
        case "ncx" : $strContentType = "application/x-dtbncx+xml"; break;
        case "nc" : $strContentType = "application/x-netcdf"; break;
        case "nlu" : $strContentType = "application/vnd.neurolanguage.nlu"; break;
        case "dna" : $strContentType = "application/vnd.dna"; break;
        case "nnd" : $strContentType = "application/vnd.noblenet-directory"; break;
        case "nns" : $strContentType = "application/vnd.noblenet-sealer"; break;
        case "nnw" : $strContentType = "application/vnd.noblenet-web"; break;
        case "rpst" : $strContentType = "application/vnd.nokia.radio-preset"; break;
        case "rpss" : $strContentType = "application/vnd.nokia.radio-presets"; break;
        case "n3" : $strContentType = "text/n3"; break;
        case "edm" : $strContentType = "application/vnd.novadigm.edm"; break;
        case "edx" : $strContentType = "application/vnd.novadigm.edx"; break;
        case "ext" : $strContentType = "application/vnd.novadigm.ext"; break;
        case "gph" : $strContentType = "application/vnd.flographit"; break;
        case "ecelp4800" : $strContentType = "audio/vnd.nuera.ecelp4800"; break;
        case "ecelp7470" : $strContentType = "audio/vnd.nuera.ecelp7470"; break;
        case "ecelp9600" : $strContentType = "audio/vnd.nuera.ecelp9600"; break;
        case "oda" : $strContentType = "application/oda"; break;
        case "ogx" : $strContentType = "application/ogg"; break;
        case "oga" : $strContentType = "audio/ogg"; break;
        case "ogv" : $strContentType = "video/ogg"; break;
        case "dd2" : $strContentType = "application/vnd.oma.dd2+xml"; break;
        case "oth" : $strContentType = "application/vnd.oasis.opendocument.text-web"; break;
        case "opf" : $strContentType = "application/oebps-package+xml"; break;
        case "qbo" : $strContentType = "application/vnd.intu.qbo"; break;
        case "oxt" : $strContentType = "application/vnd.openofficeorg.extension"; break;
        case "osf" : $strContentType = "application/vnd.yamaha.openscoreformat"; break;
        case "weba" : $strContentType = "audio/webm"; break;
        case "webm" : $strContentType = "video/webm"; break;
        case "odc" : $strContentType = "application/vnd.oasis.opendocument.chart"; break;
        case "otc" : $strContentType = "application/vnd.oasis.opendocument.chart-template"; break;
        case "odb" : $strContentType = "application/vnd.oasis.opendocument.database"; break;
        case "odf" : $strContentType = "application/vnd.oasis.opendocument.formula"; break;
        case "odft" : $strContentType = "application/vnd.oasis.opendocument.formula-template"; break;
        case "odg" : $strContentType = "application/vnd.oasis.opendocument.graphics"; break;
        case "otg" : $strContentType = "application/vnd.oasis.opendocument.graphics-template"; break;
        case "odi" : $strContentType = "application/vnd.oasis.opendocument.image"; break;
        case "oti" : $strContentType = "application/vnd.oasis.opendocument.image-template"; break;
        case "odp" : $strContentType = "application/vnd.oasis.opendocument.presentation"; break;
        case "otp" : $strContentType = "application/vnd.oasis.opendocument.presentation-template"; break;
        case "ods" : $strContentType = "application/vnd.oasis.opendocument.spreadsheet"; break;
        case "ots" : $strContentType = "application/vnd.oasis.opendocument.spreadsheet-template"; break;
        case "odt" : $strContentType = "application/vnd.oasis.opendocument.text"; break;
        case "odm" : $strContentType = "application/vnd.oasis.opendocument.text-master"; break;
        case "ott" : $strContentType = "application/vnd.oasis.opendocument.text-template"; break;
        case "ktx" : $strContentType = "image/ktx"; break;
        case "sxc" : $strContentType = "application/vnd.sun.xml.calc"; break;
        case "stc" : $strContentType = "application/vnd.sun.xml.calc.template"; break;
        case "sxd" : $strContentType = "application/vnd.sun.xml.draw"; break;
        case "std" : $strContentType = "application/vnd.sun.xml.draw.template"; break;
        case "sxi" : $strContentType = "application/vnd.sun.xml.impress"; break;
        case "sti" : $strContentType = "application/vnd.sun.xml.impress.template"; break;
        case "sxm" : $strContentType = "application/vnd.sun.xml.math"; break;
        case "sxw" : $strContentType = "application/vnd.sun.xml.writer"; break;
        case "sxg" : $strContentType = "application/vnd.sun.xml.writer.global"; break;
        case "stw" : $strContentType = "application/vnd.sun.xml.writer.template"; break;
        case "otf" : $strContentType = "application/x-font-otf"; break;
        case "osfpvg" : $strContentType = "application/vnd.yamaha.openscoreformat.osfpvg+xml"; break;
        case "dp" : $strContentType = "application/vnd.osgi.dp"; break;
        case "pdb" : $strContentType = "application/vnd.palm"; break;
        case "p" : $strContentType = "text/x-pascal"; break;
        case "paw" : $strContentType = "application/vnd.pawaafile"; break;
        case "pclxl" : $strContentType = "application/vnd.hp-pclxl"; break;
        case "efif" : $strContentType = "application/vnd.picsel"; break;
        case "pcx" : $strContentType = "image/x-pcx"; break;
        case "psd" : $strContentType = "image/vnd.adobe.photoshop"; break;
        case "prf" : $strContentType = "application/pics-rules"; break;
        case "pic" : $strContentType = "image/x-pict"; break;
        case "chat" : $strContentType = "application/x-chat"; break;
        case "p10" : $strContentType = "application/pkcs10"; break;
        case "p12" : $strContentType = "application/x-pkcs12"; break;
        case "p7m" : $strContentType = "application/pkcs7-mime"; break;
        case "p7s" : $strContentType = "application/pkcs7-signature"; break;
        case "p7r" : $strContentType = "application/x-pkcs7-certreqresp"; break;
        case "p7b" : $strContentType = "application/x-pkcs7-certificates"; break;
        case "p8" : $strContentType = "application/pkcs8"; break;
        case "plf" : $strContentType = "application/vnd.pocketlearn"; break;
        case "pnm" : $strContentType = "image/x-portable-anymap"; break;
        case "pbm" : $strContentType = "image/x-portable-bitmap"; break;
        case "pcf" : $strContentType = "application/x-font-pcf"; break;
        case "pfr" : $strContentType = "application/font-tdpfr"; break;
        case "pgn" : $strContentType = "application/x-chess-pgn"; break;
        case "pgm" : $strContentType = "image/x-portable-graymap"; break;
        case "png" : $strContentType = "image/png"; break;
        case "png" : $strContentType = "image/x-citrix-png"; break;
        case "png" : $strContentType = "image/x-png"; break;
        case "ppm" : $strContentType = "image/x-portable-pixmap"; break;
        case "pskcxml" : $strContentType = "application/pskc+xml"; break;
        case "pml" : $strContentType = "application/vnd.ctc-posml"; break;
        case "ai" : $strContentType = "application/postscript"; break;
        case "pfa" : $strContentType = "application/x-font-type1"; break;
        case "pbd" : $strContentType = "application/vnd.powerbuilder6"; break;
        case "pgp" : $strContentType = "application/pgp-encrypted"; break;
        case "pgp" : $strContentType = "application/pgp-signature"; break;
        case "box" : $strContentType = "application/vnd.previewsystems.box"; break;
        case "ptid" : $strContentType = "application/vnd.pvi.ptid1"; break;
        case "pls" : $strContentType = "application/pls+xml"; break;
        case "str" : $strContentType = "application/vnd.pg.format"; break;
        case "ei6" : $strContentType = "application/vnd.pg.osasli"; break;
        case "dsc" : $strContentType = "text/prs.lines.tag"; break;
        case "psf" : $strContentType = "application/x-font-linux-psf"; break;
        case "qps" : $strContentType = "application/vnd.publishare-delta-tree"; break;
        case "wg" : $strContentType = "application/vnd.pmi.widget"; break;
        case "qxd" : $strContentType = "application/vnd.quark.quarkxpress"; break;
        case "esf" : $strContentType = "application/vnd.epson.esf"; break;
        case "msf" : $strContentType = "application/vnd.epson.msf"; break;
        case "ssf" : $strContentType = "application/vnd.epson.ssf"; break;
        case "qam" : $strContentType = "application/vnd.epson.quickanime"; break;
        case "qfx" : $strContentType = "application/vnd.intu.qfx"; break;
        case "qt" : $strContentType = "video/quicktime"; break;
        case "rar" : $strContentType = "application/x-rar-compressed"; break;
        case "ram" : $strContentType = "audio/x-pn-realaudio"; break;
        case "rmp" : $strContentType = "audio/x-pn-realaudio-plugin"; break;
        case "rsd" : $strContentType = "application/rsd+xml"; break;
        case "rm" : $strContentType = "application/vnd.rn-realmedia"; break;
        case "bed" : $strContentType = "application/vnd.realvnc.bed"; break;
        case "mxl" : $strContentType = "application/vnd.recordare.musicxml"; break;
        case "musicxml" : $strContentType = "application/vnd.recordare.musicxml+xml"; break;
        case "rnc" : $strContentType = "application/relax-ng-compact-syntax"; break;
        case "rdz" : $strContentType = "application/vnd.data-vision.rdz"; break;
        case "rdf" : $strContentType = "application/rdf+xml"; break;
        case "rp9" : $strContentType = "application/vnd.cloanto.rp9"; break;
        case "jisp" : $strContentType = "application/vnd.jisp"; break;
        case "rtf" : $strContentType = "application/rtf"; break;
        case "rtx" : $strContentType = "text/richtext"; break;
        case "link66" : $strContentType = "application/vnd.route66.link66+xml"; break;
        case "rss" : $strContentType = "application/rss+xml"; break;
        case "xml" : $strContentType = "application/rss+xml"; break;
        case "shf" : $strContentType = "application/shf+xml"; break;
        case "st" : $strContentType = "application/vnd.sailingtracker.track"; break;
        case "svg" : $strContentType = "image/svg+xml"; break;
        case "sus" : $strContentType = "application/vnd.sus-calendar"; break;
        case "sru" : $strContentType = "application/sru+xml"; break;
        case "setpay" : $strContentType = "application/set-payment-initiation"; break;
        case "setreg" : $strContentType = "application/set-registration-initiation"; break;
        case "sema" : $strContentType = "application/vnd.sema"; break;
        case "semd" : $strContentType = "application/vnd.semd"; break;
        case "semf" : $strContentType = "application/vnd.semf"; break;
        case "see" : $strContentType = "application/vnd.seemail"; break;
        case "snf" : $strContentType = "application/x-font-snf"; break;
        case "spq" : $strContentType = "application/scvp-vp-request"; break;
        case "spp" : $strContentType = "application/scvp-vp-response"; break;
        case "scq" : $strContentType = "application/scvp-cv-request"; break;
        case "scs" : $strContentType = "application/scvp-cv-response"; break;
        case "sdp" : $strContentType = "application/sdp"; break;
        case "etx" : $strContentType = "text/x-setext"; break;
        case "movie" : $strContentType = "video/x-sgi-movie"; break;
        case "ifm" : $strContentType = "application/vnd.shana.informed.formdata"; break;
        case "itp" : $strContentType = "application/vnd.shana.informed.formtemplate"; break;
        case "iif" : $strContentType = "application/vnd.shana.informed.interchange"; break;
        case "ipk" : $strContentType = "application/vnd.shana.informed.package"; break;
        case "tfi" : $strContentType = "application/thraud+xml"; break;
        case "shar" : $strContentType = "application/x-shar"; break;
        case "rgb" : $strContentType = "image/x-rgb"; break;
        case "slt" : $strContentType = "application/vnd.epson.salt"; break;
        case "aso" : $strContentType = "application/vnd.accpac.simply.aso"; break;
        case "imp" : $strContentType = "application/vnd.accpac.simply.imp"; break;
        case "twd" : $strContentType = "application/vnd.simtech-mindmapper"; break;
        case "csp" : $strContentType = "application/vnd.commonspace"; break;
        case "saf" : $strContentType = "application/vnd.yamaha.smaf-audio"; break;
        case "mmf" : $strContentType = "application/vnd.smaf"; break;
        case "spf" : $strContentType = "application/vnd.yamaha.smaf-phrase"; break;
        case "teacher" : $strContentType = "application/vnd.smart.teacher"; break;
        case "svd" : $strContentType = "application/vnd.svd"; break;
        case "rq" : $strContentType = "application/sparql-query"; break;
        case "srx" : $strContentType = "application/sparql-results+xml"; break;
        case "gram" : $strContentType = "application/srgs"; break;
        case "grxml" : $strContentType = "application/srgs+xml"; break;
        case "ssml" : $strContentType = "application/ssml+xml"; break;
        case "skp" : $strContentType = "application/vnd.koan"; break;
        case "sgml" : $strContentType = "text/sgml"; break;
        case "sdc" : $strContentType = "application/vnd.stardivision.calc"; break;
        case "sda" : $strContentType = "application/vnd.stardivision.draw"; break;
        case "sdd" : $strContentType = "application/vnd.stardivision.impress"; break;
        case "smf" : $strContentType = "application/vnd.stardivision.math"; break;
        case "sdw" : $strContentType = "application/vnd.stardivision.writer"; break;
        case "sgl" : $strContentType = "application/vnd.stardivision.writer-global"; break;
        case "sm" : $strContentType = "application/vnd.stepmania.stepchart"; break;
        case "sit" : $strContentType = "application/x-stuffit"; break;
        case "sitx" : $strContentType = "application/x-stuffitx"; break;
        case "sdkm" : $strContentType = "application/vnd.solent.sdkm+xml"; break;
        case "xo" : $strContentType = "application/vnd.olpc-sugar"; break;
        case "au" : $strContentType = "audio/basic"; break;
        case "wqd" : $strContentType = "application/vnd.wqd"; break;
        case "sis" : $strContentType = "application/vnd.symbian.install"; break;
        case "smi" : $strContentType = "application/smil+xml"; break;
        case "xsm" : $strContentType = "application/vnd.syncml+xml"; break;
        case "bdm" : $strContentType = "application/vnd.syncml.dm+wbxml"; break;
        case "xdm" : $strContentType = "application/vnd.syncml.dm+xml"; break;
        case "sv4cpio" : $strContentType = "application/x-sv4cpio"; break;
        case "sv4crc" : $strContentType = "application/x-sv4crc"; break;
        case "sbml" : $strContentType = "application/sbml+xml"; break;
        case "tsv" : $strContentType = "text/tab-separated-values"; break;
        case "tiff" : $strContentType = "image/tiff"; break;
        case "tao" : $strContentType = "application/vnd.tao.intent-module-archive"; break;
        case "tar" : $strContentType = "application/x-tar"; break;
        case "tcl" : $strContentType = "application/x-tcl"; break;
        case "tex" : $strContentType = "application/x-tex"; break;
        case "tfm" : $strContentType = "application/x-tex-tfm"; break;
        case "tei" : $strContentType = "application/tei+xml"; break;
        case "txt" : $strContentType = "text/plain"; break;
        case "dxp" : $strContentType = "application/vnd.spotfire.dxp"; break;
        case "sfs" : $strContentType = "application/vnd.spotfire.sfs"; break;
        case "tsd" : $strContentType = "application/timestamped-data"; break;
        case "tpt" : $strContentType = "application/vnd.trid.tpt"; break;
        case "mxs" : $strContentType = "application/vnd.triscape.mxs"; break;
        case "t" : $strContentType = "text/troff"; break;
        case "tra" : $strContentType = "application/vnd.trueapp"; break;
        case "ttf" : $strContentType = "application/x-font-ttf"; break;
        case "ttl" : $strContentType = "text/turtle"; break;
        case "umj" : $strContentType = "application/vnd.umajin"; break;
        case "uoml" : $strContentType = "application/vnd.uoml+xml"; break;
        case "unityweb" : $strContentType = "application/vnd.unity"; break;
        case "ufd" : $strContentType = "application/vnd.ufdl"; break;
        case "uri" : $strContentType = "text/uri-list"; break;
        case "utz" : $strContentType = "application/vnd.uiq.theme"; break;
        case "ustar" : $strContentType = "application/x-ustar"; break;
        case "uu" : $strContentType = "text/x-uuencode"; break;
        case "vcs" : $strContentType = "text/x-vcalendar"; break;
        case "vcf" : $strContentType = "text/x-vcard"; break;
        case "vcd" : $strContentType = "application/x-cdlink"; break;
        case "vsf" : $strContentType = "application/vnd.vsf"; break;
        case "wrl" : $strContentType = "model/vrml"; break;
        case "vcx" : $strContentType = "application/vnd.vcx"; break;
        case "mts" : $strContentType = "model/vnd.mts"; break;
        case "vtu" : $strContentType = "model/vnd.vtu"; break;
        case "vis" : $strContentType = "application/vnd.visionary"; break;
        case "viv" : $strContentType = "video/vnd.vivo"; break;
        case "ccxml" : $strContentType = "application/ccxml+xml,"; break;
        case "vxml" : $strContentType = "application/voicexml+xml"; break;
        case "src" : $strContentType = "application/x-wais-source"; break;
        case "wbxml" : $strContentType = "application/vnd.wap.wbxml"; break;
        case "wbmp" : $strContentType = "image/vnd.wap.wbmp"; break;
        case "wav" : $strContentType = "audio/x-wav"; break;
        case "davmount" : $strContentType = "application/davmount+xml"; break;
        case "woff" : $strContentType = "application/x-font-woff"; break;
        case "wspolicy" : $strContentType = "application/wspolicy+xml"; break;
        case "webp" : $strContentType = "image/webp"; break;
        case "wtb" : $strContentType = "application/vnd.webturbo"; break;
        case "wgt" : $strContentType = "application/widget"; break;
        case "hlp" : $strContentType = "application/winhlp"; break;
        case "wml" : $strContentType = "text/vnd.wap.wml"; break;
        case "wmls" : $strContentType = "text/vnd.wap.wmlscript"; break;
        case "wmlsc" : $strContentType = "application/vnd.wap.wmlscriptc"; break;
        case "wpd" : $strContentType = "application/vnd.wordperfect"; break;
        case "stf" : $strContentType = "application/vnd.wt.stf"; break;
        case "wsdl" : $strContentType = "application/wsdl+xml"; break;
        case "xbm" : $strContentType = "image/x-xbitmap"; break;
        case "xpm" : $strContentType = "image/x-xpixmap"; break;
        case "xwd" : $strContentType = "image/x-xwindowdump"; break;
        case "der" : $strContentType = "application/x-x509-ca-cert"; break;
        case "fig" : $strContentType = "application/x-xfig"; break;
        case "xhtml" : $strContentType = "application/xhtml+xml"; break;
        case "xml" : $strContentType = "application/xml"; break;
        case "xdf" : $strContentType = "application/xcap-diff+xml"; break;
        case "xenc" : $strContentType = "application/xenc+xml"; break;
        case "xer" : $strContentType = "application/patch-ops-error+xml"; break;
        case "rl" : $strContentType = "application/resource-lists+xml"; break;
        case "rs" : $strContentType = "application/rls-services+xml"; break;
        case "rld" : $strContentType = "application/resource-lists-diff+xml"; break;
        case "xslt" : $strContentType = "application/xslt+xml"; break;
        case "xop" : $strContentType = "application/xop+xml"; break;
        case "xpi" : $strContentType = "application/x-xpinstall"; break;
        case "xspf" : $strContentType = "application/xspf+xml"; break;
        case "xul" : $strContentType = "application/vnd.mozilla.xul+xml"; break;
        case "xyz" : $strContentType = "chemical/x-xyz"; break;
        case "yaml" : $strContentType = "text/yaml"; break;
        case "yang" : $strContentType = "application/yang"; break;
        case "yin" : $strContentType = "application/yin+xml"; break;
        case "zir" : $strContentType = "application/vnd.zul"; break;
        case "zip" : $strContentType = "application/zip"; break;
        case "zmm" : $strContentType = "application/vnd.handheld-entertainment+xml"; break;
        case "zaz" : $strContentType = "application/vnd.zzazz.deck+xml"; break;
      }
      return $strContentType;

    }

    /**
    * Método que verifica pela extensão do arquivo se o mesmo pode ser visualizadou ou apenas baixado
    * @author Jonny Gubler
    * @param $strExtensao -> Extensão do Arquivo
    * @return $bolStatus -> Booleano com o Status da Verificação (true) Pode ser Visualizado (false) Somente pode ser Baixado
    */
    public function CheckPreviewFormato($strExtensao){

      switch($strExtensao){
        case "pdf" : $bolStatus = true; break;
        case "jpg" : $bolStatus = true; break;
        case "jpeg" : $bolStatus = true; break;
        case "png" : $bolStatus = true; break;
        case "bmp" : $bolStatus = true; break;
        default: $bolStatus = false;
      }

      return $bolStatus;

    }

    /**
    * Método que retorna o nome da Categoria Econômica da Receita de acordo com o código
    * @author Jonny Gubler
    * @param $intCdCategoriaEconomica -> Código da Categoria Econômica da Receita (Obs.: Desconsiderar o Dígito 4 ou 9)
    */
    public function GetNomeCategoriaEconomica($intCdCategoriaEconomica){

      $strNmCategoriaEconomica = "";

      switch($intCdCategoriaEconomica){
        case 1 : $strNmCategoriaEconomica = "Receitas Correntes"; break;
        case 2 : $strNmCategoriaEconomica = "Receitas de Capital"; break;
        case 7 : $strNmCategoriaEconomica = "Receitas Correntes - Intraorçamentárias"; break;
        case 8 : $strNmCategoriaEconomica = "Receitas de Capital - Intraorçamentárias"; break;
      }

      return $strNmCategoriaEconomica;

    }

    /**
    * Método que retorna o nome da Origem da Receita de acordo com o código
    * @author Jonny Gubler
    * @param $intCdOrigemReceita -> Código da Origem da Receita (Obs.: Desconsiderar o Dígito 4 ou 9)
    */
    public function GetNomeOrigemReceita($intCdOrigemReceita){

      $strNmOrigemReceita = "";

      switch($intCdOrigemReceita){
        case 11 : $strNmOrigemReceita = "Impostos, Taxas e Contribuições de Melhoria"; break;
        case 12 : $strNmOrigemReceita = "Contribuições"; break;
        case 13 : $strNmOrigemReceita = "Receita Patrimonial"; break;
        case 14 : $strNmOrigemReceita = "Receita Agropecuária"; break;
        case 15 : $strNmOrigemReceita = "Receita Industrial"; break;
        case 16 : $strNmOrigemReceita = "Receita de Serviços"; break;
        case 17 : $strNmOrigemReceita = "Transferências Correntes"; break;
        case 19 : $strNmOrigemReceita = "Outras receitas Correntes"; break;
        case 21 : $strNmOrigemReceita = "Operações de Crédito"; break;
        case 22 : $strNmOrigemReceita = "Alienação de Bens"; break;
        case 24 : $strNmOrigemReceita = "Transferências de Capital"; break;
        case 29 : $strNmOrigemReceita = "Outras receitas de Capital"; break;
        case 71 : $strNmOrigemReceita = "Impostos, Taxas e Contribuições de Melhoria - Intraorçamentárias"; break;
        case 72 : $strNmOrigemReceita = "Contribuições - Intraorçamentárias"; break;
        case 73 : $strNmOrigemReceita = "Receita Patrimonial - Intraorçamentárias"; break;
        case 74 : $strNmOrigemReceita = "Receita Agropecuária - Intraorçamentárias"; break;
        case 75 : $strNmOrigemReceita = "Receita Industrial - Intraorçamentárias"; break;
        case 76 : $strNmOrigemReceita = "Receita de Serviços - Intraorçamentárias"; break;
        case 77 : $strNmOrigemReceita = "Transferências Correntes - Intraorçamentárias"; break;
        case 79 : $strNmOrigemReceita = "Outras receitas Correntes - Intraorçamentárias"; break;
        case 81 : $strNmOrigemReceita = "Operações de Crédito - Intraorçamentárias"; break;
        case 82 : $strNmOrigemReceita = "Alienação de Bens - Intraorçamentárias"; break;
        case 84 : $strNmOrigemReceita = "Transferências de Capital - Intraorçamentárias"; break;
        case 89 : $strNmOrigemReceita = "Outras receitas de Capital - Intraorçamentárias"; break;
      }

      return $strNmOrigemReceita;

    }

    /**
    * Método que retorna o nome dos Grupos de Natureza da Despesa de acordo com o código
    * @author Jonny Gubler
    * @param $intCdGrupoNatureza -> Código da Natureza da Despesa (Obs.: Desconsiderar o Dígito 3 ou 9)
    */
    public function GetNomeGrupoNaturezaDespesa($intCdGrupoNatureza){

      $strNmGrupoNatureza = "";

      switch(intval($intCdGrupoNatureza)){
        case 31 : $strNmGrupoNatureza = "Pessoal e Encargos Sociais"; break;
        case 32 : $strNmGrupoNatureza = "Juros e Encargos da Dívida"; break;
        case 33 : $strNmGrupoNatureza = "Outras Despesas Correntes"; break;
        case 44 : $strNmGrupoNatureza = "Investimentos"; break;
        case 45 : $strNmGrupoNatureza = "Inversões Financeiras"; break;
        case 46 : $strNmGrupoNatureza = "Amortização da Dívida"; break;
        case 99 : $strNmGrupoNatureza = "Reserva de Contingência"; break;
      }

      return $strNmGrupoNatureza;

    }

    /**
    * Método que retorna o próximo dia útil de uma data
    * @author Jonny Gubler
    * @param mixed $data
    * @param mixed $saida
    */
    public function GetNextDiaUtil($data, $saida = 'd/m/Y'){

      // Converte $data em um UNIX TIMESTAMP
      $timestamp = strtotime($data);

      // Calcula qual o dia da semana de $data
      // O resultado será um valor numérico:
      // 1 -> Segunda ... 7 -> Domingo
      $dia = date('N', $timestamp);

      // Se for sábado (6) ou domingo (7), calcula a próxima segunda-feira
      if ($dia >= 6) {
        $timestamp_final = $timestamp + ((8 - $dia) * 3600 * 24);
      }
      else {
        // Não é sábado nem domingo, mantém a data de entrada
        $timestamp_final = $timestamp;
      }
      return date($saida, $timestamp_final);

    }

    /**
    * Método que Retorna o Último dia do Mes de Uma Ano e Mês
    *
    * @param mixed $intYear -> Ano de Exercício
    * @param mixed $intMonth -> Mês de Referencia
    */
    public function GetLastDayOfMonth($intYear,$intMonth){
      $intLastDay = date("t", mktime(0,0,0,$intMonth,'01',$intYear));
      return $intLastDay;
    }

    /**
    * Método que Extrai os Parâmetros de uma Url para um Array
    *
    * @param mixed $strUrl
    */
    public function GetUrlParams($qry){
      $result = array();

      //string must contain at least one = and cannot be in first position
      if(strpos($qry,'=')) {

      if(strpos($qry,'?')!==false) {
        $q = parse_url($qry);
        $qry = $q['query'];
      }
      }
      else {
        return false;
      }

      foreach (explode('&', $qry) as $couple) {
        list ($key, $val) = explode('=', $couple);
        $result[$key] = $val;
      }

      return empty($result) ? false : $result;

    }

    /**
    * Método que renomeia arquivos físicos
    *
    * @param string $strOldName  -> Antigo nome do arquivo
    * @param string $strNewName  -> Novo nome do arquivo
    * @param string $strPrefix   -> Caminho/path do arquivo
    *
    * @returns boolean -> Validação da operação
    *
    * @author André Felipe Vigarani
    */
    public function RenameFile($strPrefix, $strOldName, $strNewName){

      $strMessage = "";

      //Verifica a existência do arquivo
      if(file_exists($strPrefix.$strOldName)){

        $strOldFullPath = $strPrefix.$strOldName;
        $strNewFullPath = $strPrefix.$strNewName;

        //Renomeia
        if(!rename($strOldFullPath,$strNewFullPath)){
          $strMessage = "Erro ao renomear arquivo";
        }
      }
      else{
        $strMessage = "Arquivo a ser renomeado não encontrado";
      }

      return $strMessage;
    }

    /**
    * Método para montar de forma rápida o filtro de TIPO da unidade gestora por poder
    *
    * @param string $flPoder       -> Poder (Executivo ou Legislativo)
    * @param string $dsAlias = ''  -> Alias opcional
    *
    * @returns string -> String contendo o filtro para tipo de unidadegestora
    *
    * @author André Felipe Vigarani
    */
    public function getFiltroPoderTipoUnidadeGestora($flPoder, $dsAlias = null){

      $strFiltro = "";

      //Caso esteja passando alias
      if(!is_null($dsAlias))
        $dsAlias .= '.';

      //Verifica a existência do arquivo
      if($flPoder != ''){

        //Monta o filtro
        if ($flPoder == "E")
          $strFiltro .= " AND ".$dsAlias."fltipounidadegestora <> 'C' ";
        else if ($flPoder == "L")
          $strFiltro .= " AND ".$dsAlias."fltipounidadegestora = 'C' ";
      }

      return $strFiltro;
    }

    /**
    * Função utilizada para mostrar mensagem de erro na busca sem sucesso de
    * algum arquivo de relatório
    * 
    * @author André Felipe Vigarani
    */
    public function showMessageFileError(){
      
      $msg = new Message();
      
      // Preenchimento do fundo
      $dsHtml = '<style>
                    
                   #divFileErrorMessage{
                     background-color: #d9ecf5;
                     width: 99.1%;
                     height: 98.7%;
                     margin: auto;
                     border: 1px solid #5a8cd7;
                   }
                   
                 </style>
                 <div id="divFileErrorMessage">
                   <div id="dialog" class="frontbox-main frontbox-warning" style="display: block;">
                     <div class="frontbox-title" style="text-align: center;">Atenção!</div>
                     <div class="frontbox-message">» O arquivo de <em>Layout</em> do relatório não foi encontrado, contate o suporte.</div>
                     </div>
                   </div>
                 </div>';
      
      echo $dsHtml; 
    }
    
    /** 
    * Função responsável por retornar uma data definida por um período a partir da data atual.
    * @param $flPeriodoMensal: Define qual o período que será adicionado à data atual, onde 'M' = Mês, 'B' = Bimestre, 'T' = Trimestre,
    *                                                                                 'Q' = Quadrimestre, 'S' = Semestre e 'A' = Ano.
    * Ex: Caso o parâmetro seja 'B', a função irá retornar uma data correspondente a exatos 2 meses a partir da data atual.   
    */
    function adicionaPeriodosMensais($flPeriodoMensal){
    
      $dtFutura = '';
      
      switch ($flPeriodoMensal){
        case 'M': {
          $dtFutura = date('d-m-Y', strtotime('+1 month'));
          break;    
        }
        case 'B': {
          $dtFutura = date('d-m-Y', strtotime('+2 month'));
          break;
        }
        case 'T': {
          $dtFutura = date('d-m-Y', strtotime('+3 month'));
          break;
        }
        case 'Q': {
          $dtFutura = date('d-m-Y', strtotime('+4 month'));
          break;
        }
        case 'S': {
          $dtFutura = date('d-m-Y', strtotime('+6 month'));
          break;
        }
        case 'A': {
          $dtFutura = date('d-m-Y', strtotime('+12 month'));
          break;
        }  
      }
      return $dtFutura;
    }

    /** 
    * Função responsável por retornar uma lista de id de registros caso o usuário for de órgão setorial
    * @param $nmSchema: Define o schema em que vai ser feito a consulta
    * @param $nmTable: Define o nome da tabela em que vai ser feito a consulta
    * @return $strCondicaoOrgaoSetorial -> Retorna a condição de filtragem que vai ser utilizada na listagem dos registros, 
    * se não tiver registros que o usuário possua acesso vai retornar: " AND 'idregistro' IN(0)", 
    * se tiver registros vai ser a lista de registros "AND 'idregistro' IN (1,2,3,4...)", 
    * se o usuário não for do órgão setorial retorna vazio a string.
    */
    function verificaUsuarioOrgaoSetorial($nmSchema, $nmTable, $dsIdRegistro){
      //Importação das tbs que vão ser utilizadas
      require_once('../../../sistema/model/mdlTbLogRegistroCliente.php');
      require_once('../../../sistema/model/mdlTbUsuario.php');
      require_once('../../../global/model/mdlTbPessoa.php');
      require_once('../../../controleinterno/model/mdlTbPessoalControleInterno.php');
      
      //Selecionando o usuário da sessão
      $objTbUsuario = TbUsuario::LoadByIdUsuario($_SESSION['idUsuario']);
  
      if(is_object($objTbUsuario) && $objTbUsuario->Get('idusuario') != ""){
  
        //Selecionando a pessoa
        $objTbPessoa = TbPessoa::LoadByCdCpfCnpj($objTbUsuario->Get('cdcpf'));
  
        if(is_object($objTbPessoa) && $objTbPessoa->Get('idpessoa') != ""){
  
          //Selecionando os responsáveis pelo id da pessoa e data de vigência ativa
          $strCampos = 'pc.idpessoalcontroleinterno';
          $strCondicao = ' AND pc.idpessoa = '.$objTbPessoa->Get('idpessoa'). " AND ie.fltipovinculo = 'OS' AND pc.dtiniciovigencia <= CURRENT_DATE AND (pc.dtfimvigencia >= CURRENT_DATE OR pc.dtfimvigencia IS NULL)";
  
          $aroTbPessoalControleInterno = TbPessoalControleInterno::ListCamposByCondicaoAgrupamento($strCampos, $strCondicao,'','idpessoalcontroleinterno LIMIT 1');
  
          if(is_array($aroTbPessoalControleInterno) && count($aroTbPessoalControleInterno) > 0){
  
            //aroIdRegistro armazena os registro que o usuário possui acesso para visualizar
            $aroIdRegistro = array();
  
            //Condição do schema e tabela a ser solicitada
            $strCondicao = " AND lr.nmschema = '".$nmSchema . "' AND lr.nmtable = '".$nmTable."' AND lr.fltipooperacao = 'I' AND lr.idusuario = ".$_SESSION['idUsuario'];
            $aroTbLogRegistroCliente = TbLogRegistroCliente::ListCamposByCondicaoAgrupamento('lr.*',$strCondicao,'' , '');
  
            if(is_array($aroTbLogRegistroCliente) && count($aroTbLogRegistroCliente) > 0){
  
              //Instanciando array de registros a serem filtrados
              foreach($aroTbLogRegistroCliente as $objTbLogRegistroCliente){
                $aroIdRegistro[] = $objTbLogRegistroCliente->Get('idregistro');
              }
  
            }else{
  
              //Caso o usuário não tenha cadastrado nada
              $aroIdRegistro[] = 0;
              
            }
          }
        }
      }
      return is_array($aroIdRegistro) ? ' AND '.$dsIdRegistro.' IN('.implode(',', $aroIdRegistro).')' : '';
  
    }

    /**
     * Valida se o usuário tem acesso a consulta de pessoas para bloquear auto complete das telas de consulta que possuem
     * @return array{flTipoRestricao: string, flRestringeConsultaPessoa: bool}
     */
    function verificaUsuarioConsultaPessoa(){

      require_once("../../../sistema/model/mdlTbPerfil.php");
      require_once("../../../sistema/model/mdlTbUsuario.php");
      require_once("../../../sistema/model/mdlTbPerfilModuloRotina.php");
      require_once("../../../global/model/mdlTbConfiguracaoModulo.php");

      $flTipoRestricao = '';
      $flRestringeConsultaPessoa = false;

      $arrayModulo = array(
        35 => "FLRESTRINGECONSULTAPESSOA",
        34 => "FLRESTRINGECONSULTAPESSOA",
      );  

      $idModulo = $_SESSION["idModulo"];

      if (array_key_exists($idModulo, $arrayModulo)) {

        //Carrega a configuração FLRESTRINGECONSULTAPESSOA do módulo
        $objTbConfiguracaoModulo = TbConfiguracaoModulo::LoadByIdModuloChave($idModulo,$arrayModulo[$idModulo]);

        if(is_object($objTbConfiguracaoModulo)){
  
          //Caso esteja configurado uma das opções que restringe o acesso
          if($objTbConfiguracaoModulo->Get("dsvalor") == 'S' || $objTbConfiguracaoModulo->Get("dsvalor") == 'T'){
  
            //Setando o tipo de restrição
            $flTipoRestricao = $objTbConfiguracaoModulo->Get("dsvalor");
  
            $idUsuario = $_SESSION['idUsuario'];
            $idCliente = $_SESSION['idCliente']; 

            // Definição dos perfis para cada módulo
            $arrArray = [
                35 => [
                    'perfilLiberado' => [34, 41, 116],  
                ],
                34 => [
                    'perfilLiberado' => [33, 36, 119, 130,],  
                ]
            ];

            $perfilLiberado = implode(',', $arrArray[$idModulo]['perfilLiberado']);

            // Query para verificar se o usuário tem perfil de Administrador/Gestor/Avaliador e NÃO tem perfil de Entidade
            $dsCondicaoAdministradorGestor = sprintf(
                "AND up.idusuario = %d
                AND pf.idperfil IN (%s)
                AND up.idcliente = %d
                AND up.flstatus = 'A'",
                $idUsuario,
                $perfilLiberado,
                $idCliente
            );

            $countUsuarioPerfilAdministradorGestor = count(TbUsuario::ListByCondicaoPerfilModulo($dsCondicaoAdministradorGestor, ''));

            //Nesta condição é verificado se o usuário tem perfil de Entidade e não possui perfis de Administrador ou Gestor antes de habilitar a flag de restrição de Pessoa
            if($countUsuarioPerfilAdministradorGestor == 0){
              $flRestringeConsultaPessoa = true;
            }
  
          }
        }
      }

      return ['flTipoRestricao' => $flTipoRestricao, 'flRestringeConsultaPessoa' => $flRestringeConsultaPessoa];
    }

    /**
     * Incrementa um mes e retorna o ano atual e o mes atual
     * @param array $arrMesAno -> Primeira posição o mês e segunda o ano
     * @author Davi Gabriel
     * @return array -> Contendo na primeira posição o mês e na segunda o ano
    */
    function incrementMes($arrMesAno){
      if($arrMesAno[0] == 12){
        $arrMesAno[0] = 0;
        $arrMesAno[1]++;
      }
      $arrMesAno[0]++;
  
      return $arrMesAno;
    }

    /**
     * Função responsável por encontrar um elemento de um array, conforme regra por callback
     * @template TKey of array-key
     * @template TValue
     * @param array<TKey, TValue> $array
     * @param (callable(TValue $value): bool)|(callable(TValue $value, TKey $key): bool) $callback
     * @author Davi Gabriel
     * @return ?TValue
     */
    function array_find(array $array, callable $callback){
      foreach ($array as $key => $value) {
        if ($callback($value, $key))
          return $value;
      }

      return null;
    }

    /**
     * Função responsável por verificar se todos os elementos de um array atendem a uma condição, conforme regra definida no callback
     * @template TKey of array-key
     * @template TValue
     * @param array<TKey, TValue> $array
     * @param (callable(TValue $value): bool)|(callable(TValue $value, TKey $key): bool) $callback
     * @return bool
     */
    function array_all(array $array, callable $callback) {
      foreach ($array as $key => $value) {
          if (!$callback($value, $key)) {
              return false;
          }
      }
      return true;
    }
    /**
     * Função responsável por retornar o ícone conforme uma extensão passada
     * @param string $extension -> {"pptx", "odp", "xls", "xlsx", "csv", "pdf", "png", "jpg"}
     * @return string -> Padrão: icn_file_format_doc.png
     */
    function getIconByExtension($extension){
      $arrExtensionIcon = [
        'pptx' => 'icn_file_format_ppt.png',
        'odp' => 'icn_file_format_ppt.png',
        'xls' => 'icn_file_format_xls.png', 
        'xlsx' => 'icn_file_format_xls.png', 
        'csv' => 'icn_file_format_xls.png',
        'pdf' => 'icn_file_format_pdf.png',
        'png' => 'icn_file_format_img.png',
        'jpg' => 'icn_file_format_img.png',
      ];
      return $arrExtensionIcon[$extension] ?: 'icn_file_format_doc.png';
    }

    /**
     * Registra uma função de shutdown para remover um arquivo específico
     * @param string $nmArquivo
     * @return void
     */
    function registerUnlinkShutdown($nmArquivo){
      register_shutdown_function(function () use ($nmArquivo) {
        if (file_exists($nmArquivo)) {
          unlink($nmArquivo);
        }
      });
    }

    /**
     * Registra uma função de shutdown para remover vários arquivos específico
     * @param string[] $arrNmArquivo
     * @return void
     */
    function registerMultipleUnlinkShutdown($arrNmArquivo){
      register_shutdown_function(function () use ($arrNmArquivo) {
        foreach($arrNmArquivo as $nmArquivo){
          if (file_exists($nmArquivo)) {
            unlink($nmArquivo);
          }
        }
      });
    }

  }

  //----------------------------------------------------------------------------------------------------------------------------------------------//
  //Classe auxiliar para busca de Privilégios de acordo com o id da Rotina
  //----------------------------------------------------------------------------------------------------------------------------------------------//
  class Privilegio{

    //Propriedades Abstratas
    private $idrotina;
    private $inc;
    private $alt;
    private $exc;
    private $con;
    private $man; //Expecífico para controle de telas (Pode ser inclusão ou exclusão)

    public function __construct($idRotina){
      $dtbServer = new dtbServer();
      $utlFmt = new Format();

      $this->idmodulo = $_SESSION["idModulo"];
      $this->idrotina = $idRotina;

      //Buscando os dados de Privilégios para a rotina selecionada
      $sql = "SELECT
                CASE flconsulta WHEN 'S' THEN 'true' ELSE 'false' END AS flconsulta,
                CASE flinclusao WHEN 'S' THEN 'true' ELSE 'false' END AS flinclusao,
                CASE flalteracao WHEN 'S' THEN 'true' ELSE 'false' END AS flalteracao,
                CASE flexclusao WHEN 'S' THEN 'true' ELSE 'false' END AS flexclusao
              FROM
                shsistema.tbprivilegio pr
              LEFT JOIN
                shsistema.tbmodulorotina mr
              ON
                mr.idmodulorotina = pr.idmodulorotina
              WHERE
                pr.idcliente = ".$_SESSION["idCliente"]." AND
                pr.idusuario = ".$_SESSION["idUsuario"]." AND
                mr.idmodulo = ".$_SESSION["idModulo"]." AND
                mr.idrotina = ".$idRotina." ";

      if (!$dtbServer->Query($sql)){
        return $utlFmt->RemoveQuebraLinha($dtbServer->Message().'<br> Sql: '.$strSql);
      }
      else{
        $resSet = $dtbServer->FetchArray();
        $this->inc = trim($resSet['flinclusao']);
        $this->alt = trim($resSet['flalteracao']);
        $this->exc = trim($resSet['flexclusao']);
        $this->con = trim($resSet['flconsulta']);

        if ($this->inc == true or $this->alt == true)
          $this->man = 'true';
        else
          $this->man = 'false';
      }

    }

    /**
    *Método Get Incluir
    **/
    public function Inc(){
      return $this->inc;
    }

    /**
    *Método Get Alterar
    **/
    public function Alt(){
      return $this->alt;
    }

    /**
    *Método Get Excluir
    **/
    public function Exc(){
      return $this->exc;
    }

    /**
    *Método Get Consultar
    **/
    public function Con(){
      return $this->con;
    }

    /**
    *Método Get Consultar
    **/
    public function Man(){
      return $this->man;
    }

    /**
    *Método Get Id Rotina
    **/
    public function GetIdRotina(){
      return $this->idrotina;
    }

  }

  //----------------------------------------------------------------------------------------------------------------------------------------------//
  //Classe responsável pela montagem das condições de Pesquisa
  //----------------------------------------------------------------------------------------------------------------------------------------------//
  class Filter{

    public $intPage;
    public $intPageSize;
    public $intSkip;
    public $intTake;
    public $arrSort;
    public $arrFilter;
    public $blClienteProprietario;
    public $flAliasClienteProprietario;

    public $arrFlags;

    public function __construct($arrParams){
      $this->intPage = $arrParams["page"];
      $this->intPageSize = $arrParams["pageSize"];
      $this->intSkip = $arrParams["skip"];
      $this->intTake = $arrParams["take"];
      $this->arrFilter = $this->getDataFilter($arrParams);
      $this->arrSort = $arrParams["sort"];
      $this->arrFlags = array();
      $this->blClienteProprietario = false;
      $this->flAliasClienteProprietario = null;
    }

    private function getDataFilter($arrParams) {
      return ( is_null($arrParams["filter"]) ) ? Array('filters' => $arrParams["filters"]) : $arrParams["filter"];
    }

    public function SetFlags($strFlag,$arrStatus){

      $arrTmpFlags = array();
      $arrTmpFlags[$strFlag] = $arrStatus;

      array_push($this->arrFlags, $arrTmpFlags);

    }


    /**
    *Método Set para carga do Objeto
    **/
    public function Set($prpFilter, $valFilter){
      return $this->$prpFilter = $valFilter;
    }

     /*
    Faz a subistituição do filtro setado no setFlag
    */
    private function handlerFilterDataTypeEq($arrField) {
      /*
      Percorre os itens da flag e verifica se existe o filtro
      caso existir retorna o valor correto, caso nao encontre,
      retorna o valor padrao passado por parametro.
      */
      foreach($this->arrFlags as $arrFlag) {
        if( isset($arrFlag[$arrField['field']]) ) {
          $arrCurrentFilter = $arrFlag[$arrField['field']];

          //return $arrCurrentFilter[utf8_decode($arrField["value"])];
          $strValue = $arrCurrentFilter[utf8_decode($arrField["value"])];

          if(is_null($strValue)) {
            $strValue = $arrField["value"];
          }

          return $strValue;
        }
      }

      return utf8_decode($arrField["value"]);
    }

    /*
    Function responsavel por setar o alias no campo antes
    de ser jogado na condição do sql... Ex.: ac.idassociacao
    */
    private function hendlerFilterAlias($arrField) {
      if( isset($arrField['alias']) ) {
        return $arrField['alias'] . '.' . $arrField["field"];
      }

      return $arrField["field"];
    }

    /*
    Função para montar filtro de idclienteproprietario
    Parâmetros: $dsAlias -> Alias da tabela atual
    */
    private function filterIdClienteProprietario(){

      require_once('../../../sistema/model/mdlTbEmpresa.php');
      require_once('../../../helpdesk/model/mdlTbContrato.php');
      require_once('../../../helpdesk/model/mdlTbModuloContrato.php');
      require_once('../../../sistema/model/mdlTbCliente.php');

      //Se for informado alias, trata condição
      if(!is_null($this->flAliasClienteProprietario)){
        $dsAlias .= $this->flAliasClienteProprietario.".";
      }

      //Verificando se o cliente da sessão é empresa
      $objTbEmpresa = TbEmpresa::LoadByIdEmpresa(1);
      $arrClienteProprietario = explode(";",$objTbEmpresa->Get('idclienteproprietario'));

      $objTbClienteTemp = TbCliente::LoadByIdCliente($_SESSION['idCliente']);

      //Se o cliente da sessão for empresa
      if($objTbClienteTemp->Get("idpessoa") == $objTbEmpresa->Get("idpessoa")){

        //Carregando os clientes da empresa da sessão que possuem o módulo contratado e que
        //possuem contrato de assessoria ou assessoria/locação de sistemas
        $strCondicao = " AND cl.idempresa = 1
                         AND ct.flsituacao = 'A'
                         AND ct.cdanoexercicio = ".$_SESSION['cdAnoExercicio']."
                         AND mc.idmodulo = 40
                         AND (ct.fltipocontrato = 'A' OR ct.fltipocontrato = 'E')";
        $aroTbModuloContrato = TbModuloContrato::ListModuloContratoClienteByCondicao($strCondicao, "");

        //Monta a string com o id dos clientes da empresa
        $dsClienteEmpresa = '';
        if(is_array($aroTbModuloContrato)){
          foreach($aroTbModuloContrato as $objTbModuloContrato){
            $dsClienteEmpresa .= $objTbModuloContrato->getObjTbContrato()->GetObjTbCliente()->Get('idcliente').",";
          }
        }

        $dsClienteEmpresa = substr($dsClienteEmpresa, 0, -1);

        //Carrega o cliente da propria empresa e outros clientes (cadastro de empresa)
        $idClientePropriedade = str_replace(";",",",$objTbEmpresa->Get('idclienteproprietario'));

        $strFiltro .= " AND ((".$dsAlias."idclienteproprietario IN(".$dsClienteEmpresa.",".$idClientePropriedade.")) OR (".$dsAlias."idclienteproprietario is null))";
      }
      else{

        //Buscando informações de Contrato para verificar o Tipo de Contrato ([A]Assessoria, [E]Assessoria/Locação de Sistemas, [L]Locação de Sistemas )
        $strCondicao = " AND ct.idcliente = ".$_SESSION['idCliente']."
                         AND ct.flsituacao = 'A'
                         AND ct.cdanoexercicio = ".$_SESSION['cdAnoExercicio']."
                         AND mc.idmodulo = 40";
        $aroTbModuloContrato = TbModuloContrato::ListModuloContratoClienteByCondicao($strCondicao, "");

        //Buscando o Tipo de Contrato
        $flTipoContrato = "";
        if(is_array($aroTbModuloContrato)){
          $flTipoContrato = $aroTbModuloContrato[0]->getObjTbContrato()->Get('fltipocontrato');
        }

        //Carregando os clientes que compartilham informação com a empresa da sessão
        $objTbEmpresa = TbEmpresa::LoadByIdEmpresa(1);

        //Carrega o cliente da propria empresa (cadastro de empresa)
        $idClienteEmpresa = $_SESSION['idEmpresa'] == 1 ? 8 : 113;
        $strFiltro .= " AND ((".$dsAlias."idclienteproprietario IN(1,".$_SESSION['idCliente'].")) OR (".$dsAlias."idclienteproprietario is null))";
      }

      return $strFiltro;
    }

    public function GetWhere() {

      //Percorrento o Array de Filtros
      $arrFilters = array();
      /*
      Trata os dados do filtro pois o filtro pode vir do filtro padrão
      ou do filtro da coluna, o filtro da coluna precisa de uma atenção
      a mais pra funcionar ...
      */
      $arrFilters = $this->treatDataFilter($this->arrFilter["filters"]);

      //Filtro padrão
      $strFirstCondition  = $this->createFilter($arrFilters[0]);
      //Filtro pela coluna da consulta
      $strSecondCondition = $this->createFilter($arrFilters[1]);

      //Filtrado pela coluna mas possui filtro padrão, usa os 2 filtros ...
      //Cai aqui somente quando o ultimo filtro a ser feito seja o da coluna.
      if( !empty($strFirstCondition) && !empty($strSecondCondition) ) {
        $strResult  = $strFirstCondition;
        $strResult  .= $strSecondCondition; 
        $strResult .= str_replace('AND', ' OR ', $strSecondCondition);

        //Carregando o tipo de operador
        $strCond = substr($strResult, 1, 4);
        //Caso seja uma condição AND
        if(trim($strCond) == 'AND') {
          //Inserindo o parenteses após o primeiro AND
          $strResult = substr_replace($strResult, ' ( ', 5, 0);
        }
        //Fechando parenteses do filtro (operator) 'AND'
        $strResult .= ' ) ';
      }
      //Somente padrão
      else if( !empty($strFirstCondition) && empty($strSecondCondition) ) {
        $strResult = $strFirstCondition;
      }
      //Somente coluna da consulta
      else if( empty($strFirstCondition) && !empty($strSecondCondition) ) {
        $strResult .= ' AND ' . str_replace('AND', 'OR', substr($strSecondCondition, 4, strlen($strSecondCondition) ));
      }

      //Se usar idclienteproprietario, é implementado o filtro abaixo
      if($this->blClienteProprietario){
        $strResult .= $this->filterIdClienteProprietario();
      }

      return $strResult;
    }

    /*
    Verifica se usa filtro composto e retorna a condição necessaria
    */
    private function getOperator() {
      return ' AND ';
    }

    private function treatDataFilter($dataFilter) {
      $arrReturn = [];
      $aroFieldColumn = [];

      $dataFilter = ( isset($dataFilter) ? $dataFilter : [] );

      /*
      Esse valor vem do filtro das colunas [ *** ]
      */
      if( isset($dataFilter[0]['filter']) ) {
        $dataFilter = $dataFilter[0]['filter'];

        for( $i = 0; $i < sizeof($dataFilter); $i++ ) {
          if( isset($dataFilter[$i]['comeTo']) ) {
            //Pega o indice e adiciona no array das colunas
            array_push($aroFieldColumn, $dataFilter[$i]);
          }
        }

        /*
        Remove os indices que foram copiados no for anterior
        */
        for( $i = 0; $i < sizeof($aroFieldColumn); $i++ ) {
          unset($dataFilter[$i]);
        }
      }

      array_push( $arrReturn, $dataFilter );
      array_push( $arrReturn, $aroFieldColumn );

      return $arrReturn;
    }

    /*
    Percorre o array passado por parametro e retorna a condicao sql
    */
    private function createFilter($arrFilters) {

      $strCondition = "";
      $fmt = new Format();

      foreach($arrFilters as $key => $arrFilterField) {

        if ( $this->blValueFilterIsValid($arrFilterField) ) {

          if ($this->GetFieldType($arrFilterField) == 'string') {
            switch($arrFilterField["operator"]){
              case 'contains':
                $strCondition .= $this->getOperator() . " UPPER(shglobal.CLEAR(".$this->hendlerFilterAlias($arrFilterField).")) LIKE UPPER(shglobal.CLEAR('%".$fmt->escSqlQuotes($this->handlerFilterDataTypeEq($arrFilterField))."%')) ";
                break;
              case 'startswith':
                $strCondition .= $this->getOperator() . " UPPER(shglobal.CLEAR(".$this->hendlerFilterAlias($arrFilterField).")) LIKE UPPER(shglobal.CLEAR('".$fmt->escSqlQuotes($this->handlerFilterDataTypeEq($arrFilterField))."%')) ";
                break;
              case 'endswith':
                $strCondition .= $this->getOperator() . " UPPER(shglobal.CLEAR(".$this->hendlerFilterAlias($arrFilterField).")) LIKE UPPER(shglobal.CLEAR('%".$fmt->escSqlQuotes($this->handlerFilterDataTypeEq($arrFilterField))."')) ";
                break;
              case 'doesnotcontain':
                $strCondition .= $this->getOperator() . " UPPER(shglobal.CLEAR(".$this->hendlerFilterAlias($arrFilterField).")) NOT LIKE UPPER(shglobal.CLEAR('%".$fmt->escSqlQuotes($this->handlerFilterDataTypeEq($arrFilterField))."%')) ";
                break;
              case 'neq':
                $strCondition .= $this->getOperator() . " UPPER(shglobal.CLEAR(".$this->hendlerFilterAlias($arrFilterField).")) <> UPPER(shglobal.CLEAR('".$fmt->escSqlQuotes($this->handlerFilterDataTypeEq($arrFilterField))."')) ";
                break;
              case 'eq':
                $strCondition .= $this->getOperator() . " UPPER(shglobal.CLEAR(".$this->hendlerFilterAlias($arrFilterField).")) = UPPER(shglobal.CLEAR('".$fmt->escSqlQuotes($this->handlerFilterDataTypeEq($arrFilterField))."')) ";
                break;
              case 'in':
                $strValuesOperatorIn = '';
                foreach(explode(',', $arrFilterField["value"]) as $iKey => $strValue) {
                  $strValuesOperatorIn .= ( $iKey == 0 ) ? "'".utf8_decode($fmt->escSqlQuotes($strValue))."'" : ",'".utf8_decode($fmt->escSqlQuotes($strValue))."'";
                };
                // substitui as aspas ''  por '
                $strValuesOperatorIn = str_replace("''", "'", $strValuesOperatorIn);
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." IN ( ".$strValuesOperatorIn." ) ";
            }
          }
          else if ($this->GetFieldType($arrFilterField) == 'integer'){
            switch($arrFilterField["operator"]){
              case 'eq':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." = ".$arrFilterField["value"]." ";
                break;
              case 'neq':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." <> ".$arrFilterField["value"]." ";
                break;
              case 'startswith':
                $strCondition .= $this->getOperator() . " CAST(".$this->hendlerFilterAlias($arrFilterField)." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('".utf8_decode($arrFilterField["value"])."%')) ";
                break;
              case 'endswith':
                $strCondition .= $this->getOperator() . " CAST(".$this->hendlerFilterAlias($arrFilterField)." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('%".utf8_decode($arrFilterField["value"])."')) ";
                break;
              case 'contains':
                $strCondition .= $this->getOperator() . " CAST(".$this->hendlerFilterAlias($arrFilterField)." AS VARCHAR) LIKE UPPER(shglobal.CLEAR('%".utf8_decode($arrFilterField["value"])."%')) ";
                break;
              case 'doesnotcontain':
                $strCondition .= $this->getOperator() . " CAST(".$this->hendlerFilterAlias($arrFilterField)." AS VARCHAR) NOT LIKE UPPER(shglobal.CLEAR('%".utf8_decode($arrFilterField["value"])."%')) ";
                break;
              case 'gt':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." > ".utf8_encode($arrFilterField["value"])." ";
                break;
              case 'lt':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." < ".utf8_encode($arrFilterField["value"])." ";
                break;
              case 'gte':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." >= ".utf8_encode($arrFilterField["value"])." ";
                break;
              case 'lte':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." <= ".utf8_encode($arrFilterField["value"])." ";
                break;
              case 'in':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." IN ( ".utf8_decode($arrFilterField["value"])." ) ";
              case 'isn':
                $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField)." IS NOT NULL ";
            }

          }
          else if ($this->GetFieldType($arrFilterField) == 'date') {

            // Pega o operador
            $strOperator = $this->getOperatorDateHour($arrFilterField);

            // Pega o valor do campo
            $strDateValue = $fmt->DataBd( utf8_decode( $fmt->data( ($arrFilterField["value"] == 'not') ? '' : $arrFilterField["value"] )) );
            // Monta e retorna o filtro
            $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField) . $strOperator . $strDateValue;

          }
          else if ( in_array($this->GetFieldType($arrFilterField), ['hour', 'time']) ) {

            // Pega o operador
            $strOperator = $this->getOperatorDateHour($arrFilterField);
            // Pega o valor do campo
            $strDateValue = utf8_encode($arrFilterField["value"]);
            // Monta e retorna o filtro
            $strCondition .= $this->getOperator() . $this->hendlerFilterAlias($arrFilterField) . $strOperator . "CAST ('" . $strDateValue . "' AS TIME)";

          }
          else if($this->GetFieldType($arrFilterField) == 'numeric') {

            switch($arrFilterField["operator"]) {
              case 'eq':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." = ".$fmt->valor_bd($arrFilterField["value"]);
                break;
              case 'neq':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." <> ".$fmt->valor_bd($arrFilterField["value"]);
                break;
              case 'gt':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." > ".$fmt->valor_bd($arrFilterField["value"]);
                break;
              case 'lt':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." < ".$fmt->valor_bd($arrFilterField["value"]);
                break;
              case 'gte':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." >= ".$fmt->valor_bd($arrFilterField["value"]);
                break;
              case 'lte':
                $strCondition .= $this->getOperator() . ( $this->hendlerFilterAlias($arrFilterField) )." <= ".$fmt->valor_bd($arrFilterField["value"]);
                break;
            }

          }
        }
      }

      //Caso a condição não esteja vazia
      if($strCondition != '') {

        //Carregando o tipo de operador
        $strCond = substr($strCondition, 1, 4);

        //Caso seja uma condição AND
        if(trim($strCond) == 'AND') {

          //Inserindo o parenteses após o primeiro AND
          $strCondition = substr_replace($strCondition, ' ( ', 5, 0);
        }

        //Fechando parenteses do filtro (operator) 'AND'
        $strCondition .= ' ) ';
      }

      return $strCondition;
    }

    /*
    Retorna o operador de acordo com o campo para ser usado na data ou hora
    */
    private function getOperatorDateHour($arrFilterField) {

      $strOperator = $arrFilterField["operator"];
      $strValue    = $arrFilterField["value"];

      switch($strValue) {
        case '':
          $strEq = ' is ';
        break;
        case 'not':
          $strEq = ' is not ';
        break;
        default:
          $strEq = ' = ';
      }

      $arrOperator = [
        'eq'  => $strEq,
        'neq' => ' <> ',
        'gt'  => ' > ',
        'lt'  => ' < ',
        'gte' => ' >= ',
        'lte' => ' <= ',
        'isn' => ' IS NOT ' 
      ];

      return $arrOperator[ $strOperator ];
    }

    public function GetOrderBy(){

      $arrSort = $this->arrSort;
      $strOrderBy = "";

      if ($arrSort <> null){

        //Percorrento o Array de Ordenação
        $i = 0;
        foreach($arrSort as $arrSortFields){

          if ($i >= 1)
            $strOrderBy .= ", ";

          if ($this->GetFieldType($arrSortFields) == 'string')
            $strOrderBy .= " shglobal.clear(".$arrSortFields["field"].") ".$arrSortFields["dir"];
          else
            $strOrderBy .= " ".$arrSortFields["field"]." ".$arrSortFields["dir"];

          $i++;

        }

      }
      else {
        $strOrderBy .= " 1 ";
      }

      //Montando o Limit
      if ($this->intPageSize != null)
        $strOrderBy .= " LIMIT ".$this->intPageSize." ";
      else
        $strOrderBy .= " LIMIT ALL ";

      //Montando o Offset
      $strOrderBy .= " OFFSET ".(($this->intPage*$this->intPageSize)-$this->intPageSize)." ";

      return $strOrderBy;

    }

    // Retorna o tipo do filtro
    private function GetFieldType($strField){
      return $strField['type'];
    }

    // Valida se o valor do filtro é valido
    // para os casos de data e hora ...
    private function blValueFilterIsValid($arrFilterField) {

      $arrInvalidValueFilter = [];
      $arrTypesValid = ['date', 'time', 'hour'];

      // Se não for uma data ou hora, nao vamos permitir valores nulos, quando se usa data, o valor '' vai virar 'null'
      // se cair aqui não é data nem hora ...
      if( ! in_array($arrFilterField['type'], $arrTypesValid) ) {
        $arrInvalidValueFilter[] = '';
      }
      else {
        // Corrige o erro de quando o valor padrão for um desses, o sistema tenta aplicar um filtro, ocasionando o erro
        $arrInvalidValueFilter[] = $this->treatDataValueDate($arrFilterField['value']);
      }

      // Pega o valor atual do filtro
      $strValueFilter = trim($arrFilterField['value']);

      // Se o valor atual do filtro nao estiver contido dentro do array, permite fazer o filtro
      if( ! in_array($strValueFilter, $arrInvalidValueFilter) ) {
        return true;
      }
      else {
        return false;
      }

    }

    // Método responsãvel por validar se vai ou não fazer o filtro, pois pode ocasionar erro se o filtro
    // não for passado corretamente. Ex.: 04/02/____ -> Erro; 04/02/2021 -> Funciona.
    // O mesmo funciona para o tipo de hora.
    private function treatDataValueDate($strData) {
      // Valida se o valor passado é invalido, se sim, retorna ele mesmo para não fazer o filtro
      if(strlen(strstr($strData, '_')) > 0) {
        return $strData;
      }
      else {
        // Retorna essa mascara para passar na validação e fazer o filtro.
        return '__/__/____';
      }
    }

  }

  //----------------------------------------------------------------------------------------------------------------------------------------------//
  // Classe responsável por manipular os arquivos em ambientes na nuvem
  //----------------------------------------------------------------------------------------------------------------------------------------------//
  class ManipulaArquivoCloud {
    private $strBucket = '';

    /**
      * Método que identifica o ambiente e retorna o bucket correto
      * Retorna uma String
      **/
    private function getBucket(){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Identifica o ambiente
        switch($_SESSION['flAmbiente']){

          //Bucket de Desenvolvimento
          case 'L':    
            $this->strBucket = 'bkt-sisgov-des';
          break;
          //Bucket de Homologação
          case 'H':
            $this->strBucket = 'bkt-sisgov-hom';
          break;  
          //Bucket de Produção
          case 'W':
            $this->strBucket = 'bkt-sisgov-pro';
          break;                  
        }
      }

      return $this->strBucket;
    }
    
    /**
      * Método que verifica se o cliente efetuou a migração dos arquivos para o ambiente cloud
      * Retorna um Inteiro
      **/                                                                  
    public static function verificaClienteCloud(){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Dependências
        require_once('../../../global/model/mdlTbMigracaoArquivoCliente.php');

        $strClienteMigracao = TbMigracaoArquivoCliente::ListByCondicao("AND nmdatabase = '".$_SESSION['dsDatabase']."' AND flstatusmigracao = 'S' ", '');  

        if (is_array($strClienteMigracao)) {
          return 1;
        }else{
          return 0;
        }

      }
      else{
        return 1;
      }
      
    }
    
    /**
      * Método para instanciar a Lib da Amazon e efetuar o Upload 
      **/
    public function uploadCloud($objTbarquivoCliente){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Dependências
        require_once('libUploadAws.php');
        require_once('../../../sistema/model/mdlTbLogRegistroCliente.php');
        require_once("../../../sistema/model/mdlTbNotificacaoSistema.php");

        $intIdUsuario = isset($_SESSION['idUsuario']) ? $_SESSION['idUsuario'] : 1;
        $strDir = '../../../files/temp/objeto/';
        $strDirOrigem = '../../../files/temp/';
        
        $intIdArquivoCliente = $objTbarquivoCliente->Get("idarquivocliente");
        $nmArquivo = $objTbarquivoCliente->Get("nmarquivo");
        $dsEnderecoCloud = $objTbarquivoCliente->Get("dsenderecocloud");

        //Pegando a Extensão do Arquivo
        $strExt = strtolower(pathinfo($nmArquivo, PATHINFO_EXTENSION));

        //Verifica se a extensão não é vazia
        if($strExt == ""){
              
          if($objTbarquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbarquivoCliente->Get("dsformato");
          }
          
          $nmArquivo = $nmArquivo.".".$strExt;
        }

        $strFileDir = $strDir.$nmArquivo;
        $strFileDirOrigem = $strDirOrigem.$nmArquivo;

        //Verifica se o arquivo foi gerado como base64 por alguma rotina
        $arquivoCliente = $objTbarquivoCliente->GetHxArquivo();

        if(!is_null($arquivoCliente) && $arquivoCliente != "" ){
          
          //Joga o arquivo para a pasta temp
          file_put_contents($strFileDirOrigem, base64_decode($arquivoCliente));
        }

        if(file_exists($strFileDirOrigem)){

          //Copia o arquivo para a pasta temp/objeto
          copy($strFileDirOrigem, $strDir . pathinfo($strFileDirOrigem, PATHINFO_BASENAME));

        }
        
        //Identifica o ambiente
        $strBucket = $this->getBucket();
        
        //Carrega o arquivo no formato ZIP
        $zip = new ZipArchive;

        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip",$nmArquivo);
        
        $strFileZipDir = $strDir.$nmArquivoZip;
        
        //Limpa caso já tenha um zip, por questões de permissão
        if(file_exists($strFileZipDir)){
          unlink($strFileZipDir);  
        }

        if ($zip->open($strFileZipDir, ZipArchive::CREATE) === TRUE){

          //Adicionar o arquivo no arquivo ZIP
          $zip->addFile($strFileDir, $nmArquivo);

          //Fechando o Arquivo ZIP  
          $zip->close();

          //Monta a chave única
          $strDbCliente = $_SESSION['dsDatabase'];
          $strChaveUnica = $strDbCliente.'/'.$dsEnderecoCloud.'/'.$nmArquivoZip;

          //Instancia a classe e seta as propriedades
          $objAws = UploadAws::getInstance();
          $objAws->setBucket($strBucket);
          $objAws->setCaminhoLocal($strFileZipDir);
          $objAws->setChaveUnica($strChaveUnica);
          $objAws->setMetadata('idbanco', $intIdArquivoCliente);
                  
          //Pega a URI da Amazon ao fazer o upload
          $uriOci = $objAws->insereArquivoAws();  
          
          //Retornando timezone padrão
          date_default_timezone_set('America/Sao_Paulo');
                  
          if ($uriOci != ''){
            
            if(file_exists(trim($strFileDir))){
              if(!unlink(trim($strFileDir))){
                $error = error_get_last();
                $dsMsgNotif = "Cliente ID ".$_SESSION["idCliente"]." / Arquivo no SISGOV ".$nmArquivo." -> ".$error["message"];
                TbNotificacaoSistema::SendNotificacaoAutoJob(75, 'Erro em Exclusão de Arquivo!', $dsMsgNotif);
              }
            }          
            if(file_exists(trim($strFileZipDir))){
              if(!unlink(trim($strFileZipDir))){
                $error = error_get_last();
                $dsMsgNotif = "Cliente ID ".$_SESSION["idCliente"]." / Arquivo no SISGOV ".$nmArquivo." -> ".$error["message"];
                TbNotificacaoSistema::SendNotificacaoAutoJob(75, 'Erro em Exclusão de Arquivo!', $dsMsgNotif);
              }
            }          
            if(file_exists(trim($strFileDirOrigem))){
              if(!unlink(trim($strFileDirOrigem))){
                $error = error_get_last();
                $dsMsgNotif = "Cliente ID ".$_SESSION["idCliente"]." / Arquivo no SISGOV ".$nmArquivo." -> ".$error["message"];
                TbNotificacaoSistema::SendNotificacaoAutoJob(75, 'Erro em Exclusão de Arquivo!', $dsMsgNotif);
              }
            }
            
            return 1;
          }
          else{

            return 0;
          }
          
        }
        else{
          //Remove o arquivo do diretório de origem do upload
          unlink($strFileDirOrigem);

          return 0;
        }

      }
      else{

        //Dependências
        require_once('../../../sistema/model/mdlTbLogRegistroCliente.php');
        
        //Buscando usuário para log
        $intIdUsuario = isset($_SESSION['idUsuario']) ? $_SESSION['idUsuario'] : 1;
        
        //Pasta temporária onde o arquivo foi colocado inicialmente
        $strDirOrigem = '../../../files/temp/';
        
        //Carregando informações do arquivo
        $intIdArquivoCliente = $objTbarquivoCliente->Get("idarquivocliente");
        $nmArquivo = $objTbarquivoCliente->Get("nmarquivo");
        $dsEnderecoCloud = $objTbarquivoCliente->Get("dsenderecocloud");

        //Pegando a Extensão do Arquivo
        $strExt = strtolower(pathinfo($nmArquivo, PATHINFO_EXTENSION));

        //Verifica se a extensão não é vazia
        if($strExt == ""){
              
          if($objTbarquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbarquivoCliente->Get("dsformato");
          }
          
          $nmArquivo = $nmArquivo.".".$strExt;
        }
        
        //Montando string com pasta/nomedoarquivo.extensao
        $strFileDirOrigem = $strDirOrigem.$nmArquivo;

        //Verifica se o arquivo foi gerado como base64 por alguma rotina
        $arquivoCliente = $objTbarquivoCliente->GetHxArquivo();

        if(!is_null($arquivoCliente) && $arquivoCliente != "" ){
          //Joga o arquivo para a pasta temp
          file_put_contents($strFileDirOrigem, base64_decode($arquivoCliente));
        }
        
        //Carrega o arquivo no formato ZIP
        $zip = new ZipArchive;
        
        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip",$nmArquivo);
        
        //O arquivo zip será criado na pasta files/temp e após finalizar o zip, será copiado para a pasta final
        $strFileZipDir = $strDirOrigem.$nmArquivoZip;
        
        //Limpa caso já tenha um zip, por questões de permissão
        if(file_exists($strFileZipDir)){
          unlink($strFileZipDir);  
        }
        
        if ($zip->open($strFileZipDir, ZipArchive::CREATE) === TRUE){
          
          //Adicionar o arquivo no arquivo ZIP
          $zip->addFile($strFileDirOrigem, $nmArquivo);
          
          //Fechando o Arquivo ZIP
          $zip->close();
          
          //Monta o nome da pasta em que deve ser jogado o arquivo
          $strDirDestino = '../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$dsEnderecoCloud.'/';
          
          //Validar se existe o diretório de arquivos do cliente
          if(!is_dir('../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/')) {
            mkdir('../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/');
          }
          
          //Validar se existe o diretório para o ano/mês indicados do $dsEnderecoCloud
          $arrEnderecoCloud = explode("/",$dsEnderecoCloud);
          
          $cdAnoEnd = $arrEnderecoCloud[0];
          $cdMesEnd = $arrEnderecoCloud[1];
          
          if(!is_dir('../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$cdAnoEnd.'/')) {
            mkdir('../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$cdAnoEnd.'/');
          }
          
          if(!is_dir('../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$cdAnoEnd.'/'.$cdMesEnd.'/')) {
            mkdir('../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$cdAnoEnd.'/'.$cdMesEnd.'/');
          }
          
          if(file_exists($strFileZipDir)){
            
            //Copia o arquivo para a pasta de arquivos do cliente
            copy($strFileZipDir, $strDirDestino . pathinfo($strFileZipDir, PATHINFO_BASENAME));
            
            unlink(trim($strFileZipDir));
            unlink(trim($strFileDirOrigem));
            
            return 1;
            
          }
          else{
            return 0;
          }
          
        }
        else{
          //Remove o arquivo do diretório de origem do upload
          unlink($strFileDirOrigem);

          return 0;
        }

      }
      
    }
    
    /**
      * Método para instanciar a Lib da Amazon e efetuar o Upload para Substituir um arquivo no bucket
      **/
    public function uploadSubstituiCloud($objTbarquivoCliente,$nmArquivoNew){
      
      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Dependências
        require_once('libUploadAws.php');
        require_once('../../../sistema/model/mdlTbLogRegistroCliente.php');
        
        $strDir = '../../../files/temp/objeto/';
        $strDirOrigem = '../../../files/temp/';
        
        $intIdArquivoCliente = $objTbarquivoCliente->Get("idarquivocliente");
        $nmArquivo = $objTbarquivoCliente->Get("nmarquivo");
        $dsEnderecoCloud = $objTbarquivoCliente->Get("dsenderecocloud");
        
        //Pegando a Extensão do Arquivo
        $strExt = strtolower(pathinfo($nmArquivo, PATHINFO_EXTENSION));
        
        //Verifica se a extensão não é vazia
        if($strExt == ""){
          
          if($objTbarquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbarquivoCliente->Get("dsformato");
          }
          
          $nmArquivo = $nmArquivo.".".$strExt;
        }
        
        $strFileDir = $strDir.$nmArquivo;
        $strFileDirOrigem = $strDirOrigem.$nmArquivoNew;
        
        //Verifica se o arquivo foi gerado como base64 por alguma rotina
        $arquivoCliente = $objTbarquivoCliente->GetHxArquivo();
        
        if(!is_null($arquivoCliente) && $arquivoCliente != "" ){
          //Joga o arquivo para a pasta temp
          file_put_contents($strFileDirOrigem, base64_decode($arquivoCliente));
        }
        
        if(file_exists($strFileDirOrigem)){
          //Copia o arquivo enviado para a pasta temp/objeto/ renomeando com o nome do arquivo que está no banco de dados
          copy($strFileDirOrigem, $strDir.$nmArquivo);
        }
        
        //Identifica o ambiente
        $strBucket = $this->getBucket();
        
        //Carrega o arquivo no formato ZIP
        $zip = new ZipArchive;
        
        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip",$nmArquivo);
        
        $strFileZipDir = $strDir.$nmArquivoZip;
        
        //Limpa caso já tenha um zip, por questões de permissão
        if(file_exists($strFileZipDir)){
          unlink($strFileZipDir);
        }
        
        if ($zip->open($strFileZipDir, ZipArchive::CREATE) === TRUE){

          //Adicionar o arquivo no arquivo ZIP
          $zip->addFile($strFileDir, $nmArquivo);

          //Fechando o Arquivo ZIP
          $zip->close();

          //Monta a chave única
          $strDbCliente = $_SESSION['dsDatabase'];
          $strChaveUnica = $strDbCliente.'/'.$dsEnderecoCloud.'/'.$nmArquivoZip;

          //Instancia a classe e seta as propriedades
          $objAws = UploadAws::getInstance();
          $objAws->setBucket($strBucket);
          $objAws->setCaminhoLocal($strFileZipDir);
          $objAws->setChaveUnica($strChaveUnica);
          $objAws->setMetadata('idbanco', $intIdArquivoCliente);
          
          //Pega a URI da Amazon ao fazer o upload
          $uriOci = $objAws->insereArquivoAws();   
            
          if ($uriOci != ''){

            unlink(trim($strFileDir));
            unlink(trim($strFileZipDir));
            unlink(trim($strFileDirOrigem));

            return 1;
          }
          else{

            return 0;
          }
          
        }
        else{
          //Remove o arquivo do diretório de origem do upload
          unlink($strFileDirOrigem);

          return 0;
        }  

      }
      
    }
    
    /**
      * Método para Deleção de objetos na nuvem
      **/
    public function deletaCloud($objTbArquivoCliente){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Dependências
        require_once('libUploadAws.php');
        
        //Identifica o ambiente
        $strBucket = $this->getBucket();

        $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");

        //Pegando a Extensão do Arquivo
        $strExt = strtolower(pathinfo($nmArquivo, PATHINFO_EXTENSION));

        //Verifica se a extensão não é vazia
        if($strExt == ""){
                
          if($objTbArquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbArquivoCliente->Get("dsformato");
          }  
            $nmArquivo = $nmArquivo.".".$strExt;
        }else{
          $nmArquivo = $objTbArquivoCliente->Get("nmarquivo"); 
        }

        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($objTbArquivoCliente->Get("dsformato"),"zip",$nmArquivo);
        
        //Monta a chave única
        $strChaveUnica = $_SESSION['dsDatabase'].'/'.$objTbArquivoCliente->Get("dsenderecocloud").'/'.$nmArquivoZip;

        $objAws = UploadAws::getInstance();
        $objAws->setBucket($strBucket);
        $objAws->setChaveUnica($strChaveUnica);     
        $bolExisteOci = $objAws->removeArquivoAws(); 
        
        return $bolExisteOci;    

      }
      else{

        $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");
        
        //Pegando a Extensão do Arquivo
        $strExt = strtolower(pathinfo($nmArquivo, PATHINFO_EXTENSION));
        
        //Verifica se a extensão não é vazia
        if($strExt == ""){
          if($objTbArquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbArquivoCliente->Get("dsformato");
          }
          $nmArquivo = $nmArquivo.".".$strExt;
        }
        else{
          $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");
        }
        
        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $dsEnderecoCloud = $objTbarquivoCliente->Get("dsenderecocloud");
        $nmArquivoZip = str_replace($objTbArquivoCliente->Get("dsformato"),"zip",$nmArquivo);
        
        //Local do arquivo
        $strFileZipDir = '../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$dsEnderecoCloud.'/'.$nmArquivoZip;
        
        if(unlink(trim($strFileZipDir))){
          $intRetorno = 1;
        }
        else{
          $intRetorno = 0;
        }
        
        return $intRetorno;

      }

    }

    /**
      * Método para Deleção de objetos na nuvem que estão pendentes no banco de dados
      **/
    public function deletaPendenteCloud($strBucketParam = null, $objArquivoCliente){
      
      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        $arrMsg = [];
        $arrMsg['dsMsg'] = 'ok';

        //Dependências
        require_once('libUploadAws.php');
        require_once('../../../global/model/mdlTbArquivoCliente.php');
         
        //Identifica o ambiente
        if (is_null($strBucketParam)) {  
          $strBucket = $this->getBucket();
        }else{
          $strBucket = $strBucketParam;
        }

        $nmArquivo = $objArquivoCliente->Get("nmarquivo");

        //Pegando a Extensão do Arquivo
        $strExt = strtolower(pathinfo($nmArquivo, PATHINFO_EXTENSION));

        //Verifica se a extensão não é vazia
        if($strExt == ""){
              
          if($objArquivoCliente->Get("dsformato") != ""){
            $strExt = $objArquivoCliente->Get("dsformato");
          }  
          $nmArquivo = $nmArquivo.".".$strExt;
        }else{
          $nmArquivo = $objArquivoCliente->Get("nmarquivo"); 
        }

        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($objArquivoCliente->Get("dsformato"),"zip",$nmArquivo);
            
        //Monta a chave única
        $strChaveUnica = $_SESSION['dsDatabase'].'/'.$objArquivoCliente->Get("dsenderecocloud").'/'.$nmArquivoZip;
  
        $objAws = UploadAws::getInstance();
        $objAws->setBucket($strBucket);
        $objAws->setChaveUnica($strChaveUnica);     
        $intDeleta = $objAws->removeArquivoAws();
            
        if ($intDeleta == 0) {
          $arrMsg['dsMsg'] = 'Não foi possível remover da nuvem o arquivo pendente '.$objArquivoCliente->Get("nmarquivo"). ". Favor deletar manualmente.";
        }              
        
        return $arrMsg;

      }
      
    }

    /**
      * Método para Preview e Download de objetos na nuvem
      * @param $objTbArquivoCliente -> Nome do arquivo
      * @param $strAcao -> Ação: 'preview' ou 'download'
      **/
    public function previewDownloadCloud($objTbArquivoCliente, $strAcao){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Dependências
        require_once('libUploadAws.php');
        
        $utl = new Utils();  

        $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");

        //Pegando a Extensão do Arquivo
        $strExt = pathinfo($nmArquivo, PATHINFO_EXTENSION);

        //Verifica se a extensão não é vazia
        if($strExt == ""){
              
          if($objTbArquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbArquivoCliente->Get("dsformato");
          }  
          $nmArquivo = $nmArquivo.".".$strExt;
        }else{
          $nmArquivo = $objTbArquivoCliente->Get("nmarquivo"); 
        }

        $strTempDir = '../../../files/temp/objeto/';

        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip", $nmArquivo);

        $strTempZipDir = $strTempDir.$nmArquivoZip;

        //Identifica o ambiente
        $strBucket = $this->getBucket();

        //Monta a chave única
        $strChaveUnica = $_SESSION["dsDatabase"].'/'.$objTbArquivoCliente->Get("dsenderecocloud").'/'.$nmArquivoZip;
        
        //Busca e descompacta o arquivo Zip
        $objAws = UploadAws::getInstance();
        $objAws->setBucket($strBucket);
        $objAws->setChaveUnica($strChaveUnica);
        $arquivoZip = $objAws->downloadArquivoAws();

        //Joga para o servidor 
        file_put_contents($strTempZipDir, $arquivoZip);

        $zip = new ZipArchive;

        if ($zip->open($strTempZipDir) === TRUE) {

          $zip->extractTo($strTempDir);
          $zip->close();
          unlink($strTempZipDir);

          //Pega o conteúdo do arquivo
          $strArquivo = $strTempDir.$nmArquivo;
          $strConteudoArquivo = file_get_contents($strArquivo);
            
          //Montanto o Content Type pela extensão do arquivo
          header("Content-Type: ".$utl->GetContentType($strExt));

          //Força o download se for ação de download ou se a extensão não pode ser visualizada
          if ( ($strAcao == 'download') || ($utl->CheckPreviewFormato($strExt) == false)) {
            header("Content-Disposition: attachment; filename=".$nmArquivo);
          }
          
          ob_clean();

          //Exibe o arquivo
          echo $strConteudoArquivo;  

          unlink($strArquivo);

        }else{
          $arrResult = [];
          $arrResult['flTipo'] = 'E';
          $arrResult['dsMsg'] = 'Não foi possível efetuar o download do arquivo';

          $msg = New Message();
          $msg->LoadMessage($arrResult);
        }

      }
      else{
        
        //Dependências
        $utl = new Utils();  
        
        $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");
        
        //Pegando a Extensão do Arquivo
        $strExt = pathinfo($nmArquivo, PATHINFO_EXTENSION);
        
        //Verifica se a extensão não é vazia
        if($strExt == ""){
              
          if($objTbArquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbArquivoCliente->Get("dsformato");
          }  
          $nmArquivo = $nmArquivo.".".$strExt;
        }
        else{
          $nmArquivo = $objTbArquivoCliente->Get("nmarquivo"); 
        }
        
        //Carregando o nome do arquivo ZIP (busca o nome do arquivo e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip", $nmArquivo);
        
        //Pasta onde o arquivo zip deve ser jogado
        $strTempDirDestino = '../../../files/temp/';
        
        //Pasta/nome do arquivo final
        $strTempZipFile = $strTempDirDestino.$nmArquivoZip;
        
        //Carregando endereço do arquivo dentro da pasta do cliente
        $dsEnderecoCloud = $objTbArquivoCliente->Get("dsenderecocloud");
        
        //Monta o nome da pasta em está o arquivo zip original
        $strDirOrigemZip = '../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$dsEnderecoCloud.'/'.$nmArquivoZip;
        
        //Copia o arquivo
        copy($strDirOrigemZip, $strTempDirDestino . pathinfo($strDirOrigemZip, PATHINFO_BASENAME));
        
        $zip = new ZipArchive;
        
        if ($zip->open($strTempZipFile) === TRUE) {
          
          $zip->extractTo($strTempDirDestino);
          $zip->close();
          unlink($strTempZipFile);
          
          //Pega o conteúdo do arquivo
          $strArquivo = $strTempDirDestino.$nmArquivo;
          $strConteudoArquivo = file_get_contents($strArquivo);
          
          //Montanto o Content Type pela extensão do arquivo
          header("Content-Type: ".$utl->GetContentType($strExt));
          
          //Força o download se for ação de download ou se a extensão não pode ser visualizada
          if ( ($strAcao == 'download') || ($utl->CheckPreviewFormato($strExt) == false)) {
            header("Content-Disposition: attachment; filename=".$nmArquivo);
          }
          
          ob_clean();
          
          //Exibe o arquivo
          echo $strConteudoArquivo;  
          
          unlink($strArquivo);
          
        }
        else{
          $arrResult = [];
          $arrResult['flTipo'] = 'E';
          $arrResult['dsMsg'] = 'Não foi possível efetuar o download do arquivo';

          $msg = New Message();
          $msg->LoadMessage($arrResult);
        }

      }
        
    }

    public function geraBase64($nmArquivo, $dsFormato, $dsEnderecoCloud, $idCliente = null){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Valida os dados de entrada
        if ($nmArquivo != "" && $dsEnderecoCloud != "") {
          
          //Dependências
          require_once('libUploadAws.php');
          require_once('../../../sistema/model/mdlTbCliente.php');
          
          $utl = new Utils();  

          //Pegando a Extensão do Arquivo
          $strExt = pathinfo($nmArquivo, PATHINFO_EXTENSION);

          //Verifica se a extensão não é vazia
          if($strExt == ""){
                  
            if($dsFormato != ""){
              $strExt = $dsFormato;
            }  
              $nmArquivo = $nmArquivo.".".$strExt;
          }

          $strTempDir = '../../../files/temp/objeto/';

          //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
          $nmArquivoZip = str_replace($strExt,"zip", $nmArquivo);

          $strTempZipDir = $strTempDir.$nmArquivoZip;

          //Identifica o ambiente
          $strBucket = $this->getBucket();

          //Verifica se deve buscar arquivos de Cliente da Sessão ou de Cliente especifícado no parâmetro
          if(isset($idCliente) && $idCliente != null){
            $objTbClienteTemp = TbCliente::LoadByIdCliente($idCliente);
            if(is_object($objTbClienteTemp) && $objTbClienteTemp->Get('dsdatabase') != ''){
              $dsDatabase = $objTbClienteTemp->Get('dsdatabase');  
            }  
          }
          else{
            $dsDatabase = $_SESSION['dsDatabase'];
          }
          
          //Monta a chave única
          $strChaveUnica = $dsDatabase.'/'.$dsEnderecoCloud.'/'.$nmArquivoZip;
          
          //Busca e descompacta o arquivo Zip
          $objAws = UploadAws::getInstance();
          $objAws->setBucket($strBucket);
          $objAws->setChaveUnica($strChaveUnica);
          $arquivoZip = $objAws->downloadArquivoAws();

          //Joga para o servidor 
          file_put_contents($strTempZipDir, $arquivoZip);

          $zip = new ZipArchive;

          if ($zip->open($strTempZipDir) === TRUE) {

            $zip->extractTo($strTempDir);
            $zip->close();
            unlink($strTempZipDir);

            //Pega o conteúdo do arquivo
            $strArquivo = $strTempDir.$nmArquivo;
            $strConteudoArquivo = file_get_contents($strArquivo);

            //
            unlink($strArquivo);
            
            return base64_encode($strConteudoArquivo);
          }
        }else{
          return "";
        } 

      }
      else{

        //Valida os dados de entrada
        if ($nmArquivo != "" && $dsEnderecoCloud != "") {
          
          //Dependências
          require_once('../../../sistema/model/mdlTbCliente.php');
          
          $utl = new Utils();  

          //Pegando a Extensão do Arquivo
          $strExt = pathinfo($nmArquivo, PATHINFO_EXTENSION);
          
          //Verifica se a extensão não é vazia
          if($strExt == ""){
            if($dsFormato != ""){
              $strExt = $dsFormato;
            }
            $nmArquivo = $nmArquivo.".".$strExt;
          }
          
          //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
          $nmArquivoZip = str_replace($strExt,"zip",$nmArquivo);
          
          $strTempDir = '../../../files/temp/';
          
          $strTempZipDir = $strTempDir.$nmArquivoZip;
          
          //Verifica se deve buscar arquivos de Cliente da Sessão ou de Cliente especifícado no parâmetro
          if(isset($idCliente) && $idCliente != null){
            $objTbClienteTemp = TbCliente::LoadByIdCliente($idCliente);
            if(is_object($objTbClienteTemp) && $objTbClienteTemp->Get('dsdatabase') != ''){
              $dsDatabase = $objTbClienteTemp->Get('dsdatabase');
            }
          }
          else{
            $dsDatabase = $_SESSION['dsDatabase'];
          }
          
          //Monta o nome da pasta em está o arquivo zip original
          $strDirOrigemZip = '../../../files/arquivocliente/'.$dsDatabase.'/'.$dsEnderecoCloud.'/'.$nmArquivoZip;
          
          copy($strDirOrigemZip, $strTempDir.pathinfo($strDirOrigemZip, PATHINFO_BASENAME));
          
          $zip = new ZipArchive;
          
          if ($zip->open($strTempZipDir) === TRUE) {
            
            $zip->extractTo($strTempDir);
            $zip->close();
            unlink($strTempZipDir);
            
            //Pega o conteúdo do arquivo
            $strArquivo = $strTempDir.$nmArquivo;
            $strConteudoArquivo = file_get_contents($strArquivo);
            
            //Apagando o arquivo temporário
            unlink($strArquivo);
            
            return base64_encode($strConteudoArquivo);
          }
        }
        else{
          return "";
        }

      }
      
    }

     /**
      * Método para fazer o download do arquivo para o servidor
      **/
    public function downloadArquivoServidor($objTbArquivoCliente){

      if(getenv('SISGOV_ARQ_HOST') == "BUCKET"){

        //Dependências
        require_once('libUploadAws.php');
        
        $utl = new Utils();  

        $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");

        //Pegando a Extensão do Arquivo
        $strExt = pathinfo($nmArquivo, PATHINFO_EXTENSION);

        //Verifica se a extensão não é vazia
        if($strExt == ""){
              
          if($objTbArquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbArquivoCliente->Get("dsformato");
          }  
          $nmArquivo = $nmArquivo.".".$strExt;
        }else{
          $nmArquivo = $objTbArquivoCliente->Get("nmarquivo"); 
        }

        $strTempDir = '../../../files/temp/objeto/';

        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip", $nmArquivo);

        $strTempZipDir = $strTempDir.$nmArquivoZip;

        //Identifica o ambiente
        $strBucket = $this->getBucket();

        //Monta a chave única
        $strChaveUnica = $_SESSION["dsDatabase"].'/'.$objTbArquivoCliente->Get("dsenderecocloud").'/'.$nmArquivoZip;
        
        //Busca e descompacta o arquivo Zip
        $objAws = UploadAws::getInstance();
        $objAws->setBucket($strBucket);
        $objAws->setChaveUnica($strChaveUnica);
        $arquivoZip = $objAws->downloadArquivoAws();

        //Joga para o servidor 
        $filePut = file_put_contents($strTempZipDir, $arquivoZip);

        $zip = new ZipArchive;

        //Faz a extração
        if ($zip->open($strTempZipDir) === TRUE) {

          $zip->extractTo($strTempDir);
          $zip->close();
          unlink($strTempZipDir);
        }                            

      }
      else{

        $utl = new Utils();
        
        $nmArquivo = $objTbArquivoCliente->Get("nmarquivo");
        
        //Pegando a Extensão do Arquivo
        $strExt = pathinfo($nmArquivo, PATHINFO_EXTENSION);
        
        //Verifica se a extensão não é vazia
        if($strExt == ""){
          if($objTbArquivoCliente->Get("dsformato") != ""){
            $strExt = $objTbArquivoCliente->Get("dsformato");
          }
          $nmArquivo = $nmArquivo.".".$strExt;
        }
        else{
          $nmArquivo = $objTbArquivoCliente->Get("nmarquivo"); 
        }

        //Carregando o nome do arquivo ZIP (busca o nome e troca a extensão)
        $nmArquivoZip = str_replace($strExt,"zip", $nmArquivo);
        
        $strTempDir = '../../../files/temp/';
        
        $strTempZipDir = $strTempDir.$nmArquivoZip;
        
        //Monta o nome da pasta em está o arquivo zip original
        $strDirOrigemZip = '../../../files/arquivocliente/'.$_SESSION['dsDatabase'].'/'.$objTbArquivoCliente->Get("dsenderecocloud").'/'.$nmArquivoZip;
        
        //Copia o arquivo
        copy($strDirOrigemZip, $strTempDir . pathinfo($strDirOrigemZip, PATHINFO_BASENAME));
        
        $zip = new ZipArchive;
        
        //Faz a extração
        if ($zip->open($strTempZipDir) === TRUE) {
          
          $zip->extractTo($strTempDir);
          $zip->close();
          unlink($strTempZipDir);
        }

      }

    }

  }
  //----------------------------------------------------------------------------------------------------------------------------------------------//
?>