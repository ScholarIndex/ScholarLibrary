<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Bookmark  extends Eloquent {
        protected $collection = 'lbc_bookmarks_v2';
}
