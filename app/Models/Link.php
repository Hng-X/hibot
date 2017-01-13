<?php
namespace App\Models;

class Link extends BaseModel {

	protected $valid;

	protected $status_code;

	protected $guarded=[];

	protected $table = 'links';

/*
    public function tags()
    {
          return $this->belongsToMany(Tag::class);
    }
   */

}
