<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInspection extends ViewRecord
{
    protected static string $resource = InspectionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Inspection Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('client.name')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('client.email')
                            ->label('Client Email'),
                        Infolists\Components\TextEntry::make('client.phone')
                            ->label('Client Phone'),
                        Infolists\Components\TextEntry::make('estate.name')
                            ->label('Estate'),
                        Infolists\Components\TextEntry::make('scheduled_date')
                            ->label('Date')
                            ->date('F j, Y'),
                        Infolists\Components\TextEntry::make('inspection_time')
                            ->label('Time')
                            ->state('10:00 AM'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Additional Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Estate Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('estate.city.name')
                            ->label('City'),
                        Infolists\Components\TextEntry::make('estate.location.name')
                            ->label('Location'),
                        Infolists\Components\TextEntry::make('estate.address')
                            ->label('Address')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Requested On')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('confirm')
                ->label('Confirm Inspection')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function (): void {
                    $this->record->status = 'confirmed';
                    $this->record->save();

                    Notification::make()
                        ->title('Inspection confirmed successfully')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),

            Actions\Action::make('complete')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->action(function (): void {
                    $this->record->status = 'completed';
                    $this->record->save();

                    Notification::make()
                        ->title('Inspection marked as completed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                })
                ->visible(fn (): bool => in_array($this->record->status, ['pending', 'confirmed'])),

            Actions\Action::make('cancel')
                ->label('Cancel Inspection')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->status = 'cancelled';
                    $this->record->save();

                    Notification::make()
                        ->title('Inspection cancelled')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                })
                ->visible(fn (): bool => in_array($this->record->status, ['pending', 'confirmed'])),

            // Actions\Action::make('notify_client')
            //     ->label('Notify Client')
            //     ->icon('heroicon-o-bell')
            //     ->color('primary')
            //     ->form([
            //         Forms\Components\TextInput::make('subject')
            //             ->label('Subject')
            //             ->default(fn (): string => "Inspection Update: {$this->record->estate->name}")
            //             ->required(),
            //         Forms\Components\Textarea::make('message')
            //             ->label('Message')
            //             ->default(fn (): string => "Dear {$this->record->client->name},\n\nWe would like to provide an update regarding your inspection scheduled for {$this->record->scheduled_date->format('F j, Y')} at 10:00 AM.\n\n[Your message here]\n\nBest regards,\nEstate Management Team")
            //             ->required()
            //             ->rows(5),
            //     ])
            //     ->action(function (array $data): void {
            //         // Here you would implement the actual notification logic
            //         // For example, sending an email to the client

            //         Notification::make()
            //             ->title('Client notified successfully')
            //             ->success()
            //             ->send();
            //     }),
        ];
    }
}