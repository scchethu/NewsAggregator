<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Article extends Model
{
    use HasFactory;
    use QueryCacheable;

    protected $fillable =['title','content','published_at','news_source_id','author'];

    public function categories()
    {
        return $this->belongsToMany(NewsCategory::class,'category_article','article_id','news_category_id',);
    }

    public function source()
    {
        return $this->belongsTo(NewsSource::class,"news_source_id");
    }
}
