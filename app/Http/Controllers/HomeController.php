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
use Hash;
use Log;
use App\User;
use Mail;

Validator::extend('loggedPassword', function($attribute, $value, $parameters)
{
     return Auth::validate(array('login' => Auth::user()->login, 'password' => $value));
},'Invalid current password.');

class HomeController extends Controller
{


	public function showLogin()
	{
	
		$data = array();
		$data['page'] = "LOGIN";
	    return View::make('home.login', $data);
	}

	public function profile()
	{
	
		$data = array();
		$data['page'] = "LOGIN";
	    return View::make('home.profile', $data);
	}
	
	public function forgot(){
		$data = array();
		$data['page'] = "LOGIN";
		return View::make('home.forgot', $data);
	}

	public function newPassword(){
		
		$rules = array(
		    'email' => 'required',
		);
		
		
		$validator = Validator::make(Input::all(), $rules);
		
	
		if ($validator->fails()) {
		    return Redirect::to('forgot')->with('error',join('',$validator->errors()->all('<li>:message</li>')));
		} else {
			$u = User::where('email',Input::get('email'))->first();
			if($u){
				$new = chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).chr(rand(65,90)).rand(1000,9999);
				$u->password = Hash::make($new);
				$u->save();

				Mail::send('emails.newpassword', array('new' => $new), function($message) use ($u){
				    $message->from('lbcatalogue@archives.world', 'LBCatalogue');
				    $message->to($u->email);
					$message->subject('LB Catalogue > Password reset');
				});
				return Redirect::to('login')->with('success', 'We just sent you a new password on your email.');
			}else{
				return Redirect::to('forgot')->with('error', 'No user with this email has been found.');

			}		   
		}
	}

	public function changePassword()
	{

		$rules = array(
		    'currentpassword' => 'required|alphaNum|min:3|loggedPassword',
			'newpassword' => 'required|alphaNum|min:3',
			'confirmnewpassword' => 'required|alphaNum|min:3|same:newpassword',
		);
		
		
		$validator = Validator::make(Input::all(), $rules);
		
	
		if ($validator->fails()) {
						
		    return Redirect::to('profile')
		        ->with('error',join('',$validator->errors()->all('<li>:message</li>')));
		} else {
			$u = Auth::user();
			$u->password = Hash::make(Input::get('newpassword'));
			$u->save();
			
		    $userdata = array(
		        'login'     => $u->login,
		        'password'  => Input::get('newpassword')
		    );
		
		    return Redirect::to('documents')->with('success', 'Your password has been successfully changed');
		   
		}
	}


	public function doLogin()
	{
		// validate the info, create rules for the inputs
		$rules = array(
		    'login'    => 'required|alphaNum', // make sure the email is an actual email
		    'password' => 'required|alphaNum|min:3' // password can only be alphanumeric and has to be greater than 3 characters
		);
		
		// run the validation rules on the inputs from the form
		$validator = Validator::make(Input::all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
					
		    return Redirect::to('login')
		        ->with('error', join('',$validator->errors()->all('<li>:message</li>'))) // send back all errors to the login form
		        ->withInput(Input::except('password')); // send back the input (not the password) so that we can repopulate the form
		} else {
		
		    // create our user data for the authentication
		    $userdata = array(
		        'login'     => Input::get('login'),
		        'password'  => Input::get('password')
		    );
		
		    // attempt to do the login
		    if (Auth::attempt($userdata)) {
		
	 	 		return redirect()->intended('documents');

		
		    } else {        
		
		        // validation not successful, send back to form 
		        return Redirect::to('/login')->with('error','Invalid credentials.');
		        
		
		    }
		
		}
	}

	 public function doLogout()
	 {
	  Auth::logout(); // log the user out of our application
	  return Redirect::to('documents'); // redirect the user to the login screen
	 }
}