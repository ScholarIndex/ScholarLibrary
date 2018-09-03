<?php

namespace App\Helpers;

use App\Metadata;
use Cache;

class IIIFHelpers{
  
  public static function pageUri($bid, $number, $page = ""){


	if( ! Cache::has('bid_type_document_'.$bid)){
		$m = Metadata::where('bid', $bid)->first();
		Cache::put('bid_type_document_'.$bid, $m->type_document, 1440);
		Cache::put('bid_provenance_'.$bid, $m->provenance, 1440);
	}

	$type_document = Cache::get('bid_type_document_'.$bid);
	$provenance = Cache::get('bid_provenance_'.$bid);

	$uri = env('IIIF_ROOT');

	if($type_document == 'monograph')
		$repo = 'books';
	else
		$repo = 'journals';
	
	$fullbid = ($type_document=='monograph') ? $provenance.'_'.$bid : $bid;
	$issue = str_replace('.','_',$number);

	$p = ($page == "") ? "" : "::".$page;

	return $uri.$repo."::".$fullbid."::".$issue.$p;
	

  }

}
