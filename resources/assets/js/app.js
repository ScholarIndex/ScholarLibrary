
$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

var LBC = {

	init : function(){
	
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
		}	 


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
			$(this).toggleClass('checked');
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
			if($(this).hasClass('journal')) return;
			
			if($(this).hasClass('article'))
				document.location = '/article/overview/'+$(this).attr('data-oid');
			
			if($(this).attr('data-bid') != "")
				document.location = '/document/overview/'+$(this).attr('data-bid');
		});
		
		$(document).on('click', '.fa-arrow-circle-o-right', function(){
			if($(this).closest('.card').attr('data-bid') != "")
				document.location = '/document/overview/'+$(this).closest('.card').attr('data-bid')+'/'+$(this).closest('.card').find('select').val();
			
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

		 
		SEARCH.doSearch();
	},
	
	refreshFilters : function(){

		var filtrs = {};	
		$('.sub').each(function(){
			var sub = $(this);
			filtrs[sub.attr('data-field')] = [];
			
			$(this).find('li.checked').each(function(){
				filtrs[sub.attr('data-field')].push($(this).attr('data-key'));
			});
		});

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
	
	doSearch : function(){
		
		SEARCH.refreshFilters();
		
		var filtrs = {};	
		$('.sub').each(function(){
			var sub = $(this);
			filtrs[sub.attr('data-field')] = [];
			
			$(this).find('li.checked').each(function(){
				filtrs[sub.attr('data-field')].push($(this).attr('data-key'));
			});
		});

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
	        	'filtrs' : filtrs 
	        },
	        dataType: "json",
	        success: function (data) {
	        	$('.docs span').html(data.response.response.numFound);	
	        	$('.cat.monograph span').html(data.response.facet_counts.facet_fields.ns[_SOLR_ROOT_+'.bibliodb_books']);
	        	$('.cat.article span').html(data.response.facet_counts.facet_fields.ns[_SOLR_ROOT_+'.bibliodb_articles']);
	        	$('.cat.journal span').html(data.response.facet_counts.facet_fields.ns[_SOLR_ROOT_+'.bibliodb_journals']);
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
	        	'filtrs' : filtrs 
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
				case 'journal': return '<a href="/document/journal/'+this.object.bid+'">'+this.object.full_title+'</a>' || '';  break;
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
				        url: "/document/page/references/"+div.attr('data-oid')+"/"+$('body').attr('data-bid')+"/"+$('body').attr('data-issue'),
				        type: "GET",
				        dataType: "json",
				        success: function (data) {
							for(var ref in data){
				            	var token = data[ref]['contents'][1];
								div.find('span[_st='+token['start']+']').after('<em data-ref="'+ref+'" class="'+data[ref]['ref_type']+'"></em>');
								REF.allRefs[ref] = data[ref];
							}
							var heights = [];
							div.find('em').each(function(){
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
			
			$.ajax({
		        url: "/document/page/reference/"+ref,
		        type: "GET",
		        dataType: "html",
		        success: function (data) {
		            $('.refDetails').html(data);
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
		
		
		$(document).on('keyup','.author input.name' ,function(){
			var dropdown = $(this).closest('td').find('.authorDropdown');
			var val = $(this).val();
			dropdown.html('');
			if(val.length >=3){
				$.ajax({
			        url: "/search/authors/"+encodeURI(val),
			        type: "GET",
			        dataType: "html",
			        success: function (data) {
			            dropdown.html(data);
			        }
			    });	
			}
		});
		
		$(document).on('click', '.authorDropdown ul.bdb li', function(){
			var parent = $(this).closest('td');
			var input = parent.find('input.name');
			var viaf = parent.find('input.viaf');
			input.val($(this).text());
			viaf.val('');
			parent.find('.authorDropdown').html('');
		});


		$(document).on('click', '.authorDropdown ul.viaf li:not(.load)', function(){
			var parent = $(this).closest('td');
			var input = parent.find('input.name');
			var viaf = parent.find('input.viaf');
			input.val($(this).text());
			viaf.val($(this).attr('data-viafid'));
			parent.find('.authorDropdown').html('');
		});

		$(document).on('click', 'ul.viaf li.load', function(){
			var input = $(this).closest('td').find('input.name');
			
			var ul = $(this).closest('ul');
			$.ajax({
	        	url: "/search/viafAuthors/"+encodeURI(input.val()),
	        	type: "GET",
	        	dataType: "html",
	        	success: function (data) {
	        	    ul.html(data);
	        	}
			});
		});
		
	},
	
	prev : function() {TOC.goPage(TOC.page-2);},
	next : function() {TOC.goPage(TOC.page+2);},
	goPage : function(n) {
		if(n % 2 == 0) n--;

		nbPage = parseInt($('body').attr('data-pagecount'),10);
		bid = $('body').attr('data-bid');
		issue = $('body').attr('data-issue');

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
	        	tileSources: "http://dhlabsrv4.epfl.ch/iiif_lbc/"+bid+"::"+issue+"::"+left+"/info.json",
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
	        	tileSources: "http://dhlabsrv4.epfl.ch/iiif_lbc/"+bid+"::"+issue+"::"+right+"/info.json",
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
	
	
}



$(document).ready(LBC.init);
