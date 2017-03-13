<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App;
use App\Document;
use App\Metadata;
use App\Page;
use App\Bookmark;
use App\Validation;
use App\Rating;
use App\History;
use DB;
use Config;
use View;
use Session;
use Hash;
use Input;
use Auth;
use MongoId;
class DocumentController extends Controller
{
	const NO_ISSUE = '_';

    public function browse(){

		$data = array();
		$data['paginationOptions'] = Config::get('custom.paginationOptions');
		$data['filters'] = array(
			'BID' => array('type' => 'input', 'active' => 'active', 'defaultValue' => ''),
			'Title' => array('type' => 'input'),
			'Type' => array('type' => 'checkbox', 'values' => Metadata::distinct('type_document')->get()),
			'Language' =>  array('type' => 'checkbox', 'values' => Metadata::distinct('language')->get()),
			'Date' => array('type' => 'input'),
			'Provenance' => array('type' => 'checkbox', 'values' => Metadata::distinct('provenance')->get()),
		);
		
		$data['page'] = "DOCUMENTS";
		return view('documents.browse', $data);	
	}



    public function bookmarks($type){

		$data = array();
		$data['bookmarks'] = Bookmark::where('owner', new MongoId(Auth::user()->_id))
									 ->whereIn('type', array($type,'doc_'.$type))
									 ->orderBy('bid', 'asc')
									 ->orderBy('issue', 'asc')
									 ->orderBy('page', 'asc')
									 ->get();
		


		$data['page'] = "BOOKMARKS";
		return view('bookmarks', $data);	
	}


	public function issueSearch($bid,$query=""){

		$o = new \stdClass;
		$o->success = true;
		$o->results = array();
		

		$bid = Metadata::where('bid', $bid)->first();

		foreach($bid->issues as $k => $i){
			if(preg_match('/^'.$query.'/',$i['foldername'])){
				$n = new \stdClass;
				$n->name = $i['foldername'];
				$n->value = $i['foldername'];
				$o->results[] = $n;
			}
		}	

		return response()->json($o);

	}

	public function view($bid, $issue){
		$who = new MongoId(Auth::user()->_id);

		$data=array();	
		$data['bid'] = $bid;
		$data['issue'] = $issue;

		$v = Validation::where('bid', $bid);
		$v->where('issue', $issue);
		$data['isChecked'] = $v->count() > 0;


		$bm = Bookmark::where('bid', $bid)
						->where('issue', $issue)
						->where('type', 'doc_favorite')
						->where('owner', $who);
		$data['isFavorite'] = $bm->count() > 0;

		$bm = Bookmark::where('bid', $bid)
						->where('issue', $issue)
						->where('type', 'doc_seelater')
						->where('owner', $who);
		$data['isSeeLater'] = $bm->count() > 0;


		$displayMetadata = Config::get('custom.displayMetadata');
		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}
		if($issue == self::NO_ISSUE){
			$data['document'] = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$data['document'] = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}
					
