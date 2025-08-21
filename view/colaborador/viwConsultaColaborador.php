<?php
@header("Content-Type: text/html; charset=ISO-8859-1", true);
?>

<script>
	$(function (){
		
		var arrDataSource = [
			{
				name: "idcolaboradorsala",
				type: "integer",
				label: "Id",
				visibleFilter: 'true',
				orderFilter: '2',

				orderGrid: '1',
				widthGrid: '70',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '1',
			},
			{
				name: "nmcolaboradorsala",
				type: "string",
				label: "Nome",
				visibleFilter: 'true',
				orderFilter: '3',

				orderGrid: '2',

				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '600',
				positionPreview: '2',
			},
			{
				name: "dsemail",
				type: "string",
				label: "Email",
				visibleFilter: 'true',
				orderFilter: '4',

				orderGrid: '3',
				widthGrid: '',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '600',
				positionPreview: '3',
			},
			{
				name: "dssetor",
				type: "string",
				label: "Setor",
				visibleFilter: 'true',
				orderFilter: '6',

				orderGrid: '5',
				widthGrid: '150',
				hiddenGrid: 'false',
				headerAttributesGrid: 'text-align: center;',
				attributesGrid: 'text-align: center;',

				showPreview: 'true',
				indiceTabPreview: 'tabDadosGerais',
				widthPreview: '70',
				positionPreview: '7'
			}
		]

		//----------------------------------------------------------------------------------------------------------------//
		//Configura tela para utilizar o Splitter
		//----------------------------------------------------------------------------------------------------------------//
		arrDataSource = LoadConfigurationQuery(arrDataSource, "ConsultaColaborador");
		//----------------------------------------------------------------------------------------------------------------//

		
		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando os campos combo da pesquisa
		//----------------------------------------------------------------------------------------------------------------//
		createPgFilter(arrDataSource, "ConsultaColaborador");
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Área de botões de ações
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaColaborador #BarAcoes").kendoToolBar({
			items: [
				{
					type: "spacer"
				},
				{
					type: "buttonGroup",
					buttons: [
						{
							id: "BtnIncluir",
							spriteCssClass: "k-pg-icon k-i-l1-c1",
							text: "Incluir",
							group: "actions",
							attributes: { tabindex: "30" },
							click: function () {
								OpenWindow(true, "CadastroColaborador", "controller/colaborador/ctrColaborador.php?action=incluir", "Janela Cadastro Colaborador")
							}
						},
						{
							id: "BtnEditar",
							spriteCssClass: "k-pg-icon k-i-l1-c3",
							text: "Editar",
							group: "actions",
							enable: false,
							attributes: { tabindex: "31" },
							click: function () {

								let grid = $("#GrdConsultaColaborador").data("kendoGrid")
								let ColaboradorSelecionado = grid.dataItem(grid.select())

								OpenWindow(true, "CadastroColaborador", "controller/colaborador/ctrColaborador.php?action=editar&idColaborador=" + ColaboradorSelecionado.idcolaboradorsala, "Janela Cadastro Colaborador")
							}
						},
						{
							id: "BtnFechar",
							spriteCssClass: "k-pg-icon k-i-l1-c4",
							text: "Fechar",
							group: "actions",
							attributes: { tabindex: "32" },
							click: function () {
								$("#WinConsultaColaborador").data("kendoWindow").close();
							}
						},
					]
				}
			],
		});
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Filtro extra da consulta
		//----------------------------------------------------------------------------------------------------------------//
		function getExtraFilter(){
			let arrFilds = LoadFilterSplitter("ConsultaColaborador", arrDataSource)
			return arrFilds;
		}
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o DataSource na consulta
		//----------------------------------------------------------------------------------------------------------------//
		let DtsConsultaColaborador = new kendo.data.DataSource({
			pageSize: 100,
			serverPaging: true,
			serverFiltering: true,
			serverSorting: true,
			transport: {
				read: {
					url: "controller/colaborador/ctrColaborador.php",
					type: "GET",
					dataType: "json",
					data: function(){
						return {
							action: "ListColaborador",
							filters: getExtraFilter()
						}					
					}
				}
			},
			schema: {
				data: "jsnColaborador",
				total: "jsnTotal",
				model:{
					fields: getModelDataSource(arrDataSource)
				},
				errors: "error"
			}
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o botão de consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaColaborador #BtnPesquisar").kendoButton({
			click: function(){
				DtsConsultaColaborador.filter(getExtraFilter())
				DtsConsultaColaborador.read()
			}
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Instanciando o Grid na consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#frmConsultaColaborador #GrdConsultaColaborador").kendoGrid({
			pdf: SetPdfOptions("Listagem de Colaboradores"),
			pdfExport: function () {
				tituloPdfExport = "Listagem de Colaboradores"
			},
			dataSource: DtsConsultaColaborador,
			height: getHeightGridQuery("ConsultaColaborador"),
			columns: getColumnsQuery(arrDataSource),
			selectable: "row",
			resizable: true,
			reorderable: true,
			navigatable: true,
			columnMenu: true,
			filterable: true,
			sortable: {
				mode: "multiple",
				allowUnsort: true
			},
			sort: function () {
			},
			change: function () {
				$("#frmConsultaColaborador #BarAcoes").data("kendoToolBar").enable("#BtnEditar")
			},
			dataBound: function () {
				LoadGridExportActions("frmConsultaColaborador", "GrdConsultaColaborador", true)
			},
			pageable: {
				pageSizes: [100, 300, 500, "all"],
				numeric: false,
				input: true
			},
			columns: getColumnsQuery(arrDataSource),
			columnShow: function (e) {
				setWidthOnShowColumnGrid(e, 'ConsultaColaborador');
			},
			columnHide: function (e) {
				setWidthOnHideColumnGrid(e, 'ConsultaColaborador');
			},
			filter: function (e) {
				mountFilteredScreen('filterColumn', e, 'ConsultaColaborador', arrDataSource, DtsConsultaColaborador, getExtraFilter());
			}
		});
		$("#frmConsultaColaborador #GrdConsultaColaborador").on("dblclick", "tbody>tr", function(){
			$("#frmConsultaColaborador #BtnEditar").click();
		})
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Ação para abrir a tela de consulta
		//----------------------------------------------------------------------------------------------------------------//
		$("#WinConsultaColaborador").data("kendoWindow").open();
		//----------------------------------------------------------------------------------------------------------------//

		//----------------------------------------------------------------------------------------------------------------//
		//Cria a tela de visualização do item do grid na consulta e faz outras coisas...
		//----------------------------------------------------------------------------------------------------------------//
		createScreenPreview(arrDataSource, "ConsultaColaborador");
		//----------------------------------------------------------------------------------------------------------------//

	})
