<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use App\Models\Consultation;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Http\Request; // เพิ่มบรรทัดนี้
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ConsultationResource\Pages;
use Filament\Forms\Components\RichEditor; // สำหรับเนื้อหาเยอะๆ
use App\Filament\Resources\ConsultationResource\RelationManagers;
use App\Filament\Resources\ConsultationResource\RelationManagers\PrescribedMedicationsRelationManager;


class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'การรักษา'; // จัดกลุ่มเมนู
    protected static ?string $navigationLabel = 'บันทึกการตรวจ';
    protected static ?string $modelLabel = 'การตรวจรักษา';
    protected static ?string $pluralModelLabel = 'บันทึกการตรวจรักษา';

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
                    ->label('คนไข้')
                    ->default(fn(Request $request) => $request->query('patient_id')) // ดึงค่าจาก URL
                    ->disabled(fn(Request $request) => $request->query('patient_id') !== null), // ปิดการแก้ไขถ้ามีค่าเริ่มต้น
                Select::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('แพทย์ผู้ตรวจ')
                    ->default(fn(Request $request) => $request->query('doctor_id')) // ดึงค่าจาก URL
                    ->disabled(fn(Request $request) => $request->query('doctor_id') !== null), // ปิดการแก้ไขถ้ามีค่าเริ่มต้น
                Select::make('appointment_id')
                    ->relationship('appointment', 'id')
                    ->getOptionLabelFromRecordUsing(fn($record) => "HN: {$record->patient->hn} - " . $record->appointment_date->format('d/m/Y') . ' ' . $record->appointment_time->format('H:i'))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('อ้างอิงจากการนัดหมาย')
                    ->default(fn(Request $request) => $request->query('appointment_id')) // ดึงค่าจาก URL
                    ->disabled(fn(Request $request) => $request->query('appointment_id') !== null), // ปิดการแก้ไขถ้ามีค่าเริ่มต้น
                Select::make('queue_id')
                    ->relationship('queue', 'queue_number')
                    ->getOptionLabelFromRecordUsing(fn($record) => "คิว: {$record->queue_number} (HN: {$record->patient->hn})")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('อ้างอิงจากคิว')
                    ->default(fn(Request $request) => $request->query('queue_id')) // ดึงค่าจาก URL
                    ->disabled(fn(Request $request) => $request->query('queue_id') !== null), // ปิดการแก้ไขถ้ามีค่าเริ่มต้น
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
                    ->label('แพทย์ผู้ตรวจ'),
                TextColumn::make('consultation_date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('วันที่/เวลาตรวจ'),
                TextColumn::make('chief_complaint')
                    ->limit(50) // แสดงแค่ 50 ตัวอักษร
                    ->wrap()
                    ->label('อาการสำคัญ'),
                TextColumn::make('diagnosis')
                    ->limit(50)
                    ->wrap()
                    ->label('วินิจฉัย'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('วันที่สร้างบันทึก'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->relationship('doctor', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "แพทย์ {$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->label('กรองตามแพทย์'),
                Tables\Filters\Filter::make('consultation_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('จากวันที่'),
                        Forms\Components\DatePicker::make('to')
                            ->label('ถึงวันที่'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('consultation_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('consultation_date', '<=', $date),
                            );
                    })
                    ->label('กรองตามช่วงวันที่ตรวจ'),
            ])
            ->actions([
                Action::make('startConsultation')
                    ->label('บันทึกการตรวจ')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    // NOTE: This action might be more appropriate in AppointmentResource if its primary purpose
                    // is to initiate a consultation from an appointment.
                    // If kept here, ensure the logic correctly handles a Consultation record.
                    ->visible(fn(Consultation $record): bool => $record->appointment && $record->appointment->status !== 'completed' && $record->appointment->status !== 'cancelled')
                    ->url(fn(Consultation $record): string => ConsultationResource::getUrl('create', [
                        'patient_id' => $record->patient_id,
                        'doctor_id' => $record->doctor_id,
                        'appointment_id' => $record->appointment_id,
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
            PrescribedMedicationsRelationManager::class, // เพิ่มบรรทัดนี้
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsultations::route('/'),
            'create' => Pages\CreateConsultation::route('/create'),
            'edit' => Pages\EditConsultation::route('/{record}/edit'),
        ];
    }
}
