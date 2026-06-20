<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
        });

        // Backfill existing users
        foreach (User::all() as $user) {
            $base = Str::slug($user->name);
            $username = $base;
            $i = 2;
            while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.'-'.$i++;
            }
            $user->updateQuietly(['username' => $username]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
