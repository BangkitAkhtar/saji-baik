<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table): void {
            $table->string('contact_note')->default('Hubungi petugas outlet saat pengambilan (demo).');
            $table->string('image')->default('outlet-illustration.png');
            $table->unsignedSmallInteger('pickup_grace_minutes')->default(30);
        });
        Schema::table('meals', function (Blueprint $table): void {
            $table->foreignId('source_meal_id')->nullable()->constrained('meals')->nullOnDelete();
            $table->unique(['source_meal_id', 'available_date']);
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->dateTime('pickup_deadline_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('missed_at')->nullable();
            $table->index(['status', 'completed_at']);
        });
        DB::table('orders')->where('status', 'completed')->update(['completed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['status', 'completed_at']);
            $table->dropColumn(['pickup_deadline_at', 'completed_at', 'missed_at']);
        });
        Schema::table('meals', function (Blueprint $table): void {
            $table->dropUnique(['source_meal_id', 'available_date']);
            $table->dropConstrainedForeignId('source_meal_id');
        });
        Schema::table('outlets', fn (Blueprint $table) => $table->dropColumn(['contact_note', 'image', 'pickup_grace_minutes']));
    }
};
