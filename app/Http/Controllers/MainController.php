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
use App\History;
use App\Bookmark;
use App\BibliodbAuthor;
use App\BibliodbArticle;
use App\BibliodbJournal;
use App\BibliodbAsve;
use App\BibliodbBook;
use App\Disambiguation;
use App\Reference;
use bheller\ImagesGenerator\ImagesGeneratorProvider;
use Illuminate\Http\Request;
use PiwikTracker;

define("LRBB" , env('SOLR_ROOT').'.bibliodb_books');
define("LRBA" , env('SOLR_ROOT').'.bibliodb_articles');
define("LRBJ" , env('SOLR_ROOT').'.bibliodb_journals');
define("LRBC" , env('SOLR_ROOT').'.bibliodb_contributions');
define("LRBI" , env('SOLR_ROOT').'.bibliodb_issues');
define("LRP" , env('SOLR_ROOT').'.pages');


class MainController extends Controller
{
		
		public function __construct(){
			$key = env('MATOMO_APIKEY', '');
			if($key !== ""){
				$this->piwikTracker = new PiwikTracker($idSite = env('MATOMO_SITEID',-1), $apiUrl = env('MATOMO_URL',''));
				$this->piwikTracker->setTokenAuth($key);
			}		
		}
		private $solrconnection = null;
		private $piwikTracker = null;
        public function welcome()
        {
			if(Auth::check()){
				 return redirect('search');
			}else{
				if(!is_null($this->piwikTracker)){
					$this->piwikTracker->doTrackPageView("Homepage");
				}
				return View::make('welcome', array());
			}
		}

		public function search()
		{
			Session::forget('lastQuery');
			return $this->lastSearch();
		}

		public function lastSearch()
		{
			
			return View::make('search', array('hasTopMenu' => 'hasTopMenu', 'dataJs' => 'SEARCH', 'data' => Session::get('lastQuery', array())));
		}

