<?php

namespace App\Filament\Resources\ConsultationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select; // Import Select
use Filament\Forms\Components\TextInput; // Import TextInput
use Filament\Forms\Components\Textarea; // Import Textarea
use Filament\Tables\Columns\TextColumn; // Import TextColumn

class PrescribedMedicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'prescribedMedications';

    protected static ?string $title = 'ยาที่สั่งจ่าย'; // ชื่อของ Relation Manager
    protected static ?string $label = 'ยาที่สั่งจ่าย'; // ชื่อสำหรับรายการเดียว
    protected static ?string $pluralLabel = 'ยาที่สั่งจ่าย'; // ชื่อสำหรับหลายรายการ

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('medication_id')
                    ->relationship('medication', 'name') // ดึงข้อมูลยาจาก Medication Model
                    ->searchable()
                    ->preload() // โหลดข้อมูลยามาล่วงหน้า (มีประโยชน์ถ้ามีรายการไม่เยอะมาก)
                    ->required()
                    ->columnSpanFull() // ให้ Select กินพื้นที่เต็มความกว้าง
                    ->label('เลือกยา'),
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1) // จำนวนต้องไม่ต่ำกว่า 1
                    ->label('จำนวนที่สั่งจ่าย'),
                TextInput::make('unit')
                    ->maxLength(255)
                    ->nullable()
                    ->label('หน่วย (เช่น เม็ด, ml, ซอง)'),
                Textarea::make('instructions')
                    ->maxLength(65535)
                    ->nullable()
                    ->columnSpanFull() // ให้ textarea กินพื้นที่เต็มความกว้าง
                    ->label('คำแนะนำการใช้ยา'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('medication.name')
            ->columns([
                TextColumn::make('medication.name') // แสดงชื่อยา
                    ->searchable()
                    ->sortable()
                    ->label('ยาที่สั่ง'),
                TextColumn::make('quantity')
                    ->numeric()
                    ->label('จำนวน'),
                TextColumn::make('unit')
                    ->label('หน่วย'),
                TextColumn::make('instructions')
                    ->limit(50) // แสดงแค่ 50 ตัวอักษรแรก
                    ->wrap() // หากยาวเกิน ให้ขึ้นบรรทัดใหม่
                    ->label('คำแนะนำ'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('สั่งเมื่อ'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
