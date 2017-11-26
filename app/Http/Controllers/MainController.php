<?php

namespace App\Http\Controllers;
use View;
use Validator;
use Input;
use Redirect;
use Auth;
use Hash;
use Log;
use App\User;
use Mail;
use Session;
use Cache;
use App\Document;
use App\Metadata;
use App\Page;
use App\BibliodbAuthor;
use App\BibliodbArticle;
use App\BibliodbJournal;
use App\BibliodbAsve;
use App\BibliodbBook;
use App\Disambiguation;
use App\Reference;
use bheller\ImagesGenerator\ImagesGeneratorProvider;
use Illuminate\Http\Request;

define("LRBB" , env('SOLR_ROOT').'.bibliodb_books');
define("LRBA" , env('SOLR_ROOT').'.bibliodb_articles');
define("LRBJ" , env('SOLR_ROOT').'.bibliodb_journals');
define("LRBC" , env('SOLR_ROOT').'.bibliodb_contributions');
define("LRBI" , env('SOLR_ROOT').'.bibliodb_issues');
define("LRP" , env('SOLR_ROOT').'.pages');

class MainController extends Controller
{

		public function __construct(){
		
		}
		private $solrconnection = null;

        public function welcome()
        {
            return View::make('welcome', []);
        }

		public function search()
		{
			Session::forget('lastQuery');
			return $this->lastSearch();
		}

		public function lastSearch()
		{
			
			return View::make('search', ['hasTopMenu' => 'hasTopMenu', 'dataJs' => 'SEARCH', 'data' => Session::get('lastQuery', [])]);
		}

		public function about()
		{
			return View::make('about');
		}
	
		public function connectSolr(){
	
			if($this->solrconnection == null){
				define('SOLR_SERVER_HOSTNAME', 'dhlabsrv5.epfl.ch');
				define('SOLR_SECURE', true);
				define('SOLR_PATH', env('SOLR_CORE'));
				define('SOLR_SERVER_PORT', 8983);
				define('SOLR_SERVER_USERNAME', '');
				define('SOLR_SERVER_PASSWORD', '');
				define('SOLR_SERVER_TIMEOUT', 10);
	
				$options = array
				(
					'path' => SOLR_PATH,
					'hostname' => SOLR_SERVER_HOSTNAME,
					'login'    => SOLR_SERVER_USERNAME,
					'password' => SOLR_SERVER_PASSWORD,
					'port'     => SOLR_SERVER_PORT
				);
				$this->solrconnection = new \SolrClient($options);
			}	
		}
	
		public function searchPost(Request $request){
			

			$q = $request->q;
			$ns = $request->ns;
			$page = $request->page;
			$in = $request->in;
			$filtrs = $request->filtrs;

			Session::put('lastQuery', ['q' => $q, 'ns' => $ns, 'page' => $page, 'in' => $in]);

			$this->connectSolr();
			$partials = [];
			if($in['authors'] == 'true')	$partials[] = "authors:(*".$q."*)";
			if($in['titles'] == 'true')	$partials[] = "title:(*".$q."*)";
			if($in['publishers'] == 'true')	$partials[] = "publisher:(*".$q."*)";

			$qry = ($q != "" && count($partials)>0) ? implode(" OR ",$partials) : '*:*';	
			$qry = "(".$qry.")";
			if(count($ns)==1){
				switch($ns[0]){
					case 'monograph' : 		$coll = LRBB; break;
					case 'article' : 		$coll = LRBA; break;
					case 'journal' : 		$coll = LRBJ; break;
					case 'contribution' : 	$coll = LRBC; break;
				}
				if(isset($coll)){
					if($coll == LRBB)
						$qry = "(ns:".$coll." AND provenance:lbcatalogue) AND ( ".$qry." )";
					else
						$qry = "ns:".$coll." AND ( ".$qry." )";
				}
			}else{
				$qry = $qry ." AND ((ns:".LRBB." AND provenance:lbcatalogue) OR ns:".LRBA." OR ns:".LRBJ." OR ns:".LRBC.")";
			}

			if(count($filtrs)>0){
				foreach($filtrs as $field => $keys){
					$cond = [];
					foreach($keys as $k){
						$cond[] = $field.":".$k;
					}
					$qry = $qry . " AND (".implode(' OR ', $cond).")";
				}	
			}
			$query = new \SolrQuery;
			$query->setQuery($qry);
			$query->setStart(12 * ($page-1));
			$query->setRows(12);
			$query->addField('_id');
			$query->addField('ns');
			$query->addField('score');
			$query->setFacet(true);
			$query->addFacetField('ns');
			$query_response = $this->solrconnection->query($query);
			$response = $query_response->getResponse();
			$results = array();
			if($response->response->numFound>0){
				foreach($response->response->docs as $doc){
					switch($doc->ns){
						case LRBB :
							$d = BibliodbBook::where('_id', $doc->_id)->first();
							$d->_type_ = 'monograph'; 	
							$d->_meta_ = Metadata::where('bid', $d->bid)->first();	
							break;
						case LRBA : 	
							$d = BibliodbArticle::where('_id', $doc->_id)->first();
							$d->_type_ = 'article';	$d->_journal_ = BibliodbJournal::where('bid', $d->journal_bid)->first();
							break;
						case LRBJ :
							$d = BibliodbJournal::where('_id', $doc->_id)->first();
							$d->_type_ = 'journal';
							$d->_meta_ = Metadata::where('bid', $d->bid)->first();
							break;
						case LRBC :
							$d = new \stdClass;
							$d->_type_ = 'contribution';
							break;
						
					}
					$results[$doc->_id] = $d;
				}	
			}


			return json_encode(array('response' => $response, 'results' => $results));


		}

