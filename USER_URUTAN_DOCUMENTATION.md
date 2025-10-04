# User Urutan Field - Developer Workflow

## Overview

Field `urutan` telah ditambahkan ke table `users` untuk mengatur urutan tahap developer dalam workflow persetujuan.

## Database Schema

### Field yang Ditambahkan

-   **urutan**: `integer`, default 0, dengan index
-   **Comment**: "Urutan tahap developer"

### Migration

```bash
php artisan migrate
```

File migration: `2025_10_04_113604_add_urutan_to_users_table.php`

## Model Updates

### User Model

Field `urutan` telah ditambahkan ke:

-   `$fillable` array
-   `casts()` method sebagai integer

### New Methods Added

#### Scopes

-   `scopeOrderByUrutan($query, $direction = 'asc')` - Order users by urutan
-   `scopeByUrutan($query, int $urutan)` - Get users by specific urutan

#### Helper Methods

-   `getNextUser()` - Get next user in urutan sequence
-   `getPreviousUser()` - Get previous user in urutan sequence
-   `isFirstInSequence()` - Check if this is the first user
-   `isLastInSequence()` - Check if this is the last user

#### Static Methods

-   `getDeveloperWorkflowUsers()` - Get all users in workflow (urutan > 0) ordered by urutan

## Command Line Management

### Artisan Command: `user:urutan`

#### List All Users

```bash
php artisan user:urutan list
```

#### Set User Urutan

```bash
# By user ID
php artisan user:urutan set --user-id=1 --urutan=1

# By email
php artisan user:urutan set --email=admin@gmail.com --urutan=1
```

#### View Workflow

```bash
php artisan user:urutan workflow
```

#### Reset All Urutan

```bash
php artisan user:urutan reset
```

## Seeder

### UserUrutanSeeder

Provides example setup with predefined developer stages:

1. **Verifikator Awal**
2. **Developer/Pengembang**
3. **Bank Analisis**
4. **Supervisor**
5. **Manager**

```bash
php artisan db:seed --class=UserUrutanSeeder
```

## Usage Examples

### Getting Users in Workflow Order

```php
$workflowUsers = User::getDeveloperWorkflowUsers();
```

### Getting Next User in Sequence

```php
$currentUser = User::find(1);
$nextUser = $currentUser->getNextUser();
```

### Checking User Position

```php
$user = User::find(1);
if ($user->isFirstInSequence()) {
    // This is the first user in workflow
}

if ($user->isLastInSequence()) {
    // This is the last user in workflow
}
```

### Filtering Users by Urutan

```php
// Get all users with urutan 1
$verifikators = User::byUrutan(1)->get();

// Get users ordered by urutan
$orderedUsers = User::orderByUrutan('asc')->get();
```

## Workflow Logic

### Urutan Values

-   **0**: User is not part of developer workflow
-   **1+**: User is part of workflow, ordered by this number

### Typical Workflow

1. **Urutan 1**: Initial verification
2. **Urutan 2**: Developer/Engineering review
3. **Urutan 3**: Bank analysis
4. **Urutan 4**: Supervisor approval
5. **Urutan 5**: Manager final approval

## Integration Points

### With DataPemohonObserver

Field `urutan` dapat digunakan untuk:

-   Menentukan user mana yang harus menerima notifikasi berikutnya
-   Automasi routing persetujuan berdasarkan tahap
-   Tracking progress melalui workflow

### With Persetujuan System

-   Dapat diintegrasikan dengan controller persetujuan
-   Auto-assignment berdasarkan urutan
-   Workflow progress tracking

## Database Indexes

-   Index pada field `urutan` untuk performance query

## Current Setup

Setelah menjalankan seeder, setup saat ini:

1. **admin** (urutan 1) - Verifikator Awal
2. **Test User** (urutan 2) - Developer/Pengembang
3. **verifikator** (urutan 3) - Bank Analisis
4. **Developer** (urutan 0) - Not in Workflow

## Next Steps

1. Integrate dengan UI Filament untuk management urutan
2. Add workflow automation berdasarkan urutan
3. Implement notification system berdasarkan urutan
4. Add workflow progress tracking