</script>

<div class="k-form">
	<form id="frmConsultaColaborador" style="height: 100%;">
		<div id="splConsulta">
			<div id="splHeader">
				<div class="k-bg-blue screen-filter-content">
					<table>
						<tr>
							<td style="width: 120px;text-align: right;vertical-align: top;padding-top: 6px;">
								Filtro(s):
							</td>

							<td>
								<div id="fltConsultaColaborador" style="width: auto;"></div>
							</td>

							<td style="vertical-align: bottom;padding-bottom: 5px;">
								<span id="BtnPesquisar" style="cursor: pointer;width: 100px;height: 24px;"
									title="Pesquisar" data-role="button" class="k-button k-button-icon" role="button"
									aria-disabled="false" tabindex="29">
									<span class="k-sprite k-pg-icon k-i-l1-c2"
										style="margin: 0 auto; text-align: center;"></span>
									<span style="margin: 0 auto; margin-right: 3px;">Pesquisar</span>
								</span>

								<span id="BtnAddFilter"
									style="cursor: pointer;width: 21px !important;height: 21px !important"
									title="Adicionar Filtro" data-role="button" class="k-button k-button-icon"
									role="button" aria-disabled="false" tabindex="">
									<span class="k-sprite k-pg-icon k-i-l1-c1"
										style="margin: 0 auto;margin-top: 1.4px;"></span>
								</span>
							</td>
						</tr>
					</table>

					<div id="BarAcoes" style="text-align: right;height: 28px;"></div>
				</div>
			</div>
			<div id="splMiddle">
				<div id="GrdConsultaColaborador" data-use-state-screen="true">

				</div>
			</div>
			<div id="splFooter">
				<div id="bottonConsultaColaborador">
					<div id="tabStripConsultaColaborador">
						<ul>
							<li id="tabDadosGerais" class="k-state-active"><label>Detalhes</label></li>
						</ul>
						<div id="tabDadosGeraisVisualizacaoConsultaColaborador"></div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>