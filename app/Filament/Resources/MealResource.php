<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealResource\Pages;
use App\Filament\Resources\MealResource\RelationManagers;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Subcategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Products';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('subcategory_id', null))
                    ->rules(['required', 'exists:categories,id']),
                Forms\Components\Select::make('subcategory_id')
                    ->label('Subcategory')
                    ->relationship('subcategory', 'name', fn (Builder $query, callable $get) =>
                        $query->where('category_id', $get('category_id'))
                    )
                    ->searchable()
                    ->preload()
                    ->rules(['nullable', 'exists:subcategories,id']),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null)
                    ->rules(['required', 'string', 'max:255']),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->rules(['required', 'string', 'max:255']),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->rules(['required', 'string']),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->required()
                    ->rules(['required', 'image']),
                Forms\Components\TextInput::make('offer_title')
                    ->maxLength(255)
                    ->rules(['nullable', 'string', 'max:255']),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->default(0)
                    ->prefix('$')
                    ->rules(['required', 'numeric', 'min:0']),
                Forms\Components\TextInput::make('discount_price')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->nullable()
                    ->prefix('$')
                    ->helperText('Optional. When set, this price is shown as the discounted price.')
                    ->rules(['nullable', 'numeric', 'min:0']),
                Forms\Components\TextInput::make('size')
                    ->maxLength(255)
                    ->rules(['nullable', 'string', 'max:255']),
                Forms\Components\DatePicker::make('expiry_date')
                    ->rules(['nullable', 'date']),
                Forms\Components\Textarea::make('includes')
                    ->columnSpanFull()
                    ->rules(['nullable', 'string']),
                Forms\Components\Textarea::make('how_to_use')
                    ->columnSpanFull()
                    ->rules(['nullable', 'string']),
                Forms\Components\Textarea::make('features')
                    ->columnSpanFull()
                    ->helperText('Key product features or highlights (e.g. Organic, Gluten-free). One per line or comma-separated.')
                    ->rows(3)
                    ->rules(['nullable', 'string']),
                Forms\Components\Select::make('brand')
                    ->label('Brand')
                    ->options(fn () => Meal::query()
                        ->whereNotNull('brand')
                        ->where('brand', '!=', '')
                        ->distinct()
                        ->orderBy('brand')
                        ->pluck('brand', 'brand'))
                    ->searchable()
                    ->nullable()
                    ->helperText('Select from existing brands. Use the field below to add a new brand name.'),
                Forms\Components\TextInput::make('brand_new')
                    ->label('Or enter new brand name')
                    ->maxLength(255)
                    ->nullable()
                    ->dehydrated(false)
                    ->rules(['nullable', 'string', 'max:255'])
                    ->helperText('Leave empty if you selected a brand above. Filled value will be used as brand when saving.'),
                Forms\Components\TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->rules(['required', 'integer', 'min:0']),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured product')
                    ->helperText('Featured products are highlighted on the homepage and in recommendations.')
                    ->default(false),
                Forms\Components\Toggle::make('is_available')
                    ->label('Available for sale')
                    ->helperText('When off, this product is hidden from the catalog.')
                    ->default(true),
                Forms\Components\Toggle::make('is_hot')
                    ->label('Hot / Ready-to-eat')
                    ->helperText('When on, this meal appears in the Hot Meals API.')
                    ->default(false),
                Forms\Components\DatePicker::make('available_date')
                    ->rules(['nullable', 'date']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subcategory.name')
                    ->label('Subcategory')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('offer_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->rating >= 4 => 'success',
                        $record->rating >= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('rating_count')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->stock_quantity > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('sold_count')
                    ->numeric()
                    ->sortable()
                    ->label('Sold'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),
                Tables\Columns\IconColumn::make('is_hot')
                    ->boolean()
                    ->label('Hot'),
                Tables\Columns\TextColumn::make('available_date')
                    ->date()
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Available'),
                Tables\Filters\Filter::make('in_stock')
                    ->label('In Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '>', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMeals::route('/'),
            'create' => Pages\CreateMeal::route('/create'),
            'view' => Pages\ViewMeal::route('/{record}'),
            'edit' => Pages\EditMeal::route('/{record}/edit'),
        ];
    }
}
