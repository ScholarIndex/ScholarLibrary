
$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

var LBC = {
	
	ajx : {abort: function () {}},
	ajx2 : {abort: function() {}},
	init : function(){
		
		$.ajaxSetup({
			error: function(xhr,status,error){
				if(xhr.status > 0){
					if(xhr.status == 401){
						alertify.error("You don't have permission to execute this action");
					}else{	
						alertify.error("Unknown error");
					}
				}
				return false;
			}
		});
	
		var datajs = $('body').attr('data-js');
		if(datajs !== ""){	
			window[datajs].init();
		}

		$(document).on('click', '.fa.fa-question-circle', function(){$('#helpwrapper').fadeToggle();});

		if($('body').attr('data-documentid') !== ""){
			$.ajax({
		        url: "/document/progress/"+$('body').attr('data-documentid'),
		        type: "GET",
		        dataType: "html",
		        success: function (data) {
		            $('.progress').html(data);
		        }
		    });		
		    
		    LBC.refreshBookmarks();	
		}
		
		$(document).on('click', '.bookmarks .fa', function(){
			$.ajax({
		        url: "/document/ajaxbookmarks/"+$(this).attr('data-action')+"/"+$(this).attr('data-type')+"/"+$(this).attr('data-documentid')+"/"+$(this).attr('data-pageid'),
		        type: "GET",
		        dataType: "html",
		        success: function (data) {
					alertify.success("Bookmark modified successfully");
					LBC.refreshBookmarks();
		        }
		    });			
		});
		
		$(document).on('click', '.indexgolden .fa', function(){
			if(! LBC.checkCred()) return;
			
			$.ajax({
		        url: "/document/ajaxindexgolden/"+$(this).attr('data-action')+"/"+$(this).attr('data-type')+"/"+$(this).attr('data-documentid')+"/"+$(this).attr('data-pageid'),
		        type: "GET",
		        dataType: "html",
		        success: function (data) {
					alertify.success("Page status modified successfully");
					LBC.refreshIndexgolden();
		        }
		    });			
		});	
	},

	checkCred : function(){
		var roles = $('body').attr('data-cred').split(',');
		if($.inArray('editor', roles) == -1){
			alertify.error("You don't have permission to execute this action");
			return false;	
		}else{
			return true;
		}
	},
	
	refreshBookmarks : function(){
		LBC.ajx.abort();
		documentId = $('body').attr('data-documentid');
		pageId = $('body').attr('data-pageid');
		LBC.ajx = $.ajax({
	        url: "/document/bookmarks/"+documentId+"/"+pageId,
	        type: "GET",
	        dataType: "html",
	        success: function (data) {
	            $('.bookmarks').html(data);
	        }
	    });				
	},
	
	refreshIndexgolden : function(){
		LBC.ajx2.abort();
		documentId = $('body').attr('data-documentid');
		pageId = $('body').attr('data-pageid');
		LBC.ajx2 = $.ajax({
	        url: "/document/indexgolden/"+documentId+"/"+pageId,
	        type: "GET",
	        dataType: "html",
	        success: function (data) {
	            $('.indexgolden').html(data);
	        }
	    });				
	}	
};

var ARTICLEOVERVIEW = {
	init : function(){}
};

var OVERVIEW = {
	init : function(){
	}
};

var SCANS = {
	init : function(){
	}
};


