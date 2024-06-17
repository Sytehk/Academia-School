<?php

namespace App\Services\SystemUpdate;

use App\Models\SystemUpdate;
use App\Models\SystemUpdateLog;
use Illuminate\Support\Carbon;

class UpdateLogger
{
    private ?SystemUpdate $update = null;

    private array $buffer = [];

    public function attach(SystemUpdate $update): self
    {
        $this->update = $update;

        return $this;
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $entry = [
            'time' => now()->format('H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->buffer[] = $entry;

        if ($this->update) {
            SystemUpdateLog::query()->create([
                'system_update_id' => $this->update->id,
                'level' => $level,
                'message' => $message,
                'context' => $context ?: null,
                'logged_at' => Carbon::now(),
            ]);
        }
    }

    public function entries(): array
    {
        return $this->buffer;
    }

    public function formatted(): string
    {
        return collect($this->buffer)
            ->map(fn ($e) => "[{$e['time']}] {$e['message']}")
            ->implode(PHP_EOL);
    }
}
