<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
  protected $fillable = [ 't_date', 'instance_id', 'nickname' ];
}
