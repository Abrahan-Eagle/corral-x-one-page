<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'full-access'
    ];

 
    //13-Jul-20
    public function users(){
        return $this->belongsToMany('App\Models\User')->withTimestamps();
    }

    //13-Jul-20
    public function permissions(){
        return $this->belongsToMany('App\Models\Permission')->withTimestamps();
    }

}