var VIEWER = {
	init : function(){
		
		VIEWER.loadPage();
		
		$( window ).hashchange(VIEWER.loadPage);
		
		$(document).keydown(function(e) {
			var n = parseInt(location.hash.replace('#',''),10);
			if(isNaN(n)) n = 1;

			max = parseInt($('.filmstrip .thumb:last').attr('data-n'),10);
		    switch(e.which) {
		        case 37: // left
				if(n > 1)
					location.hash = n-1;	
		        break;
					
		        case 39: // right
				if(n < max)
					location.hash = n+1;
		        break;
		
		
		        default: return; // exit this handler for other keys
		    }
		    e.preventDefault(); // prevent the default action (scroll / move caret)
		});
		
		$(document).on('click', '.action.metadata span', function(){
			$(this).closest('.action').toggleClass('open');			
		});
		
		$(document).on('click', '.action.split span', function(){
			if($('div.splitter').length > 0){
				$('div.splitter').addClass('enabled');			
			}else{
				$('#textview p:first').before("<div class='splitter enabled'><span class='splitHere'>validate</span><span class='splitRemove'>&times;</span></div>");
			}
			$('#textview').sortable({
				axis: "y",
				handle: $("div.splitter") 
			});
		});
		
		
		
        $(document).on('click', '.splitRemove', function(){
			$('.splitter').remove();
			$.ajax({
					url: "/document/saveSplit",
					type:'POST',
					dataType:'JSON',
					data:{
					"page": $('a.pn.b').attr('data-pageobj'),
					"split_after_line" : "remove"
				},
				success:function(data){
					if(data.result == "success"){
						alertify.success("Split removed successfully.");
					}
				}
			});
		});		
		
        $(document).on('click', '.splitHere', function(){
			var line = $('.splitter').prev('p').attr('l');
			$.ajax({
					url: "/document/saveSplit",
					type:'POST',
					dataType:'JSON',
					data:{
					"page": $('a.pn.b').attr('data-pageobj'),
					"split_after_line" : line,
				},
				success:function(data){
					if(data.result == "success"){
						$('div.splitter').removeClass("enabled");
						alertify.success("Split line saved successfully.");
					}
				}
			});
		});
		 		
        $(document).on('click', '.action.metadata .save', function(){
        	
        	
        	
        	var saisie = new RegExp('^([0-9]+)?(,[0-9]+)?$');

	        if ( ! saisie.test($('#ppn').val())) {
	        	alertify.error("Erreur saisie");	
	        	return;
	        }
	        	
			$.ajax({
					url: "/document/savePpn",
					type:'POST',
					dataType:'JSON',
					data:{
					"page": $('a.pn.b').attr('data-pageobj'),
					"ppn" : $('#ppn').val(),
					"propagate" : $('#propagate').is(':checked') ? 1 : 0
				},
				success:function(data){
					if(data.result == "success"){
						$('.action.metadata').removeClass('open');
						$('#propagate').prop('checked', false);			
						alertify.success("Printed page number saved successfully.");
					}
				}
			});
		});		 		
		
	}, 
	
	loadPage : function(){
		var n = location.hash.replace('#','');
		if(n == "") n=1;
				
		bid = $('body').attr('data-bid');
		issue = $('body').attr('data-issue');
		
		var cat = ($('body').attr('data-type') == 'monograph') ? 'books' : 'journals';
		bid = ($('body').attr('data-type') == 'monograph') ? $('body').attr('data-provenance')+"_"+bid : bid;
		$('#pageview').html('');
		var viewer = OpenSeadragon({
	    	id: 'pageview',
	   	 	prefixUrl: "/i/openseadragon/",
	    	tileSources: $('#pageview').attr('data-baseuri')+"::"+n+"/info.json",
	    	zoomPerScroll: 1.5,
	    	showNavigationControl: false,
	    	immediateRender: true
		});			
		
		$.ajax({
	        url: "/document/page/text/"+$('.thumb[data-n='+n+']').attr('data-oid'),
	        type: "GET",
	        dataType: "html",
	        success: function (data) {
	            $('#textview').html(data);
	            $('#ppn').val($('a.pn.b').attr('data-ppn'));
	            $('body').attr('data-pageid', $('.thumb[data-n='+n+']').attr('data-oid'));
	            LBC.refreshBookmarks();
	            LBC.refreshIndexgolden();
	        }
	    });				
			
		$('.thumb').removeClass('active');
		$('.thumb[data-n='+n+']').addClass('active');
		$('.filmstrip').scrollTo($('.thumb.active'),200, {offset : {left: -$('.filmstrip').width()/2+100}});
		
	}
	
	
};

var JOURNAL = {

	init : function(){
		$(document).on('click', '.button', function(){
			document.location = '/document/overview/'+$('body').attr('data-bid')+'/'+$('select').val()
		});
	},

};

