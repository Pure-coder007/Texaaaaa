<?php

namespace App\Filament\Pbo\Resources\PboPointsResource\Widgets;

use Filament\Widgets\Widget;

class ReferralShareWidget extends Widget
{
    protected static string $view = 'filament.pbo.widgets.referral-link-widget';

    protected int | string | array $columnSpan = 'full';

    public function getReferralUrl(): string
    {
        return route('filament.pbo.auth.register', ['ref' => $this->getReferralCode()]);
    }

    public function getReferralCode(): string
    {
        return auth()->user()->pbo_code ?? 'PBOCODE';
    }

    public function copyReferralLink(): void
    {
        $this->dispatch('copied');
    }
}