		$data['metadataList'] = array();
     	foreach($displayMetadata as $k){
			list($label,$key,$type) = $k;
     		if(isset($metadata[$key])){
				$val = $metadata[$key];
			
     		}elseif(strpos($key,'.') !== FALSE){
				list($k, $sk) = explode('.',$key);
				$val = $metadata[$k][$sk];
			}

			$params = "class='editable' contenteditable='true' data-type='".$type."' data-key='".$key."' ";

			if($type=='string' && $val != "") 
				$data['metadataList'][$label] = "<p ".$params.">".$val."</p>";

			if($type == 'arrayString' && count($val)){
			 	$data['metadataList'][$label] = "<p ".$params.">".join("<br />",$val)."</p>";
		}

		}
		$data['page'] = "DOC";
		return view('documents.view', $data);	
	}

	public function page($bid, $issue, $page){
		$data = array();	
		$data['bid'] = $bid;
		$data['issue'] = $issue;
		$data['pg'] = $page;
		
		$data['pageModeLBC'] = Session::get('pageModeLBC', 'selection');
		
		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}

		if($issue == self::NO_ISSUE){
			$data['document'] = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$data['document'] = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}
		$data['rnd'] = rand(1,count($data['document']['pages']));
		$data['pageObject'] = Page::find($data['document']['pages'][$page-1]);

		$who = new MongoId(Auth::user()->_id);
		$bm = Bookmark::where('bid', $bid)
						->where('issue', $issue)
						->where('page', $page)
						->where('type', 'favorite')
						->where('owner', $who);
		$data['isFavorite'] = $bm->count() > 0;

		$bm = Bookmark::where('bid', $bid)
						->where('issue', $issue)
						->where('page', $page)
						->where('type', 'seelater')
						->where('owner', $who);
		$data['isSeeLater'] = $bm->count() > 0;

		$r = Rating::where('bid', $bid)
					->where('issue', $issue)
					->where('page', $page)
					->where('type', 'ocr');
		if($r->count() > 0){
			$r = $r->first();
			$data['ratingOcr'] = $r->value;
		}else{
			$data['ratingOcr'] = 0;
		}


		$r = Rating::where('bid', $bid)
					->where('issue', $issue)
					->where('page', $page)
					->where('type', 'scan');
		if($r->count() > 0){
			$r = $r->first();
			$data['ratingScan'] = $r->value;
		}else{
			$data['ratingScan'] = 0;
		}
		$data['lines'] = $data['pageObject']['fulltext'] == '' ? array() : array_filter(preg_split('/\n|\r\n?/', $data['pageObject']['fulltext']));
		if(count($data['lines']) != count($data['pageObject']['lines'])){
			\Log::error("Document MongoDB structure problem (fulltext nb lines != lines array length)");
			\Log::error(print_r($data['pageObject'],1));
			App::abort(404, "Document MongoDB structure problem (fulltext nb lines != lines array length)");
		}
			

		$data['isIndex'] = $data['pageObject']['in_index'];
		$data['isGolden'] = $data['pageObject']['in_golden'];
		$data['indexPages'] = array_map(array($this,'mapOID'),$data['document']['index']['page_ids']);
