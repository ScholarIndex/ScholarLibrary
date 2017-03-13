var bLazy = null;

var DOCUMENTS = {
	searchXhR : null,
	paginXhR : null,
	
	globalControls : function(){
		
		$(document).on('change','#pagination #page',function(e){
		 	e.preventDefault();
		 	DOCUMENTS.search();
		 });
		 
		 $(document).on('click', '#nextPage', function(){
		 	var pageNb = parseInt($('#pagination #page').val());
		 	var pageCnt = parseInt($('#pagination #pageCount').text());
		 	var next = Math.min(pageNb+1, pageCnt);
		 	$('#pagination #page').val(next);
		 	DOCUMENTS.search();
		 });
		 
		 $(document).on('click', '#prevPage', function(){
		 	var pageNb = parseInt($('#pagination #page').val());
		 	var pageCnt = parseInt($('#pagination #pageCount').text());
		 	var prev = Math.max(pageNb-1, 1);
		 	$('#pagination #page').val(prev);
		 	DOCUMENTS.search();
		 });		
		 
		 $(document).on('click', '#filters .filterSearchInput .remove', function(){
			$(this).parent().find("input").val("").focus();
			DOCUMENTS.search();
		 });
		 
 		 $(document).on('keyup', '#filters .filterSearchInput input', function(){
			DOCUMENTS.search();
		 });		
		 
		 $(document).on('click', '.openDoc', function(e){
		 	that = $(this);
		 	that.addClass('loading');
		 	if($(this).closest('.card').find('.searchIssue').length>0 && $(this).closest('.card').find('.searchIssue').dropdown('get value') == ""){
		 		$(this).closest('.card').find('.searchIssue').addClass('error');
		 		alertify.error('Please select an issue');
		 		that.removeClass('loading');
		 	}
		 	
		 	var bid = $(this).closest('.card').attr('data-bid');
		 	var issue = $(this).closest('.card').attr('data-issue');

		 	
		 	$.ajax({
		       url: 'http://dhlabsrv4.epfl.ch/iiif_lbc/'+bid+'::'+issue+'::1/info.json',
		       type: 'GET',
		       dataType: 'jsonp',
		       success: function (jqxhr, txt_status) {
		       		document.location.href = "/document/"+bid+"/"+issue;
		       },
		       error : function(){
		       		alertify.error('Page images missing on server.');
		       		that.removeClass('loading');
		       }
		    });
		 });
		
	},
	
	initFiltersMenu : function(){
		$('#filters form').submit(function(){return false;});
		$('.ui.accordion').accordion({
			exclusive: false,
		});
		
		$('.ui.checkbox').checkbox({
			onChange : function(){DOCUMENTS.search();},
		});

	},
	
	initPaginationMenu: function(){
		$('#pagination form').submit(function(){return false;});
		 $('#pagination .ui.dropdown').dropdown({
		 	onChange: function(){DOCUMENTS.search();},
		 });
		 
		 		 
	},
	

	
	
	
	// Applying filters
	search : function(reloadPagination){
		if( DOCUMENTS.searchXhR &&  DOCUMENTS.searchXhR.readystate != 4){
            DOCUMENTS.searchXhR.abort();
        }
		
		
		data = {
			filters : $("#filters form").serialize(),
			pagination : $("#pagination form").serialize(),
		};
		
		DOCUMENTS.searchXhR = $.ajax({
			url: '/documents/ajaxSearch',
			method: 'GET',
			data: data,
			beforeSend: function(){
				$('#documentsResults').html('<div class="ui loader active"></div>');
				Holder.run();
			},
			success:function(data){
				$('#documentsResults').html(data.documentsResults);
				$('#pagination').html(data.pagination);
				DOCUMENTS.initPaginationMenu();
				
				$('.ui.dropdown.searchIssue').dropdown({
					apiSettings: { url: '/documents/{bid}/issueSearch/{query}'},
					onChange : function(){
						var issue = $(this).dropdown("get value");
						var card = $(this).closest('.card');
						var cardOpenButton = card.find('.openDoc');
						cardOpenButton.attr('href', cardOpenButton.attr('href')+"/"+issue);
						card.attr('data-issue', issue);
					}
				});
			}
		});		
	},
	
	init : function(){
	
		DOCUMENTS.globalControls();
		
		DOCUMENTS.initFiltersMenu();
	 
	 	DOCUMENTS.search();		
	}
	
};

