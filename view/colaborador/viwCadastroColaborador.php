<script>

	$(function () {
    //-----------------------------------------------------------------------------------------//
    //Instanciando os campos da tela de cadastro
    //-----------------------------------------------------------------------------------------//
    $("#nrCapacidade").kendoNumericTextBox({
		  min: 0,
		  format: ""
	  })
    //-----------------------------------------------------------------------------------------//

    //-----------------------------------------------------------------------------------------//
    //Barra de ações
    //-----------------------------------------------------------------------------------------//
    $("#frmCadastroColaborador #BarAcoes").kendoToolBar({
      items: [
        {
          type: "spacer",
        },
        {
          type: "buttonGroup",
          buttons: [
            {
              id: "BtnGravar",
              spriteCssClass: "k-pg-icon k-i-l1-c5",
              text: "Gravar",
              group: "actions",
              attributes: { tabindex: "33" },
              click: function () {

                $.post(
                  "controller/colaborador/ctrColaborador.php?action=gravar",
                   $("#frmCadastroColaborador").serialize(),
                   function(response){
                    Message(response.flDisplay, response.flTipo, response.dsMsg);
                    if(response.flTipo == "S"){
                      $("#frmConsultaColaborador #BtnPesquisar").click()
                      $("#frmCadastroColaborador #BtnLimpar").click()
                    }
                   },
                   "json"
                )
              }
            },
            {
              id: "BtnExcluir",
              spriteCssClass: "k-pg-icon k-i-l1-c7",
              text: "Excluir",
              group: "actions",
              enable: false,
              attributes: { tabindex: "34" },
              click: function () {

                $.post(
                  "controller/colaborador/ctrColaborador.php?action=excluir",
                  $("#frmCadastroColaborador").serialize(),
                   function(response){
                    Message(response.flDisplay, response.flTipo, response.dsMsg);
                    if(response.flTipo == "S"){
                      $("#frmConsultaColaborador #BtnPesquisar").click()
                      $("#WinCadastroColaborador").data("kendoWindow").close()
                    }
                  },
                  "json"
                )
              }
            },
            {
              id: "BtnLimpar",
              spriteCssClass: "k-pg-icon k-i-l1-c6",
              text: "Limpar",
              group: "actions",
              attributes: { tabindex: "34" },
              click: function () {

                $("#WinCadastroColaborador").data("kendoWindow").refresh(
                  {
                    url: "controller/colaborador/ctrColaborador.php?action=incluir"
                  }
              )

              }
            },
            {
              id: "BtnFechar",
              spriteCssClass: "k-pg-icon k-i-l1-c4",
              text: "Fechar",
              group: "actions",
              attributes: { tabindex: "35" },
              click: function () {
                $("#WinCadastroColaborador").data("kendoWindow").close()
              }
            }

          ]
        }
      ]
    })
    //-----------------------------------------------------------------------------------------//

    //-----------------------------------------------------------------------------------------//
    //Ações diversas da tela de cadastro
    //-----------------------------------------------------------------------------------------//
    if($("#frmCadastroColaborador #idColaborador").val() != ""){
      $("#frmCadastroColaborador #BarAcoes").data("kendoToolBar").enable("#BtnExcluir")
    }
    $("#WinCadastroColaborador").data("kendoWindow").center().open();
    //-----------------------------------------------------------------------------------------//
	})

</script>

<div class="k-form">
  <form id="frmCadastroColaborador" style="height: 100%;">
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td style="text-align: right; width: 120px;">Id:</td>
        <td>
          <input type="text" id="idColaborador" name="idColaborador" tabindex="-1" class="k-textbox k-input-disabled" readonly="readonly" value="<?php echo $objTbColaborador->Get("idcolaboradorsala") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Nome:</td>
        <td>
          <input type="text" id="nmColaborador" name="nmColaborador" tabindex="1" class="k-textbox" style="width: 600px;" value="<?php echo $objTbColaborador->Get("nmcolaboradorsala") ?>">
        </td>
      </tr>
    </table>
    <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Email:</td>
        <td>
          <input type="text" id="dsEmail" name="dsEmail" tabindex="2" class="k-textbox" style="width: 600px;" value="<?php echo $objTbColaborador->Get("dsemail") ?>">
        </td>
      </tr>
    </table>
     <table width="100%" cellspacing="2" cellpadding="0" role="presentation">
      <tr>
        <td class="k-required" style="text-align: right; width: 120px;">Setor:</td>
        <td>
          <input type="text" id="dsSetor" name="dsSetor" tabindex="2" class="k-textbox" style="width: 600px;" value="<?php echo $objTbColaborador->Get("dssetor") ?>">
        </td>
      </tr>
    </table>

    <div id="BarAcoes"></div>

  </form>
</div>