#		$data['checkedIndex'] = (in_array($data['pageObject']['printed_page_number'],$data['document']['index']['filenumbers'])) ? 'checked' : '';

		$data['page'] = "PAGE";
		return view('documents.page',$data);	
	}

	public function ajaxBookmark($type,$action){

		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$page = Input::get('page');

		$who = new MongoId(Auth::user()->_id);

		switch($action){
			
			case 'remove':
				
				$bm = Bookmark::where('bid', $bid);
				$bm->where('issue', $issue);
				$bm->where('page', $page);
				$bm->where('owner', $who);
				$bm->where('type', $type);
				$bm->delete();
				History::log("delete bookmark ".$type, $who, $bid , $issue, $page);
				$o = new \stdClass;
				$o->result = "success";
				return response()->json($o);
				break;

			case 'add':
				
				$bm = new Bookmark;
				$bm->bid = $bid;
				$bm->issue = $issue;
				$bm->page = $page;
				$bm->type = $type;
				$bm->owner = $who;
				$bm->save();
				History::log("add bookmark ".$type, $who, $bid , $issue, $page);

				$o = new \stdClass;
				$o->result = "success";
				return response()->json($o);
				break;

		}
		

	}

	public function ajaxPageindex($action){
		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$page = Input::get('page');
		$pageObj = Input::get('pageObj');

		$who = new MongoId(Auth::user()->_id);
		$pg = Page::find($pageObj);
		$pg->in_index = ($action == 'add');
		$pg->save();
		History::log($action." page to index pages ", $who, $bid , $issue, $page);
		
		if($issue == self::NO_ISSUE){
			$doc = Document::where('bid', $bid)->first();
		}else{
			$doc = Document::where('bid', $bid)->where('number',$issue)->first();
		}
	
		$n = new \stdClass;
		$n->_id = new MongoId($pageObj);
		if($action == 'add')
			$doc->push('index.page_ids', $n);
		else
			$doc->pull('index.page_ids', $n);
		$doc->save();

		$o = new \stdClass;
		$o->result = "success";
		return response()->json($o);

	}

	public function ajaxPagegolden($action){
		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$page = Input::get('page');
		$pageObj = Input::get('pageObj');

		$who = new MongoId(Auth::user()->_id);
		$pg = Page::find($pageObj);
		$pg->in_golden = ($action == 'add');
		$pg->save();
		History::log($action." page to golden set ", $who, $bid , $issue, $page);
		
		$o = new \stdClass;
		$o->result = "success";
		return response()->json($o);

	}

	public function ajaxCheck($action){

		$bid = Input::get('bid');
		$issue = Input::get('issue');

		$who = new MongoId(Auth::user()->_id);

		switch($action){
			
			case 'uncheck':
				$v = Validation::where('bid', $bid);
				$v->where('issue', $issue);
				$v->delete();
				History::log("Mark document as unchecked", $who, $bid , $issue);
				$o = new \stdClass;
				$o->result = "success";
				return response()->json($o);
				break;

			case 'check':
				
				$v = new Validation;
				$v->bid = $bid;
				$v->issue = $issue;
				$v->updated_by = $who;
				$v->save();
				History::log("Mark document as checked", $who, $bid , $issue);
				$o = new \stdClass;
				$o->result = "success";
				return response()->json($o);
				break;

		}
		

	}

	public function ajaxLoadMorePages() {
		define("NB",30);

		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$count = Input::get('count');

		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}
		if($issue == self::NO_ISSUE){
			$document = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$document = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}
		$data = array();
		$data['bid'] = $bid;
		$data['issue'] = $issue;
		$data['indexPages'] = array_map(array($this,'mapOID'),$document['index']['page_ids']);
		$data['pages'] = array_slice($document['pages'],$count,NB,true);
		$data['hasMore'] = count($document['pages'])> $count + NB;
		$o = array();
		$v = View::make('documents.ajaxLoadMorePages', $data);
		$o['documentsResults'] = $v->render();
		return response()->json($o);
	}

	public function ajaxLoadTocPages() {

		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$pg = Input::get('pg');

		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}
		if($issue == self::NO_ISSUE){
			$document = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$document = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}
		$data = array();
		$data['bid'] = $bid;
		$data['issue'] = $issue;
		$data['pg'] = $pg;
		$data['prev'] = isset( $document['pages'][$pg-1-1] )? $document['pages'][$pg-1-1] : false;
		$data['curr'] = isset( $document['pages'][$pg-1]   )? $document['pages'][$pg-1]   : false;
		$data['next'] = isset( $document['pages'][$pg-1+1] )? $document['pages'][$pg-1+1] : false;

		$o = array();
		$v = View::make('documents.ajaxLoadTocPages', $data);
		$o['documentsResults'] = $v->render();
		return response()->json($o);
	}

	public function ajaxLoadTocEntries() {

		$bid = Input::get('bid');
		$issue = Input::get('issue');

		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}

		$data = array();
		$data['bid'] = $bid;
		$data['issue'] = $issue;

		if($issue == self::NO_ISSUE){
			$data['document'] = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$data['document'] = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}

		$data['articles'] = (isset($data['document']['articles'])) ? $data['document']['articles'] : array();
		usort($data['articles'],function($a, $b){ 
			if ($a['start_page'] == $b['start_page']) { return 0; } 
			$s = $a['start_page'] == "" ? 99999999 : $a['start_page'];
			$e = $b['start_page'] == "" ? 99999999 : $b['start_page'];
			return ($s < $e) ? -1 : 1;
		}); 

		$o = array();
		$v = View::make('documents.ajaxLoadTocEntries', $data);
		$o['documentsResults'] = $v->render();
		return response()->json($o);
	}

	public function ajaxLoadTocOverview() {

		$bid = Input::get('bid');
		$issue = Input::get('issue');

		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}

		$data = array();
		$data['bid'] = $bid;
		$data['issue'] = $issue;
	
		if($issue == self::NO_ISSUE){
			$data['document'] = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$data['document'] = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}
		
		$data['articles'] = (isset($data['document']['articles'])) ? array_filter($data['document']['articles'],function($v){return $v['start_page'] != "";}) : array();
		usort($data['articles'],function($a, $b){ if ($a['start_page'] == $b['start_page']) { return 0; } return ($a['start_page'] < $b['start_page']) ? -1 : 1;});
		$o = array();
		$v = View::make('documents.ajaxLoadTocOverview', $data);
		$o['documentsResults'] = $v->render();
		return response()->json($o);
	}

	public function ajaxLoadIndexOverview() {

		$bid = Input::get('bid');
		$issue = Input::get('issue');

		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}

		$data = array();
		$data['bid'] = $bid;
		$data['issue'] = $issue;
	
		if($issue == self::NO_ISSUE){
			$doc = Document::where('bid', $bid)->first()->getAttributes();
		}else{
			$doc = Document::where('bid', $bid)->where('number',$issue)->first()->getAttributes();
		}
		$data['document'] = $doc;

		
		$index_pages = array_map(array($this,'mapOID'),$doc['index']['page_ids']);
		$data['pages'] = array_intersect($doc['pages'], $index_pages);
		
		$o = array();
		$v = View::make('documents.ajaxLoadIndexOverview', $data);
		$o['documentsResults'] = $v->render();
		return response()->json($o);
	}
	private static function mapOID($a){return $a['_id'];}

	public function ajaxRate() {
		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$page = Input::get('page');
		$type = Input::get('type');
		$value = Input::get('value');

		$r = Rating::where('bid', $bid);
			$r->where('issue', $issue);
			$r->where('page', $page);
			$r->where('type', $type);
		$r = $r->first();

		if( ! is_object($r) ){
			$r = new Rating;
			$r->bid = $bid;
			$r->issue = $issue;
			$r->page = $page;	
			$r->type = $type;
			$r->value = -1;
		}

		$who = new MongoId(Auth::user()->_id);
		$o = new \stdClass;
		if($r->value != $value && $value != 0){
			$r->value = $value;
			$r->update_by = $who;
			$r->save();
			$o->result = "success";
			History::log("Rate ".$type, $who, $bid , $issue, $page);
			
		}else{
			$o->result = "nochange";
		}
		return response()->json($o);

	}

	public function ajaxToc($action) {

		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$toc = Input::get('toc');
		
		$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
	
		if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
			App::abort(404, "Document issue must be specified");
		}

		$who = new MongoId(Auth::user()->_id);
		
		if($issue == self::NO_ISSUE){
			$doc = Document::where('bid', $bid)->first();
		}else{
			$doc = Document::where('bid', $bid)->where('number',$issue)->first();
		}

		$doc->articles = $toc;
		$doc->save();		
		
		
	
	
		
		History::log("Update TOC", $who, $bid , $issue);
		$o = new \stdClass;
		$o->result = "success";
		return response()->json($o);
		break;

	}

	public function ajaxMeta($action) {

		$bid = Input::get('bid');

		$metadata = Metadata::where('bid', $bid)->first();

		$who = new MongoId(Auth::user()->_id);
		switch($action){

			case 'update':
								
				$key = Input::get('key');
				
				if($key == 'bid'){
					$o = new \stdClass;
					$o->result = "nochange";
					return response()->json($o);
				}

				$type = Input::get('type');
				$val = Input::get('val');


				$hasChange = false;
				switch($type){
					case "string":
						$newVals = $val;
						$hasChange = ($metadata->{$key} != $val);
						break;

					case "arrayString":
						$newVals = explode("\n", $val);
						$hasChange = ! empty(array_diff($metadata->{$key},$newVals));
						break;
				} 

				if($hasChange){
					$metadata->{$key} = $newVals;
					$metadata->save();
					History::log("Update metadata ".$key, $who, $bid );
					$o = new \stdClass;
					$o->result = "success";
					return response()->json($o);
				}else{
					$o = new \stdClass;
					$o->result = "nochange";
					return response()->json($o);
				}



				break;

		}
		


	}

	public function ajaxSearch(){

		$sameFilters = Session::get('documents.filters','') == $_REQUEST['filters'];
		Session::put('documents.filters', $_REQUEST['filters']);

		parse_str($_REQUEST['filters'], $filters);
		parse_str($_REQUEST['pagination'],$pagination);

		
		$md = new Metadata;
		if(isset($filters['Type']))
			$md = $md->whereIn('type_document', $filters['Type']);

		if(isset($filters['Language']))
			$md = $md->whereIn('language', $filters['Language']);

		if(isset($filters['Provenance']))
			$md = $md->whereIn('provenance', $filters['Provenance']);
	
		if(isset($filters['BID']) && $filters['BID']!= "")
			$md = $md->where('bid', 'like' ,$filters['BID'].'%');

		if(isset($filters['Title']) && $filters['Title']!= "")
			$md = $md->where('title.surface', 'like' ,'%'.$filters['Title'].'%');

		if(isset($filters['Date']) && $filters['Date']!= "")
			$md = $md->where('date', $filters['Date']);







		$skip = 0;
		$docsPerPage = 50;

		$data = array();

		$data['paginationOptions'] = Config::get('custom.paginationOptions');
		$data['docsPerPage'] = isset($pagination['docsPerPage'])?$pagination['docsPerPage']:20;

		$data['page'] = (!$sameFilters) ? 1 : (isset($pagination['page'])?$pagination['page']:1);
		$data['skip'] = ($data['page']-1)*$data['docsPerPage'];
		$data['docsCount'] = $md->count();
		$data['pageCount'] = ceil($data['docsCount']/$data['docsPerPage']);
		$data['first'] = $data['skip'] + 1;
		$data['last'] = $data['skip'] + min($data['docsCount'], $data['docsPerPage']); 
		$data['docs'] = $md->skip($data['skip'])->take($data['docsPerPage'])->get();


		$o = array();
		$v = View::make('documents.ajaxResults', $data);
		$o['documentsResults'] = $v->render();
		$u =  View::make('documents.pagination', $data);
		$o['pagination'] = $u->render();
		return response()->json($o);
	}

	public function saveFootnotes(){
		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$who = new MongoId(Auth::user()->_id);
		$selectedLines = array_filter(explode(',', Input::get('in_footnotes')));
		$page = Page::find(Input::get('page'));
		$lines = $page->lines;
		foreach($lines as $k => $_){
			$lines[$k]['in_footnote'] = in_array($k,$selectedLines);
		}
		$page->lines = $lines;;
		$page->has_footnotes = count($selectedLines)>0;
		$page->save();
		
		History::log("Update footnote lines", $who, $bid , $issue);
		$o = new \stdClass;
		$o->result = "success";
		return response()->json($o);
		break;

	}

	public function saveSplit(){
		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$who = new MongoId(Auth::user()->_id);
		$line = Input::get('split_after_line');
		$page = Page::find(Input::get('page'));
		$page->split_after_line = $line;
		$page->save();
		
		History::log("Update split line ".Input::get('page'), $who, $bid , $issue);
		$o = new \stdClass;
		$o->result = "success";
		return response()->json($o);
		break;

	}
	public function session($key, $value){
		Session::put($key, $value);
	}

	public function ajaxSavePrintedPage(){
		$bid = Input::get('bid');
		$issue = Input::get('issue');
		$who = new MongoId(Auth::user()->_id);
		$page = Input::get('page');
		$type = Input::get('type');
		$printed_page_numbers = array_filter(explode(',',Input::get('printed_page_number')));
		$o = new \stdClass;
		if( (count($printed_page_numbers) == 0 ) ||
			(count($printed_page_numbers) == 1 && is_numeric($printed_page_numbers[0])) ||
			(count($printed_page_numbers) == 2 && is_numeric($printed_page_numbers[0]) && is_numeric($printed_page_numbers[1]) && $printed_page_numbers[0] < $printed_page_numbers[1] )){
			


			$metadata = Metadata::where('bid', $bid)->first()->getAttributes();
		
			if(count($metadata['issues']) > 0 && $issue == self::NO_ISSUE ){
				App::abort(404, "Document issue must be specified");
			}

			if($issue == self::NO_ISSUE){
				$doc = Document::where('bid', $bid)->first();
			}else{
				$doc = Document::where('bid', $bid)->where('number',$issue)->first();
			}
			
			$pgo = Page::find($doc['pages'][$page-1]);
			$pgo->printed_page_number = $printed_page_numbers;
			$pgo->save();
			if($type == 'propagate'){
				//from next to end	
				$n = 1;
				for($i = $page; $i<=count($doc['pages'])-1;$i++){
					$pgo = Page::find($doc['pages'][$i]);
					switch(count($printed_page_numbers)){
						case 0: $pgo->printed_page_number = array(); break;
						case 1: $pgo->printed_page_number = array($printed_page_numbers[0]+$n++); break;
						case 2: $pgo->printed_page_number = array($printed_page_numbers[1]+$n++); break;
					}		
					$pgo->save();
				}
			}
			$o->result = "success";	
		}else{
			$o->result = "error";
		}
		return response()->json($o);
	}
}
