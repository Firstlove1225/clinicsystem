<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescribedMedicationResource\Pages;
use App\Filament\Resources\PrescribedMedicationResource\RelationManagers;
use App\Models\PrescribedMedication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select; // Import Select
use Filament\Forms\Components\TextInput; // Import TextInput
use Filament\Forms\Components\Textarea; // Import Textarea
use Filament\Tables\Columns\TextColumn; // Import TextColumn

class PrescribedMedicationResource extends Resource
{
    protected static ?string $model = PrescribedMedication::class;

    // เราไม่ต้องการให้แสดงในเมนูนำทางหลัก เพราะจะถูกใช้เป็น Relation Manager เท่านั้น
    protected static bool $shouldRegisterNavigation = false;

    // ถ้ายังต้องการไอคอนและกลุ่มเมนู (แต่จะไม่มีผลถ้า $shouldRegisterNavigation เป็น false)
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass'; // ไอคอนตัวอย่าง
    protected static ?string $navigationGroup = 'คลังยาและการเงิน';
    protected static ?string $navigationLabel = 'ยาที่สั่งจ่าย (ภายใน)'; // ชื่อในเมนูนำทาง
    protected static ?string $modelLabel = 'ยาที่สั่งจ่าย';
    protected static ?string $pluralModelLabel = 'ยาที่สั่งจ่าย';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // consultation_id ไม่ต้องแสดงในฟอร์มโดยตรง เพราะ Relation Manager จัดการให้แล้ว
                Select::make('medication_id')
                    ->relationship('medication', 'name') // ดึงข้อมูลยาจาก Medication Model
                    ->searchable()
                    ->preload()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consultation.id') // แสดง ID ของการตรวจรักษา
                    ->sortable()
                    ->label('Consultation ID'),
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
                // สามารถเพิ่ม filters ที่นี่ได้ หากจำเป็นต้องค้นหาจากตาราง PrescribedMedication โดยตรง
                // เช่น Filter ตามยาที่สั่ง
                Tables\Filters\SelectFilter::make('medication_id')
                    ->relationship('medication', 'name')
                    ->searchable()
                    ->preload()
                    ->label('กรองตามยา'),
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
            // ไม่มี Relation Managers สำหรับ PrescribedMedication
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrescribedMedications::route('/'),
            'create' => Pages\CreatePrescribedMedication::route('/create'),
            'edit' => Pages\EditPrescribedMedication::route('/{record}/edit'),
        ];
    }
}
