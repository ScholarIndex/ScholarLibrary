<?php

Route::get('/',				'DocumentController@browse');
Route::get('documents',		'DocumentController@browse');

Route::get('login', 'HomeController@showLogin');
Route::post('login','HomeController@doLogin');
Route::get('logout', 'HomeController@doLogout');
Route::get('profile', array('middleware' => 'auth', 'uses' => 'HomeController@profile'));
Route::post('changepassword', array('middleware' => 'auth', 'uses' => 'HomeController@changePassword'));
Route::get('forgot', 'HomeController@forgot');
Route::post('newpassword', 'HomeController@newPassword');

Route::get('admin/users', 'AdminController@users');

Route::get('documents/ajaxSearch','DocumentController@ajaxSearch');
Route::post('document/rate', 'DocumentController@ajaxRate');
Route::post('document/ajaxLoadMorePages', 'DocumentController@ajaxLoadMorePages');
Route::post('document/ajaxLoadTocPages', 'DocumentController@ajaxLoadTocPages');
Route::post('document/ajaxLoadTocEntries', 'DocumentController@ajaxLoadTocEntries');
Route::post('document/ajaxLoadTocOverview', 'DocumentController@ajaxLoadTocOverview');
Route::post('document/ajaxLoadIndexOverview', 'DocumentController@ajaxLoadIndexOverview');
Route::post('document/ajaxSavePrintedPage', 'DocumentController@ajaxSavePrintedPage');
Route::post('document/pageindex/{action}', 'DocumentController@ajaxPageindex');
Route::post('document/pagegolden/{action}', 'DocumentController@ajaxPagegolden');

Route::post('document/saveFootnotes', 'DocumentController@saveFootnotes');
Route::post('document/saveSplit', 'DocumentController@saveSplit');
Route::get('bookmarks/{type}', array('middleware' => 'auth', 'uses' => 'DocumentController@bookmarks'));
Route::get('documents/{bid}/issueSearch/{query?}','DocumentController@issueSearch');
Route::get('document/{bid}/{issue}', 	array('middleware' => 'auth', 'uses' => 'DocumentController@view'));
Route::get('document/{bid}/{issue}/{page}', 	array('middleware' => 'auth', 'uses' => 'DocumentController@page'));
Route::post('document/{action}', 'DocumentController@ajaxCheck');
Route::post('document/meta/{action}', 'DocumentController@ajaxMeta');
Route::post('document/toc/{action}', 'DocumentController@ajaxToc');
Route::post('document/bm/{type}/{action}', 'DocumentController@ajaxBookmark');
Route::get('session/{key}/{value}', 'DocumentController@session'); 

