<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class NewsSource extends Model
{
    use HasFactory;
    use QueryCacheable;


    protected $fillable = ['name'];
}
