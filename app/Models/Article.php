<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    public function categories()
    {
        return $this->belongsToMany(NewsCategory::class,'category_article','article_id','news_category_id',);
    }

    public function source()
    {
        return $this->belongsTo(NewsSource::class,"news_source_id");
    }
}
