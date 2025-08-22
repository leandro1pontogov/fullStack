<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="ISO-8859-1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <link href="img/icons/pg.favicon.png" rel="shortcut icon">

  <link href="css/pg.kendo.common.min.css" rel="stylesheet">
  <link href="css/pg.kendo.blueopal.min.css" rel="stylesheet">
  <link href="css/pg.kendo.blueopal.mobile.min.css" rel="stylesheet">
  <link href="css/pg.kendo.colors.min.css" rel="stylesheet">
  <link href="css/pg.icons.css" rel="stylesheet">
  <link href="css/pg.libFrontbox.css" rel="stylesheet">
  <link href="css/pg.multiple-select.css" rel="stylesheet">
  <link href="css/pg.loading.css" rel="stylesheet">
  <link href="css/kendo.global.min.css" rel="stylesheet">
  <link href="css/ckeditor.pontogov.css" rel="stylesheet">
  <link rel="stylesheet" href="js/prettyPhoto/css/prettyPhoto.css" type="text/css" media="screen" />
  <link href="js/rs-plugin/css/settings.css" rel="stylesheet">
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/redmond/jquery-ui.css">

  <script src="js/libJquery.js" charset="ISO-8859-1"></script>
  <script src="js/jszip.min.js" charset="ISO-8859-1"></script>
  <script src="js/kendo.all.min.js" charset="ISO-8859-1"></script>
  <script src="js/kendo.messages.pt-BR.min.js" charset="ISO-8859-1"></script>
  <script src="js/kendo.masked.date.picker.js" charset="ISO-8859-1"></script>
  <script src="js/kendo.culture.pt-BR.min.js" charset="ISO-8859-1"></script>
  <script src="js/pako_deflate.min.js" charset="ISO-8859-1"></script>
  <script src="js/libPgFilter.js" charset="ISO-8859-1"></script>

  <script src="js/fusion/fusioncharts.js"></script>
  <script src="js/fusion/themes/fusioncharts.theme.fint.js"></script>

  <script src="js/libUtils.js" charset="ISO-8859-1"></script>
  <script src="js/libFrontbox.js" charset="ISO-8859-1"></script>
  <script src="js/kendo.timezones.min.js" charset="ISO-8859-1"></script>
  <script src="js/ckeditor/ckeditor.js"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/multiple-select.js"></script>
  <script src="js/prettyPhoto/js/jquery.prettyPhoto.js" type="text/javascript"></script>
  <script src="js/rs-plugin/js/jquery.themepunch.tools.min.js"></script>
  <script src="js/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
  <script src="js/mascara.js" type="text/javascript"></script>
  <script src="js/jquery.mask.js" charset="ISO-8859-1"></script>

  <style>
    .k-prompt-container,
    .k-window-content {
      height: 100% !important;
      overflow: hidden !important;
    }

    #splConsulta #splHeader {
      background-color: #e0ecff;
      position: absolute;
    }

    #BarAcoes {
      position: relative;
      width: 100%;
      bottom: 0px;
    }

    #splConsulta #splHeader .k-bg-blue.screen-filter-content {
      max-height: 58px;
      overflow: auto;
    }

    #splConsulta #splFooter {
      height: inherit;
    }

    #splConsulta #splFooter>div:nth-child(1) {
      height: inherit;
      overflow: hidden;
    }

    #splConsulta #splFooter>div:nth-child(1) .k-tabstrip-wrapper>div {
      height: inherit;
      overflow: hidden;
    }

    #bottonConsultaAvaliacaoPrestacaoConta .k-tabstrip-wrapper {
      height: inherit;
    }

    .k-item.k-state-default {
      z-index: 0;
    }

    .k-form {
      height: 99%;
    }

    .k-form>form {
      height: 100%;
    }

    .k-form #splConsulta {
      height: inherit;
    }

    .k-form #splConsulta #splMiddle {
      overflow: hidden;
    }

    .k-header-column-menu.k-state-active {
      z-index: 0;
    }

    #splConsulta #splMiddle>div {
      height: 100% !important;
    }

    .k-splitter {
      border-width: 0px !important;
    }

    .k-i-hbar {
      margin-top: 2px !important;
      height: 2px !important;
    }
  </style>

  <script>

    function OpenWindow(blModal, nmJanela, dsUrlController, dsTitulo = '') {
      $("#DivWindowArea").append('<div id="Win' + nmJanela + '"></div>');

      $('#Win' + nmJanela).kendoWindow({
        title: dsTitulo,
        modal: blModal,
        content: dsUrlController,
        height: blModal ? "auto" : $("#DivWindowArea").height() - 26,
        width: blModal ? "800px" : "99.6%",
        visible: !blModal,
        draggable: blModal,
        resizable: false,
        closable: blModal,
        close: function () {
          $('#Win' + nmJanela).fadeOut("fast", function () {
            $('#Win' + nmJanela).data("kendoWindow").destroy();
          });
        },
      });
    }
  </script>

  <script>
    $(function () {
      kendo.culture("pt-BR")
      $("#menu").kendoMenu({
        dataSource: [
          {
            text: "Janelas",
            items: [
              {
                text: "Janela de Salas",
                select: function () {
                  OpenWindow(false, "ConsultaSala", "controller/sala/ctrSala.php?action=winConsulta", "Janela Consulta Sala")
                }
              },
              {
                text: "Janela de Colaboradores",
                select: function () {
                  OpenWindow(false, "ConsultaColaborador", "controller/colaborador/ctrColaborador.php?action=winConsulta", "Janela Consulta Colaborador")
                }
              },
              {
                text: "Janela de Reservas",
                select: function () {
                  OpenWindow(false, "ConsultaReserva", "controller/reserva/ctrReserva.php?action=winConsulta", "Janela Consulta Reserva")
                }
              }
            ]
          }
        ]
      });
    });
  </script>

</head>

<body>
  <ul id="menu"></ul>

  <div id="DivWindowArea"
    style="width: 100%; height: calc(100% - 25px); padding: 3px; background-repeat: no-repeat; background-position: bottom right; background-size: 45%; background-position-x: 99% !important; background-position-y: 98% !important;">
  </div>

  <div id="popNotificacao"></div>
  <div id="app"></div>
</body>

</html>