<?php

namespace lbs\auth\app\models;

class User extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing = true;
    protected $keytype = "int";
}
