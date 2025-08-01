<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationStockResource\Pages;
use App\Filament\Resources\MedicationStockResource\RelationManagers;
use App\Models\MedicationStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput; // Import TextInput
use Filament\Forms\Components\Select; // Import Select
use Filament\Forms\Components\DatePicker; // Import DatePicker
use Filament\Tables\Columns\TextColumn; // Import TextColumn
use Filament\Tables\Filters\SelectFilter; // Import SelectFilter
use Filament\Tables\Filters\Filter; // Import Filter
use Illuminate\Database\Eloquent\Builder as EloquentBuilder; // Import for query builder

class MedicationStockResource extends Resource
{
    protected static ?string $model = MedicationStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // ลองใช้ไอคอนนี้
    protected static ?string $navigationGroup = 'คลังยาและการเงิน'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'สต็อกยา'; // ชื่อในเมนูนำทาง
    protected static ?string $modelLabel = 'สต็อกยา'; // ชื่อเรียกสำหรับรายการเดียว
    protected static ?string $pluralModelLabel = 'สต็อกยา'; // ชื่อเรียกสำหรับหลายรายการ

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('medication_id')
                    ->relationship('medication', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('ชื่อยา'),
                TextInput::make('lot_number')
                    ->maxLength(255)
                    ->nullable()
                    ->label('เลขที่ Lot'),
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0) // ไม่ให้จำนวนติดลบ
                    ->label('จำนวน'),
                DatePicker::make('expiry_date')
                    ->native(false) // ใช้ Filament's custom date picker
                    ->nullable()
                    ->label('วันหมดอายุ'),
                TextInput::make('location')
                    ->maxLength(255)
                    ->nullable()
                    ->label('ตำแหน่งจัดเก็บ'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medication.name')
                    ->searchable()
                    ->sortable()
                    ->label('ชื่อยา'),
                TextColumn::make('lot_number')
                    ->searchable()
                    ->label('Lot Number'),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->label('จำนวน'),
                TextColumn::make('expiry_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('วันหมดอายุ')
                    ->badge() // แสดงเป็น badge
                    ->color(fn(string $state): string => match (true) {
                        // ตรวจสอบวันหมดอายุ
                        \Carbon\Carbon::parse($state)->isPast() => 'danger', // หมดอายุแล้ว
                        \Carbon\Carbon::parse($state)->isBefore(now()->addMonths(3)) => 'warning', // ใกล้หมดอายุ (ภายใน 3 เดือน)
                        default => 'success', // ยังไม่หมดอายุ
                    }),
                TextColumn::make('location')
                    ->searchable()
                    ->label('ตำแหน่งจัดเก็บ'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('เพิ่มเมื่อ'),
            ])
            ->filters([
                SelectFilter::make('medication_id')
                    ->relationship('medication', 'name')
                    ->searchable()
                    ->preload()
                    ->label('กรองตามยา'),
                Filter::make('expiry_date_status')
                    ->label('สถานะวันหมดอายุ')
                    ->query(function (EloquentBuilder $query): EloquentBuilder {
                        return $query; // ไม่ต้องทำอะไรตรงนี้ เพราะเราจะใช้ checkboxes
                    })
                    ->form([
                        Forms\Components\Checkbox::make('expired')
                            ->label('หมดอายุแล้ว'),
                        Forms\Components\Checkbox::make('expiring_soon')
                            ->label('ใกล้หมดอายุ (ใน 3 เดือน)'),
                        Forms\Components\Checkbox::make('not_expired')
                            ->label('ยังไม่หมดอายุ'),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query
                            ->when(
                                $data['expired'],
                                fn(EloquentBuilder $query): EloquentBuilder => $query->whereDate('expiry_date', '<', now()),
                            )
                            ->when(
                                $data['expiring_soon'],
                                fn(EloquentBuilder $query): EloquentBuilder => $query->whereBetween('expiry_date', [now(), now()->addMonths(3)]),
                            )
                            ->when(
                                $data['not_expired'],
                                fn(EloquentBuilder $query): EloquentBuilder => $query->whereDate('expiry_date', '>', now()->addMonths(3)),
                            );
                    })
            ])
            ->actions([
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
            'index' => Pages\ListMedicationStocks::route('/'),
            'create' => Pages\CreateMedicationStock::route('/create'),
            'edit' => Pages\EditMedicationStock::route('/{record}/edit'),
        ];
    }
}
