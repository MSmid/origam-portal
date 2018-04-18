<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  protected $append = [
    'status'
  ];
  protected $fillable = [
    'name', 'data_source_id', 'slug_datatable_name'
  ];

  public function dataSource() {
    return $this->belongsTo(DataSource::class);
  }

  public function getStatusAttribute() {
    return $this->dataSource->getStatusAttribute();
  }
}
