<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationResource\Pages;
use App\Filament\Resources\MedicationResource\RelationManagers;
use App\Models\Medication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput; // Import TextInput
use Filament\Forms\Components\Select; // Import Select
use Filament\Forms\Components\Textarea; // Import Textarea
use Filament\Tables\Columns\TextColumn; // Import TextColumn

class MedicationResource extends Resource
{
    protected static ?string $model = Medication::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // ไอคอนสำหรับข้อมูลยา
    protected static ?string $navigationGroup = 'คลังยาและการเงิน'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'ข้อมูลยา'; // ชื่อในเมนูนำทาง
    protected static ?string $modelLabel = 'ข้อมูลยา'; // ชื่อเรียกสำหรับรายการเดียว
    protected static ?string $pluralModelLabel = 'ข้อมูลยา'; // ชื่อเรียกสำหรับหลายรายการ

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true) // ตรวจสอบชื่อยาซ้ำ (ยกเว้นตอนแก้ไขรายการเดิม)
                    ->maxLength(255)
                    ->label('ชื่อยา (เช่น Paracetamol 500 mg)'),
                TextInput::make('generic_name')
                    ->maxLength(255)
                    ->nullable()
                    ->label('ชื่อสามัญทางยา (Generic Name)'),
                Select::make('dosage_form')
                    ->options([
                        'tablet' => 'เม็ด',
                        'capsule' => 'แคปซูล',
                        'solution' => 'น้ำ/สารละลาย',
                        'cream' => 'ครีม/เจล',
                        'syrup' => 'ยาน้ำเชื่อม',
                        'injection' => 'ยาฉีด',
                        'other' => 'อื่นๆ',
                    ])
                    ->nullable()
                    ->label('รูปแบบยา'),
                TextInput::make('strength')
                    ->maxLength(255)
                    ->nullable()
                    ->label('ความแรง (เช่น 500 mg, 10 ml/5ml)'),
                Textarea::make('description')
                    ->maxLength(65535) // ใช้ text area สำหรับข้อมูลยาวๆ
                    ->nullable()
                    ->columnSpanFull() // ให้ textarea กินพื้นที่เต็มความกว้าง
                    ->label('รายละเอียด/สรรพคุณ'),
                TextInput::make('price_per_unit')
                    ->numeric() // ให้รับได้เฉพาะตัวเลข
                    ->nullable()
                    ->inputMode('decimal') // เพิ่ม inputMode เพื่อช่วยให้คีย์บอร์ดมือถือแสดงปุ่มทศนิยม
                    ->label('ราคาต่อหน่วย'),
                TextInput::make('unit')
                    ->maxLength(255)
                    ->nullable()
                    ->label('หน่วย (เช่น เม็ด, ขวด, ซอง)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('ชื่อยา'),
                TextColumn::make('generic_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) // ซ่อนคอลัมน์นี้ตอนแรก
                    ->label('ชื่อสามัญ'),
                TextColumn::make('dosage_form')
                    ->label('รูปแบบยา')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'tablet' => 'เม็ด',
                        'capsule' => 'แคปซูล',
                        'solution' => 'น้ำ/สารละลาย',
                        'cream' => 'ครีม/เจล',
                        'syrup' => 'ยาน้ำเชื่อม',
                        'injection' => 'ยาฉีด',
                        'other' => 'อื่นๆ',
                        default => $state,
                    }),
                TextColumn::make('strength')
                    ->label('ความแรง'),
                TextColumn::make('price_per_unit')
                    ->money('THB') // แสดงเป็นสกุลเงินบาท
                    ->sortable()
                    ->label('ราคา/หน่วย'),
                TextColumn::make('unit')
                    ->label('หน่วย'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) // ซ่อนคอลัมน์นี้ตอนแรก
                    ->label('วันที่เพิ่ม'),
            ])
            ->filters([
                // สามารถเพิ่ม filters ที่นี่ได้ในอนาคต เช่น filter ตามรูปแบบยา
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
            // สามารถเพิ่ม Relation Managers ที่นี่ได้ในอนาคต (เช่น Stock)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedications::route('/'),
            'create' => Pages\CreateMedication::route('/create'),
            'edit' => Pages\EditMedication::route('/{record}/edit'),
        ];
    }
}
