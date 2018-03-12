<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usr extends Model
{
    protected  $connection = 'mysql';

    protected $table = 'usr';

    public  $timestamps = false;

}
