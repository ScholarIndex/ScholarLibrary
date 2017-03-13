<?php

namespace App\Http\Controllers;
/*
use App\Http\Controllers\Controller;
use App;
use DB;
use Config;

use Session;
use Hash;*/
use View;
use Validator; 
use Input;
use Redirect;
use Auth;
use App\User;
class AdminController extends Controller
{


	public function users(){
		$data = array();


		
		return view('admin.users', $data);
	}

}