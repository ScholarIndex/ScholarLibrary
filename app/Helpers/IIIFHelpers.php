<?php

namespace App\Helpers;

use App\Metadata;
use Cache;

class IIIFHelpers{

  public static function bidWithProv($bid, $number){
        if( ! Cache::has('bid_type_document_'.$bid)){
                $m = Metadata::where('bid', $bid)->first();
                Cache::put('bid_type_document_'.$bid, $m->type_document, 1440);
				if( $m->type_document == 'monograph'){
                        Cache::put('bid_provenance_'.$bid, $m->provenance, 1440);
                }
		}
		$type_document = Cache::get('bid_type_document_'.$bid);
		
		if($type_document != 'monograph'){

			if( ! Cache::has('bid_provenance_'.$bid.'_'.$number)){
				$m = Metadata::where('bid', $bid)->first();
				foreach($m->issues as $iss){
					if($iss['foldername'] == $number){
						Cache::put('bid_provenance_'.$bid.'_'.$number, $iss['provenance'], 1440);
						break;
					}
				}
			}
        }


	if($type_document=='monograph'){
		$provenance = Cache::get('bid_provenance_'.$bid);
	}else{
		$provenance = Cache::get('bid_provenance_'.$bid.'_'.$number);
	}

        if($type_document == 'monograph')
                $repo = 'books';
        else
                $repo = 'journals';
	
		
	$fullbid = $provenance.'_'.$bid;

	return $repo."::".$fullbid;	  
  }
  
  public static function pageUri($bid, $number, $page = ""){


        if( ! Cache::has('bid_type_document_'.$bid)){
                $m = Metadata::where('bid', $bid)->first();
                Cache::put('bid_type_document_'.$bid, $m->type_document, 1440);
				if( $m->type_document == 'monograph'){
                        Cache::put('bid_provenance_'.$bid, $m->provenance, 1440);
                }
		}
		$type_document = Cache::get('bid_type_document_'.$bid);
		
		if($type_document != 'monograph'){

			if( ! Cache::has('bid_provenance_'.$bid.'_'.$number)){
				$m = Metadata::where('bid', $bid)->first();
				foreach($m->issues as $iss){
					if($iss['foldername'] == $number){
						Cache::put('bid_provenance_'.$bid.'_'.$number, $iss['provenance'], 1440);
						break;
					}
				}
			}
        }


	if($type_document=='monograph'){
		$provenance = Cache::get('bid_provenance_'.$bid);
	}else{
		$provenance = Cache::get('bid_provenance_'.$bid.'_'.$number);
	}
	$uri = env('IIIF_ROOT');

	if($type_document == 'monograph')
		$repo = 'books';
	else
		$repo = 'journals';
	
	//$fullbid = ($type_document=='monograph') ? $provenance.'_'.$bid : $bid;
	$fullbid = $provenance.'_'.$bid;
	$issue = str_replace('.','_',$number);

	$p = ($page == "") ? "" : "::".$page;

	return $uri.$repo."::".$fullbid."::".$issue.$p;
	

  }

}
