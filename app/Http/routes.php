<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'MainController@welcome');
Route::get('/about', 'MainController@about');

Route::group(array('middleware' => 'auth'), function () {

	Route::get('/search', 'MainController@search');
	Route::get('/lastSearch', 'MainController@lastSearch');
	Route::post('/yearChart', 'MainController@yearChart');
	Route::post('/search', 'MainController@searchPost');
	Route::post('/count', 'MainController@countPost');
	Route::post('/filters', 'MainController@filters');
	Route::get('/mydocuments', 'MainController@mydocuments');
	
	Route::get('/article/overview/{oid}', 'MainController@articleOverview');
	Route::get('/article/scans/{oid}', 'MainController@articleScans');
	Route::get('/article/viewer/{oid}', 'MainController@articleViewer');
	Route::get('/article/references/{oid}', 'MainController@articleReferences');
	Route::get('/article/progress/{oid}', 'MainController@articleProgress');


	Route::get('/document/overview/{bid}/{issue?}', 'MainController@documentOverview');
	Route::get('/document/journal/{bid}/{issue?}', 'MainController@documentJournal');
	
	Route::get('/document/progress/{document_id}', 'MainController@documentProgress');
	
	
	Route::get('/document/bookmarks/{document_id}/{page_id?}', 'MainController@documentBookmarks');
	Route::get('/document/ajaxbookmarks/{action}/{type}/{document_id}/{page_id?}', 'MainController@documentAjaxBookmarks');
	Route::get('/document/indexgolden/{document_id}/{page_id}', 'MainController@documentIndexgolden');
	Route::get('/document/ajaxindexgolden/{action}/{type}/{document_id}/{page_id}', 'MainController@documentAjaxIndexgolden');


	Route::get('/document/scans/{bid}/{issue?}', 'MainController@documentScans');
	Route::get('/document/references/{bid}/{issue?}', 'MainController@documentReferences');
	Route::get('/document/page/reference/{oid}', 'MainController@documentReference');
	Route::post('/document/page/references/textsearch', 'MainController@documentTextSearch');
	Route::get('/document/toc/{bid}/{issue?}', 'MainController@documentToc');
	Route::get('/document/viewer/{bid}/{issue?}', 'MainController@documentViewer');
	
	Route::get('/document/page/references/{oid}', 'MainController@pageReferences'); 
	Route::get('/document/page/text/{oid}', 'MainController@pageText'); 
	Route::get('/search/authors/{term?}', 'MainController@searchAuthors');
	Route::get('/search/reftitle/{source}/{term?}', 'MainController@searchReftitle');
	
	Route::post('/document/saveSplit', 'MainController@saveSplit');
	Route::post('/document/savePpn', 'MainController@savePpn');
	Route::post('/document/saveArticle', 'MainController@saveArticle');
	
	Route::post('/document/saveReferenceDisambiguationValid', 'MainController@saveRefDisValid');
	Route::post('/document/saveReferenceDisambiguation', 'MainController@saveRefDis');
	Route::post('/document/saveReferenceDisambiguationState', 'MainController@saveRefDisState');
	Route::post('/document/saveReferenceState', 'MainController@saveRefState');

});

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

