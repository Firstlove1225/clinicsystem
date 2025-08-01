<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Patient;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PatientResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Filament\Resources\PatientResource\RelationManagers\ConsultationsRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\AppointmentsRelationManager; // เพิ่มบรรทัดนี้

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'คนไข้';
    protected static ?string $modelLabel = 'คนไข้';
    protected static ?string $pluralModelLabel = 'คนไข้';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('hn')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label('HN (เลขที่คนไข้)'),
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255)
                    ->label('ชื่อ'),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255)
                    ->label('นามสกุล'),
                DatePicker::make('date_of_birth')
                    ->required()
                    ->native(false)
                    ->label('วันเดือนปีเกิด'),
                Select::make('gender')
                    ->options([
                        'Male' => 'ชาย',
                        'Female' => 'หญิง',
                        'Other' => 'อื่นๆ',
                    ])
                    ->required()
                    ->label('เพศ'),
                TextInput::make('address')
                    ->maxLength(255)
                    ->nullable()
                    ->label('ที่อยู่'),
                TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255)
                    ->nullable()
                    ->label('เบอร์โทรศัพท์'),
                TextInput::make('emergency_contact_name')
                    ->maxLength(255)
                    ->nullable()
                    ->label('ชื่อผู้ติดต่อฉุกเฉิน'),
                TextInput::make('emergency_contact_phone')
                    ->tel()
                    ->maxLength(255)
                    ->nullable()
                    ->label('เบอร์โทรผู้ติดต่อฉุกเฉิน'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hn')
                    ->searchable()
                    ->sortable()
                    ->label('HN'),
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->label('ชื่อ'),
                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable()
                    ->label('นามสกุล'),
                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->label('วันเกิด'),
                TextColumn::make('gender')
                    ->label('เพศ'),
                TextColumn::make('phone_number')
                    ->label('เบอร์โทร'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('วันที่สร้าง'),
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
            AppointmentsRelationManager::class,
            ConsultationsRelationManager::class, // เพิ่มบรรทัดนี้
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            //    'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
