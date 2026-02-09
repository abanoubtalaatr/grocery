<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('address_id')
                    ->label('Address')
                    ->relationship('address', 'address_line_1', fn (Builder $query, callable $get) =>
                        $query->where('user_id', $get('user_id'))
                    )
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'stripe' => 'Stripe',
                        'cash' => 'Cash',
                        'card' => 'Card',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('payment_method_id')
                    ->maxLength(255)
                    ->label('Payment Method ID'),
                Forms\Components\Select::make('delivery_type')
                    ->options([
                        'standard' => 'Standard',
                        'express' => 'Express',
                        'scheduled' => 'Scheduled',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'placed' => 'Order Placed',
                        'processing' => 'Processing',
                        'shipping' => 'Shipping',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('placed_at')
                    ->label('Placed At'),
                Forms\Components\DateTimePicker::make('processing_at')
                    ->label('Processing At'),
                Forms\Components\DateTimePicker::make('shipping_at')
                    ->label('Shipping At'),
                Forms\Components\DateTimePicker::make('out_for_delivery_at')
                    ->label('Out for Delivery At'),
                Forms\Components\DateTimePicker::make('delivered_at')
                    ->label('Delivered At'),
                Forms\Components\DateTimePicker::make('cancelled_at')
                    ->label('Cancelled At'),
                Forms\Components\DateTimePicker::make('estimated_delivery_time')
                    ->label('Estimated Delivery Time'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'placed' => 'warning',
                        'processing' => 'info',
                        'shipping' => 'info',
                        'out_for_delivery' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivery_type')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('placed_at')
                    ->label('Placed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('processing_at')
                    ->label('Processing At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_at')
                    ->label('Shipping At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('out_for_delivery_at')
                    ->label('Out for Delivery At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estimated_delivery_time')
                    ->label('Estimated Delivery')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cancelled_at')
                    ->label('Cancelled At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'placed' => 'Order Placed',
                        'processing' => 'Processing',
                        'shipping' => 'Shipping',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'stripe' => 'Stripe',
                        'cash' => 'Cash',
                        'card' => 'Card',
                    ]),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
