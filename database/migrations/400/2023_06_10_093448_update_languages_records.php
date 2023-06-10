<?php

use App\Models\Languages;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Languages::where(['id' => 1])->update(['code' => 'es']);
        Languages::where(['id' => 2])->update(['code' => 'en']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
