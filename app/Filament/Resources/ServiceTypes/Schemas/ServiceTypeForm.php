<?php

namespace App\Filament\Resources\ServiceTypes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class ServiceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->columnSpanFull()
                    ->schema([
                        // Kolom kiri: lebar 2/3
                        Group::make()
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Section::make('Informasi Jasa')
                                    ->columns(2)
                                    ->schema([
                                        // Baris 1: nama dan harga acuan sejajar
                                        TextInput::make('name')->label('Nama Jasa')
                                            ->required()
                                            ->maxLength(255)
                                            ->trim(),
                                        TextInput::make('default_price')->label('Harga Acuan')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                            ->stripCharacters('.')
                                            ->minValue(0)
                                            ->maxValue(9999999999)
                                            ->prefix('Rp'),
                                        // Baris 2: deskripsi selebar card
                                        Textarea::make('description')->label('Deskripsi')
                                            ->columnSpanFull()
                                            ->rows(8)
                                            ->maxLength(2000),
                                    ]),
                            ]),

                        // Kolom kanan: lebar 1/3
                        Section::make('Foto Jasa')
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                FileUpload::make('photo')
                                    ->hiddenLabel()
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                    ->disk('public')
                                    ->directory('service-types')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight('200')
                                    ->maxSize(2048)
                                    ->helperText('JPG atau PNG, maksimal 2 MB.'),
                            ]),
                    ]),
            ]);
    }
}