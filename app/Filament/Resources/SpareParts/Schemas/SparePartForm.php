<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;

class SparePartForm
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
                                Section::make('Informasi Part')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('code')->label('Kode')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->trim(),
                                        TextInput::make('name')->label('Nama')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('category_id')->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')->label('Nama Kategori')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique('categories', 'name'),
                                                Textarea::make('description')->label('Deskripsi'),
                                            ]),
                                        TextInput::make('location')->label('Lokasi')
                                            ->maxLength(255)
                                            ->placeholder('Contoh: Rak A1'),
                                    ]),

                                Section::make('Stok & Harga')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('stock')->label('Stok')
                                            ->required()
                                            ->integer()
                                            ->minValue(0)
                                            ->maxValue(999999)
                                            ->default(0),
                                        TextInput::make('min_stock')->label('Stok Minimal')
                                            ->required()
                                            ->integer()
                                            ->minValue(0)
                                            ->maxValue(999999)
                                            ->default(1),
                                        TextInput::make('price')->label('Harga')
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make("\$money(\$input, ',', '.', 0)"))
                                            ->stripCharacters('.')
                                            ->minValue(0)
                                            ->maxValue(9999999999)
                                            ->prefix('Rp'),
                                    ]),
                            ]),

                        // Kolom kanan: lebar 1/3
                        Section::make('Foto Part')
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                FileUpload::make('photo')
                                    ->hiddenLabel()
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                    ->disk('public')
                                    ->directory('spare-parts')
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