		public function about()
		{
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->doTrackPageView("About");
			}
			return View::make('about');
		}
	
		public function connectSolr(){
	
			if($this->solrconnection == null){
				define('SOLR_SERVER_HOSTNAME', env('SOLR_HOST'));
				define('SOLR_SECURE', true);
				define('SOLR_PATH', env('SOLR_CORE'));
				define('SOLR_SERVER_PORT', env('SOLR_PORT'));
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
			$sort = $request->sort;

			Session::put('lastQuery', array('q' => $q, 'ns' => $ns, 'page' => $page, 'in' => $in));

			$this->connectSolr();
			$partials = array();
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

			if(is_array($filtrs) && count($filtrs)>0){
				foreach($filtrs as $field => $keys){
					$cond = array();
					foreach($keys as $k){
						$cond[] = $field.":\"".$k."\"";
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
			
			switch($sort){
				case 'score': 					$query->addSortField('score', \SolrQuery::ORDER_DESC); break;
				case 'title': 					$query->addSortField('title', \SolrQuery::ORDER_ASC); break;
				case 'year_asc':	$query->addSortField('year', \SolrQuery::ORDER_ASC); break;
				case 'year_desc':	$query->addSortField('year', \SolrQuery::ORDER_DESC); break;
			}
		
			
			\Log::error($qry);
			$query_response = $this->solrconnection->query($query);
			$response = $query_response->getResponse();

			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->doTrackSiteSearch($q, join(',',$ns), $response->response->numFound);
				$this->piwikTracker->doTrackPageView("Search");
			}
			
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
							$d->_type_ = 'article';
							$d->_journal_ = BibliodbJournal::where('bid', $d->journal_bid)->first();
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
			$partials = array();
			if($in['authors'] == 'true')	$partials[] = "authors:(*".$q."*)";
			if($in['titles'] == 'true')	$partials[] = "title:(*".$q."*)";
			if($in['publishers'] == 'true')	$partials[] = "publisher:(*".$q."*)";

			$qry = ($q != "" && count($partials)>0) ? implode(" OR ",$partials) : '*:*';	
			$qry = "(".$qry.")";
			$qry = $qry ." AND ((ns:".LRBB." AND provenance:lbcatalogue) OR ns:".LRBA." OR ns:".LRBJ." OR ns:".LRBC.")";

			if(is_array($filtrs) && count($filtrs)>0){
				foreach($filtrs as $field => $keys){
					$cond = array();
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

		public function yearChart(Request $request){

			$q = $request->q;
			$in = $request->in;
			$filtrs = $request->filtrs;

			$this->connectSolr();
			$partials = array();
			if($in['authors'] == 'true')	$partials[] = "authors:(*".$q."*)";
			if($in['titles'] == 'true')	$partials[] = "title:(*".$q."*)";
			if($in['publishers'] == 'true')	$partials[] = "publisher:(*".$q."*)";

			$qry = ($q != "" && count($partials)>0) ? implode(" OR ",$partials) : '*:*';	
			$qry = "(".$qry.")";
			$qry = $qry ." AND ((ns:".LRBB." AND provenance:lbcatalogue) OR ns:".LRBA." OR ns:".LRBJ." OR ns:".LRBC.")";


			$query = new \SolrQuery;
			$query->setQuery($qry);
			$query->addField('_id');
			$query->addField('ns');
			$query->addField('score');
			$query->setFacet(true);
			$query->addFacetField('year');
			$query->setFacetSort(\SolrQuery::FACET_SORT_INDEX);
			$query->setFacetMinCount(1);
			$query->setFacetLimit(-1);
			$query_response = $this->solrconnection->query($query);
			$response = $query_response->getResponse();
			$results = array();
	

			$rs = (array)$response->facet_counts->facet_fields->year;

			if( count($rs) == 0)
				return json_encode(array());

			$results = array();	
			
			foreach($rs as $y => $v)
				$results[$y] = $v;
			$allYears = array_keys($results);
			

			$data = array();
			$data['minYear'] = min($allYears);
			$data['maxYear'] = max($allYears);
			$data['minVal'] = min($results);
			$data['maxVal'] = max($results);
			$data['selectedMin'] = isset($filtrs['year']['mindate']) ? $filtrs['year']['mindate'] : min($allYears);
			$data['selectedMax'] = isset($filtrs['year']['maxdate']) ? $filtrs['year']['maxdate'] : max($allYears);
			$data['values'] = array();
			

			for($y = $data['minYear']; $y <= $data['maxYear']; $y++)
				$data['values'][$y] = isset($results[$y]) ? $results[$y] : 0;

			$data['val'] = array_values($data['values']);
			$data['orig'] = $response->facet_counts->facet_fields->year;
			return json_encode($data);


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
					$o['first_index_page'] = Page::where('document_id', new \MongoDB\BSON\ObjectID($d->_id))->where('in_index', true)->orderBy('single_page_file_number', 'asc')->first();
					$o['articles'] = BibliodbArticle::where('document_id', new \MongoDB\BSON\ObjectID($d->_id))->get();
					break;	
				case 'monograph' :
					$o['first_index_page'] = Page::where('document_id', new \MongoDB\BSON\ObjectID($d->_id))->where('in_index', true)->orderBy('single_page_file_number', 'asc')->first();
					$o['bibliodb'] = BibliodbBook::where('bid', $bid)->first();
					$o['articles'] = array();
			}
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $d->id, "page");
				$this->piwikTracker->doTrackPageView("DocumentOverview");
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
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $bid, "page");
				$this->piwikTracker->doTrackPageView("DocumentJournal");
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
				$pa = Page::where('_id', $p)->first(array('fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids'));
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
			$o['documentId'] = $d->id;
			$o['pages'] = $pages;
			$o['fakeimage'] = $image;
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $d->id, "page");
				$this->piwikTracker->doTrackPageView("DocumentScans");
			}			
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
				$pa = Page::where('_id', $p)->first(array('fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids'));
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
			$o['documentId'] = $d->id;
			$o['pages'] = $pages;
			$o['fakeimage'] = $image;
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $d->id, "page");
				$this->piwikTracker->doTrackPageView("DocumentViewer");
			}			
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

			
			$pa = Page::where('document_id', new \MongoDB\BSON\ObjectID($d->id))->get(array('_id', 'single_page_file_number'));
			foreach($pa as $p){
				$pages[$p->single_page_file_number] = $p->id;
			}
			#foreach($d->pages as $p){				
			#	$pa = Page::where('_id', $p)->first(['single_page_file_number']);
			#	$pages[$pa->single_page_file_number] = $p;
			#}
			
			ksort($pages);

			$o = array();
			
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'REF';
			$o['referencesActive'] = 'active';
			$o['pagecount'] = count($pages);
			$o['bid'] = $bid;
			if($issue != null)
				$o['issue'] = $issue;
			$o['document'] = $d;
			$o['documentId'] = $d->id;
			$o['metadata'] = $d->metadata;
			$o['pages'] = $pages;
			
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $d->id, "page");
				$this->piwikTracker->doTrackPageView("DocumentReferences");
			}			
			return View::make('document.references', $o);
		}

		public function documentReference($oid)
		{
			$o = array();


			$ref = Reference::where('_id', $oid)->first();
			$o['ref'] = $ref;

			if($ref->ref_type=='primary' || $ref->ref_type=='secondary'){
				$dis = Disambiguation::where('reference', new \MongoDB\BSON\ObjectID($oid))->where('type', 'reference_disambiguation')->first();
				$o['dis'] = $dis;
	
				if($dis){
					if($ref->ref_type=='primary'){
						$asve = BibliodbAsve::where('_id', new \MongoDB\BSON\ObjectID($dis->archival_document))->first();
						$o['asve'] = $asve;
					}else{
						$book = BibliodbBook::where('_id', new \MongoDB\BSON\ObjectID($dis->book))->first();
						$o['book'] = $book;
						#$o['authors'] = array();
						#$disauth =  Disambiguation::where('book', new \MongoDB\BSON\ObjectID($book->id))->where('type', 'author_of_disambiguation')->get();
						#foreach($disauth as $dA)
						#	$o['authors'][] = BibliodbAuthor::where('_id', new \MongoDB\BSON\ObjectID($dA['author']))->first();  
						#
						#	in partial/reference.blade.php if book 
						#		@if(count($authors)) <tr class="data"><th>Author(s)</th>		<td>{!!implode('<br />', array_map(function ($author) { return $author->author_final_form; } , $authors))!!}</td><td></td></tr> @endif
						#
					}
				
				}
			}

			return View::make('document.partials.reference', $o);
		}

		public function saveRefDisValid(Request $request){
			
			if( ! in_array('editor',Auth::user()->roles)){
				$o = new \stdClass;
            			$o->result = "credentials_error";
            			return response()->json($o,401);
			}
			
			
			$refId = $request->reference;
			
			$dis = Disambiguation::where('reference', new \MongoDB\BSON\ObjectID($refId))->where('type', 'reference_disambiguation')->first();
			
			$dis->checked = true;
			$dis->correct = true;
			$dis->save();
			
			$o = new \stdClass;
			$o->success = true;
			$o->dis = $dis->id;
			return response()->json($o);	
		}
		
		public function saveRefDis(Request $request){
			
			if( ! in_array('editor',Auth::user()->roles)){
				$o = new \stdClass;
            			$o->result = "credentials_error";
            			return response()->json($o,401);
			}
			
			$refId = $request->reference;
			$ref_type = $request->type;
			$title = $request->title;
			$ref = Reference::where('_id', $refId)->first();
			$dis = Disambiguation::where('reference', new \MongoDB\BSON\ObjectID($refId))->where('type', 'reference_disambiguation')->first();
			if( ! $dis){
				$dis = new Disambiguation;
				$dis->provenance = "lbcatalogue";
				$dis->reference = new \MongoDB\BSON\ObjectID($refId);
				$dis->type = "reference_disambiguation";
				$dis->document_id = new \MongoDB\BSON\ObjectID($ref->document_id);
			}
			if($ref->ref_type !== $ref_type){
				$ref->ref_type = $ref_type;
				$ref->save();
				$dis->unset('archival_document');
				$dis->unset('book');
			}
			
			if($title == '--nomatch--'){
				$dis->correct = false;
				$dis->unset('archival_document');
				$dis->unset('book');
			}else{
				if($ref->ref_type == 'primary'){
					$dis->archival_document = new \MongoDB\BSON\ObjectID($title);
				}else{
					$dis->book = new \MongoDB\BSON\ObjectID($title);
				}
				
				
				$dis->correct = true;
			}
			$dis->checked = true;
			$dis->save();

			$o = new \stdClass;
			$o->success = true;
			$o->ref = $ref;
			$o->dis = $dis;
			return response()->json($o);	
						
		}
		
		public function saveRefDisState(Request $request){
			
			if( ! in_array('editor',Auth::user()->roles)){
				$o = new \stdClass;
            			$o->result = "credentials_error";
            			return response()->json($o,401);
			}
			
			$dis = Disambiguation::where('_id', $request->dis)->first();
			$dis->{$request->field} = $request->value=='true';
			$dis->save();			
			
			$o = new \stdClass;
			$o->success = true;
			$o->dis = $dis;
			return response()->json($o);		
		}

		public function saveRefState(Request $request){
			
			if( ! in_array('editor',Auth::user()->roles)){
				$o = new \stdClass;
            			$o->result = "credentials_error";
            			return response()->json($o,401);
			}
			
			
			$ref = Reference::where('_id', $request->ref)->first();
			$ref->{$request->field} = $request->value=='true';
			if($request->field == 'correct')
				$ref->checked = true;
			$ref->save();			
			
			$o = new \stdClass;
			$o->success = true;
			$o->ref = $ref;
			return response()->json($o);		
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

			
			/*if(!is_null($this->piwikTracker)){
				$this->piwikTracker->doTrackSiteSearch($search, 'Pages', $response->response->numFound);
				$this->piwikTracker->doTrackPageView("SearchInText");
			}*/
			
			$pages = array();
			if($response->response->numFound > 0){
				foreach($response->response->docs as $p)
					$pages[] = (int)$p->single_page_file_number;
			}
			sort($pages);
			return json_encode($pages);
		}

		public function pageReferences($oid){

			$pa = Page::find($oid);
		
			$references = array();

			$refs = Reference::where('document_id', $pa->document_id)->where('start_img_number',$pa->single_page_file_number)->get();
					
			
			foreach($refs as $r){
				if(count($r->contents)>1){
					$dis = Disambiguation::where('reference', new \MongoDB\BSON\ObjectID($r->_id))->where('provenance', 'processing')->first();
					$r['dis'] = $dis ? 'disamb' : '';
					$references[$r->_id] = $r;
				}
			}
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $oid, "page");
				$this->piwikTracker->doTrackPageView("DocumentPageReferences");
			}					
			return json_encode($references);
		}

		public function pageText($oid){

			$pa = Page::find($oid);
			$txt = "";
			$fid = ' ';
			$tokens = array();
			foreach($pa->lines as $i => $l){
				$txt .= "<p l='".$i."'>";
				foreach($l['tokens'] as $t){
					$txt .= '<span _st='.$t['offset_start'].' _en='.$t['offset_end'].'>'.$t['surface'].$fid.'</span>';
				}
				$txt .= "</p>";
				if(isset($pa->split_after_line) && $i == $pa->split_after_line)
					$txt .= "<div class='splitter'><span class='splitHere'>validate</span><span class='splitRemove'>&times;</span></div>";
			}
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $oid, "page");
				$this->piwikTracker->doTrackPageView("DocumentPageText");
			}			
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
				$pa = Page::where('_id', $p)->first(array('in_index', 'in_golden', 'is_annotated', 'single_page_file_number'));
				$pages[$pa->single_page_file_number] = $pa;				
			}
			ksort($pages);

			$disamb = array();
			if($d->type=='journal_issue'){
				// Get articles
				$articles = BibliodbArticle::where('document_id', new \MongoDB\BSON\ObjectID($d->_id))->orderBy('start_img_number', 'asc')->get();
				foreach($articles as $a){
					$dsb = Disambiguation::where('type', 'author_of_disambiguation')->where('article', new \MongoDB\BSON\ObjectID($a->id))->get();
					$disamb[$a->id] = array();
					foreach($dsb as $aof)
						$disamb[$a->id][(string)$aof->author] = $aof->surface;
					
				}
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
			$o['documentId'] = $d->id;
			$o['metadata'] = $d->metadata;
			$o['pages'] = $pages;
			$o['articles'] = $articles;
			$o['disamb'] = $disamb;
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $d->id, "page");
				$this->piwikTracker->doTrackPageView("DocumentTableOfContent");
			}
			return View::make('document.toc', $o);
		}

		public function searchAuthors($term = null){
			if(is_null($term)){
				$o = new \stdClass;
				$o->success = true;
				$o->results = array();
				return response()->json($o);			
			}
			
			
			$authors = BibliodbAuthor::where('author_final_form', 'like', '%'.$term.'%')->limit(10)->get();
			$o = new \stdClass;
			$o->success = true;
			$o->results = array();
			foreach($authors as $a){
				$au = new \stdClass;
				$au->name = "<span class='ui label mini blue'>LBC</span>&nbsp;".$a->author_final_form;
				$au->text = $a->author_final_form;
				$au->value = $a->id;
				$o->results[] = $au;
			}
			
			$authors = json_decode(file_get_contents('http://www.viaf.org/viaf/AutoSuggest?query='.urlencode($term).'&sortKeys=holdingscount'));
			if(count($authors->result)){
				foreach($authors->result as $a){
					if($a->nametype == "personal"){
						$au = new \stdClass;
						$au->name = "<span class='ui label mini purple'>VIAF</span>&nbsp;".$a->displayForm;
						$au->text = $a->displayForm;
						$au->value = "viaf:".$a->viafid;
						$o->results[] = $au;					
					}
				}
			}
			$au = new \stdClass;
			$au->name = "<span class='ui label mini orange'>CREATE</span>&nbsp;".$term;
			$au->text = $term;
			$au->value = 'new:'.$term;
			$o->results[] = $au;
			
			return response()->json($o);
		}

		public function searchReftitle($source, $term = null){
			if(is_null($term)){
				$o = new \stdClass;
				$o->success = true;
				$o->results = array();
				return response()->json($o);			
			}
			
			switch($source){
				case 'asve':
					$asve = BibliodbAsve::where('label', 'like', '%'.$term.'%')->limit(10)->get();
					$o = new \stdClass;
					$o->success = true;
					$o->results = array();
					foreach($asve as $a){
						$au = new \stdClass;
						$au->name = $a->label;
						$au->text = $a->label;
						$au->value = $a->id;
						$o->results[] = $au;
					}		
					break;
					
				case 'book':
					$books = BibliodbBook::where('title', 'like', '%'.$term.'%')->limit(10)->get();
					$o = new \stdClass;
					$o->success = true;
					$o->results = array();
					foreach($books as $a){
						$au = new \stdClass;
						$au->name = $a->title;
						$au->text = $a->title;
						$au->value = $a->id;
						$o->results[] = $au;
					}							
					break;
			}
			
			$au = new \stdClass;
			$au->name = "-- No match --";
			$au->text = "-- No match --";
			$au->value = "--nomatch--";
			$o->results[] = $au;
			
			return response()->json($o);
		}



		public function searchViafAuthors($term){
			$auth = array();

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

			$a = BibliodbArticle::where('_id', new \MongoDB\BSON\ObjectID($oid))->first();
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
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $oid, "page");
				$this->piwikTracker->doTrackPageView("ArticleOverview");
			}
			return View::make('document.article.overview', $o);
		}


		public function articleScans($oid)
		{
			$a = BibliodbArticle::where('_id', new \MongoDB\BSON\ObjectID($oid))->first();
			if( ! $a){abort(404);}

			$d = Document::where('_id',new \MongoDB\BSON\ObjectID($a->document_id))->first();

	
			$pages = array();

			foreach($d->pages as $p){
				
				$pa = Page::where('_id', $p)->first(array('fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids'));
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
			$o['documentId'] = $d->id;
			$o['oid'] = $oid;
			$o['bid'] = $d->bid;
			$o['issue'] = $d->number;
			$o['fakeimage'] = $image;
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $oid, "page");
				$this->piwikTracker->doTrackPageView("ArticleScans");
			}
			return View::make('document.article.scans', $o);
		}



		public function articleViewer($oid)
		{
			$a = BibliodbArticle::where('_id', new \MongoDB\BSON\ObjectID($oid))->first();
			if( ! $a){abort(404);}
			$d = Document::where('_id',new \MongoDB\BSON\ObjectID($a->document_id))->first();

	
			$pages = array();

			foreach($d->pages as $p){
				
				$pa = Page::where('_id', $p)->first(array('fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids'));
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
			$o['documentId'] = $d->id;
			$o['oid'] = $oid;
			$o['bid'] = $d->bid;
			$o['issue'] = $d->number;
			$o['fakeimage'] = $image;
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $oid, "page");
				$this->piwikTracker->doTrackPageView("ArticleViewer");
			}			
			return View::make('document.article.viewer', $o);
		}


		public function articleReferences($oid)
		{


			$a = BibliodbArticle::where('_id', new \MongoDB\BSON\ObjectID($oid))->first();
			if( ! $a){abort(404);}
			$d = Document::where('_id',new \MongoDB\BSON\ObjectID($a->document_id))->first();

			$pages = array();

			foreach($d->pages as $p){
				
				$pa = Page::where('_id', $p)->first(array('fulltext', 'printed_page_number', 'in_index', 'in_golden', 'is_annotated', 'single_page_file_number', 'annotations_ids'));
				if($pa->single_page_file_number >= $a->start_img_number && $pa->single_page_file_number <= $a->end_img_number)
					$pages[$pa->single_page_file_number] = $pa->_id;				
			}

			ksort($pages);

			$o = array();
			
			$o['hasTopMenu'] = '';
			$o['dataJs'] = 'REF';
			$o['referencesActive'] = 'active';
			$o['pagecount'] = count($pages);
			$o['bid'] = $d->bid;
			$o['issue'] = $d->number;
			$o['document'] = $d;
			$o['documentId'] = $d->id;
			$o['pages'] = $pages;
			$o['oid'] = $oid;
			$o['article'] = $a;
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->setCustomVariable(4, "OID", $oid, "page");
				$this->piwikTracker->doTrackPageView("ArticleReferences");
			}
			return View::make('document.article.references', $o);
		}


		public function filters(Request $request){
			

			$q = $request->q;
			$ns = $request->ns;
			$in = $request->in;
			$field = $request->field;
			$filtrs = $request->filtrs;

			$this->connectSolr();
			$partials = array();
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

				
			$countRefs = 	Disambiguation::where('document_id', new \MongoDB\BSON\ObjectID($document_id))->where('type', 'reference_disambiguation')->count();
			$countChecked = Disambiguation::where('document_id', new \MongoDB\BSON\ObjectID($document_id))->where('type', 'reference_disambiguation')->where('checked', true)->count();

			return View::make('document.partials.progress', array('countRefs' => $countRefs, 'countChecked' => $countChecked));
		}
		
		
        public function saveSplit(){
			
				if( ! in_array('editor',Auth::user()->roles)){
					$o = new \stdClass;
							$o->result = "credentials_error";
							return response()->json($o,401);
				}

			
			
                $who = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                $line = Input::get('split_after_line');

                $page = Page::find(Input::get('page'));
				if($line == "remove"){
					$page->unset('split_after_line');
				}else{
                	$page->split_after_line = (int)$line;
				}
                $page->save();

                $page_number = $page->single_page_file_number;
                $bid = $page->document->bid;
                $issue = $page->document->number;

				if($line == "remove"){
                	History::log("Update split line : ".$line, $who, $bid , $issue, $page_number);
				}else{
					History::log("Remove split line", $who, $bid , $issue, $page_number);
				}
                $o = new \stdClass;
                $o->result = "success";
                return response()->json($o);
        }

		
        public function savePpn(){
			
				if( ! in_array('editor',Auth::user()->roles)){
					$o = new \stdClass;
							$o->result = "credentials_error";
							return response()->json($o,401);
				}
			
                $who = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
                $ppn = Input::get('ppn');
				$ppns = array_map('intval',explode(',',$ppn));
				
                $page = Page::find(Input::get('page'));
                $page->printed_page_number = $ppns;
                $page->save();
                
                if(Input::get('propagate') == 1){
                	$next = $ppns[count($ppns)-1]+1;
					$pa = $page->document->pages;
					$index = array_search($page->_id, $pa);
					$prop = 0;
					for($i = $index+1; $i < count($pa); $i++){
						$np = Page::find($pa[$i]);
						$np->printed_page_number = array($next++);
						$np->save();
						$prop++;
					}
				}

                $page_number = $page->single_page_file_number;
                $bid = $page->document->bid;
                $issue = $page->document->number;

                History::log("Update printed_page_number ".(Input::get('propagate')==1?'(with propagate to '.$prop.' pages)':'').": ".$ppn, $who, $bid , $issue, $page_number);
                $o = new \stdClass;
                $o->result = "success";
                return response()->json($o);
        }

		public function documentBookmarks($document_id,$page_id = null){
			$who = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
			$docid = new \MongoDB\BSON\ObjectID($document_id);
			if( ! is_null($page_id)) $pgid = new \MongoDB\BSON\ObjectID($page_id); else $pgid = "";
			
			$o = array();
			$o['b'] = Bookmark::where('owner', $who)->where('document_id', $docid)->where('page_id', "")->where('type','bookmark')->first();
			$o['s'] = Bookmark::where('owner', $who)->where('document_id', $docid)->where('page_id', "")->where('type','seelater')->first();
			$o['documentId'] = $document_id;
			$o['hasPage'] = false;
			
			if( ! is_null($page_id)){
				$o['hasPage'] = true;
				$o['bp'] = Bookmark::where('owner', $who)->where('document_id', $docid)->where('page_id', $pgid)->where('type','bookmark')->first();
				$o['sp'] = Bookmark::where('owner', $who)->where('document_id', $docid)->where('page_id', $pgid)->where('type','seelater')->first();
				$o['pageId'] = $page_id;
			}
			return View::make('document.partials.bookmarks', $o);
			
		}

		public function documentAjaxBookmarks($action, $type ,$document_id,$page_id = null){
			$who = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
			$docid = new \MongoDB\BSON\ObjectID($document_id);
			
			if( ! is_null($page_id)) $pgid = new \MongoDB\BSON\ObjectID($page_id);
			$doc = Document::where('_id', $docid)->first();
		
			if( ! is_null($page_id)){
				$pgid = new \MongoDB\BSON\ObjectID($page_id);
				$pg = Page::where('_id',$pgid)->first();
			}
			
			switch($action){
				case 'add': 
					$bkm = new Bookmark;
					$bkm->owner = $who;
					$bkm->document_id = $docid;
					$bkm->page_id = "";
					$page_number = "";
					if( ! is_null($page_id)){
						$bkm->page_id = $pgid;
						$page_number = $pg->single_page_file_number;
					}
					$bkm->type = $type;
					$bkm->save();
					History::log("Add ".$type." bkm ", $who, $doc->bid , $doc->number, $page_number);
					break;
					
				case 'del': 
					if( ! is_null($page_id)){
						$bkm = Bookmark::where('owner', $who)->where('document_id', $docid)->where('page_id', $pgid)->where('type',$type)->first();
						History::log("Del ".$type." bkm ", $who, $doc->bid , $doc->number, $pg->single_page_file_number);
						$bkm->delete();
					}else{
						$bkm = Bookmark::where('owner', $who)->where('document_id', $docid)->where('page_id', "")->where('type',$type)->first();
						History::log("Del ".$type." bkm ", $who, $doc->bid , $doc->number, "");
						$bkm->delete();
					}
					break;
			}
		    $o = new \stdClass;
            $o->result = "success";
            return response()->json($o);

			
		}

		public function mydocuments(){
			$who = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
			$o = array();
			$o['bookmarks'] = Bookmark::where('owner', $who)->where('type' , 'bookmark')->get();
			foreach($o['bookmarks'] as &$b){
				$b->thedoc = Document::where('_id', new \MongoDB\BSON\ObjectID($b->document_id))->first();
				if($b->page_id != ""){
					$b->thepage = Page::where('_id', new \MongoDB\BSON\ObjectID($b->page_id))->first();
					$b->thepagenumber = $b->thepage->single_page_file_number;
				}else{
					$b->thepagenumber = "";
				}
			}
				
			$o['seelaters'] = Bookmark::where('owner', $who)->where('type' , 'seelater')->get();
			
			foreach($o['seelaters'] as &$b){
				$b->thedoc = Document::where('_id', new \MongoDB\BSON\ObjectID($b->document_id))->first();
				if($b->page_id != ""){
					$b->thepage = Page::where('_id', new \MongoDB\BSON\ObjectID($b->page_id))->first();
					$b->thepagenumber = $b->thepage->single_page_file_number;
				}else{
					$b->thepagenumber = "";
				}
			}
			if(!is_null($this->piwikTracker)){
				$this->piwikTracker->doTrackPageView("My Documents");
			}				
			return View::make('mydocuments',$o);
			
		}
		
		
		public function documentIndexgolden($document_id,$page_id){
			$docid = new \MongoDB\BSON\ObjectID($document_id);
			$pgid = new \MongoDB\BSON\ObjectID($page_id);
			$page = Page::where('_id', $pgid)->first();
						
			$o = array();
			$o['documentId'] = $document_id;
			$o['pageId'] = $page_id;
			$o['ip'] = $page->in_index;
			$o['gp'] = $page->in_golden;
			
			return View::make('document.partials.indexgolden', $o);
			
		}


		
		public function documentAjaxIndexGolden($action, $type ,$document_id,$page_id){
			
			if( ! in_array('editor',Auth::user()->roles)){
				$o = new \stdClass;
            			$o->result = "credentials_error";
            			return response()->json($o,401);
			}

			$who = new \MongoDB\BSON\ObjectID(Auth::user()->_id);
			$docid = new \MongoDB\BSON\ObjectID($document_id);
			$pgid = new \MongoDB\BSON\ObjectID($page_id);
		

			$doc = Document::where('_id', $docid)->first();
			
			if( ! isset($doc->index)){
				$doc->index = array(
					'page_ids' => array()
				);
			}
			$docindex = $doc->index;
			$page = Page::where('_id',$pgid)->first();
			
			
			
			switch($action){
				case 'add': 
					switch($type){
						case 'index': $page->in_index = true; break;
						case 'golden': $page->in_golden = true; break;
					}
					$page->save();
					
					$z = new \stdClass;
					$z->{'_id'} = $pgid;
					$inarray = false;
					foreach($docindex['page_ids'] as $inx => $inxo)
						if($inxo['_id'] == $pgid)
							$inarray = true;
					if( ! $inarray){
						array_push($docindex['page_ids'], $z);
						$doc->index = $docindex;
						$doc->save();
					}
					
					
					
					History::log("Add page ".$page->single_page_file_number." in ".$type, $who, $doc->bid , $doc->number, $page->single_page_file_number);
					break;
					
				case 'del': 
					switch($type){
						case 'index': $page->in_index = false; break;
						case 'golden': $page->in_golden = false; break;
					}
					$page->save();
					
					$z = new \stdClass;
					$z->{'_id'} = $pgid;
					$inarray = false;
					foreach($docindex['page_ids'] as $inx => $inxo)
						if($inxo['_id'] == $pgid)
							$inarray = $inx;
					if($inarray !== false){
						unset($docindex['page_ids'][$inarray]);
						$doc->index = $docindex;
						$doc->save();
					}
					
					History::log("Del page ".$page->single_page_file_number." from ".$type, $who, $doc->bid , $doc->number, $page->single_page_file_number);
					break;
			}
		    $o = new \stdClass;
            $o->result = "success";
            return response()->json($o);

		}		

		public function saveArticle(Request $request){
			
			if( ! in_array('editor',Auth::user()->roles)){
				$o = new \stdClass;
            			$o->result = "credentials_error";
            			return response()->json($o,401);
			}
			
			$docid = new \MongoDB\BSON\ObjectID($request->document_id);
			$doc = Document::find($docid);
	
			if($request->article_id == 'new'){
				$article = new BibliodbArticle;
				$article->provenance = 'lbcatalogue';
				$article->journal_bid = $doc->bid;
				$journal = BibliodbJournal::where('bid', $doc->bid)->first();
				$article->journal_short_title = $journal->short_title;
				$article->document_id = $docid;
				
				$docArticles = BibliodbArticle::where('document_id', $docid)->get();
				$m = 0;
				foreach($docArticles as $dA){
					list($bid, $number, $n) = explode(":",$dA->internal_id);
					$m = max($m,$n);
				}
				$article->internal_id = $doc->bid.":".$doc->number.":".++$m;
				$metadata = Metadata::where('bid', $doc->bid)->first();
			
				foreach($metadata->issues as $mD){
					if($mD['foldername'] == $doc->number){
						$article->year = $mD['year'];
						$article->digitization_provenance = $mD['provenance'];
						$article->volume = $mD['issue'];
					}
						
				}

				
			}else{
				$article_id = new \MongoDB\BSON\ObjectID($request->article_id);
				$article = BibliodbArticle::find($article_id);			
			}
			$article->title = $request->title;
			$article->start_img_number = $request->page_start;
			$article->end_img_number = $request->page_end;
						
			$authorsId = explode('|#|', $request->authors);
			
			$authorsMongoObject = array();
			
			foreach($authorsId as $au){
				
				// Create new
				if(preg_match('/^new:/', $au)){
					$a = BibliodbAuthor::where('author_final_form',substr($au, 4))->where('viaf_id', null)->first();
			
					if( ! $a){
						$a = new BibliodbAuthor;
						$a->provenance = "lbcatalogue";
						$a->author_final_form = substr($au, 4);
						$a->checked = false;
						$a->surface_forms = array(substr($au,4));
						$a->save();
					}
					
					$authorsMongoObject[] = $a;
	
					
				// Viaf_Author
				}else if (preg_match('/^viaf:/',$au)){
					
					$a = BibliodbAuthor::where('viaf_id', substr($au,5))->first();
					if( ! $a){
						$viafquery = file_get_contents('http://www.viaf.org/viaf/'.substr($au,5).'/viaf.json');
						$author = json_decode($viafquery);
						
						$final_form = $author->{"ns1:mainHeadings"}->{"ns1:data"}->{"ns1:text"};
						$a = new BibliodbAuthor;
						$a->provenance = "lbcatalogue";
						$a->author_final_form = $final_form;
						$a->checked = false;
						$a->surface_forms = array($final_form);
						$a->viaf_id = substr($au,5);
						$a->save();
					}
					
					$authorsMongoObject[] = $a;
					
				// Bibliodb_Author	
				}else{
					$a = BibliodbAuthor::find(new \MongoDB\BSON\ObjectID($au));
					$authorsMongoObject[] = $a;
				}
			}
			
			$aaa = array();
			$authorDisambToAdd = array();
			foreach($authorsMongoObject as $aff){
				$aaa[] = $aff->author_final_form;
				$authorDisambToAdd[$aff['id']] = $aff;
			}
			$article->authors = $aaa;
			// HISTORY
			$article->save();
			
			$authorDisambExists = array();
			$disamb = Disambiguation::where('type', 'author_of_disambiguation')->where('article', new \MongoDB\BSON\ObjectID($article->id))->get();
			foreach($disamb as $dE)
				$authorDisambExists[(string)$dE->author] = $dE;
			
			$toAdd = array_diff_key($authorDisambToAdd, $authorDisambExists);
			
			foreach($toAdd as $author){
					$d = new Disambiguation;
					$d->provenance = "lbcatalogue";
					$d->surface = $author->surface_forms[0];
					$d->author = new \MongoDB\BSON\ObjectID($author->id);
					$d->article =  new \MongoDB\BSON\ObjectID($article->id);
					$d->checked = false;
					$d->correct = false;
					$d->type = "author_of_disambiguation";
					$d->save();
					// HISTORY
			}
			$toRemove = array_diff_key($authorDisambExists, $authorDisambToAdd);
			
			foreach($toRemove as $dE){
				History::log("Delete article/author ".$dE->article."/".$dE->author." disambiguation [".$dE->surface."]", new \MongoDB\BSON\ObjectID(Auth::user()->_id), $doc->bid , $doc->number);
				$dE->delete();
			}
			
			
			
		    $o = new \stdClass;
            $o->result = "success";
			$o->article_id = $article->id;
			$o->authors = join(", ", $aaa);
			$o->title = $article->title;
			$o->pagerange = $article->start_img_number." - ".$article->end_img_number;
            return response()->json($o);			
			
			
		}
			


}
