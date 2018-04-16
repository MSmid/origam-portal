<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Synchronization extends Model
{
    protected $fillable = [
      'finished_at', 'message', 'status', 'data_source_id'
    ];
}
