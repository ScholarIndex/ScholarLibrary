<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;
use App\Metadata;

class Document extends Eloquent {
	protected $collection = 'documents';
    
	public function metadata()
    {
        return $this->hasOne('App\Metadata', 'bid', 'bid');
    }

}

