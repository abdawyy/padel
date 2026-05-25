<?php

namespace App\Filament\Saas\Resources\AuditLogs;

use App\Filament\Saas\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Audit Log';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Billing';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('event_type')->badge()->searchable(),
                TextColumn::make('actor.name')->label('Actor')->placeholder('—'),
                TextColumn::make('subject_type')->toggleable(),
                TextColumn::make('subject_id')->toggleable(),
                TextColumn::make('payload')
                    ->formatStateUsing(fn ($state) => json_encode($state))
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'saas.academy.approved' => 'Academy approved',
                        'saas.academy.rejected' => 'Academy rejected',
                        'payment.success' => 'Payment success',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
        ];
    }
}
