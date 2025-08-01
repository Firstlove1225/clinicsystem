<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get; // Import Get for reactive forms
use Filament\Forms\Set; // Import Set for reactive forms
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Str; // Import Str for string manipulation (e.g., lowercase)

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceItems';

    protected static ?string $title = 'รายการใบแจ้งหนี้';
    protected static ?string $label = 'รายการใบแจ้งหนี้';
    protected static ?string $pluralLabel = 'รายการใบแจ้งหนี้';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('item_type')
                    ->options([
                        'service' => 'บริการ',
                        'medication' => 'ยา',
                    ])
                    ->default('service')
                    ->live() // ทำให้ฟอร์มรีเฟรชเมื่อค่าเปลี่ยน
                    ->afterStateUpdated(function (Set $set) {
                        $set('item_id', null); // ล้าง item_id เมื่อเปลี่ยน item_type
                        $set('unit_price', 0);
                        $set('quantity', 1);
                        $set('item_name', null);
                    })
                    ->required()
                    ->label('ประเภทรายการ'),

                Select::make('item_id')
                    ->options(function (Get $get) {
                        $type = $get('item_type');
                        if ($type === 'service') {
                            return \App\Models\Service::pluck('name', 'id');
                        } elseif ($type === 'medication') {
                            return \App\Models\Medication::pluck('name', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->preload()
                    ->live() // ทำให้ฟอร์มรีเฟรชเมื่อค่าเปลี่ยน
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $type = $get('item_type');
                        $item = null;
                        if ($type === 'service') {
                            $item = \App\Models\Service::find($state);
                        } elseif ($type === 'medication') {
                            $item = \App\Models\Medication::find($state);
                        }

                        if ($item) {
                            $set('unit_price', $item->price ?? $item->price_per_unit);
                            $set('item_name', $item->name); // บันทึกชื่อรายการ
                        } else {
                            $set('unit_price', 0);
                            $set('item_name', null);
                        }
                        // คำนวณ sub_total ทันที
                        $quantity = $get('quantity') ?: 1;
                        $unitPrice = $get('unit_price') ?: 0;
                        $set('sub_total', $quantity * $unitPrice);
                    })
                    ->required()
                    ->label('เลือกรหัสรายการ'),

                TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->live() // ทำให้ฟอร์มรีเฟรชเมื่อค่าเปลี่ยน
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $quantity = $get('quantity') ?: 1;
                        $unitPrice = $get('unit_price') ?: 0;
                        $set('sub_total', $quantity * $unitPrice);
                    })
                    ->required()
                    ->minValue(1)
                    ->label('จำนวน'),

                TextInput::make('unit_price')
                    ->numeric()
                    ->disabled() // ไม่ให้แก้ไขเอง เพราะมาจาก Service/Medication
                    ->label('ราคาต่อหน่วย'),

                TextInput::make('sub_total')
                    ->numeric()
                    ->default(0.00)
                    ->disabled() // ไม่ให้แก้ไขเอง เพราะคำนวณอัตโนมัติ
                    ->label('รวมรายการนี้'),

                // เก็บค่า item_name ใน field hidden เพื่อให้บันทึกลง DB ได้
                Hidden::make('item_name')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_name')
            ->columns([
                TextColumn::make('item_type')
                    ->label('ประเภท')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'service' => 'บริการ',
                        'medication' => 'ยา',
                        default => $state,
                    }),
                TextColumn::make('item_name')
                    ->label('รายการ'),
                TextColumn::make('quantity')
                    ->numeric()
                    ->label('จำนวน'),
                TextColumn::make('unit_price')
                    ->money('THB')
                    ->label('ราคา/หน่วย'),
                TextColumn::make('sub_total')
                    ->money('THB')
                    ->label('รวม'),
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
