<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Filament\Resources\AddressResource\RelationManagers;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules(['required', 'exists:users,id']),
                Forms\Components\TextInput::make('label')
                    ->maxLength(255)
                    ->rules(['nullable', 'string', 'max:255']),
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255)
                    ->rules(['required', 'string', 'min:2', 'max:255']),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20)
                    ->rules([
                        'required',
                        'string',
                        'max:20',
                        'regex:/^\+?[1-9]\d{1,14}$/',
                    ])
                    ->validationMessages([
                        'regex' => 'The phone must be a valid E.164 number (e.g. +201234567890).',
                    ]),
                Forms\Components\TextInput::make('country_code')
                    ->required()
                    ->maxLength(5)
                    ->default('+20')
                    ->rules([
                        'required',
                        'string',
                        'max:5',
                        'regex:/^\+\d{1,4}$/',
                    ])
                    ->validationMessages([
                        'regex' => 'The country code must start with + followed by 1-4 digits (e.g. +20).',
                    ]),
                Forms\Components\TextInput::make('street_address')
                    ->required()
                    ->maxLength(500)
                    ->rules(['required', 'string', 'min:3', 'max:500']),
                Forms\Components\TextInput::make('building_number')
                    ->maxLength(50)
                    ->rules(['nullable', 'string', 'max:50']),
                Forms\Components\TextInput::make('floor')
                    ->maxLength(50)
                    ->rules(['nullable', 'string', 'max:50']),
                Forms\Components\TextInput::make('apartment')
                    ->maxLength(50)
                    ->rules(['nullable', 'string', 'max:50']),
                Forms\Components\TextInput::make('landmark')
                    ->maxLength(255)
                    ->rules(['nullable', 'string', 'max:255']),
                Forms\Components\TextInput::make('city')
                    ->required()
                    ->maxLength(100)
                    ->rules(['required', 'string', 'min:2', 'max:100']),
                Forms\Components\TextInput::make('state')
                    ->maxLength(100)
                    ->rules(['nullable', 'string', 'max:100']),
                Forms\Components\TextInput::make('postal_code')
                    ->maxLength(20)
                    ->rules(['nullable', 'string', 'max:20']),
                Forms\Components\TextInput::make('country')
                    ->required()
                    ->maxLength(100)
                    ->default('Egypt')
                    ->rules(['required', 'string', 'min:2', 'max:100']),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->rules(['nullable', 'string', 'max:1000']),
                Forms\Components\Toggle::make('is_default')
                    ->required()
                    ->rules(['boolean']),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->rules(['nullable', 'numeric', 'between:-90,90'])
                    ->validationMessages([
                        'between' => 'Latitude must be between -90 and 90.',
                    ]),
                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->rules(['nullable', 'numeric', 'between:-180,180'])
                    ->validationMessages([
                        'between' => 'Longitude must be between -180 and 180.',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('street_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('apartment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('landmark')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
                Tables\Columns\TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
