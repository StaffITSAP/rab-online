<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanAllResource\Pages;
use App\Models\Pengajuan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use App\Filament\Forms\Pengajuan\BasePengajuanForm;
use App\Filament\Forms\Pengajuan\AssetFormSection;
use App\Filament\Forms\Pengajuan\DinasFormSection;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\PengajuanStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms;

class PengajuanAllResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationGroup = 'Detail Pengajuan RAB';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $label = 'Semua Pengajuan';
    protected static ?string $pluralLabel = 'Semua Pengajuan';
    protected static ?string $slug = 'semua-pengajuan';

    /**
     * PENTING: Reuse form schema dari PengajuanResource supaya hal. edit tidak kosong.
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        // Reuse persis schema yang sudah Anda pakai di PengajuanResource
        return \App\Filament\Resources\PengajuanResource::form($form);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        // (opsional) tampilkan kolom yang Anda perlukan di index “Semua Pengajuan”
        return \App\Filament\Resources\PengajuanResource::table($table);
    }


    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanAlls::route('/'),
            'edit' => Pages\EditPengajuanAll::route('/{record}/edit'),
            // tambahkan create/edit jika memang ingin, atau cukup index saja
        ];
    }

    public static function afterSave(Form $form): void
    {
        $record = $form->getRecord();
        $total = 0;

        if ($record->tipe_rab_id == 1) {
            $total = $record->pengajuan_assets()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 2) {
            $total = $record->pengajuan_dinas()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 3) {
            $total = $record->pengajuan_marcomm_kegiatans()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 4) {
            $total = $record->pengajuan_marcomm_promosis()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 5) {
            $total = $record->pengajuan_marcomm_kebutuhans()->sum('subtotal');
        } // tambahkan elseif lagi jika ada tipe lain

        $record->update(['total_biaya' => $total]);
    }


    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        // Update expired pengajuan terlebih dahulu sebelum menampilkan data
        static::updateExpiredPengajuan();

        // Superadmin boleh lihat semua
        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery();
        }

        // Ambil ID pengajuan yang user ini adalah approver
        $pengajuanIdsSebagaiApprover = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        return parent::getEloquentQuery()
            ->where(function ($query) use ($user, $pengajuanIdsSebagaiApprover) {
                $query
                    // sebagai pemilik pengajuan
                    ->where('user_id', $user->id)
                    // atau sebagai approver di pengajuan lain
                    ->orWhereIn('id', $pengajuanIdsSebagaiApprover);
            });
    }
    private static function updateExpiredPengajuan(): void
    {
        $today = Carbon::now()->startOfDay();
        Pengajuan::where('status', 'menunggu')
            ->whereNotNull('tgl_realisasi')
            ->whereDate('tgl_realisasi', '<=', $today->copy()->subDays(1))
            ->update(['status' => 'expired']);
    }
}
