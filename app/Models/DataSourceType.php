<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSourceType extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'name',
  ];

  public $timestamps = false;
}
