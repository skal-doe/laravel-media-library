<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SkalDoe\MediaLibrary\MediaCollection;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('media_id')->constrained('media')->cascadeOnDelete();
            $table->uuidMorphs('mediable');
            $table->string('collection_name')->default(MediaCollection::DEFAULT);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id', 'collection_name'], 'media_attachments_mediable_collection_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};
