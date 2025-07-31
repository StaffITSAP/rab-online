<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\TipeRAB;

class BasePengajuanForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Hidden::make('user_id')
                ->default(fn() => Auth::id())
                ->dehydrated()
                ->required(),

            Forms\Components\Select::make('tipe_rab_id')
                ->label('Tipe RAB')
                ->relationship('tipeRab', 'nama')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($set, $state) {
                    $kode = TipeRAB::find($state)?->kode ?? 'XXX';
                    $set('no_rab', \App\Models\Pengajuan::generateNoRAB($state));
                }),

            Forms\Components\TextInput::make('no_rab')
                ->label('No RAB')
                ->readOnly()
                ->required(),
        ];
    }
}
