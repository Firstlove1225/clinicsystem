<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'การจัดการนัดหมายและคิว'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'นัดหมาย';
    protected static ?string $modelLabel = 'การนัดหมาย';
    protected static ?string $pluralModelLabel = 'การนัดหมาย';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('patient_id')
                    ->relationship('patient', 'first_name') // แสดงชื่อคนไข้
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name} (HN: {$record->hn})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('คนไข้'),
                Select::make('doctor_id')
                    ->relationship('doctor', 'first_name') // แสดงชื่อแพทย์
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('แพทย์'),
                DatePicker::make('appointment_date')
                    ->required()
                    ->native(false)
                    ->minDate(now()) // นัดหมายได้ตั้งแต่ปัจจุบันเป็นต้นไป
                    ->label('วันที่นัดหมาย'),
                TimePicker::make('appointment_time')
                    ->required()
                    ->native(false)
                    ->seconds(false) // ไม่แสดงวินาที
                    ->label('เวลานัดหมาย'),
                Textarea::make('reason')
                    ->maxLength(65535)
                    ->nullable()
                    ->label('เหตุผลการนัดหมาย'),
                Select::make('status')
                    ->options([
                        'pending' => 'รอการยืนยัน',
                        'confirmed' => 'ยืนยันแล้ว',
                        'in_queue' => 'อยู่ในคิว',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->default('pending')
                    ->required()
                    ->label('สถานะ'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->formatStateUsing(fn($record) => "แพทย์ {$record->doctor->first_name} {$record->doctor->last_name}")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('doctor', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->label('แพทย์'),
                TextColumn::make('appointment_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('วันที่นัดหมาย'),
                TextColumn::make('appointment_time')
                    ->time('H:i')
                    ->sortable()
                    ->label('เวลานัดหมาย'),
                TextColumn::make('status')
                    ->badge() // แสดงเป็น badge สวยงาม
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('วันที่สร้าง'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'รอการยืนยัน',
                        'confirmed' => 'ยืนยันแล้ว',
                        'in_queue' => 'อยู่ในคิว',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ])
                    ->label('กรองตามสถานะ'),
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->label('กรองตามแพทย์'),
                Tables\Filters\Filter::make('appointment_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('จากวันที่'),
                        DatePicker::make('to')
                            ->label('ถึงวันที่'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '<=', $date),
                            );
                    })
                    ->label('กรองตามช่วงวันที่'),
            ])
            ->actions([
                Action::make('checkIn')
                    ->label('สร้างคิว')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('success')
                    ->visible(fn(Appointment $record): bool => $record->status === 'confirmed')
                    ->action(function (Appointment $record) {
                        // สร้างคิวใหม่
                        $queue = \App\Models\Queue::create([
                            'patient_id' => $record->patient_id,
                            'doctor_id' => $record->doctor_id,
                            'appointment_id' => $record->id,
                            'queue_number' => \App\Models\Queue::generateNextQueueNumber(), // ต้องสร้าง method นี้ใน Queue Model
                            'status' => 'waiting',
                        ]);

                        // อัปเดตสถานะการนัดหมาย
                        $record->update(['status' => 'in_queue']);

                        // แสดงข้อความสำเร็จ
                        \Filament\Notifications\Notification::make()
                            ->title('สร้างคิวสำเร็จ')
                            ->body("คิวสำหรับ {$record->patient->first_name} {$record->patient->last_name} ถูกสร้างแล้ว: #{$queue->queue_number}")
                            ->success()
                            ->send();
                    }),
                Action::make('startConsultation')
                    ->label('บันทึกการตรวจ')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->visible(fn(Appointment $record): bool => $record->status !== 'completed' && $record->status !== 'cancelled')
                    ->url(fn(Appointment $record): string => ConsultationResource::getUrl('create', [
                        'patient_id' => $record->patient_id,
                        'doctor_id' => $record->doctor_id,
                        'appointment_id' => $record->id,
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
