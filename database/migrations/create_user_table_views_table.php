<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Auth\User;

class CreateCustomTableViewsTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_table_views', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon');

            config('filament-table-views.user-table-view-model.color_attribute_is_json', false)
                ? $table->json('color')
                : $table->string('color');

            $table->boolean('is_public')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_globally_highlighted')->default(false);

            $table->foreignIdFor(config('auth.providers.users.model', User::class))->constrained()->cascadeOnDelete();

            $table->json('query_constraint_data');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_table_views');
    }
}