<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            // Legacy rows stay private until their owner is established explicitly.
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }
    public function down(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
