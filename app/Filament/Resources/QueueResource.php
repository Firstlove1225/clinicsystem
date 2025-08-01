<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Filament\Resources\QueueResource\RelationManagers;
use App\Models\Queue; // เปลี่ยนจาก App\Models\Queue เป็น App\Models\Queue
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon; // สำหรับ Carbon::today()
use Filament\Tables\Actions\Action; // เพิ่มบรรทัดนี้
use App\Models\Consultation; // เพิ่มบรรทัดนี้


class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'การจัดการนัดหมายและคิว';
    protected static ?string $navigationLabel = 'คิวเข้าตรวจ';
    protected static ?string $modelLabel = 'คิวเข้าตรวจ';
    protected static ?string $pluralModelLabel = 'คิวเข้าตรวจ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('patient_id')
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name} (HN: {$record->hn})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('คนไข้'),
                Select::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('แพทย์ที่ต้องการพบ (ถ้ามี)'),
                TextInput::make('queue_number')
                    ->required()
                    ->maxLength(255)
                    ->default(function () {
                        // สร้างหมายเลขคิวอัตโนมัติแบบง่ายๆ เช่น QYYMMDDHHMMSS
                        return 'Q' . Carbon::now()->format('ymdHis');
                    })
                    ->label('หมายเลขคิว'),
                DateTimePicker::make('check_in_time')
                    ->required()
                    ->native(false)
                    ->default(now())
                    ->label('เวลาลงทะเบียน'),
                Select::make('status')
                    ->options([
                        'waiting' => 'รอเรียก',
                        'calling' => 'กำลังเรียก',
                        'in_progress' => 'กำลังตรวจ',
                        'completed' => 'ตรวจเสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->default('waiting')
                    ->required()
                    ->label('สถานะคิว'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('queue_number')
                    ->searchable()
                    ->sortable()
                    ->label('หมายเลขคิว'),
                TextColumn::make('patient.first_name')
                    ->formatStateUsing(fn($record) => "{$record->patient->first_name} {$record->patient->last_name} (HN: {$record->patient->hn})")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('patient', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('hn', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->label('คนไข้'),
                TextColumn::make('doctor.first_name')
                    ->formatStateUsing(fn($record) => $record->doctor ? "แพทย์ {$record->doctor->first_name} {$record->doctor->last_name}" : '-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('doctor', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->label('แพทย์'),
                TextColumn::make('check_in_time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('เวลาลงทะเบียน'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'waiting' => 'info',
                        'calling' => 'warning',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->label('สถานะ'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('วันที่สร้าง'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'waiting' => 'รอเรียก',
                        'calling' => 'กำลังเรียก',
                        'in_progress' => 'กำลังตรวจ',
                        'completed' => 'ตรวจเสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->label('กรองตามสถานะ'),
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->label('กรองตามแพทย์'),
                Tables\Filters\Filter::make('check_in_time')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('วันที่'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('check_in_time', $date),
                            );
                    })
                    ->label('กรองตามวันที่ลงทะเบียน'),
            ])
            ->actions([
                // เพิ่ม Action นี้
                Action::make('startConsultation')
                    ->label('เริ่มตรวจ')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->visible(fn(Queue $record): bool => $record->status !== 'completed' && $record->status !== 'cancelled') // แสดงเฉพาะคิวที่ยังไม่เสร็จสิ้น
                    ->url(fn(Queue $record): string => ConsultationResource::getUrl('create', [
                        'patient_id' => $record->patient_id,
                        'doctor_id' => $record->doctor_id,
                        'queue_id' => $record->id,
                    ])),
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
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
