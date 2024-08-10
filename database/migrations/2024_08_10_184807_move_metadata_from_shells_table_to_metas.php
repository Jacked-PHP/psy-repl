<?php

use App\Enums\ShellMeta;
use App\Models\Shell;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Shell::query()->chunk(100, function ($shells) {
            /** @var Shell $shell */
            foreach ($shells as $shell) {
                $shell->setManyMeta([
                    ShellMeta::IS_DOCKER_CONTEXT->value => $shell->is_docker_context,
                    ShellMeta::DOCKER_CONTAINER->value => $shell->docker_container,
                    ShellMeta::DOCKER_WORKDIR->value => $shell->docker_workdir,
                ]);
            }
        });

        Schema::table('shells', function (Blueprint $table) {
            $table->dropColumn('is_docker_context');
            $table->dropColumn('docker_container');
            $table->dropColumn('docker_workdir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no way back
    }
};
