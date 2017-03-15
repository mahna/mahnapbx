var globalIndexNaiGrid = 0;
function naiGrid( data, optionalData ){
	var elem = $(data.elem);
	if( typeof optionalData == 'undefined' ) optionalData = {};
	var width = elem.parent().width();
	
	var colModel = [];
	var colNames = [];
	
	for( i in data.cols ){
		if( typeof data.cols[i] == 'undefined' ) continue;
		colModel[i] = ( typeof data.cols[i].model == 'string' ) ? { name: data.cols[i].model } : data.cols[i].model;
		colNames[i] = data.cols[i].name;
	}
	var gridId = 'jqgrid'+(globalIndexNaiGrid==0?'':globalIndexNaiGrid);
	var grid = $('<table>').attr('id',gridId).appendTo( data.elem );
	var pager = '#'+$('<div>').attr('id','jqgridpager'+(globalIndexNaiGrid==0?'':globalIndexNaiGrid)).appendTo( data.elem ).attr('id');
	globalIndexNaiGrid++;
	console.log(data.url);
	grid.jqGrid( $.extend( {
		url: data.url,
		datatype: "xml",
		postData: {
			oper: 'grid',
			query: data.query
		},
		rowNum:0,
		colModel: colModel,
		colNames: colNames,
		width: width,
		height: "auto",
		autowidth:true,
		shrinkToFit: true,
		forceFit: true,
		caption: '',
		width: width,
		autowidth: false,
		viewrecords: true,
		sortname: ( data.sortname? data.sortname : ''),
		sortorder: ( data.sortorder? data.sortorder : 'desc'),
		pager: data.pager!==false?pager:false
	}, optionalData) );
	
	var navGridParams = {edit:false,add:false,del:false,search:false, refresh: false};
	if( data.excel ){
		grid.navGrid(pager,navGridParams).navButtonAdd(pager,{
			caption:"فایل اکسل", 
			buttonicon:'ui-icon-arrowthickstop-1-s', 
			onClickButton: function(){ 
				$.ajax({
					url: data.url,
					data: {
						oper: 'excel',
						sidx: ( data.sortname? data.sortname : ''),
						sord: ( data.sortorder? data.sortorder : 'desc'),
						colNames: colNames,
						query: data.query
					},
				}).done( function(e){
					if( e == 'overflow' )
						alert( 'محدوده جست‌وجوی خود را به حداکثر 65536 رکورد کاهش دهید.' );
					else
						window.open( "data:application/vnd.ms-excel;charset=utf-8," + encodeURIComponent(e));
				});
				
			},
			position:"last"
		});
	}
	
	
	grid.navGrid(pager,navGridParams).navButtonAdd(pager,{
		caption:"نمایش کوئری", 
		buttonicon:'ui-icon-info', 
		onClickButton: function(){ 
			var queryThis = grid.getGridParam('userData').querythis;
			var query = grid.getGridParam('userData').query;
			
			$('body').addClass('ltr').html('<span class="red">Query:</span><br>'+query+'<hr><span class="red">Query2:</span><br>'+queryThis);


		}, 
		position:"last"
	});
		
		
		
	if( grid.jqGrid().getGridParam('sortname') == '' ){
		elem.find('.ui-grid-ico-sort').addClass('hidden');
		elem.find('.ui-jqgrid-sortable').click(function(){
			elem.find('.ui-grid-ico-sort').removeClass('hidden');
		});
	}
	$('.ui-pg-div:contains("نمایش کوئری")').addClass('show_query');
	
	
	$(window).on("resize", function () {
		newWidth = elem.parent().width();
		grid.jqGrid("setGridWidth", newWidth, true);
	});

	var isOnView = function() {
		var headerHeight = 37;
		var pagerHeight = 59;
		
		rect = $('#'+gridId)[0].getBoundingClientRect();
		

		// DOMRect { x: 8, y: 8, width: 100, height: 100, top: 8, right: 108, bottom: 108, left: 8 }
		var windowHeight = (window.innerHeight || document.documentElement.clientHeight);
		var windowWidth = (window.innerWidth || document.documentElement.clientWidth);

		// http://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap
		var vertInView = (rect.top + headerHeight <= windowHeight) && ((rect.top + rect.height) >= 0);
		var horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

		var a = ((rect.top + rect.height) - $(window).height());
		return (vertInView && horInView) && (a + pagerHeight > 0 );
	}

	var fix = function () {
		if( isOnView() ){
			$(pager).css({
				bottom: 0,
				position: 'fixed'
			});
		}
		else{
			$(pager).css({
				bottom: 0,
				position: 'relative'
			});
		}
		

	}
	$(document).on("scroll", fix );
	setTimeout( fix, 500 );

	//grid.setGridWidth( width );
	return grid;
}