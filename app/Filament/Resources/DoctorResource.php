<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Models\User; // ต้อง import Model User

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'การจัดการบุคลากร'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'แพทย์';
    protected static ?string $modelLabel = 'แพทย์';
    protected static ?string $pluralModelLabel = 'แพทย์';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name') // เชื่อมโยงกับฟิลด์ 'name' ของ Model User
                    ->label('บัญชีผู้ใช้งาน (ถ้ามี)')
                    ->nullable()
                    ->placeholder('เลือกบัญชีผู้ใช้งานสำหรับแพทย์'),
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255)
                    ->label('ชื่อ'),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255)
                    ->label('นามสกุล'),
                TextInput::make('specialty')
                    ->maxLength(255)
                    ->nullable()
                    ->label('ความเชี่ยวชาญ'),
                TextInput::make('license_number')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->nullable()
                    ->label('เลขที่ใบอนุญาต'),
                TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255)
                    ->nullable()
                    ->label('เบอร์โทรศัพท์'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name') // แสดงชื่อผู้ใช้งานที่เชื่อมโยง
                    ->label('บัญชีผู้ใช้'),
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->label('ชื่อ'),
                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable()
                    ->label('นามสกุล'),
                TextColumn::make('specialty')
                    ->searchable()
                    ->label('ความเชี่ยวชาญ'),
                TextColumn::make('license_number')
                    ->searchable()
                    ->label('เลขที่ใบอนุญาต'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
