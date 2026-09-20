<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id('role_id');
            $table->string('slug', 64)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('can_manage_schedule')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id')->nullable()->after('email');

            $table->foreign('role_id')
                ->references('role_id')->on('roles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
