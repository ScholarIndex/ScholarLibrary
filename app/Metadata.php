<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;


class Metadata extends Elegant {
   
	protected $collection = 'metadata';
	protected static $readOnly = true;



}
