<?php

namespace App\Filament\Saas\Resources\Subscriptions;

use App\Filament\Saas\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Saas\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Saas\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\ClubSaasSubscription;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = ClubSaasSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Billing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Subscription')
                    ->schema([
                        Select::make('club_id')
                            ->relationship('club', 'name')
                            ->searchable()->preload()->required(),
                        Select::make('saas_plan_id')
                            ->relationship('plan', 'name')
                            ->searchable()->preload()->nullable(),
                        Select::make('billing_cycle')
                            ->options(['monthly' => 'Monthly', 'yearly' => 'Yearly', 'trial' => 'Trial'])
                            ->default('monthly')->required()->native(false),
                        Select::make('status')
                            ->options([
                                'trial'     => 'Trial',
                                'active'    => 'Active',
                                'past_due'  => 'Past Due',
                                'cancelled' => 'Cancelled',
                                'expired'   => 'Expired',
                            ])
                            ->default('active')->required()->native(false),
                        TextInput::make('amount_paid')->numeric()->prefix('$')->minValue(0)->required(),
                        TextInput::make('payment_reference')->maxLength(255)->nullable(),
                        DatePicker::make('starts_at')->required()->default(now()),
                        DatePicker::make('ends_at')->required()->default(now()->addMonth()),
                        Textarea::make('notes')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('club.name')->searchable()->sortable(),
                TextColumn::make('plan.name')->label('Plan')->placeholder('—')->sortable(),
                TextColumn::make('billing_cycle')
                    ->badge()
                    ->color(fn (string $state) => $state === 'yearly' ? 'success' : 'info'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'    => 'success',
                        'trial'     => 'warning',
                        'past_due'  => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('amount_paid')->money('USD')->sortable(),
                TextColumn::make('starts_at')->date()->sortable(),
                TextColumn::make('ends_at')->date()->sortable()
                    ->color(fn (ClubSaasSubscription $record) => $record->isExpired() ? 'danger' : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(['trial' => 'Trial', 'active' => 'Active', 'past_due' => 'Past Due', 'cancelled' => 'Cancelled', 'expired' => 'Expired']),
                SelectFilter::make('billing_cycle')
                    ->options(['monthly' => 'Monthly', 'yearly' => 'Yearly']),
                SelectFilter::make('saas_plan_id')
                    ->relationship('plan', 'name')->label('Plan'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSubscriptions::route('/'),
            'create' => CreateSubscription::route('/create'),
            'edit'   => EditSubscription::route('/{record}/edit'),
        ];
    }
}
