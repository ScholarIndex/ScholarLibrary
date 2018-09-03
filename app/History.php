<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class History  extends Eloquent {
        protected $collection = 'lbc_history';



        public static function log($action, $user, $bid = "" , $issue ="", $page = "" ){
                $h = new History;
                $h->action = $action;
                $h->bid = $bid;
                $h->issue = $issue;
                $h->page = $page;
                $h->user = $user;
                $h->save();
        }


}
