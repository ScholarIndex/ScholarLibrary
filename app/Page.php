<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Page extends Elegant {
        protected $collection = 'pages';


	public function document(){
		return $this->belongsTo('App\Document');
	}

}
