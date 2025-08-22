<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->integer('price')->unsigned();
            $table->string('brand')->nullable();
            $table->foreignId('condition_id')->constrained()->onDelete('cascade');
            $table->string('image_url')->nullable();
            $table->boolean('is_sold')->default(false);
            $table->timestamps();

            // インデックス
            $table->index('user_id');
            $table->index('condition_id');
            $table->index('is_sold');
            $table->index('price');
            $table->index(['is_sold', 'created_at']); // 商品一覧用の複合インデックス
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
