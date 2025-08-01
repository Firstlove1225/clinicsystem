<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput; // Import TextInput
use Filament\Forms\Components\Select; // Import Select
use Filament\Tables\Columns\TextColumn; // Import TextColumn
use Filament\Tables\Columns\BadgeColumn; // Import BadgeColumn
use Filament\Forms\Components\Hidden; // Import Hidden
use Filament\Forms\Components\Placeholder; // Import Placeholder
use Closure; // Import Closure for dynamic default values

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // ไอคอนสำหรับใบแจ้งหนี้
    protected static ?string $navigationGroup = 'คลังยาและการเงิน'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'ใบแจ้งหนี้'; // ชื่อในเมนูนำทาง
    protected static ?string $modelLabel = 'ใบแจ้งหนี้'; // ชื่อเรียกสำหรับรายการเดียว
    protected static ?string $pluralModelLabel = 'ใบแจ้งหนี้'; // ชื่อเรียกสำหรับหลายรายการ

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('invoice_number')
                    ->default(function (Forms\Get $get) {
                        // สร้างเลขที่ใบแจ้งหนี้อัตโนมัติเมื่อสร้างใหม่
                        if (!$get('id')) { // ถ้าเป็นการสร้างใหม่ (ไม่มี ID)
                            return 'INV-' . date('YmdHis') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        }
                        return null; // ไม่ต้องตั้งค่าเมื่อแก้ไข
                    })
                    ->disabledOn('edit') // ปิดการแก้ไขเมื่ออยู่ในโหมดแก้ไข
                    ->required()
                    ->maxLength(255)
                    ->label('เลขที่ใบแจ้งหนี้'),
                Select::make('patient_id')
                    ->relationship('patient', 'first_name') // แสดงชื่อคนไข้
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}") // แสดงชื่อเต็ม
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('คนไข้'),
                Select::make('consultation_id')
                    ->relationship('consultation', 'id') // แสดง ID การตรวจรักษา
                    ->getOptionLabelFromRecordUsing(fn($record) => "Consultation #{$record->id} - {$record->created_at->format('d/m/Y H:i')}") // แสดงรายละเอียดการตรวจ
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->label('เชื่อมโยงกับการตรวจรักษา'),
                Select::make('status')
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'paid' => 'ชำระแล้ว',
                        'partial_paid' => 'ชำระบางส่วน',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->default('pending')
                    ->required()
                    ->label('สถานะ'),
                TextInput::make('total_amount')
                    ->numeric()
                    ->default(0.00)
                    ->disabled() // ปิดการแก้ไข เพราะจะคำนวณอัตโนมัติ
                    ->label('ยอดรวม (บาท)'),
                TextInput::make('paid_amount')
                    ->numeric()
                    ->default(0.00)
                    ->inputMode('decimal')
                    ->minValue(0)
                    ->label('ยอดที่ชำระแล้ว (บาท)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('เลขที่ใบแจ้งหนี้'),
                TextColumn::make('patient.first_name')
                    ->formatStateUsing(fn($record) => "{$record->patient->first_name} {$record->patient->last_name}") // แสดงชื่อเต็มคนไข้
                    ->searchable()
                    ->sortable()
                    ->label('คนไข้'),
                TextColumn::make('total_amount')
                    ->money('THB')
                    ->sortable()
                    ->label('ยอดรวม'),
                TextColumn::make('paid_amount')
                    ->money('THB')
                    ->sortable()
                    ->label('ชำระแล้ว'),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'info' => 'partial_paid',
                        'danger' => 'cancelled',
                    ])
                    ->label('สถานะ'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('สร้างเมื่อ'),
            ])
            ->filters([
                // ส่วนนี้คือจุดที่เคยมีปัญหา: ต้องไม่มี .table() ต่อท้าย Select
                Tables\Filters\SelectFilter::make('status') // ใช้ Tables\Filters\SelectFilter
                    ->options([
                        'pending' => 'รอดำเนินการ',
                        'paid' => 'ชำระแล้ว',
                        'partial_paid' => 'ชำระบางส่วน',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->label('กรองตามสถานะ'),

                Tables\Filters\SelectFilter::make('patient_id') // ใช้ Tables\Filters\SelectFilter
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->label('กรองตามคนไข้'),
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
            // จะเพิ่ม InvoiceItemsRelationManager ที่นี่ในขั้นตอนถัดไป
            RelationManagers\InvoiceItemsRelationManager::class, // เพิ่มบรรทัดนี้หลังจากสร้าง Relation Manager แล้ว
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    // เพิ่มเมธอดนี้เพื่อสร้างเลขที่ใบแจ้งหนี้อัตโนมัติก่อนบันทึก
    // (หากคุณต้องการควบคุม query เพิ่มเติม)
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->withoutGlobalScopes([
    //         SoftDeletingScope::class,
    //     ]);
    // }
}