var SEARCH = {
	
	page : null,
	
	init : function(){
		
		SEARCH.page = $('.noPage').text();
		
		$(document).on('click', '.chk', function(e){
			if($(this).hasClass('year'))	$('.yearfilter').toggleClass('disabled');
			$(this).toggleClass('checked');
			SEARCH.page = 1;
			SEARCH.doSearch();
		});
		
		$(document).on('click', '.reset.allfilters', function(){
			$('#sidemenu .chk').removeClass('checked');
			SEARCH.page = 1;
			SEARCH.doSearch();			
		});
		
		$(document).on('click', '.allfilters.unselect', function(){
        	$(this).closest('ul').find('li').removeClass('checked');
			SEARCH.page = 1;
			SEARCH.doSearch();
        });
        $(document).on('click', '.allfilters.select', function(){
        	$(this).closest('ul').find('li').addClass('checked');
			SEARCH.page = 1;
			SEARCH.doSearch(); 
        });
		
        $(document).on('click', '.allfilters.viewmore', function(){
        	$(this).closest('ul').find('li:hidden:lt(7)').show();
			if($(this).closest('ul').find('li:hidden').length == 0)
				$(this).remove();
        });
				
		
		$(document).on('click', '.sub', function(e){
			if(e.target != this) return;
			$(this).toggleClass('open');
		});
		
		
		$(document).on('click', '.countersbar li.docs', function () {
			$('.countersbar li.cat').removeClass('disabled');
			SEARCH.page = 1;
			SEARCH.doSearch();
		});
		
		
		$(document).on('click', '.countersbar li.cat', function () {
			if( $(this).hasClass('disabled') || $('.countersbar li.cat.disabled').length==0){
				$('.countersbar li.cat').addClass('disabled');
				$(this).removeClass('disabled');
				SEARCH.page = 1;
				SEARCH.doSearch();
			}else{
				$('.countersbar li.cat').removeClass('disabled');
				SEARCH.page = 1;
				SEARCH.doSearch();
			}
		});
		
		$(document).on('click', '.card', function(e){
			e.stopPropagation();
			e.preventDefault();
			if($(this).hasClass('journal'))
				document.location = '/document/journal/'+$(this).attr('data-bid');
			else if($(this).hasClass('article'))
				document.location = '/article/overview/'+$(this).attr('data-oid');
			else if($(this).attr('data-bid') != "")
				document.location = '/document/overview/'+$(this).attr('data-bid');
		});
		
		$(document).on('click','#content', function(){
			$('.countersbar li.cat').removeClass('disabled');
			SEARCH.page = 1;
			SEARCH.doSearch();
		});		
		
		$(document).on('click', '.fa-arrow-circle-o-right', function(e){
			e.preventDefault();
			e.stopPropagation();
			if($(this).closest('.card').attr('data-bid') != "")
				document.location = '/document/overview/'+$(this).closest('.card').attr('data-bid')+'/'+$(this).closest('.card').find('select').val();
			
		});
		
		$(document).on('click', '.card.journal select', function(e){
			e.preventDefault();
			e.stopPropagation();			
		});
		
	
        $(document).on('keyup','#search input', function(e){
            var code = e.keyCode || e.which;
             if(code == 13) { //Enter keycode
             	SEARCH.doSearch();
             }
        });
		
		$(document).on('click', '.fa-angle-left', function(){
			SEARCH.page--;		
			SEARCH.doSearch();	
		});		

		$(document).on('click', '.fa-angle-right', function(){
			SEARCH.page++;		
			SEARCH.doSearch();	
		});		

		$(document).on('change', 'select#sort', function(){
			SEARCH.page = 1;		
			SEARCH.doSearch();	
		});		

		 
		SEARCH.doSearch();
	},
	
	getFilters : function(){
		var filtrs = {};	
		$('.sub').each(function(){
			var sub = $(this);
			filtrs[sub.attr('data-field')] = [];
			
			$(this).find('li.checked').each(function(){
				filtrs[sub.attr('data-field')].push($(this).attr('data-key'));
			});
		});	
		
		var mindate = $('input[name="mindate"]').val();
        var maxdate = $('input[name="maxdate"]').val();
        if($('.chk.year').hasClass('checked') && (mindate != "" || maxdate != "")){
            filtrs.year = {};
            filtrs.year.mindate = mindate;
            filtrs.year.maxdate = maxdate;
        }
        
        
		return filtrs;
	},
	
	refreshFilters : function(){


		
		var filtrs = SEARCH.getFilters();

		$('.sub').each(function(){
			var sub = $(this);
			$.ajax({
		        url: "/filters",
		        type: "POST",
		        data: {
		        	'q': $('#search input').val(),
		        	'ns': $.map($('.countersbar li.cat:not(.disabled)'), function(a) { return $(a).attr("data-ns");}),
		        	'in' : {
		        		'authors' : $('.filters .chk.authors').hasClass('checked'),
		        		'titles' : $('.filters .chk.titles').hasClass('checked'), 
		        		'publishers' : $('.filters .chk.publishers').hasClass('checked'),	
		        	},
		        	'field' : sub.attr('data-field'),
		        	'filtrs' : filtrs,
		        			
	
		        },
		        dataType: "html",
		        success: function (data) {
		        	sub.find('ul').html(data);
				}
			});		
		});
	},
	
	counterNotation : function(n){
		
		if(n > 9999){
			return (Math.floor(n/1000))+"k";
		}else{
			return n;
		}
		
	},
	
	doSearch : function(){
		
		SEARCH.refreshFilters();
		SEARCH.yearBarChart();
		
		var filtrs = SEARCH.getFilters();

		$.ajax({
	        url: "/count",
	        type: "POST",
	        data: {
	        	'q': $('#search input').val(),
	        	'page' : SEARCH.page,
	        	'in' : {
	        		'authors' : $('.filters .chk.authors').hasClass('checked'),
	        		'titles' : $('.filters .chk.titles').hasClass('checked'), 
	        		'publishers' : $('.filters .chk.publishers').hasClass('checked'),	
	        	},
	        	'filtrs' : filtrs, 
	        	'sort' : $('select#sort').val()
	        },
	        dataType: "json",
	        success: function (data) {
	        	$('.docs span').html(SEARCH.counterNotation(data.response.response.numFound));	
	        	$('.cat.monograph span').html(SEARCH.counterNotation(data.response.facet_counts.facet_fields.ns[_SOLR_ROOT_+'.bibliodb_books']));
	        	$('.cat.article span').html(SEARCH.counterNotation(data.response.facet_counts.facet_fields.ns[_SOLR_ROOT_+'.bibliodb_articles']));
	        	$('.cat.journal span').html(SEARCH.counterNotation(data.response.facet_counts.facet_fields.ns[_SOLR_ROOT_+'.bibliodb_journals']));
				$('.cat.contribution span').html(0);
	        }
	    });	

		
		$.ajax({
	        url: "/search",
	        type: "POST",
	        data: {
	        	'q': $('#search input').val(),
	        	'ns': $.map($('.countersbar li.cat:not(.disabled)'), function(a) { return $(a).attr("data-ns");}),
	        	'page' : SEARCH.page,
	        	'in' : {
	        		'authors' : $('.filters .chk.authors').hasClass('checked'),
	        		'titles' : $('.filters .chk.titles').hasClass('checked'), 
	        		'publishers' : $('.filters .chk.publishers').hasClass('checked'),	
	        	},
	        	'filtrs' : filtrs,
	        	'sort' : $('select#sort').val()
	        },
	        dataType: "json",
	        success: function (data) {
	        	nbPages = Math.ceil(data.response.response.numFound / 12);
	        	$('.docs span').html(data.response.response.numFound);
	        	$('span.noPage').html(SEARCH.page);
	        	$('span.nbPage').html(nbPages);
	        	if(SEARCH.page == 1)
	        		$('.fa-angle-left').hide();
	        	else
	        		$('.fa-angle-left').show();
	        	
	        	if(SEARCH.page == nbPages)
	        		$('.fa-angle-right').hide();
	        	else
	        		$('.fa-angle-right').show();
	        	
				$('div.results').html('');
				for(var res in data.results){
					SEARCH.result.object = data.results[res];
					
					div = $('<div class="card '+SEARCH.result.getClass()+'" data-oid="'+SEARCH.result.getOID()+'" data-bid="'+SEARCH.result.getBid()+'"></div>');
					div.append('<p>'+SEARCH.result.getType()+'</p>');
					div.append('<p>'+SEARCH.result.getTitle()+'</p>');
					div.append('<p>'+SEARCH.result.getAuthor()+'</p>');
					div.append('<p>'+SEARCH.result.getDate()+'</p>');
					div.append('<p>'+SEARCH.result.getVolume()+'</p>');
					div.append('<p>'+SEARCH.result.getTitleOfJournal()+'</p>');
					$('div.results').append(div);	
				}
	        }
	    });	

	},
	
    yearBarChart : function() {
    	
    	var filtrs = SEARCH.getFilters();
    	
    	
        $.ajax({
            url: "/yearChart",
            type: "POST",
            data: {
	        	'q': $('#search input').val(),
	        	'ns': $.map($('.countersbar li.cat:not(.disabled)'), function(a) { return $(a).attr("data-ns");}),
	        	'page' : SEARCH.page,
	        	'in' : {
	        		'authors' : $('.filters .chk.authors').hasClass('checked'),
	        		'titles' : $('.filters .chk.titles').hasClass('checked'), 
	        		'publishers' : $('.filters .chk.publishers').hasClass('checked'),	
	        	},
	        	'filtrs' : filtrs   
            },
            dataType: "json",
            success: function (data) {
                $('.barchart').html('');
                var range = $('.yearslider')[0];
                try{
                    range.noUiSlider.destroy();
                }catch(e){}
                if(data.length == 0) return;
                var range = $('.yearslider')[0];
                var dataArray = data.val;
                var barwidth = 160/dataArray.length;
                var max = Math.max.apply(null, dataArray);
                var svg = d3.select(".barchart").append("svg")
                          .attr("height","75px")
                          .attr("width","160px");
                svg.selectAll("rect")
                    .data(dataArray)
                    .enter().append("rect")
                          .attr("class", "baryear")
                          .attr("height", function(d, i) {return (d/max*75)})
                          .attr("width",barwidth)
                          .attr("x", function(d, i) {return (i * barwidth)})
                          .attr("y", function(d, i) {return 75 - (d/max*75)});
                noUiSlider.create(range, {
                    start: [data.selectedMin,data.selectedMax],
                    connect: true, // Display a colored bar between the handles
                    direction: 'ltr', // Put '0' at the bottom of the slider
                    behaviour: 'tap-drag', // Move handle on tap, bar is draggable
                    step: 1,
                    tooltips: true,
                    range: {
                        'min': data.minYear-(data.maxYear-data.minYear)/4,
                        'max': data.maxYear+(data.maxYear-data.minYear)/4
                    },
                        pips: {
                        mode: 'values',
                        values: [data.minYear,data.maxYear],
                        density: 5
                    },
                      format: {
                          to: function ( value ) {
                            return parseInt(value,10);
                          },
                          from: function (value) { return value; }
                        }
                });
                range.noUiSlider.on('change', function ( values, handle ) {
                    if ( values[handle] < data.minYear ) {
                        range.noUiSlider.set([data.minYear,null]);
                    } else if ( values[handle] > data.maxYear ) {
                        range.noUiSlider.set([null,data.maxYear]);
                    }
                });
                range.noUiSlider.on('set', function ( values) {
                    $('input[name="mindate"]').val(values[0]);
                    $('input[name="maxdate"]').val(values[1]);
					SEARCH.page = 1;
					SEARCH.doSearch();
                });
            }
        });
    },
	
	result : { 
		object : null,
		
		getOID : function(){
			return this.object['_id'];
		},
		
		getBid : function(){
			return this.object['bid'] || '';	
		},
		
		getClass : function(){
			return this.object['_type_'];
		},
		
		getType : function(){
			switch(this.object['_type_']){
				case 'monograph': return 'Book';  break;
				case 'article': return 'Article';  break;
				case 'journal': return 'Journal '+SEARCH.result.getIssues();  break;
			}
			return '';
		},
		
		getIssues : function(){
			var options = "<option>search issue</option>";
			var n = 0;
			for(var issue in SEARCH.result.object._meta_.issues){			
				var is = SEARCH.result.object._meta_.issues[issue];
				if(is.marked_as_removed == false){
					options += "<option value='"+is.foldername+"'>"+is.foldername+"</option>";
					n++;
				}
			}
			return '<span>'+n+' Issues <select>'+options+'</select> <i class="fa fa-arrow-circle-o-right"></i></span>';
		},
		
		getTitle : function(){
			switch(this.object['_type_']){
				case 'monograph': return this.object.title || '';  break;
				case 'article': return this.object.title || '';  break;
				case 'journal': return this.object.full_title || '';  break;
			}
		},
		
		getAuthor : function(){
			switch(this.object['_type_']){
				case 'monograph': return this.object.author || '';  break;
				case 'article': return this.object.authors[0] || '';  break;
			}
			return '';
		},
		getVolume : function(){
			switch(this.object['_type_']){
				case 'article': return 'Volume : '+this.object.volume || '';  break;
			}
			return '';	
		},
		getDate : function(){
			switch(this.object['_type_']){
				case 'monograph': return this.object.publication_year || '' ;  break;
				case 'journal': 
					var min = 9999;
					var max = 0;
					for(var issue in SEARCH.result.object._meta_.issues){			
						var is = SEARCH.result.object._meta_.issues[issue];
						if(is.year < min) min = is.year;
						if(is.year > max) max = is.year;
					}
					return 'From '+min+' to '+max;  
					break;
				case 'article': return this.object.year || ''; break;
			}
			return '';
		},
		getTitleOfJournal : function(){
			switch(this.object['_type_']){
				case 'article': return this.object._journal_.short_title || '';  break;
			}
			return '';
		},
		
	}
	
	
};

