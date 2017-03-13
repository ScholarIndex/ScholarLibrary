<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;

class Rating  extends Eloquent {
	protected $collection = 'lbc_quality_ratings';
}

