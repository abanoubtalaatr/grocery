<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaticPageResource\Pages;
use App\Filament\Resources\StaticPageResource\RelationManagers;
use App\Models\StaticPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaticPageResource extends Resource
{
    protected static ?string $model = StaticPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label('URL slug')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. about-us, contact, privacy-policy')
                    ->helperText('URL-friendly identifier used in the page link. Use lowercase letters, numbers, and hyphens only. No spaces.')
                    ->unique(ignoreRecord: true)
                    ->rules(['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                    ->validationMessages(['regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.']),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Page title shown to users'),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->placeholder('Main page content (HTML or plain text)'),
                Forms\Components\Section::make('SEO (Meta tags)')
                    ->description('Optional. Used by search engines and when the page is shared.')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta title')
                            ->maxLength(60)
                            ->placeholder('e.g. About Us | Your Site Name')
                            ->helperText('Title for search results and browser tabs. Recommended: 50–60 characters.')
                            ->rules(['nullable', 'string', 'max:60']),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta description')
                            ->maxLength(160)
                            ->rows(3)
                            ->placeholder('Short summary of the page for search results')
                            ->helperText('Brief description for search results. Recommended: 150–160 characters.')
                            ->rules(['nullable', 'string', 'max:160']),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Meta keywords')
                            ->maxLength(255)
                            ->placeholder('e.g. contact, support, help')
                            ->helperText('Comma-separated keywords for SEO. Optional.')
                            ->rules(['nullable', 'string', 'max:255']),
                    ])
                    ->collapsible(),
                Forms\Components\Toggle::make('is_published')
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('meta_title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
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
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('order')
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
            'index' => Pages\ListStaticPages::route('/'),
            'create' => Pages\CreateStaticPage::route('/create'),
            'edit' => Pages\EditStaticPage::route('/{record}/edit'),
        ];
    }
}
