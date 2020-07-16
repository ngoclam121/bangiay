"use strict";
jQuery(document).ready(function(){
	// Fallback for case if library was not loaded
	if(!jQuery.fn.jqGrid) {
		return;
	}
	var tblId = 'wtbpTableTbl',
		tableObj = jQuery('#'+ tblId);
	tableObj.jqGrid({
		url: wtbpTblDataUrl
	,	datatype: 'json'
	,	autowidth: true
	,	shrinkToFit: true
	,	colNames:[toeLangWtbp('ID'), toeLangWtbp('Title'), toeLangWtbp('Shortcode')]
	,	colModel:[
			{name: 'id', index: 'id', searchoptions: {sopt: ['eq']}, width: '50', align: 'center'}
		,	{name: 'title', index: 'title', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'shortcode', index: 'shortcode', searchoptions: {sopt: ['eq']}, align: 'center'}
		]
	,	postData: {
			search: {
				text_like: jQuery('#'+ tblId+ 'SearchTxt').val()
			}
		}
	,	rowNum:10
	,	rowList:[10, 20, 30, 1000]
	,	pager: '#'+ tblId+ 'Nav'
	,	sortname: 'id'
	,	viewrecords: true
	,	sortorder: 'desc'
	,	jsonReader: { repeatitems : false, id: '0' }
	,	caption: toeLangWtbp('Current PopUp')
	,	height: '100%'
	,	emptyrecords: toeLangWtbp('You have no Tables for now.')
	,	multiselect: true
	,	onSelectRow: function(rowid, e) {
			var tblId = jQuery(this).attr('id')
			,	selectedRowIds = jQuery('#'+ tblId).jqGrid ('getGridParam', 'selarrrow')
			,	totalRows = jQuery('#'+ tblId).getGridParam('reccount')
			,	totalRowsSelected = selectedRowIds.length;
			if(totalRowsSelected) {
				jQuery('#wtbpTableRemoveGroupBtn').removeAttr('disabled');
				if(totalRowsSelected == totalRows) {
					jQuery('#cb_'+ tblId).prop('indeterminate', false);
					jQuery('#cb_'+ tblId).attr('checked', 'checked');
				} else {
					jQuery('#cb_'+ tblId).prop('indeterminate', true);
				}
			} else {
				jQuery('#wtbpTableRemoveGroupBtn').attr('disabled', 'disabled');
				jQuery('#cb_'+ tblId).prop('indeterminate', false);
				jQuery('#cb_'+ tblId).removeAttr('checked');
			}
			wtbpCheckUpdate(jQuery(this).find('tr:eq('+rowid+')').find('input[type=checkbox].cbox'));
			wtbpCheckUpdate('#cb_'+ tblId);
		}
	,	beforeRequest: function() {
			jQuery('#wtbpTableTblNav_center .ui-pg-table').addClass('woobewoo-hidden');
		}
	,	gridComplete: function(a, b, c) {
			var tblId = jQuery(this).attr('id');
			jQuery('#wtbpTableRemoveGroupBtn').attr('disabled', 'disabled');
			jQuery('#cb_'+ tblId).prop('indeterminate', false);
			jQuery('#cb_'+ tblId).removeAttr('checked');
			// Custom checkbox manipulation
			wtbpInitCustomCheckRadio('#'+ jQuery(this).attr('id') );
			wtbpCheckUpdate('#cb_'+ jQuery(this).attr('id'));
			jQuery('#wtbpTableTblNav_center .ui-pg-table').removeClass('woobewoo-hidden');
		}
	,	loadComplete: function() {
			var tblId = jQuery(this).attr('id');
			if (this.p.reccount === 0) {
				jQuery(this).hide();
				jQuery('#'+ tblId+ 'EmptyMsg').removeClass('woobewoo-hidden');
			} else {
				jQuery(this).show();
				jQuery('#'+ tblId+ 'EmptyMsg').addClass('woobewoo-hidden');
			}
		}
	});
	jQuery(window).on('load resize', tableObj, function(event) {
		tableObj.jqGrid('setGridWidth', jQuery('#containerWrapper').width());
	});
	jQuery('#'+ tblId+ 'NavShell').append( jQuery('#'+ tblId+ 'Nav') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-selbox').insertAfter( jQuery('#'+ tblId+ 'Nav').find('.ui-paging-info') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-table td:first').remove();
	// Make navigation tabs to be with our additional buttons - in one row
	jQuery('#'+ tblId+ 'Nav_center').prepend( jQuery('#'+ tblId+ 'NavBtnsShell') ).css({
		'width': '80%'
	,	'white-space': 'normal'
	,	'padding-top': '8px'
	});
	jQuery('#'+ tblId+ 'SearchTxt').keyup(function(){
		var searchVal = jQuery.trim( jQuery(this).val() );
		if( true ) {
			wtbpGridDoListSearch({
				text_like: searchVal
			}, tblId);
		}
	});

	jQuery('#'+ tblId+ 'EmptyMsg').insertAfter(jQuery('#'+ tblId+ '').parent());
	jQuery('#'+ tblId+ '').jqGrid('navGrid', '#'+ tblId+ 'Nav', {edit: false, add: false, del: false});
	jQuery('#cb_'+ tblId+ '').change(function(){
		jQuery(this).attr('checked')
			? jQuery('#wtbpTableRemoveGroupBtn').removeAttr('disabled')
			: jQuery('#wtbpTableRemoveGroupBtn').attr('disabled', 'disabled');
	});

	jQuery('#wtbpTableRemoveGroupBtn').click(function(){
		var selectedRowIds = jQuery('#wtbpTableTbl').jqGrid ('getGridParam', 'selarrrow')
		,	listIds = [];
		for(var i in selectedRowIds) {
			var rowData = jQuery('#wtbpTableTbl').jqGrid('getRowData', selectedRowIds[ i ]);
			listIds.push( rowData.id );
		}
		var popupLabel = '';
		if(listIds.length == 1) {	// In table label cell there can be some additional links
			var labelCellData = wtbpGetGridColDataById(listIds[0], 'title', 'wtbpTableTbl');
			popupLabel = jQuery(labelCellData).text();
		}
		var confirmMsg = listIds.length > 1
			? toeLangWtbp('Are you sur want to remove '+ listIds.length+ ' Tables?')
			: toeLangWtbp('Are you sure want to remove "'+ popupLabel+ '" Table?')
		if(confirm(confirmMsg)) {
			jQuery.sendFormWtbp({
				btn: this
			,	data: {mod: 'wootablepress', action: 'removeGroup', listIds: listIds}
			,	onSuccess: function(res) {
					if(!res.error) {
						jQuery('#wtbpTableTbl').trigger( 'reloadGrid' );
					}
				}
			});
		}
		return false;
	});
	wtbpInitCustomCheckRadio('#'+ tblId+ '_cb');
});
