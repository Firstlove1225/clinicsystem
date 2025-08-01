<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;

class ConsultationsRelationManager extends RelationManager
{
    protected static string $relationship = 'consultations';
    protected static ?string $label = 'การตรวจรักษา'; // ชื่อแท็บสำหรับรายการเดียว
    protected static ?string $pluralLabel = 'ประวัติการตรวจรักษา'; // ชื่อแท็บสำหรับหลายรายการ

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->required()
                    ->label('แพทย์ผู้ตรวจ'),
                DateTimePicker::make('consultation_date')
                    ->required()
                    ->native(false)
                    ->default(now())
                    ->label('วันที่และเวลาที่ตรวจ'),
                Textarea::make('chief_complaint')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('อาการสำคัญ (Chief Complaint)'),
                RichEditor::make('diagnosis')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('การวินิจฉัย (Diagnosis)'),
                RichEditor::make('treatment')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('การรักษา (Treatment)'),
                RichEditor::make('medication')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('ยาที่สั่ง (Medication)'),
                RichEditor::make('notes')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('บันทึกเพิ่มเติม'),
                // ไม่ต้องมี patient_id, appointment_id, queue_id เพราะเป็น Relation Manager แล้ว
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('consultation_date') // ใช้ฟิลด์นี้เป็น Title
            ->columns([
                TextColumn::make('consultation_date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('วันที่/เวลาตรวจ'),
                TextColumn::make('doctor.first_name')
                    ->formatStateUsing(fn($record) => "แพทย์ {$record->doctor->first_name} {$record->doctor->last_name}")
                    ->searchable()
                    ->sortable()
                    ->label('แพทย์ผู้ตรวจ'),
                TextColumn::make('chief_complaint')
                    ->limit(50)
                    ->wrap()
                    ->label('อาการสำคัญ'),
                TextColumn::make('diagnosis')
                    ->limit(50)
                    ->wrap()
                    ->label('วินิจฉัย'),
                TextColumn::make('medication')
                    ->limit(50)
                    ->wrap()
                    ->label('ยาที่สั่ง'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('สร้างเมื่อ'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // อนุญาตให้สร้างบันทึกการตรวจใหม่จากหน้านี้
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
