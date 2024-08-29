<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    
    protected $table = 'menus';
    protected $guarded = ['id'];

    public function items()
    {
      return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    public function parent_items()
    {
      return $this->hasMany(MenuItem::class)->whereNull('parent_id');
    }
}