		public function countPost(Request $request){
			

			$q = $request->q;
			$in = $request->in;
			$filtrs = $request->filtrs;

			$this->connectSolr();
			$partials = [];
			if($in['authors'] == 'true')	$partials[] = "authors:(*".$q."*)";
			if($in['titles'] == 'true')	$partials[] = "title:(*".$q."*)";
			if($in['publishers'] == 'true')	$partials[] = "publisher:(*".$q."*)";

			$qry = ($q != "" && count($partials)>0) ? implode(" OR ",$partials) : '*:*';	
			$qry = "(".$qry.")";
			$qry = $qry ." AND ((ns:".LRBB." AND provenance:lbcatalogue) OR ns:".LRBA." OR ns:".LRBJ." OR ns:".LRBC.")";

			if(count($filtrs)>0){
				foreach($filtrs as $field => $keys){
					$cond = [];
					foreach($keys as $k){
						$cond[] = $field.":".$k;
					}
					$qry = $qry . " AND (".implode(' OR ', $cond).")";
				}	
			}
			$query = new \SolrQuery;
			$query->setQuery($qry);
			$query->addField('_id');
			$query->addField('ns');
			$query->addField('score');
			$query->setFacet(true);
			$query->addFacetField('ns');
			$query_response = $this->solrconnection->query($query);
			$response = $query_response->getResponse();
			$results = array();
	
			return json_encode(array('response' => $response, 'results' => $results));


		}

		public function documentOverview($bid, $issue = null)
		{
			if($issue == null)
				$d = Document::where('bid',$bid)->first();
			else
				$d = Document::where('bid',$bid)->where('number', $issue)->first();

			if( ! $d){abort(404);}
			$o = array();
			$o['hasTopMenu'] = '';
			$o['overviewActive'] = 'active';
			$o['dataJs'] = 'OVERVIEW';
			$o['bid'] = $bid;
			if($issue != null)
				$o['issue'] = $issue;
			$o['document'] = $d;
			$o['documentId'] = $d->id;
			$o['metadata'] = $d->metadata;
			switch($d->metadata->type_document){
				case 'journal' : 
					$o['bibliodb'] = BibliodbJournal::where('bid', $bid)->first(); 
					$o['first_index_page'] = Page::where('document_id', new \MongoId($d->_id))->where('in_index', true)->orderBy('single_page_file_number', 'asc')->first();
					$o['articles'] = BibliodbArticle::where('document_id', new \MongoId($d->_id))->get();
					break;	
				case 'monograph' :
					$o['first_index_page'] = Page::where('document_id', new \MongoId($d->_id))->where('in_index', true)->orderBy('single_page_file_number', 'asc')->first();
					$o['bibliodb'] = BibliodbBook::where('bid', $bid)->first();
					$o['articles'] = [];
			}
			return View::make('document.overview', $o);
		}

