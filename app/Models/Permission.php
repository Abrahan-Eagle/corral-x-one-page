<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{

    protected $fillable = [
        'name', 'slug', 'description'
    ];
    
    //13-Jul-20
    public function roles(){
        return $this->belongsToMany('App\Models\Role')->withTimestamps();
    }
    

}
