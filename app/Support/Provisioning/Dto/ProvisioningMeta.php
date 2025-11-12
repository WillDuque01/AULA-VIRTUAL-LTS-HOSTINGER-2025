<?php

namespace App\Support\Provisioning\Dto;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ProvisioningMeta
{
    public function __construct(
        private readonly ?User $user,
        private readonly ?string $ipAddress,
        private readonly ?string $userAgent,
        private readonly bool $shouldWriteEnv,
        private readonly bool $shouldCacheConfig,
        private readonly bool $shouldPersistAudit,
    ) {
    }

    public static function fromDefaults(): self
    {
        $request = Request::instance();

        return new self(
            Auth::user(),
            $request?->ip(),
            $request?->userAgent(),
            shouldWriteEnv: ! app()->runningUnitTests(),
            shouldCacheConfig: ! app()->runningUnitTests(),
            shouldPersistAudit: ! app()->runningUnitTests(),
        );
    }

    public static function make(
        ?User $user = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?bool $shouldWriteEnv = null,
        ?bool $shouldCacheConfig = null,
        ?bool $shouldPersistAudit = null,
    ): self {
        $defaults = self::fromDefaults();

        return new self(
            $user ?? $defaults->user(),
            $ipAddress ?? $defaults->ipAddress(),
            $userAgent ?? $defaults->userAgent(),
            $shouldWriteEnv ?? $defaults->shouldWriteEnv(),
            $shouldCacheConfig ?? $defaults->shouldCacheConfig(),
            $shouldPersistAudit ?? $defaults->shouldPersistAudit(),
        );
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function shouldWriteEnv(): bool
    {
        return $this->shouldWriteEnv;
    }

    public function shouldCacheConfig(): bool
    {
        return $this->shouldCacheConfig;
    }

    public function shouldPersistAudit(): bool
    {
        return $this->shouldPersistAudit;
    }
}
