<?php

namespace App\Filament\Resources\SpareParts;

use App\Filament\Resources\SpareParts\Pages\CreateSparePart;
use App\Filament\Resources\SpareParts\Pages\EditSparePart;
use App\Filament\Resources\SpareParts\Pages\ListSpareParts;
use App\Filament\Resources\SpareParts\Schemas\SparePartForm;
use App\Filament\Resources\SpareParts\Tables\SparePartsTable;
use App\Models\SparePart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SparePartResource extends Resource
{
    protected static ?string $model = SparePart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Spare Part'; // BARU

    protected static string|UnitEnum|null $navigationGroup = 'Katalog'; // UBAH, dulu 'Inventori'

    protected static ?int $navigationSort = 2; // UBAH, dulu 1

    protected static ?string $modelLabel = 'Spare Part';

    protected static ?string $pluralModelLabel = 'Spare Part';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SparePartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SparePartsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name'];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpareParts::route('/'),
            'create' => CreateSparePart::route('/create'),
            'edit' => EditSparePart::route('/{record}/edit'),
        ];
    }
}