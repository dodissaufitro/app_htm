# UPDATE: User Urutan dan Access Control

## Summary Perubahan

Telah ditambahkan field `urutan` di form kelola user dan implementasi akses kontrol untuk edit kelola status berdasarkan urutan user.

## Fitur Yang Ditambahkan

### 1. Form Kelola User - Field Urutan

#### ✅ UserResource.php Updates:

-   **Field Urutan**: Input numerik dengan default 0
-   **Helper Text**: Penjelasan workflow (0 = Tidak dalam workflow, 1+ = Urutan workflow)
-   **Workflow Viewer**: Button untuk melihat workflow saat ini
-   **Badge Column**: Menampilkan urutan di table dengan badge styling

#### ✅ Filter Baru:

-   Filter berdasarkan urutan (1-5)
-   Filter "Dalam Workflow" (urutan > 0)
-   Filter akses terbatas/penuh (existing)

#### ✅ Actions Baru:

-   **Set Urutan Individual**: Edit urutan per user dengan auto-swap jika konflik
-   **Bulk Set Urutan**: Set urutan multiple users dengan auto-increment
-   **Workflow Info**: Real-time display workflow saat ini

### 2. Access Control untuk Edit Status

#### ✅ Pembatasan Akses:

-   **"Kelola Status" Action**: Hanya visible untuk user dengan `urutan = 1`
-   **Bulk "Atur Akses Status"**: Hanya visible untuk user dengan `urutan = 1`
-   **Controller Protection**: Check urutan di `updateStatus()` method

#### ✅ UI Updates:

-   **Form Persetujuan**: Hidden untuk user selain urutan 1
-   **Info Alert**: Menampilkan informasi akses dan urutan user saat ini
-   **Status Display**: Read-only status display dengan badge untuk non-authorized users

## Current Workflow Setup

```
1. admin (Verifikator Awal) ← DAPAT EDIT STATUS
   ↓
2. verifikator (Developer)
   ↓
3. Developer (Bank Analisis)
```

## Code Changes

### UserResource.php

```php
// Field urutan dengan workflow viewer
Forms\Components\TextInput::make('urutan')
    ->label('Urutan Developer')
    ->numeric()
    ->default(0)
    ->helperText('0 = Tidak dalam workflow, 1+ = Urutan dalam workflow developer')
    ->suffixAction(
        Forms\Components\Actions\Action::make('view_workflow')
            ->icon('heroicon-o-eye')
            ->tooltip('Lihat Workflow')
            ->action(function () {
                $workflowUsers = User::getDeveloperWorkflowUsers();
                $workflow = $workflowUsers->map(fn($u) => "{$u->urutan}. {$u->name}")->join(' → ');
                \Filament\Notifications\Notification::make()
                    ->title('Developer Workflow')
                    ->body($workflow ?: 'Belum ada user dalam workflow')
                    ->send();
            })
    ),

// Access control untuk manage status
->visible(function () {
    $currentUser = Auth::user();
    return $currentUser && $currentUser instanceof User && $currentUser->urutan === 1;
})
```

### PersetujuanController.php

```php
// Check access berdasarkan urutan
$currentUser = Auth::user();
if (!$currentUser || $currentUser->urutan !== 1) {
    session()->flash('error', 'Anda tidak memiliki akses untuk mengubah status. Hanya user dengan urutan 1 yang dapat mengubah status.');
    return redirect()->back();
}
```

### View persetujuan/pemohon.blade.php

```blade
@if(Auth::user() && Auth::user()->urutan === 1)
    <!-- Form edit status -->
@else
    <!-- Info akses terbatas + read-only status display -->
@endif
```

## Testing

### ✅ Test Commands:

```bash
# Lihat workflow saat ini
php artisan user:urutan workflow

# List semua user dengan urutan
php artisan user:urutan list

# Set urutan user
php artisan user:urutan set --email=admin@gmail.com --urutan=1
```

### ✅ Test Access:

1. **User dengan urutan = 1 (admin)**: ✅ Dapat edit status, melihat "Kelola Status" action
2. **User dengan urutan > 1**: ❌ Tidak dapat edit status, melihat info akses terbatas
3. **User dengan urutan = 0**: ❌ Tidak dapat edit status, melihat info "tidak dalam workflow"

## UI Features

### Kelola User:

-   ✅ Field urutan dengan workflow viewer
-   ✅ Badge urutan di table
-   ✅ Filter berdasarkan urutan
-   ✅ Bulk action set urutan
-   ✅ Individual action set urutan dengan swap detection

### Form Persetujuan:

-   ✅ Dynamic form visibility berdasarkan urutan
-   ✅ Info alert untuk non-authorized users
-   ✅ Read-only status display dengan badge
-   ✅ User urutan info dalam alert

## Security

-   ✅ Double protection: UI level (Filament) + Controller level
-   ✅ Session flash message yang informatif
-   ✅ Logging dengan user urutan info
-   ✅ Transaction safety di controller

## Next Steps

1. ✅ **Implemented**: Field urutan di form user
2. ✅ **Implemented**: Access control edit status untuk urutan = 1
3. 🔄 **Future**: Integration dengan notification system berdasarkan urutan
4. 🔄 **Future**: Auto-assignment berdasarkan workflow urutan
5. 🔄 **Future**: Workflow progress tracking di UI

## Status: ✅ COMPLETED

Semua requirement telah diimplementasikan dengan success:

-   Field urutan di kelola user form ✅
-   Edit kelola status hanya untuk urutan = 1 ✅
-   UI yang informatif dan user-friendly ✅
-   Security yang robust ✅