var REF = {
	allRefs : [],
	
	highlight : function(){
		
		var q = $('#textSearch input').val();
		
				
		$('.fulltext').unmark({
      		done: function() {
	        	$('.fulltext').mark(q, {
	          		separateWordSearch: true,
	          		accuracy:'complementary'
	        	});
      		}
    	});
	
		
	},
	
	loadPage : function(div){
		
		$.ajax({
		    	beforeSend : function(){
		    		div.removeClass('notLoaded');
		    	},
		        url: "/document/page/text/"+div.attr('data-oid'),
		        type: "GET",
		        dataType: "html",
		        success: function (data) {
		            div.html(data);
					REF.highlight();
					$.ajax({
				        url: "/document/page/references/"+div.attr('data-oid'),
				        type: "GET",
				        dataType: "json",
				        success: function (data) {
							for(var ref in data){
				            	var token = data[ref]['contents'][1];
								div.find('span[_st='+token['start']+']').after('<em data-ref="'+ref+'" class="'+data[ref]['dis']+' '+data[ref]['ref_type']+'"></em>');
								REF.allRefs[ref] = data[ref];
							}
														
							var heights = [];
							
							$(div.find('em').get().reverse()).each(function() { 
								if(heights[$(this).position().top] == undefined){
									heights[$(this).position().top] = 0;
								}else{
									heights[$(this).position().top] += 1;
									$(this).addClass('x'+heights[$(this).position().top]);
								}
							});
				        }
				    });	
						            
		            
		        }
		    });	
		
	},
	
	init : function(){
		$(document).on('mouseenter','div.notLoaded' ,function(){
			var div = $(this);
			REF.loadPage(div);
		});
		
		$("#showallrefs").change(function() {
		    if(this.checked) {
		        $('.fulltext').removeClass('displayonlydisamb');
		        $('.fulltext').addClass('displayallrefs');
		    }else{
		        $('.fulltext').addClass('displayonlydisamb');
		        $('.fulltext').removeClass('displayallrefs');	
		    }
		});
		
		$('div.notLoaded:lt(5)').each(function(){
			div = $(this);
			REF.loadPage(div);
		});
		
		$(document).on('click', 'a.pn', function(){
			document.location.href = document.location.href.replace('references','viewer')+"#"+$(this).text(); 
		});
		
		$(document).on('click', 'em', function(){
			$('em').removeClass('active');
			$('span').removeClass('hl');
			$(this).addClass('active');
			var div = $(this).closest('div.page');
			var ref = $(this).attr('data-ref');
			$('.refDetails').html('');
			$('.refDetails').removeClass('editmode');
			$.ajax({
		        url: "/document/page/reference/"+ref,
		        type: "GET",
		        dataType: "html",
		        success: function (data) {
		            $('.refDetails').html(data);
		            $('.refDetails').removeClass('editmode');
		            $('.refDetails .ui.dropdown.typeselect').dropdown();
		        }
		    });				
			
			
			var contents = REF.allRefs[ref]['contents'];
		 	for(var content in contents){
				var token = contents[content];
				div.find('span').filter(function(){
					return $(this).attr('_st') >= token['start'] && $(this).attr('_en') <= token['end'];
				}).addClass('hl');
	        }
			
			
			
		});		
		
		$(document).on('click', '.action.edit', function(){
			if(! LBC.checkCred()) return;
			
			var rD = $(this).closest('.refDetails');
			rD.toggleClass('editmode');
			
			rD.find('tr.type').css('color', 'black');
			rD.find('tr.type span.val').hide();
			rD.find('tr.type .ui.dropdown').show();
			rD.find('tr.type .action.validate').show();
		});


		
		$(document).on('click', '.action.valid', function(){
			if(! LBC.checkCred()) return;
			
			
			var ref = $(this).closest('table').attr('data-ref');
			
			$.ajax({
		        url: "/document/saveReferenceDisambiguationValid",
		        type: "POST",
		        dataType: "json",
		        data : {
		        	reference: ref,
		        },
		        beforeSend : function(){
		        	$('.refDetails').html('');
		        },
		        success: function (data) {
					$('em.active').click();
		        }
		    });				
			
		});



		$(document).on('click', '.action.cancel', function(){
			$('em.active').click();
		});
		
		$(document).on('click', 'tr.type .validate', function(){
			if(! LBC.checkCred()) return;
			
			tr = $(this).closest('tr');
			rD = tr.closest('.refDetails');
			old = tr.find('span.val').text();
			tr.find('span.val').text(tr.find('.ui.dropdown').dropdown('get text'));
			changetype = (old != tr.find('.ui.dropdown').dropdown('get text'));
				
			if(tr.find('.ui.dropdown').dropdown('get value') == 'primary') source = 'asve';
			if(tr.find('.ui.dropdown').dropdown('get value') == 'secondary') source = 'book';
			tr.find('span.val').show();
			tr.find('.dropdown').hide();
			if(changetype){
				rD.find('tr.data').hide();
			}
			$(this).hide();
			rD.find('tr.title').show();
			rD.find('tr.title').css('color', 'black');
			rD.find('tr.title span.val').hide();
			rD.find('tr.title .ui.dropdown.'+source).show();
			rD.find('tr.title .ui.dropdown.'+source).dropdown({
				apiSettings: {
					url: '/search/reftitle/'+source+'/{query}?rand='+Math.random(),
					throttle: 200
				}
			});
			rD.find('tr.title .action.validate').show();
			
			
		});
		
		$(document).on('click', 'tr.title .validate', function(){
			if(! LBC.checkCred()) return;
			
			
			var ref = $(this).closest('table').attr('data-ref');
			var type = $('.ui.dropdown.typeselect').dropdown('get value');
			var title = (type == 'primary') ? $('.ui.dropdown.title.asve').dropdown('get value') :  $('.ui.dropdown.title.book').dropdown('get value');
				
			
			
			$.ajax({
		        url: "/document/saveReferenceDisambiguation",
		        type: "POST",
		        dataType: "json",
		        data : {
		        	reference: ref,
		        	type: type,
		        	title: title
		        },
		        beforeSend : function(){
		        	$('.refDetails').html('');
		        },
		        success: function (data) {
					$('em.active').click();
		        }
		    });				
			
		});
		
				
		$(document).on('click', '.fa.disamb', function(){
			
			if(! LBC.checkCred()) return;
			
			
			if($(this).hasClass('fa-spinner'))
				return;
				
			var from = ($(this).hasClass('fa-check')) ? 'fa-check' : 'fa-times';
			var to = ($(this).hasClass('fa-check')) ? 'fa-times' : 'fa-check';
			var value = (to == 'fa-check') ? true : false; 
			var dis = $(this).closest('table').attr('data-dis');
			var field = $(this).attr('data-field');
			var button = $(this);
			
			$.ajax({
		        url: "/document/saveReferenceDisambiguationState",
		        type: "POST",
		        dataType: "json",
		        data : {
		        	dis: dis,
		        	field: field,
		        	value: value
		        },
		        beforeSend : function(){
		        	button.removeClass(from);
		        	button.addClass('fa-spinner fa-pulse');
		        },
		        success: function (data) {
		        	button.removeClass('fa-spinner fa-pulse');
		        	button.addClass(to);
		        	alertify.success("Flag change successfully");	
		        }
		    });					
			
		});
		

		$(document).on('click', '.fa.refer', function(){
			
			if(! LBC.checkCred()) return;
			
			if($(this).hasClass('fa-spinner'))
				return;
				
			var from = ($(this).hasClass('fa-check')) ? 'fa-check' : 'fa-times';
			var to = ($(this).hasClass('fa-check')) ? 'fa-times' : 'fa-check';
			var value = (to == 'fa-check') ? true : false; 
			var ref = $(this).closest('table').attr('data-ref');
			var field = $(this).attr('data-field');
			var button = $(this);
			
			$.ajax({
		        url: "/document/saveReferenceState",
		        type: "POST",
		        dataType: "json",
		        data : {
		        	ref: ref,
		        	field: field,
		        	value: value
		        },
		        beforeSend : function(){
		        	button.removeClass(from);
		        	button.addClass('fa-spinner fa-pulse');
		        },
		        success: function (data) {
		        	button.removeClass('fa-spinner fa-pulse');
		        	button.addClass(to);
		        	if(field == 'correct'){
		        		$('.fa.refer[data-field="checked"]').removeClass("fa-times").addClass("fa-check");
		        	}
		        	alertify.success("Flag change successfully");	
		        }
		    });					
			
		});
				
		
		$(document).on('keyup', '#textSearch input', function(){
			var input = $(this);
			if(input.val().length >= 3){
				$.ajax({
			        url: "/document/page/references/textsearch",
			        type: "POST",
			        dataType: "json",
			        data : {
			        	document_id : $('body').attr('data-documentid'),
			        	search : input.val()
			        },
			        success: function (data) {
			        	$('.separator').remove();
			        	$('.page').hide();
			        	if(data.length > 0){
				        	for(var n in data){
				        		$('.page[data-n="'+data[n]+'"]').show();
				        	}
				        
					        visiblePages = $('.page:visible');
				        	for(var z = 0; z < visiblePages.length - 1; z++){
				        		var me = $(visiblePages[z]);
				        		var next = me.next('.page:visible');
				        		c = parseInt(me.attr('data-n'),10);
				        		n = parseInt(next.attr('data-n'),10);
				        		if(n != c + 1){
				        			me.after('<div class="separator">•••</div>');	
				        		}
				        	}
				        	
						}else{
							$('.page').show();
						}

			        	REF.highlight();
			        }
			       
			    });	
		  }else{
		  	REF.highlight();
		  	$('.separator').remove();
		  	$('.page').show();
		  }
			
			
		});
		
	}
	
};

