<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    use HasFactory;
    
    protected $table = 'menu_items';
    protected $guarded = ['id'];

    public function children()
    {
      return $this->hasMany(MenuItem::class, 'parent_id')->with('children');
    }

    public function menu()
    {
      return $this->belongsTo(Menu::class);
    }

    public function link($absolute = false)
    {
        return $this->prepareLink($absolute, $this->route, $this->url);
    }

    protected function prepareLink($absolute, $route, $url)
    {       
        if (!is_null($route)) {
            if (!Route::has($route)) {
                return '#';
            }

            return route($route, $absolute);
        }

        if ($absolute) {
            return url($url);
        }

        return $url;
    }

    public function highestOrderMenuItem($parent = null)
    {
        $order = 1;

        $item = $this->where('parent_id', '=', $parent)
            ->orderBy('order', 'DESC')
            ->first();

        if (!is_null($item)) {
            $order = intval($item->order) + 1;
        }

        return $order;
    }
}
