<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;


class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationLabel = 'Pengajuan RAB';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Pemohon')
                ->options(fn() => \App\Models\User::pluck('name', 'id'))
                ->default(fn() => auth()->id())
                ->disabled()
                ->dehydrated() // penting: agar tetap dikirim saat submit meskipun disabled
                ->required(),

            Forms\Components\Select::make('tipe_rab_id')
                ->label('Tipe RAB')
                ->relationship('tipeRAB', 'nama')
                ->required(),

            Forms\Components\TextInput::make('total_biaya')
                ->label('Total Biaya')
                ->numeric()
                ->required(),

            Forms\Components\DatePicker::make('tgl_realisasi'),
            Forms\Components\DatePicker::make('tgl_pulang'),
            Forms\Components\TextInput::make('jam'),
            Forms\Components\TextInput::make('jml_personil')
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_rab')->label('No RAB'),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                Tables\Columns\TextColumn::make('tipeRAB.nama')->label('Tipe RAB'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn($state) => match ($state) {
                    'disetujui' => 'success',
                    'ditolak' => 'danger',
                    'expired' => 'gray',
                    'menunggu' => 'warning',
                }),
                Tables\Columns\TextColumn::make('total_biaya')->money('IDR', true),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y H:i')) // contoh: 26 Juli 2025 14:52
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
