<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class NewsCategory extends Model
{
    use HasFactory;
    use QueryCacheable;
    protected $fillable = ['name'];
}
