<?php

namespace App\Filament\Saas\Resources\Academies;

use App\Filament\Saas\Resources\Academies\Pages\CreateAcademy;
use App\Filament\Saas\Resources\Academies\Pages\EditAcademy;
use App\Filament\Saas\Resources\Academies\Pages\ListAcademies;
use App\Filament\Saas\Resources\Academies\Pages\ViewAcademy;
use App\Models\Club;
use App\Models\SaasPlan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AcademyResource extends Resource
{
    protected static ?string $model = Club::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Academies';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Academies';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Academy Details')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        Select::make('sport_type')
                            ->options([
                                'padel'       => 'Padel',
                                'tennis'      => 'Tennis',
                                'pickleball'  => 'Pickleball',
                                'squash'      => 'Squash',
                                'multi_sport' => 'Multi Sport',
                            ])
                            ->default('padel')
                            ->required()
                            ->native(false),
                        Textarea::make('address')->required()->rows(3)->columnSpanFull(),
                        Select::make('subscription_status')
                            ->options([
                                'active'   => 'Active',
                                'inactive' => 'Inactive',
                                'trial'    => 'Trial',
                            ])
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Academy Details')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('sport_type')->badge(),
                        TextEntry::make('address')->columnSpanFull(),
                        TextEntry::make('subscription_status')->badge()
                            ->color(fn (string $state) => match ($state) {
                                'active'   => 'success',
                                'trial'    => 'warning',
                                'inactive' => 'danger',
                                default    => 'gray',
                            }),
                        TextEntry::make('registration_status')->badge()
                            ->color(fn (string $state) => match ($state) {
                                'approved' => 'success',
                                'pending'  => 'warning',
                                'rejected' => 'danger',
                                default    => 'gray',
                            }),
                        TextEntry::make('rejection_reason')
                            ->placeholder('—')
                            ->visible(fn (Club $record) => $record->registration_status === 'rejected'),
                        TextEntry::make('approved_at')->dateTime()->placeholder('—'),
                        TextEntry::make('approvedBy.name')->label('Approved By')->placeholder('—'),
                        TextEntry::make('courts_count')
                            ->label('Total Courts')
                            ->state(fn (Club $record) => $record->courts()->count()),
                        TextEntry::make('users_count')
                            ->label('Total Users')
                            ->state(fn (Club $record) => $record->users()->count()),
                        TextEntry::make('created_at')->dateTime(),
                    ]),

                Section::make('Current SaaS Subscription')
                    ->schema([
                        TextEntry::make('latestSaasSubscription.plan.name')
                            ->label('Plan')
                            ->placeholder('—'),
                        TextEntry::make('latestSaasSubscription.billing_cycle')
                            ->label('Billing Cycle')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('latestSaasSubscription.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'active'    => 'success',
                                'trial'     => 'warning',
                                'past_due'  => 'danger',
                                'cancelled', 'expired' => 'gray',
                                default     => 'gray',
                            })
                            ->placeholder('—'),
                        TextEntry::make('latestSaasSubscription.amount_paid')
                            ->label('Amount Paid')
                            ->money('USD')
                            ->placeholder('—'),
                        TextEntry::make('latestSaasSubscription.starts_at')
                            ->label('Starts At')
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('latestSaasSubscription.ends_at')
                            ->label('Ends At')
                            ->date()
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sport_type')->badge()->sortable(),
                TextColumn::make('registration_status')
                    ->label('Registration')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('subscription_status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'   => 'success',
                        'trial'    => 'warning',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('latestSaasSubscription.plan.name')
                    ->label('SaaS Plan')
                    ->placeholder('—'),
                TextColumn::make('latestSaasSubscription.billing_cycle')
                    ->label('Cycle')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('latestSaasSubscription.ends_at')
                    ->label('Sub. Ends')
                    ->date()
                    ->placeholder('—')
                    ->color(fn (Club $record) => $record->latestSaasSubscription?->isExpired() ? 'danger' : null),
                TextColumn::make('courts_count')
                    ->counts('courts')
                    ->label('Courts'),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users'),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('registration_status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('subscription_status')
                    ->options([
                        'active'   => 'Active',
                        'trial'    => 'Trial',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Club $record) => $record->registration_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Club $record): void {
                        $record->update([
                            'registration_status' => 'approved',
                            'subscription_status' => 'trial',
                            'approved_at'         => now(),
                            'approved_by'         => auth()->id(),
                            'rejection_reason'    => null,
                        ]);

                        // Cancel any pending paid subscription — trial comes first
                        $record->saasSubscriptions()
                            ->where('status', 'pending')
                            ->update(['status' => 'cancelled']);

                        // Create a 14-day free trial subscription
                        \App\Models\ClubSaasSubscription::create([
                            'club_id'       => $record->id,
                            'saas_plan_id'  => null,
                            'billing_cycle' => 'trial',
                            'amount_paid'   => 0,
                            'starts_at'     => now()->toDateString(),
                            'ends_at'       => now()->addDays(14)->toDateString(),
                            'status'        => 'trial',
                            'notes'         => 'Auto-generated 14-day free trial on academy approval.',
                        ]);

                        Notification::make()
                            ->title("Academy \"{$record->name}\" approved with 14-day free trial.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Club $record) => $record->registration_status === 'pending')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Club $record, array $data): void {
                        $record->update([
                            'registration_status' => 'rejected',
                            'subscription_status' => 'inactive',
                            'rejection_reason'    => $data['rejection_reason'],
                        ]);
                        $record->saasSubscriptions()
                            ->where('status', 'pending')
                            ->latest()
                            ->first()
                            ?->update(['status' => 'cancelled']);

                        Notification::make()
                            ->title("Academy \"{$record->name}\" rejected.")
                            ->danger()
                            ->send();
                    }),

                Action::make('activate_plan')
                    ->label('Activate Plan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (Club $record) => $record->registration_status === 'approved'
                        && $record->subscription_status === 'inactive'
                        && $record->saasSubscriptions()->where('status', 'cancelled')->where('billing_cycle', '!=', 'trial')->exists()
                    )
                    ->requiresConfirmation()
                    ->modalDescription('This will activate the academy\'s paid subscription.')
                    ->action(function (Club $record): void {
                        $sub = $record->saasSubscriptions()
                            ->where('status', 'cancelled')
                            ->where('billing_cycle', '!=', 'trial')
                            ->latest()
                            ->first();

                        if ($sub) {
                            $endsAt = $sub->billing_cycle === 'yearly'
                                ? now()->addYear()
                                : now()->addMonth();

                            $sub->update([
                                'status'    => 'active',
                                'starts_at' => now()->toDateString(),
                                'ends_at'   => $endsAt->toDateString(),
                            ]);
                            $sub->syncClubStatus();
                        }

                        Notification::make()
                            ->title("Paid plan activated for \"{$record->name}\".")
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAcademies::route('/'),
            'create' => CreateAcademy::route('/create'),
            'view'   => ViewAcademy::route('/{record}'),
            'edit'   => EditAcademy::route('/{record}/edit'),
        ];
    }
}
