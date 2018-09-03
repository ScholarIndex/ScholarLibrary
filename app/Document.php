<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Document extends Elegant {
    protected $collection = 'documents';
	
	protected $rules = array(
   		'bid' => 'required|unique:documents,number',
   		'path' => 'required',
		'type' => 'required|in:monograph,journal_issue',
       	'content_ingester_version' => 'required',
		'internal_id' => 'required',
		'updated_at' => 'required',
		'ingestion_timestamp' => 'required',
   	);
	
	protected $casts = array(
		'bid' => 'string',
		'ingestion_timestamp' => 'datetime',
		'updated_at' => 'datetime', 
		'issue_number' => 'string',
		'internal_id' => 'string',
		'content_ingester_version' => 'string',
		'path' => 'string',
		'type' => 'string',
	);

	public function metadata(){
		return $this->hasOne('App\Metadata', '_id','metadata_id');		
	}

	public function __toString(){
		return sprintf("<Document: mongoid = %s, bid=%s, internal_id=%s, type=%s, number of pages=%i>", $this->id, $this->bid, $this->internal_id, $this->type, count($this->pages));
	}





}
