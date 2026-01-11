<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTradeColumnsToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('payment_method');
            $table->timestamp('completed_at')->nullable()->after('is_completed');
            $table->boolean('buyer_evaluated')->default(false)->after('completed_at');
            $table->boolean('seller_evaluated')->default(false)->after('buyer_evaluated');
            
            // インデックス追加
            $table->index('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['is_completed']);
            $table->dropColumn([
                'is_completed',
                'completed_at',
                'buyer_evaluated',
                'seller_evaluated',
            ]);
        });
    }
}
