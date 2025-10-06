# User Create Form - Developer Lokasi Hunian Feature

## Overview

Fitur ini menambahkan pemilihan lokasi hunian yang conditional yang muncul ketika role "Developer" dipilih saat membuat user baru. Developer dapat ditugaskan untuk menangani lokasi hunian tertentu.

## Features

### 1. Conditional Field Display

-   Field "Lokasi Hunian Developer" hanya muncul ketika role "Developer" dipilih
-   Field menggunakan live updates untuk real-time visibility
-   Multiple selection untuk memilih beberapa lokasi sekaligus

### 2. Database Schema

#### Migration: `add_lokasi_hunian_to_users_table`

```sql
ALTER TABLE users ADD COLUMN lokasi_hunian JSON NULL
COMMENT 'Lokasi hunian yang ditangani developer (JSON array of DataHunian IDs)';

ALTER TABLE users ADD INDEX idx_users_urutan (urutan);
```

#### Field Details

-   **Type**: JSON
-   **Nullable**: Yes
-   **Purpose**: Store array of DataHunian IDs assigned to developer
-   **Index**: Added on `urutan` field for performance

### 3. Model Updates

#### User Model Enhancements

```php
// Fillable field
protected $fillable = [
    'name', 'email', 'password', 'allowed_status', 'urutan', 'lokasi_hunian'
];

// Cast to array
protected function casts(): array {
    return [
        'lokasi_hunian' => 'array',
        // ... other casts
    ];
}
```

#### New Methods Added

-   `lokasiHunian()`: Get DataHunian records assigned to developer
-   `isDeveloperWithLocations()`: Check if user is developer with location assignments
-   `getLokasiHunianNamesAttribute()`: Get array of location names
-   `canHandleLocation(int $dataHunianId)`: Check if developer can handle specific location

### 4. Form Configuration

#### Field Definition

```php
Forms\Components\Select::make('lokasi_hunian')
    ->label('Lokasi Hunian Developer')
    ->options(function () {
        return \App\Models\DataHunian::select('nama_pemukiman', 'id')
            ->distinct()
            ->orderBy('nama_pemukiman')
            ->pluck('nama_pemukiman', 'id')
            ->toArray();
    })
    ->multiple()
    ->searchable()
    ->preload()
    ->placeholder('Pilih lokasi hunian yang akan ditangani developer ini')
    ->helperText('Lokasi hunian yang akan menjadi tanggung jawab developer ini')
    ->visible(fn (Forms\Get $get): bool =>
        collect($get('roles'))->contains(fn($roleId) =>
            Role::find($roleId)?->name === 'Developer'
        )
    )
    ->columnSpanFull()
```

#### Key Features

-   **Conditional Visibility**: Only shows when Developer role selected
-   **Multiple Selection**: Can select multiple locations
-   **Searchable**: Easy to find specific locations
-   **Preload**: All options loaded for better UX
-   **Live Updates**: Responds to role changes immediately

### 5. Table Display Enhancements

#### New Column Added

```php
Tables\Columns\TagsColumn::make('lokasi_hunian_names')
    ->label('Lokasi Developer')
    ->getStateUsing(function (User $record) {
        if (!$record->hasRole('Developer') || empty($record->lokasi_hunian)) {
            return [];
        }
        return $record->lokasi_hunian_names;
    })
    ->color('info')
    ->separator(', ')
    ->limitList(2)
    ->expandableLimitedList()
    ->placeholder('Semua Lokasi')
    ->toggleable()
```

#### Features

-   Only shows for Developer users
-   Shows location names as tags
-   Expandable list for many locations
-   Placeholder text when no specific locations assigned

### 6. Filtering Options

#### New Filter Added

```php
Tables\Filters\Filter::make('developer_with_locations')
    ->label('Developer dengan Lokasi')
    ->query(fn(Builder $query): Builder =>
        $query->whereHas('roles', fn($q) => $q->where('name', 'Developer'))
              ->whereNotNull('lokasi_hunian')
    )
```

### 7. Sample Data

#### DataHunian Sample Records

-   Perumahan Green Valley (Bintaro)
-   Cluster Harmony Heights (Serpong)
-   Apartemen Sky Garden (Jakarta Selatan)
-   Villa Bukit Indah (Bogor)
-   Townhouse Modern Living (Tangerang)

### 8. Testing Commands

#### Available Commands

```bash
# Test overall functionality
php artisan test:developer-lokasi

# Test specific user
php artisan test:developer-lokasi {user_id}

# Create sample data
php artisan create:sample-data-hunian

# Test create user with locations
php artisan test:create-developer-with-lokasi
```

#### Test Results

-   ✅ Field visibility works correctly
-   ✅ Multiple location selection works
-   ✅ Data storage and retrieval works
-   ✅ Helper methods function properly
-   ✅ Table display shows correctly
-   ✅ Filtering works as expected

### 9. Usage Scenarios

#### Scenario 1: Create Developer with Specific Locations

1. Select "Developer" role in form
2. "Lokasi Hunian Developer" field appears
3. Select one or more locations
4. Save user - locations stored in JSON field

#### Scenario 2: Developer with All Locations

1. Select "Developer" role
2. Leave "Lokasi Hunian Developer" empty
3. Developer can handle all locations (no restrictions)

#### Scenario 3: Non-Developer User

1. Select any role except "Developer"
2. "Lokasi Hunian Developer" field hidden
3. No location assignments needed

### 10. Business Logic

#### Location Access Control

-   **Empty lokasi_hunian**: Developer can handle ALL locations
-   **Specific lokasi_hunian**: Developer limited to assigned locations only
-   **Non-Developer**: Location assignments ignored

#### Permission Inheritance

-   Super Admin: Can see and edit all location assignments
-   Admin: Can manage developer location assignments
-   Developer: Can see their own location assignments (read-only)

### 11. Integration Points

#### With DataPemohon

-   Can filter permohonan by developer's assigned locations
-   Location-based workflow assignment
-   Regional developer specialization

#### With Workflow System

-   Assign permohonan to developer based on property location
-   Location-specific approval processes
-   Regional performance tracking

### 12. Future Enhancements

#### Planned Features

1. **Location-based Auto Assignment**: Automatically assign permohonan to developer based on property location
2. **Regional Reports**: Generate reports by developer's assigned regions
3. **Location Capacity**: Set maximum permohonan per location per developer
4. **Location Hierarchy**: Support for area/zone based assignments
5. **Geolocation Integration**: Map-based location selection

#### Technical Improvements

1. **Caching**: Cache location options for better performance
2. **Validation**: Add validation rules for location assignments
3. **Audit Trail**: Track location assignment changes
4. **Bulk Assignment**: Tool for bulk location assignment updates

### 13. Performance Considerations

#### Optimizations Applied

-   Index on `urutan` field for developer queries
-   Preload options to reduce N+1 queries
-   JSON field for efficient storage
-   Limited list display to prevent UI overload

#### Monitoring Points

-   DataHunian table growth
-   Query performance on location filtering
-   JSON field query efficiency
-   Form load times with many locations

## Summary

Fitur ini berhasil mengimplementasikan:

-   ✅ Conditional field display berdasarkan role Developer
-   ✅ Multiple location selection dengan search
-   ✅ Database storage menggunakan JSON field
-   ✅ Helper methods untuk location management
-   ✅ Table display dengan filtering
-   ✅ Comprehensive testing commands
-   ✅ Integration dengan existing workflow system

Developer sekarang dapat ditugaskan untuk menangani lokasi hunian tertentu, memberikan kontrol yang lebih granular dalam pengelolaan permohonan berdasarkan wilayah.
