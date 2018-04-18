<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scheduler extends Model
{
    protected $fillable = [
      'start_at', 'name', 'data_source_id', 'period', 'is_active'
    ];
}