		public function documentJournal($bid)
		{
			$m = Metadata::where('bid', $bid)->first();
			if( ! $m){abort(404);}
			$o = array();
			$o['hasTopMenu'] = '';
			$o['overviewActive'] = 'active';
			$o['bid'] = $bid;
			$o['metadata'] = $m;
			$o['dataJs'] = 'JOURNAL';
			$o['document'] = new \stdClass;
			$o['document']->type = 'journal';
			$o['bibliodb'] = BibliodbJournal::where('bid', $bid)->first();
			
			$o['yearMin'] = 9999;			
			$o['yearMax'] = 0;

			foreach($m->issues as $i){
				if(isset($i['year'])){
					if($i['year'] > $o['yearMax']) $o['yearMax'] = $i['year'];
					if($i['year'] < $o['yearMin']) $o['yearMin'] = $i['year'];
				}
			}
			return View::make('document.journal', $o);
		}

		public function documentScans($bid, $issue = null)
		{
			if($issue == null)
				$d = Document::where('bid',$bid)->first();
			else
				$d = Document::where('bid',$bid)->where('number', $issue)->first();
	
			if( ! $d){abort(404);}
			$pages = array();

			foreach($d->pages as $p){
				$pa = Page::where('_id', $p)->first(['fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids']);
				$pages[$pa->single_page_file_number] = $pa;				
			}
			ksort($pages);

			$faker = \Faker\Factory::create();
			$faker->addProvider(new ImagesGeneratorProvider($faker));
			$i = $faker->imageGenerator(null, 120, 140, 'jpg', true, null, '#ececec');
			$image = "data:image/png;base64,".base64_encode(file_get_contents($i));
			unlink($i);

			$o = array();
			$o['hasTopMenu'] = '';
			$o['scansActive'] = 'active';
			$o['dataJs'] = 'SCANS';
			$o['bid'] = $bid;
			if($issue != null)
				$o['issue'] = $issue;
			$o['document'] = $d;
			$o['metadata'] = $d->metadata;
			$o['pages'] = $pages;
			$o['fakeimage'] = $image;
			return View::make('document.scans', $o);
		}

		public function documentViewer($bid, $issue = null)
		{
			if($issue == null)
				$d = Document::where('bid',$bid)->first();
			else
				$d = Document::where('bid',$bid)->where('number', $issue)->first();
	
			if( ! $d){abort(404);}
			$pages = array();

			foreach($d->pages as $p){
				$pa = Page::where('_id', $p)->first(['fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids']);
				$pages[$pa->single_page_file_number] = $pa;				
			}
			ksort($pages);

			$faker = \Faker\Factory::create();
			$faker->addProvider(new ImagesGeneratorProvider($faker));
			$i = $faker->imageGenerator(null, 80, 100, 'jpg', true, null, '#ececec');
			$image = "data:image/png;base64,".base64_encode(file_get_contents($i));
			unlink($i);

			$o = array();
			$o['hasTopMenu'] = '';
			$o['viewerActive'] = 'active';
			$o['dataJs'] = 'VIEWER';
			$o['type'] = $d->type;
			
			$o['bid'] = $bid;


			switch($d->metadata->type_document){
				case 'journal' : 
					$o['bibliodb'] = BibliodbJournal::where('bid', $bid)->first(); 
					break;	
				case 'monograph' :
					$o['bibliodb'] = BibliodbBook::where('bid', $bid)->first();
			}
			$o['provenance'] = $o['bibliodb']->digitization_provenance;
		
				$o['issue'] = $issue;
			$o['document'] = $d;
			$o['metadata'] = $d->metadata;
			$o['pages'] = $pages;
			$o['fakeimage'] = $image;
			return View::make('document.viewer', $o);
		}