var DOC = {

	saveMeta : function(field){
		var key = field.attr('data-key');
		var val = field.html().replace(/<br\s*[\/]?>/gi, "\n");;
		var type = field.attr('data-type');
		var bid = field.closest('.issue').attr('data-bid');
		
		$.ajax({
        	url: "/document/meta/update",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": bid,
            	"key": key,
            	"type" : type,
				"val" : val,
        	},
        	success:function(data){
        		if(data.result == "success")
	            	alertify.success("Metadata saved successfully.");
        	},
        	error:function(data){
        		alertify.error("An error happened while saving metadata.");
        		
        	},
    	});		
	},
	
	
	


	globalControls : function(){
		$(document).on('keypress', '.editable', function (e) {return e.which != 13});

		$(document).on('blur', '.metadata .editable', function(e){
			DOC.saveMeta($(this));
		});
		
		$(document).on('click','.toctable .icon.add', function(){
			var newrow = $('.toctable tr.model').clone();
		 	$('.toctable tr.model').before(newrow);
		 	newrow.removeClass('model');
		 	
		 	newrow.find('.editable.sectionEndPage, .editable.sectionStartPage').droppable({
        		tolerance: 'pointer',
        		accept : '.icon.drag',
        		activeClass: 'ui-drag-accept',
        		hoverClass: 'ui-drag-hover',
        		drop : function(event,ui){
					$(this).text(ui.helper.attr('data-page'));
					DOC.saveToc();
        		} 
        	});
		});
	},
	initFavSeelater : function(){
		
		$(document).on("click", ".item.bm .button", function(){
        	action = $(this).closest('.item.bm').attr('data-action');
        	type = $(this).closest('.item.bm').attr('data-type');
        	pgInfo = $(this).closest(".issue");
        	that = $(this);
        	that.addClass('loading');
        	term = (action == 'add') ? 'added' : 'removed';
        	typeterm = (type == 'doc_favorite') ? 'Favorite (doc)' : 'See later (doc)';
			$.ajax({
            	url: "/document/bm/"+type+"/"+action,
            	type:'POST',
            	dataType:'JSON',
            	data:{
            		"bid": pgInfo.attr('data-bid'),
            		"issue": pgInfo.attr('data-issue')
            	},
            	success:function(){
                	alertify.success(typeterm+" "+term+" successfully.");
                	$(".item.bm[data-type='"+type+"']").toggle();
                	that.removeClass('loading');
            	}
        	});        	
        });
		
	},
	initCheckToggle : function(){
		
 		var color = {
 			"checker" : "red",
 			"unchecker" : "green",
 		};

		$(document).on({
		    mouseenter: function(){
		        $(this).removeClass(color[$(this).attr('id')]);	
		        $(this).find("span").text($(this).attr('data-txt-hover'));
		        $(this).find("i").toggleClass('remove check');
		    },
		    mouseleave: function(){
		        $(this).addClass(color[$(this).attr('id')]);
		        $(this).find("span").text($(this).attr('data-txt-normal'));
		        $(this).find("i").toggleClass('remove check');
		    },
		    click: function(){
				action = $(this).attr('data-action');
	        	pgInfo = $(this).closest(".segment");
	        	that = $(this);
	        	that.addClass('loading');
				$.ajax({
	            	url: "/document/"+action,
	            	type:'POST',
	            	dataType:'JSON',
	            	data:{
	            		"bid": pgInfo.attr('data-bid'),
	            		"issue": pgInfo.attr('data-issue')
	            	},
	            	success:function(){
	                	alertify.success("Document "+action+" successfully.");
	                	$(".item.ch").toggle();
	                	that.removeClass('loading');
	            	}
	        	});  	    	
		    }
		    
		}, '#checker,#unchecker');
		
	
		
		
		
		
	},
	
	loadMorePage : function(){
		pgInfo = $(".ui.segment.issue").closest(".segment");
		$.ajax({
        	url: "/document/ajaxLoadMorePages",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": pgInfo.attr('data-bid'),
        		"issue": pgInfo.attr('data-issue'),
        		"count": $(".ui.cards.pages .card").length
        	},
        	success:function(data){
        		$('.ui.cards.pages .ui.loader.active').remove();
        		$('.ui.cards.pages .ui.button.loadMore').remove();
            	$('.ui.cards.pages').append(data.documentsResults);
            	if(bLazy !== null)
					bLazy.revalidate();
        	}
    	});  			
	},
	
	loadingTocManager: false,
	
	loadTocPages : function(pg){
		DOC.loadingTocManager = true;
		$('.ui.cards.tocpages').html('<div class="ui loader active"></div>');
		pgInfo = $(".ui.segment.issue").closest(".segment");
		$.ajax({
        	url: "/document/ajaxLoadTocPages",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": pgInfo.attr('data-bid'),
        		"issue": pgInfo.attr('data-issue'),
        		"pg": pg
        	},
        	success:function(data){
        		$('.ui.cards.tocpages .ui.loader.active').remove();
            	$('.ui.cards.tocpages').append(data.documentsResults);
            	if(bLazy !== null)
					bLazy.revalidate();
				
				$('.ui.cards.tocpages .card .icon.drag').draggable({
					cursor : "move",
      				cursorAt : { top: -5, left: -5 },
      				revert: "invalid",
					helper: function(event){var page = $(this).closest('.card').attr('data-page'); return $('<div class="ui button labeled icon drag ui-widget-header" data-page="'+page+'" style="cursor:move"><i class="icon move"></i>Page '+page+'</div>');},
				});
				
				
				DOC.loadingTocManager = false;
        	}
    	});		
		
		
	},
	
	reloadTocEntries: function(){
		$('.ui.toctable').html('<div class="ui loader active"></div>');			
		$('.ui.button.startSection, .ui.button.endSection').addClass('disabled');

		
		pgInfo = $(".ui.segment.issue").closest(".segment");
		$.ajax({
        	url: "/document/ajaxLoadTocEntries",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": pgInfo.attr('data-bid'),
        		"issue": pgInfo.attr('data-issue')
        	},
        	success:function(data){
				$('.ui.toctable .ui.loader.active').remove();
            	$('.ui.toctable').append(data.documentsResults);
            	
            	$('.ui.toctable .editable.sectionEndPage,.ui.toctable .editable.sectionStartPage').droppable({
            		tolerance: 'pointer',
            		accept : '.icon.drag',
            		activeClass: 'ui-drag-accept',
            		hoverClass: 'ui-drag-hover',
            		drop : function(event,ui){
						$(this).text(ui.helper.attr('data-page'));
						DOC.saveToc();
            		}
            	});
            	
        	}
    	});			
		
	},
	
	loadTocOverview: function(){
		$('.ui.tocOverview').html('<div class="ui loader active"></div>');			
		
		pgInfo = $(".ui.segment.issue").closest(".segment");
		$.ajax({
        	url: "/document/ajaxLoadTocOverview",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": pgInfo.attr('data-bid'),
        		"issue": pgInfo.attr('data-issue')
        	},
        	success:function(data){
				$('.ui.tocOverview .ui.loader.active').remove();
            	$('.ui.tocOverview').append(data.documentsResults);
        	}
    	});			
		
	},

	loadIndexOverview: function(){
		$('.ui.indexOverview').html('<div class="ui loader active"></div>');			
		
		pgInfo = $(".ui.segment.issue").closest(".segment");
		$.ajax({
        	url: "/document/ajaxLoadIndexOverview",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": pgInfo.attr('data-bid'),
        		"issue": pgInfo.attr('data-issue')
        	},
        	success:function(data){
				$('.ui.indexOverview .ui.loader.active').remove();
            	$('.ui.indexOverview').append(data.documentsResults);
        	}
    	});			
		
	},
	
	saveToc : function(){
		
		var toc = [];
		
		$('.toctable tbody tr').each(function(){
			var a = $(this).find('.editable[data-field="author"]').text();
			var t = $(this).find('.editable[data-field="title"]').text();
			var s = $(this).find('.editable[data-field="start_page"]').text();
			var e = $(this).find('.editable[data-field="end_page"]').text();
			if(a+t != ""){
				var entry = {
					author : a,
					title : t,
					start_page : s,
					end_page : e
				};
				toc.push(entry);
			}
		});
		
		
		$.ajax({
        	url: "/document/toc/save",
        	type:'POST',
        	dataType:'JSON',
        	data:{
        		"bid": pgInfo.attr('data-bid'),
        		"issue": pgInfo.attr('data-issue'),
        		"toc": toc,
        	},
        	success:function(data){
        		switch(data.result){
        			case 'success' :
        				alertify.success("TOC updated successfully.");
        				break;    			
        		}
				
        	},
        	
    	});	
		
	},
	
	
	
	
	init : function(){
		DOC.globalControls();
		
		$('.ui.checkbox').checkbox({
			onChange : function(){DOCUMENTS.search();},
		});
		
		$('.tabular.menu .item').tab({
			onVisible : function(tab){
				switch(tab){
					case 'overview':
						DOC.loadTocOverview();
						DOC.loadIndexOverview();
						break;
					
					case 'pages':
						if($(".ui.cards.pages .card").length==0){
							DOC.loadMorePage();	
						}
						break;
							
					case 'tocmanager':
						if($(".ui.cards.tocpages .card").length==0 && ! DOC.loadingTocManager){
							DOC.loadTocPages(1);	
						}
						DOC.reloadTocEntries();				
						break;
				}
		
			}
		});
		
		$(document).on('click', '.inTocManager', function(){
			DOC.loadTocPages($(this).attr('data-page'));
			$('.tabular.menu .item').tab('change tab', 'tocmanager');
			
		});
		
		$(document).on('click','.ui.button.loadMore',function(){$(this).addClass("loading");DOC.loadMorePage();});
		$(window).scroll(function() {
		   if($(window).scrollTop() + $(window).height() == $(document).height()) {
		       if($('.ui.button.loadMore').length>0 && ! $('.ui.button.loadMore').hasClass('loading')){
		       		$('.ui.button.loadMore').addClass("loading");
		       		DOC.loadMorePage();
		       }
		   }
		});
		
		$(document).on('click', '.prevTocPage', function(){
			if($('.ui.card.first').length){
				DOC.loadTocPages($('.ui.card.first').attr('data-page'));
			}
		});
		
		$(document).on('click', '.nextTocPage', function(){
			if($('.ui.card.last').length){
				DOC.loadTocPages($('.ui.card.last').attr('data-page'));				
			}
		});
		
		$('.ui.dropdown.pageSelector').dropdown({
			onChange: function(value){ 
				if(value != ""){
					DOC.loadTocPages(value);
					$('.ui.dropdown.pageSelector').dropdown('restore defaults');
				}
			}
		});
		
		$(document).on('blur', '.toctable .editable', DOC.saveToc);
		
		$(document).on('click', '.toctable .icon.trash', function(){
			var that = $(this);
			alertify.confirm('Are you sure ?', function(){
				that.closest('tr').remove();
				DOC.saveToc();
			});
		});
		
		  
		 
  		 DOC.initCheckToggle();
  		 DOC.initFavSeelater();
  		 DOC.loadTocOverview();
  		 DOC.loadIndexOverview();
  		 
  		 bLazy = new Blazy({
  		 	container : ".ui.segment.pages",	
  		 });
	},
	
};