var TOC = {
	page : null,
	trAddNew : null,
	init : function() {
		
		TOC.goPage(1);
		
		$(document).on('click','a.prev',TOC.prev);
		$(document).on('click','a.next',TOC.next);
		
		$('#goPage').change(function(){
			n = parseInt($(this).val());
			if(n !== TOC.page){
				TOC.goPage(n);	
			}
		});
		
		$(document).on('click', '.fa-pencil-square-o', function(){
			$(this).toggleClass('fa-pencil-square-o fa-save');
			$(this).closest('tr').toggleClass('editOff editOn');
		});
		
		$(document).on('click', '.fa-plus-square-o', function(){
			trAddNew = $('tr[data-id="new"]').clone();
	
			$(this).toggleClass('fa-plus-square-o fa-save');
			$(this).closest('tr').toggleClass('editOff editOn');
		});
		
		$(document).on('click', '.fa-save', function(){
			tr = $(this).closest('tr');
		
			var datas = {
					"document_id": $('body').attr('data-documentid'),
					"article_id": tr.attr('data-id'),
					"authors" : tr.find('.dropdown.author').dropdown('get value'),
					"title" : tr.find('input.title').val(),
					"page_start" : tr.find('.dropdown.page.start').dropdown('get value'),
					"page_end" : tr.find('.dropdown.page.end').dropdown('get value')
			};
			
			if(datas.title == ""){
				alertify.error("Title is required.");			
				return false;
			}
			
			if(datas.page_start == ""){
				alertify.error("Page start is required.");			
				return false;
			}
			
			if(datas.page_end == ""){
				alertify.error("Page end is required.");			
				return false;
			}
			
			if(parseInt(datas.page_end,10) < parseInt(datas.page_start,10)){
				alertify.error("Page end should be greather than page start.");			
				return false;
			}
						
		
			$.ajax({
				url: "/document/saveArticle",
				type:'POST',
				dataType:'JSON',
				data: datas,
				success:function(data){
					if(data.result == "success"){
						alertify.success("Article updated successfully.");
						
						if(datas['article_id'] == 'new'){
							$('tr[data-id="new"]').attr('data-id', data.article_id);						
						}
						var tr = $('tr[data-id="'+data.article_id+'"]');
						tr.find('td.author .textual').text(data.authors);
						tr.find('td.title .textual').text(data.title);
						tr.find('td.pagerange .textual').text(data.pagerange);
						tr.find('td.actions .fa-save').toggleClass('fa-pencil-square-o fa-save');
						tr.toggleClass('editOn editOff');
						
						tr.closest('table').append(trAddNew);
						
						$('tr[data-id="new"] .ui.dropdown.author').dropdown({
							apiSettings: {
								url: '/search/authors/{query}?rand='+Math.random(),
								throttle: 200
							},
							delimiter: '|#|'
						});
						$('tr[data-id="new"] .ui.dropdown.page').dropdown();	
		
		
					}
				}
			});
					

		});
				
		
		
		$('.ui.dropdown.author').dropdown({
			apiSettings: {
				url: '/search/authors/{query}?rand='+Math.random(),
				throttle: 200
			},
			delimiter: '|#|'
		});
		$('.ui.dropdown.page').dropdown();		
	},
	
	prev : function() {TOC.goPage(TOC.page-2);},
	next : function() {TOC.goPage(TOC.page+2);},
	goPage : function(n) {
		if(n % 2 == 0) n--;

		nbPage = parseInt($('body').attr('data-pagecount'),10);
		bid = $('body').attr('data-bid');
		issue = $('body').attr('data-issue');
		bidwithprov = $('body').attr('data-bidwithprov');
		if(n < 1 || n > nbPage)
			return;
		
		TOC.page = n;
		$('#goPage').val(TOC.page);

		left = n;
		right = n+1;	
		if(right > nbPage) right = null;
			
		$('#openseadragon_left').html('');
		$('#openseadragon_right').html('');	
			
		if(left !== null){
			var viewerL = OpenSeadragon({
	        	id: 'openseadragon_left',
	       	 	prefixUrl: "/i/openseadragon/",
	        	tileSources: _IIIF_ROOT_+bidwithprov+"::"+issue+"::"+left+"/info.json",
	        	zoomPerScroll: 1.5,
	        	showNavigationControl: false,
	        	immediateRender: true
	    	});			
	    	$('.leftPg').html(left);
		}
		if(right !== null){
			var viewerR = OpenSeadragon({
	        	id: 'openseadragon_right',
	       	 	prefixUrl: "/i/openseadragon/",
	        	tileSources: _IIIF_ROOT_+bidwithprov+"::"+issue+"::"+right+"/info.json",
	        	zoomPerScroll: 1.5,
	        	showNavigationControl: false,
	        	immediateRender: true
	    	});		
	    	$('.rightPg').html(right);	
		}
		
		if(left == 1){
			$('a.prev').hide();	
		}else{
			$('a.prev').show();		
		}

		if(right == nbPage || left == nbPage){
			$('a.next').hide();	
		}else{
			$('a.next').show();		
		}
		
			
	}
	
	
};



$(document).ready(LBC.init);