		public function documentReferences($bid, $issue = null)
		{
			if($issue == null)
				$d = Document::where('bid',$bid)->first();
			else
				$d = Document::where('bid',$bid)->where('number', $issue)->first();

			if( ! $d){abort(404);}
			$pages = array();

			foreach($d->pages as $p){				
				$pa = Page::find($p);
				$pages[$pa->single_page_file_number] = $pa->_id;
			}

			ksort($pages);

			$o = array();
			$o['documentId'] = $d->id;
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'REF';
			$o['referencesActive'] = 'active';
			$o['pagecount'] = count($pages);
			$o['bid'] = $bid;
			if($issue != null)
				$o['issue'] = $issue;
			$o['document'] = $d;
			$o['metadata'] = $d->metadata;
			$o['pages'] = $pages;
			return View::make('document.references', $o);
		}

		public function documentReference($oid)
		{
			$o = array();


			$ref = Reference::where('_id', $oid)->first();
			$o['ref'] = $ref;

			if($ref->ref_type=='primary' || $ref->ref_type=='secondary'){
				$dis = Disambiguation::where('reference', new \MongoId($oid))->where('provenance', 'processing')->first();
				$o['dis'] = $dis;
	
				if($dis){
					if($ref->ref_type=='primary'){
						$asve = BibliodbAsve::where('_id', new \MongoId($dis->archival_document))->first();
						$o['asve'] = $asve;
					}else{
						$book = BibliodbBook::where('_id', new \MongoId($dis->book))->first();
						$o['book'] = $book;
					}
				
				}
			}

			return View::make('document.partials.reference', $o);
		}

		public function documentTextSearch(Request $request){

			$search = $request->search;
			$document_id = $request->document_id;

			$this->connectSolr();
	
			$qry = "ns:(".LRP.") AND fulltext:(*".$search."*) AND document_id:(".$document_id.")";	
			
			$query = new \SolrQuery;
			$query->setQuery($qry);
			$query->setStart(0);
			$query->setRows(99999);
			$query->addField('_id');
			$query->addField('single_page_file_number');
			$query_response = $this->solrconnection->query($query);
			$response = $query_response->getResponse();

			$pages = [];
			if($response->response->numFound > 0){
				foreach($response->response->docs as $p)
					$pages[] = (int)$p->single_page_file_number;
			}
			sort($pages);
			return json_encode($pages);
		}

		public function pageReferences($oid, $bid, $issue = ""){

			$pa = Page::find($oid);
		
			if(Cache::has('_references_'.$pa->document_id)){
				$references = Cache::get('_references_'.$pa->document_id);
			}else{		
				$references = array();
	
				$refs = Reference::where('document_id', $pa->document_id)->get();
						
				
				foreach($refs as $r){
					if(count($r->contents)>1){
						foreach($r->contents as $c){
							if( ! isset($references[$c['single_page_file_number']])) $references[$c['single_page_file_number']] = array();
							$references[$c['single_page_file_number']][$r->_id] = $r;
						}
					}
				}
				ksort($references);
					
				Cache::put('_references_'.$pa->document_id, $references,1440);
			}
			if(isset($references[$pa->single_page_file_number]))
				return json_encode($references[$pa->single_page_file_number]);
			else
				return json_encode(array());
		}

		public function pageText($oid){

			$pa = Page::find($oid);
			$txt = "";
			$fid = ' ';
			$tokens = array();
			foreach($pa->lines as $l){
				$txt .= "<p>";
				foreach($l['tokens'] as $t){				
					//$tokens[$t['offset_start']] = '<span _st='.$t['offset_start'].' _en='.$t['offset_end'].'>'.$t['surface'].$fid.'</span>';
					$txt .= '<span _st='.$t['offset_start'].' _en='.$t['offset_end'].'>'.$t['surface'].$fid.'</span>';
				}
				//$tokens[$t['offset_start']] .= "<br />";
				$txt .= "</p>";
			}

			//$txt .= implode('',$tokens);
			return View::make('document.partials.pageText', array('txt' => $txt, 'pageobj' => $pa));
		}

