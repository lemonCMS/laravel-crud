<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BlogTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('blog_id');
            $table->string('tag')->index();
        });

        \DB::table('blog_tags')->insert([
            'id' => 1,
            'blog_id' => 1,
            'tag' => 'Tag 1',
        ]);

        \DB::table('blog_tags')->insert([
            'id' => 2,
            'blog_id' => 1,
            'tag' => 'Tag 2',
        ]);

        \DB::table('blog_tags')->insert([
            'id' => 3,
            'blog_id' => 1,
            'tag' => 'Tag 3',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_tag');
    }
}
