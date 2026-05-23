<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class BillingAlerts extends Component
{
    #[Computed]
    public function pendingPaymentsJobs(): int
    {
        return $this->countJobsByQueue('payments');
    }

    #[Computed]
    public function pendingNotificationsJobs(): int
    {
        return $this->countJobsByQueue('notifications');
    }

    #[Computed]
    public function failedJobsLastHour(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }

        return DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHour())
            ->count();
    }

    #[Computed]
    public function failedJobsTotal(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }

        return DB::table('failed_jobs')->count();
    }

    #[Computed]
    public function webhooksLastHour(): int
    {
        if (! Schema::hasTable('stripe_webhook_events')) {
            return 0;
        }

        return DB::table('stripe_webhook_events')
            ->where('processed_at', '>=', now()->subHour())
            ->count();
    }

    #[Computed]
    public function staleWebhookMinutes(): ?int
    {
        if (! Schema::hasTable('stripe_webhook_events')) {
            return null;
        }

        $processedAt = DB::table('stripe_webhook_events')->max('processed_at');

        if (! is_string($processedAt) || $processedAt === '') {
            return null;
        }

        return (int) now()->diffInMinutes($processedAt);
    }

    #[Computed]
    public function activeAlerts(): array
    {
        $alerts = [];

        if ($this->failedJobsLastHour > 0) {
            $alerts[] = [
                'severity' => 'critical',
                'title' => 'Fallos de cola en la ultima hora',
                'description' => sprintf('%d job(s) fallidos detectados recientemente.', $this->failedJobsLastHour),
            ];
        }

        if ($this->pendingPaymentsJobs >= 50) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => 'Backlog alto en cola payments',
                'description' => sprintf('Hay %d job(s) pendientes en payments.', $this->pendingPaymentsJobs),
            ];
        }

        if ($this->pendingNotificationsJobs >= 100) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => 'Backlog alto en cola notifications',
                'description' => sprintf('Hay %d job(s) pendientes en notifications.', $this->pendingNotificationsJobs),
            ];
        }

        if ($this->staleWebhookMinutes !== null && $this->staleWebhookMinutes >= 30) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => 'Webhook sin actividad reciente',
                'description' => sprintf('Ultimo webhook procesado hace %d minuto(s).', $this->staleWebhookMinutes),
            ];
        }

        return $alerts;
    }

    #[Computed]
    public function recentFailedJobs(): Collection
    {
        if (! Schema::hasTable('failed_jobs')) {
            return collect();
        }

        return DB::table('failed_jobs')
            ->select(['id', 'uuid', 'queue', 'exception', 'failed_at'])
            ->orderByDesc('failed_at')
            ->limit(12)
            ->get()
            ->map(static function (object $row): object {
                $row->exception_summary = strtok((string) $row->exception, "\n") ?: 'Unknown queue exception';

                return $row;
            });
    }

    #[Computed]
    public function recentWebhookEvents(): Collection
    {
        if (! Schema::hasTable('stripe_webhook_events')) {
            return collect();
        }

        return DB::table('stripe_webhook_events')
            ->select(['stripe_event_id', 'type', 'processed_at'])
            ->orderByDesc('processed_at')
            ->limit(12)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.billing-alerts');
    }

    private function countJobsByQueue(string $queue): int
    {
        if (! Schema::hasTable('jobs')) {
            return 0;
        }

        return DB::table('jobs')
            ->where('queue', $queue)
            ->count();
    }
}
