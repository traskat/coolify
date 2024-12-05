<?php

use App\Models\Team;
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
        $teams = Team::all();
        foreach ($teams as $team) {
            $team->discordNotificationSettings()->updateOrCreate(
                ['team_id' => $team->id],
                [
                    'discord_enabled' => $team->discord_enabled ?? false,
                    'discord_webhook_url' => $team->discord_webhook_url,

                    'deployment_success_discord_notification' => $team->discord_notifications_deployments ?? false,
                    'deployment_failure_discord_notification' => $team->discord_notifications_deployments ?? true,
                    'backup_success_discord_notification' => $team->discord_notifications_database_backups ?? false,
                    'backup_failure_discord_notification' => $team->discord_notifications_database_backups ?? true,
                    'scheduled_task_success_discord_notification' => $team->discord_notifications_scheduled_tasks ?? false,
                    'scheduled_task_failure_discord_notification' => $team->discord_notifications_scheduled_tasks ?? true,
                    'status_change_discord_notification' => $team->discord_notifications_status_changes ?? false,
                    'server_disk_usage_discord_notification' => $team->discord_notifications_server_disk_usage ?? true,
                ]
            );
        }

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'discord_enabled',
                'discord_webhook_url',
                'discord_notifications_test',
                'discord_notifications_deployments',
                'discord_notifications_status_changes',
                'discord_notifications_database_backups',
                'discord_notifications_scheduled_tasks',
                'discord_notifications_server_disk_usage',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('discord_enabled')->default(false);
            $table->string('discord_webhook_url')->nullable();
            $table->boolean('discord_notifications_test')->default(true);
            $table->boolean('discord_notifications_deployments')->default(true);
            $table->boolean('discord_notifications_status_changes')->default(true);
            $table->boolean('discord_notifications_database_backups')->default(true);
            $table->boolean('discord_notifications_scheduled_tasks')->default(true);
            $table->boolean('discord_notifications_server_disk_usage')->default(true);
        });

        $teams = Team::with('discordNotificationSettings')->get();
        foreach ($teams as $team) {
            if ($settings = $team->discordNotificationSettings) {
                $team->update([
                    'discord_enabled' => $settings->discord_enabled,
                    'discord_webhook_url' => $settings->discord_webhook_url,
                    'discord_notifications_test' => true,
                    'discord_notifications_deployments' => $settings->deployment_success_discord_notification,
                    'discord_notifications_status_changes' => $settings->status_change_discord_notification,
                    'discord_notifications_database_backups' => $settings->backup_success_discord_notification,
                    'discord_notifications_scheduled_tasks' => $settings->scheduled_task_success_discord_notification,
                    'discord_notifications_server_disk_usage' => $settings->server_disk_usage_discord_notification,
                ]);
            }
        }
    }
};
