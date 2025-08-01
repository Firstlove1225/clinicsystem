<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn; // เพิ่ม use นี้

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';
    protected static ?string $label = 'การนัดหมาย'; // ชื่อแท็บสำหรับรายการเดียว
    protected static ?string $pluralLabel = 'ประวัติการนัดหมาย'; // ชื่อแท็บสำหรับหลายรายการ

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('appointment_date')
                    ->required()
                    ->label('วันที่นัดหมาย'),
                Forms\Components\TimePicker::make('appointment_time')
                    ->required()
                    ->label('เวลานัดหมาย'),
                Forms\Components\Select::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->required()
                    ->label('แพทย์'),
                Forms\Components\Textarea::make('reason')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('เหตุผลการนัดหมาย'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'รอการยืนยัน',
                        'confirmed' => 'ยืนยันแล้ว',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->default('pending')
                    ->required()
                    ->label('สถานะ'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('appointment_date')
            ->columns([
                TextColumn::make('appointment_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('วันที่นัดหมาย'),
                TextColumn::make('appointment_time')
                    ->time('H:i')
                    ->sortable()
                    ->label('เวลานัดหมาย'),
                TextColumn::make('doctor.first_name')
                    ->formatStateUsing(fn($record) => "แพทย์ {$record->doctor->first_name} {$record->doctor->last_name}")
                    ->searchable()
                    ->sortable()
                    ->label('แพทย์'),
                TextColumn::make('reason')
                    ->limit(50)
                    ->label('เหตุผล'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'in_queue' => 'info',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                    })
                    ->label('สถานะ'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('สร้างเมื่อ'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // อนุญาตให้สร้างการนัดหมายใหม่จากหน้านี้
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
