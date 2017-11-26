<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Model as Eloquent;
use Validator;



class Elegant extends Eloquent
{
/*
    protected static $readOnly = false;

    protected $rules = array();

    protected $errors;

    public function validate()
    {
        // make a new validator object
        $v = Validator::make($this->data, $this->rules);

        // check for failure
        if ($v->fails())
        {
            // set errors and return false
            $this->errors = $v->errors;
            return false;
        }

        // validation pass
        return true;
    }

    public function errors()
    {
        return $this->errors;
    }


    protected static function boot()
    {
        // This is an important call, makes sure that the model gets booted
        // properly!
        parent::boot();
        
        // You can also replace this with static::creating or static::updating
        // if you want to call specific validation functions for each case.
        static::saving(function($model)
        {
		if($model::$readOnly)
			return false;
		else
        		return $model->validate();
        });
    }
*/



}
