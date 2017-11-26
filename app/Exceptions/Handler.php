<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

	public function render($request, Exception $e)
	{
	
	    // 404 page when a model is not found
	    if ($e instanceof ModelNotFoundException) {
	        return response()->view('errors.404', [], 404);
	    }
	
	    // Custom error 500 view on production
	    if (env('APP_DEBUG') == FALSE) {
	        return response()->view('errors.500', [], 500);
	    }
	
	    return parent::render($request, $e);
	
	}
}