		public function documentToc($bid, $issue = null)
		{
			if($issue == null)
				$d = Document::where('bid',$bid)->first();
			else
				$d = Document::where('bid',$bid)->where('number', $issue)->first();

			if( ! $d){abort(404);}
			$pages = array();

			foreach($d->pages as $p){
				$pa = Page::where('_id', $p)->first(['fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids']);
				$pages[$pa->single_page_file_number] = $pa;				
			}
			ksort($pages);

			if($d->type=='journal_issue'){
				// Get articles
				$articles = BibliodbArticle::where('document_id', new \MongoId($d->_id))->get();
			}

			if($d->type=='monograph'){
				// Get contributions
			}

			$o = array();
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'TOC';
			$o['pagecount'] = count($pages);
			$o['tocActive'] = 'active';
			$o['bid'] = $bid;
			if($issue != null)
				$o['issue'] = $issue;
			$o['document'] = $d;
			$o['metadata'] = $d->metadata;
			$o['pages'] = $pages;
			$o['articles'] = $articles;

			return View::make('document.toc', $o);
		}

		public function searchAuthors($term){
			$authors = BibliodbAuthor::where('author_final_form', 'like', '%'.$term.'%')->orderBy('author_final_form', 'asc')->get();
			return View::make('document.partials.authors', array('authors' => $authors));
		}

		public function searchViafAuthors($term){
			$auth = [];

			$authors = json_decode(file_get_contents('http://viaf.org/viaf/search?query='.urlencode('local.personalNames all "'.$term.'"').'&maximumRecords=10&httpAccept=application/json&recordSchema=BriefVIAF'));
			foreach($authors->searchRetrieveResponse->records as $a){
				
				if(is_array($a->record->recordData->{'v:mainHeadings'}->data))
					$auth[$a->record->recordData->viafID->{'#text'}] = $a->record->recordData->{'v:mainHeadings'}->data[0]->text;
				else
					$auth[$a->record->recordData->viafID->{'#text'}] = $a->record->recordData->{'v:mainHeadings'}->data->text;		
			}

			return View::make('document.partials.viafAuthors', array('authors' => $auth));
		}

		public function articleOverview($oid)
		{

			$a = BibliodbArticle::where('_id', new \MongoID($oid))->first();
			if( ! $a){abort(404);}
			
			
			
			$o = array();
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'ARTICLEOVERVIEW';
			$o['overviewActive'] = 'active';
			$o['article'] = $a;

			list($bid, $number, $n) = explode(':',$a->internal_id);
			$o['next'] = BibliodbArticle::where('internal_id', $bid.":".$number.":".($n+1))->first();
			$o['prev'] = BibliodbArticle::where('internal_id', $bid.":".$number.":".($n-1))->first();


			$o['oid'] = $oid;
			return View::make('document.article.overview', $o);
		}


		public function articleScans($oid)
		{
			$a = BibliodbArticle::where('_id', new \MongoID($oid))->first();
			if( ! $a){abort(404);}

			$d = Document::where('_id',new \MongoID($a->document_id))->first();

	
			$pages = array();

			foreach($d->pages as $p){
				
				$pa = Page::where('_id', $p)->first(['fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids']);
				if($pa->single_page_file_number >= $a->start_img_number && $pa->single_page_file_number <= $a->end_img_number)
					$pages[$pa->single_page_file_number] = $pa;				
			}
			ksort($pages);

			$faker = \Faker\Factory::create();
			$faker->addProvider(new ImagesGeneratorProvider($faker));
			$i = $faker->imageGenerator(null, 120, 140, 'jpg', true, null, '#ececec');
			$image = "data:image/png;base64,".base64_encode(file_get_contents($i));
			unlink($i);

			$o = array();
			$o['hasTopMenu'] = '';
			$o['scansActive'] = 'active';
			$o['pages'] = $pages;
			$o['article'] = $a;
			$o['document'] = $d;
			$o['oid'] = $oid;
			$o['bid'] = $d->bid;
			$o['issue'] = $d->number;
			$o['fakeimage'] = $image;
			return View::make('document.article.scans', $o);
		}



