<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput; // Import TextInput
use Filament\Forms\Components\Textarea; // Import Textarea
use Filament\Tables\Columns\TextColumn; // Import TextColumn

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar'; // ไอคอนสำหรับบริการ
    protected static ?string $navigationGroup = 'คลังยาและการเงิน'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'รายการบริการ'; // ชื่อในเมนูนำทาง
    protected static ?string $modelLabel = 'รายการบริการ'; // ชื่อเรียกสำหรับรายการเดียว
    protected static ?string $pluralModelLabel = 'รายการบริการ'; // ชื่อเรียกสำหรับหลายรายการ

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true) // ตรวจสอบชื่อบริการซ้ำ (ยกเว้นตอนแก้ไขรายการเดิม)
                    ->maxLength(255)
                    ->label('ชื่อบริการ'),
                Textarea::make('description')
                    ->maxLength(65535)
                    ->nullable()
                    ->columnSpanFull() // ให้ textarea กินพื้นที่เต็มความกว้าง
                    ->label('รายละเอียดบริการ'),
                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->inputMode('decimal') // เพิ่ม inputMode เพื่อช่วยให้คีย์บอร์ดมือถือแสดงปุ่มทศนิยม
                    ->minValue(0) // ราคาต้องไม่ติดลบ
                    ->label('ราคาบริการ'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('ชื่อบริการ'),
                TextColumn::make('price')
                    ->money('THB') // แสดงเป็นสกุลเงินบาท
                    ->sortable()
                    ->label('ราคาบริการ'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) // ซ่อนคอลัมน์นี้ตอนแรก
                    ->label('วันที่เพิ่ม'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
