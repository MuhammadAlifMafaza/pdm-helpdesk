<?php

namespace App\Events;

// use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HelpdeskActivityCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $module,
        public string $activity,
        public string $kode,
        public ?int $actorId = null,
        public array $data = [],
        public ?string $url = null,
        public ?string $referenceId = null,
    ) {}
}