		public function articleViewer($oid)
		{
			$a = BibliodbArticle::where('_id', new \MongoID($oid))->first();
			if( ! $a){abort(404);}
			$d = Document::where('_id',new \MongoID($a->document_id))->first();

	
			$pages = array();

			foreach($d->pages as $p){
				
				$pa = Page::where('_id', $p)->first(['fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids']);
				if($pa->single_page_file_number >= $a->start_img_number && $pa->single_page_file_number <= $a->end_img_number)
					$pages[$pa->single_page_file_number] = $pa;				
			}
			ksort($pages);

			$faker = \Faker\Factory::create();
			$faker->addProvider(new ImagesGeneratorProvider($faker));
			$i = $faker->imageGenerator(null, 80, 100, 'jpg', true, null, '#ececec');
			$image = "data:image/png;base64,".base64_encode(file_get_contents($i));
			unlink($i);

			$o = array();
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'VIEWER';
			$o['viewerActive'] = 'active';
			$o['pages'] = $pages;
			$o['article'] = $a;
			$o['document'] = $d;
			$o['oid'] = $oid;
			$o['bid'] = $d->bid;
			$o['issue'] = $d->number;
			$o['fakeimage'] = $image;
			return View::make('document.article.viewer', $o);
		}


		public function articleReferences($oid)
		{


			$a = BibliodbArticle::where('_id', new \MongoID($oid))->first();
			if( ! $a){abort(404);}
			$d = Document::where('_id',new \MongoID($a->document_id))->first();

			$pages = array();

			foreach($d->pages as $p){
				
				$pa = Page::where('_id', $p)->first(['fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids']);
				if($pa->single_page_file_number >= $a->start_img_number && $pa->single_page_file_number <= $a->end_img_number)
					$pages[$pa->single_page_file_number] = $pa->_id;				
			}

			ksort($pages);

			$o = array();
			$o['documentId'] = $d->id;
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'REF';
			$o['referencesActive'] = 'active';
			$o['pagecount'] = count($pages);
			$o['bid'] = $d->bid;
			$o['issue'] = $d->number;
			$o['document'] = $d;
			$o['pages'] = $pages;
			$o['oid'] = $oid;
			$o['article'] = $a;
			return View::make('document.article.references', $o);
		}


		public function filters(Request $request){
			

			$q = $request->q;
			$ns = $request->ns;
			$in = $request->in;
			$field = $request->field;
			$filtrs = $request->filtrs;

			$this->connectSolr();
			$partials = [];
			if($in['authors'] == 'true')	$partials[] = "authors:(*".$q."*)";
			if($in['titles'] == 'true')	$partials[] = "title:(*".$q."*)";
			if($in['publishers'] == 'true')	$partials[] = "publisher:(*".$q."*)";

			$qry = ($q != "" && count($partials)>0) ? implode(" OR ",$partials) : '*:*';	
			$qry = "(".$qry.")";


			if(count($ns)==1){
				switch($ns[0]){
					case 'monograph' : 		$coll = LRBB; break;
					case 'article' : 		$coll = LRBA; break;
					case 'journal' : 		$coll = LRBJ; break;
					case 'contribution' : 	$coll = LRBC; break;
				}
				if(isset($coll)){
					if($coll == LRBB)
						$qry = "(ns:".$coll." AND provenance:lbcatalogue) AND ( ".$qry." )";
					else
						$qry = "ns:".$coll." AND ( ".$qry." )";
				}
			}else{
				$qry = $qry ." AND ((ns:".LRBB." AND provenance:lbcatalogue) OR ns:".LRBA." OR ns:".LRBJ." OR ns:".LRBC.")";
			}



			$query = new \SolrQuery;
			$query->setQuery($qry);
			$query->setStart(0);
			$query->setRows(0);
			$query->setFacet(true);
			$query->addFacetField($field);
			$query_response = $this->solrconnection->query($query);
			$response = $query_response->getResponse();
	
			return View::make('filters', array('field' => $field,'filtrs' => $filtrs, 'response' => $response->facet_counts->facet_fields->{$field}));


		}

		public function documentProgress($document_id){

				
			$countRefs = 	Disambiguation::where('document_id', new \MongoId($document_id))->where('type', 'reference_disambiguation')->count();
			$countChecked = Disambiguation::where('document_id', new \MongoId($document_id))->where('type', 'reference_disambiguation')->where('checked', true)->count();

			return View::make('document.partials.progress', array('countRefs' => $countRefs, 'countChecked' => $countChecked));
		}


}
