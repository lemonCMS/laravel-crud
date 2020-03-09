<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Blogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->index();
            $table->longText('description');
            $table->timestamps();
            $table->softDeletes();
        });

        \DB::table('blogs')->insert([
            'id' => 1,
            'title' => 'Blog post 1',
            'description' => 'Description of a blog post NO 1',
        ]);

        \DB::table('blogs')->insert([
            'id' => 2,
            'title' => 'Blog post 2',
            'description' => 'Description of a blog post NO 2',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog');
    }
}
