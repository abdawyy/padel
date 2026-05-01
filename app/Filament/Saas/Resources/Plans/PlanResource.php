<?php

namespace App\Filament\Saas\Resources\Plans;

use App\Filament\Saas\Resources\Plans\Pages\CreateSaasPlan;
use App\Filament\Saas\Resources\Plans\Pages\EditSaasPlan;
use App\Filament\Saas\Resources\Plans\Pages\ListSaasPlans;
use App\Models\SaasPlan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = SaasPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Plans';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Billing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Plan Details')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(100),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->helperText('Lowercase, no spaces (e.g. starter, professional)'),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('monthly_price')->numeric()->prefix('$')->minValue(0)->required(),
                        TextInput::make('yearly_price')->numeric()->prefix('$')->minValue(0)->required(),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_active')->default(true),
                    ]),

                Section::make('Features')
                    ->description('Define limits and features for this plan.')
                    ->schema([
                        KeyValue::make('features')
                            ->keyLabel('Feature Key')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->helperText('e.g. max_courts = 5, max_users = 20'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('monthly_price')->money('USD')->sortable(),
                TextColumn::make('yearly_price')->money('USD')->sortable(),
                TextColumn::make('subscriptions_count')->counts('subscriptions')->label('Clubs')->badge(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSaasPlans::route('/'),
            'create' => CreateSaasPlan::route('/create'),
            'edit'   => EditSaasPlan::route('/{record}/edit'),
        ];
    }
}
