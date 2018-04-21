<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDashboardSetting extends Model
{
    protected $appends = [];

    public function users() {
      return $this->belongsTo(User::class);
    }
}
