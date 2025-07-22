<?php

declare(strict_types=1);

use Dvarilek\FilamentTableViews\Models\SavedTableView;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_table_view_user_configs', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_default')->default(false);
            $table->integer('order')->default(0);

            $table->foreignIdFor(SavedTableView::class)
                ->constrained('saved_table_views')
                ->cascadeOnDelete();
            $table->morphs('user');

            $table->unique(['saved_table_view_id', 'user_id', 'user_type'], 'unique_view_user_config');
            $table->index(['user_id', 'user_type', 'is_default'], 'user_default_config_index');
            $table->index(['user_id', 'user_type', 'order'], 'user_order_config_index');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_table_view_user_configs');
    }
};