var PAGE = {

	activateSelectMode : function(){
		$(document).off('mouseenter mousedown','.fulltext p');

		$('.fulltext').removeClass('footnote');
		$('.fulltext').removeClass('split');
		$('.fulltext').addClass('selection');

		$('.item.action:visible').hide();
		$('.item.action.copySelection').show();
	},
	
	activateFootnoteMode : function(){
		$('.fulltext').addClass('footnote');
		$('.fulltext').removeClass('split');
		$('.fulltext').removeClass('selection');

		$('.item.action:visible').hide();
		$('.item.action.saveFootnotes').show();

		$(document).off('mouseenter mousedown','.fulltext p');
		$(document).on('mouseenter mousedown','.fulltext p',function(e){
			if(e.which === 1)
				$(this).toggleClass('isfootnote');
		});
	},
	
	activateSplitMode : function(){
		$('.fulltext').removeClass('footnote');
		$('.fulltext').addClass('split');
		$('.fulltext').removeClass('selection');
				
		$('.item.action:visible').hide();
		$('.item.action.splitHere').show();
		
		$(document).off('mouseenter mousedown','.fulltext p');
		$(document).on('mouseenter mousedown','.fulltext p',function(e){
			if(e.which === 1){
				$('.fulltext p').removeClass('issplitup');
				$('.fulltext p').removeClass('issplitdown');
				$(this).addClass('issplitup');
				$(this).next('p').addClass('issplitdown');
			}
		});		
	},
	

	
	updateActions : function(){
		var value = $('.ui.dropdown').dropdown('get value');
		$.get('/session/pageModeLBC/'+value);
		switch(value){
			case 'selection': PAGE.activateSelectMode(); break;
			case 'footnote': PAGE.activateFootnoteMode(); break;
			case 'split': PAGE.activateSplitMode(); break;
		}	
	},
	changeGolden : function(action, pgInfo){
		
			$('.item.golden .button').addClass('loading');
			term = (action == 'add') ? 'added to' : 'removed from';
			
			$.ajax({
            	url: "/document/pagegolden/"+action,
            	type:'POST',
            	dataType:'JSON',
            	data:{
            		"bid": pgInfo.attr('data-bid'),
            		"issue": pgInfo.attr('data-issue'),
            		"page": pgInfo.attr('data-page'),
            		"pageObj" : pgInfo.attr('data-obj')
            	},
            	success:function(){
                	alertify.success("Page "+term+" golden set successfully.");
                	$(".item.golden[data-action='"+action+"']").hide();
                	$(".item.golden[data-action!='"+action+"']").show();
                	$(".item.golden .button").removeClass('loading');
            	}
        	});  
		
	},
	init: function(){
		
	    var viewer = OpenSeadragon({
        	id: "openseadragon1",
       	 	prefixUrl: "/i/openseadragon/",
        	tileSources: $("#openseadragon1").attr('data-src'),
        	zoomPerScroll: 1.5,
        	showNavigationControl: false,
        	immediateRender: true
    	});
    	
    	$('.ui.dropdown').dropdown({
    		direction: 'upward',
    		onChange: PAGE.updateActions,
    	});
    	PAGE.updateActions();
    	
    		
    	   	
    	   	
        $(document).on("click", ".item.bm .button", function(){
        	action = $(this).closest('.item.bm').attr('data-action');
        	type = $(this).closest('.item.bm').attr('data-type');
        	pgInfo = $(this).closest(".page");
        	that = $(this);
        	that.addClass('loading');
        	term = (action == 'add') ? 'added' : 'removed';
        	typeterm = (type == 'favorite') ? 'Favorite' : 'See later';
			$.ajax({
            	url: "/document/bm/"+type+"/"+action,
            	type:'POST',
            	dataType:'JSON',
            	data:{
            		"bid": pgInfo.attr('data-bid'),
            		"issue": pgInfo.attr('data-issue'),
            		"page": pgInfo.attr('data-page')
            	},
            	success:function(){
                	alertify.success(typeterm+" "+term+" successfully.");
                	$(".item.bm[data-type='"+type+"']").toggle();
                	that.removeClass('loading');
            	}
        	});        	
        });
        
        $(document).on("click", ".item.inx .button", function(){
        	action = $(this).closest('.item.inx').attr('data-action');
        	pgInfo = $(this).closest(".page");
        	that = $(this);
        	that.addClass('loading');
        	term = (action == 'add') ? 'added to' : 'removed from';
			$.ajax({
            	url: "/document/pageindex/"+action,
            	type:'POST',
            	dataType:'JSON',
            	data:{
            		"bid": pgInfo.attr('data-bid'),
            		"issue": pgInfo.attr('data-issue'),
            		"page": pgInfo.attr('data-page'),
            		"pageObj" : pgInfo.attr('data-obj')
            	},
            	success:function(){
                	alertify.success("Page "+term+" index pages successfully.");
                	$(".item.inx").toggle();
                	that.removeClass('loading');
            	}
        	});        	
        });
        
        
        $(document).on("click", ".item.golden .button", function(){
        	action = $(this).closest('.item.golden').attr('data-action');
        	pgInfo = $(this).closest(".page");
        	PAGE.changeGolden(action, pgInfo);
        });         
        
		$(".ui.footer").scrollTo(".ui.card.active", 500, {offset:{left:-50}});
		
		$('.ui.rating').rating({
			onRate : function(value){

				pgInfo = $(this).closest(".page");pgInfo = $(this).closest(".page");
				type = $(this).attr('data-type');
				$.ajax({
	            	url: "/document/rate",
	            	type:'POST',
	            	dataType:'JSON',
	            	data:{
	            		"bid": pgInfo.attr('data-bid'),
	            		"issue": pgInfo.attr('data-issue'),
	            		"page": pgInfo.attr('data-page'),
	            		"type" : type,
	            		"value" : value
	            	},
	            	success:function(data){
	            		if(data.result == "success")
	                		alertify.success("Rating saved successfully.");
	            	}
	        	});					
			}
		});
		
		
		bLazy = new Blazy({
			container : ".ui.footer",	
		});
		
		$('.ui.button.metadatas').popup({
    		inline: true,
    		hoverable: true,
    		position: 'bottom right',
    		delay: {
			  show: 10,
			  hide: 500
			},
			context: '.main'
  		});
		

		
		$(document).on('click', '.saveFootnotes', function(){
			var dataList = $('.fulltext p.isfootnote').map(function() {
    			return $(this).attr('data-line');
			}).get();
			var lines = dataList.join(',');
			var pgInfos = $(this).closest('.page');
			$.ajax({
	            	url: "/document/saveFootnotes",
	            	type:'POST',
	            	dataType:'JSON',
	            	data:{
	            		"page": pgInfos.attr('data-obj'),
	            		"bid" : pgInfos.attr('data-bid'),
	            		"issue" : pgInfos.attr('data-issue'),
	            		"in_footnotes" : lines,
	            	},
	            	success:function(data){
	            		if(data.result == "success"){
	                		alertify.success("Footnotes saved successfully.");
	                		PAGE.changeGolden('add', pgInfos);
	                		
	                	}
	            	}
	        	});
			
		});
		
		$(document).on('click', '.splitHere', function(){
			if($('.fulltext p.issplitup').length != 1){
				alertify.error("No selected lines");	
			}else{
				var line = $('.fulltext p.issplitup').attr('data-line');
	
				var pgInfos = $(this).closest('.page');
				$.ajax({
	            	url: "/document/saveSplit",
	            	type:'POST',
	            	dataType:'JSON',
	            	data:{
	            		"page": pgInfos.attr('data-obj'),
	            		"bid" : pgInfos.attr('data-bid'),
	            		"issue" : pgInfos.attr('data-issue'),
	            		"split_after_line" : line,
	            	},
	            	success:function(data){
	            		if(data.result == "success")
	                		alertify.success("Split line saved successfully.");
	            	}
	        	});
			}
		});		
		
		$(document).keydown(function(e) {
                if ( this !== e.target && (/textarea|select/i.test( e.target.nodeName ) ||
                        e.target.type === "text" || $(e.target).prop('contenteditable') == 'true' )) {
                    return;
                }
 
                switch (e.which) {
                    case 37: // left
                        window.location.href = $('.footer .card.active').prev('.card').attr('href');
                        break;
                   
                    case 39: // right
                        window.location.href = $('.footer .card.active').next('.card').attr('href');
                        break;
                }
           });
           $(document).on('click', '.buttons.savePrinted .button', function(){
           		var pgInfos = $(this).closest('.page');
           		var type = $(this).attr('data-type');
           		$.ajax({
	            	url: "/document/ajaxSavePrintedPage",
	            	type:'POST',
	            	dataType:'JSON',
	            	data:{
	            		"page": pgInfos.attr('data-page'),
	            		"bid" : pgInfos.attr('data-bid'),
	            		"issue" : pgInfos.attr('data-issue'),
	            		"type" : type,
	            		"printed_page_number" : $('#printedPageNumber').val()
	            	},
	            	success:function(data){
	            		if(data.result == "success")
	                		alertify.success("Printed page number saved successfully.");
	                	if(data.result == "error")
	                		alertify.error("An error occurs when saving printed page numbers");
	            	}
	        	});
           });
	}
};





$(document).ready(function(){
	
	
	alertify.defaults.maintainFocus = false;
    
	$.ajaxSetup({
		headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
	});

	var page = $("body").attr('data-page');
	
	window[page].init();	

});
