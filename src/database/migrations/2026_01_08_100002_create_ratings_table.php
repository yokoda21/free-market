<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            // ユニーク制約：1つの購入に対して評価者は1回のみ評価可能
            $table->unique(['purchase_id', 'rater_id']);
            
            // インデックス
            $table->index('rated_user_id');
            $table->index('rater_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
