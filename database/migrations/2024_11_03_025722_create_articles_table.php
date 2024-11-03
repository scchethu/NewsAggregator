<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('image_url')->nullable();
            $table->string('news_type')->nullable();
            $table->string('title')->fulltext();
            $table->text('content');
            $table->string('author')->index()->nullable();

            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->date('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
