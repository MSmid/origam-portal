<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{

    protected $appends = ['status'];

    protected $fillable = ['sync_data', 'entity_name'];

    public function synchronizations() {
      return $this->hasMany(Synchronization::class);
    }

    public function getStatusAttribute() {
      $status = $this->synchronizations()->orderBy('started_at', 'desc')->limit(1)->value('status');

      return ($status ? $status : 'none');
    }

    
}
