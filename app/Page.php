<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;

class Page  extends Eloquent {
	protected $collection = 'pages';
}

