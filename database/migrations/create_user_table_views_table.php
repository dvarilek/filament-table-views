<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomTableViewsTable extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-table-views.custom_table_view_model.table', 'custom_table_views'), function (Blueprint $table) {
            $table->id();

            $table->string('name', 64);
            $table->text('description')->nullable();
            $table->string('icon', 255)->nullable();

            config('filament-table-views.custom_table_view_model.color_attribute_is_json', false)
                ? $table->json('color')
                : $table->string('color', 255);

            $table->boolean('is_public')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_globally_highlighted')->default(false);

            $table->morphs('owner');
            $table->string('model_type', 255);

            $table->json('query_constrains');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-table-views.custom_table_view_model.table', 'custom_table_views'));
    }
}
