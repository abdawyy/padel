<?php

namespace App\Filament\Resources\Clubs\RelationManagers;

use App\Models\ClubUser;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'clubUsers';

    protected static ?string $title = 'Staff & Managers';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Existing user')
                ->searchable()
                ->preload()
                ->options(fn (): array => User::query()
                    ->orderBy('name')
                    ->limit(200)
                    ->get()
                    ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"])
                    ->all())
                ->visibleOn('create')
                ->nullable(),
            TextInput::make('invite_email')
                ->label('Or invite by email')
                ->email()
                ->visibleOn('create')
                ->nullable(),
            Select::make('role')
                ->options([
                    'owner' => 'Owner',
                    'manager' => 'Manager',
                    'staff' => 'Staff',
                ])
                ->required()
                ->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable(),
                TextColumn::make('user.email')->label('Email'),
                TextColumn::make('role')->badge(),
                TextColumn::make('created_at')->dateTime()->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['user_id']) && filled($data['invite_email'] ?? null)) {
                            $user = User::query()->firstOrCreate(
                                ['email' => $data['invite_email']],
                                [
                                    'name' => Str::before($data['invite_email'], '@'),
                                    'password' => Hash::make(Str::random(16)),
                                    'role' => 'club_admin',
                                    'is_active' => true,
                                ]
                            );
                            $data['user_id'] = $user->id;
                        }

                        unset($data['invite_email']);

                        return $data;
                    })
                    ->using(function (array $data, RelationManager $livewire): ClubUser {
                        $clubId = $livewire->getOwnerRecord()->id;

                        if (ClubUser::query()->where('club_id', $clubId)->where('user_id', $data['user_id'])->exists()) {
                            Notification::make()->title('User is already on staff')->danger()->send();
                            throw new \RuntimeException('duplicate');
                        }

                        return ClubUser::query()->create([
                            'club_id' => $clubId,
                            'user_id' => $data['user_id'],
                            'role' => $data['role'],
                        ]);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
