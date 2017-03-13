<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;

class Validation  extends Eloquent {
	protected $collection = 'lbc_validations';
}

