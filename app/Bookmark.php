<?php

namespace App;

use Jenssegers\Mongodb\Model as Eloquent;

class Bookmark  extends Eloquent {
	protected $collection = 'lbc_bookmarks';
}

