# AGENTS.md

# Laravel Project Architecture Guidelines

Dokumen ini adalah pedlaran utama bagi seluruh developer maupun AI agent dalam mengembangkan sistem agar struktur kode tetap konsisten, scalable, maintainable, dan mengikuti prinsip:

- Domain Driven Design (DDD)
- Service Pattern
- Repository Pattern
- DTO Pattern
- SOLID Principles
- Clean Architecture

---

# 1. Core Principles

## Thin Controller Principle

**Controller tidak boleh berisi business logic.**

Controller hanya bertanggung jawab untuk:

- menerima HTTP request
- memanggil Form Request
- validasi awal melalui Request class
- mengubah request tervalidasi menjadi DTO
- meneruskan DTO ke Service
- mengembalikan HTTP response

### Dilarang:

- Query database langsung di Controller
- Logic bisnis di Controller
- Manipulasi model langsung di Controller
- Validasi manual di Controller

---

# 2. Application Layer Structure

```bash
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Services/
├── Providers/
├── View/
│   ├── Components/
└── └── Composers/
```

---

## Layer Responsibilities

### Controllers

Tugas:

- Entry point HTTP request
- Memanggil Request validation
- Menggunakan DTO
- Memanggil Service
- Return response

### Requests (Form Request)

Tugas:

- Authorization
- Validation rules
- Transform validated data ke DTO

### Standard Request Pattern

```php
class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super,admin,analyst,supervisor,manager'],
        ];
    }

    public function toDTO(): CreateUserDto
    {
        return new CreateUserDto($this->validated());
    }
}
```

---

## Request + DTO Validation Rule

Setiap Form Request wajib:

### Wajib memiliki:

- `authorize()`
- `rules()`
- `toDTO()`

### Tujuan:

- Menjaga validasi tetap terpusat
- Menghindari passing raw request ke Service
- Menjamin hanya data tervalidasi yang masuk ke business layer
- Menjaga type safety
- Menstandarkan transfer data antar layer

### Golden Rule:

> Request melakukan validasi dan transformasi ke DTO, bukan business logic.

---

### DTO Responsibilities

DTO digunakan untuk:

- Membawa data tervalidasi
- Menstandarkan struktur input
- Mengurangi coupling antar layer
- Mempermudah testing
- Menjaga keamanan data

### Dilarang:

- Mengirim `$request->all()` ke service
- Mengirim array mentah tanpa DTO

---

# 3. Business Logic Placement

## Seluruh logic bisnis wajib ditempatkan di:

```bash
app/Services
atau
src/Domain/{SubDomain}/Services
```

### Service Responsibilities:

- Business rules
- Data orchestration
- Repository coordination
- Transaction handling
- Cross-domain logic
- Validation bisnis lanjutan

---

# 4. Business Layer Structure (Domain Layer)

```bash
src/
└── Domain/
    └── {EntityName}/
        ├── Models/
        ├── DTOs/
        ├── Interfaces/
        ├── Repositories/
        └── Services/
```

---

# 5. Subdomain Rule

## One Entity = One Subdomain

Setiap entitas utama memiliki subdomain sendiri.

### Contoh:

```bash
src/Domain/User/
src/Domain/Product/
src/Domain/Report/
src/Domain/Location/
```

### User Domain Example:

```bash
src/Domain/User/
├── Models/
│   ├── User.php
│   ├── PasswordHistory.php
│   └── PasswordSetting.php
├── DTOs/
│   └── CreateUserDto.php
├── Interfaces/
│   └── UserRepositoryInterface.php
├── Repositories/
│   └── UserRepository.php
└── Services/
    └── UserService.php
```

---

# 6. Repository Pattern Rule

## Semua akses model wajib melalui Repository

### Dilarang:

```php
User::create(...)
User::find(...)
User::where(...)
```

langsung di Controller atau Service.

---

## Wajib menggunakan:

### Interface:

```php
UserRepositoryInterface
```

### Repository Implementation:

```php
UserRepository
```

### Provider Binding:

```php
$this->app->bind(
    UserRepositoryInterface::class,
    UserRepository::class
);
```

---

## Repository Rule:

1. Service hanya bergantung pada Interface
2. Repository menangani query database
3. Model hanya representasi data
4. Interface wajib didaftarkan di Provider

---

# 7. Standard Data Flow

```text
Request
   ↓
Controller
   ↓
Form Request Validation
   ↓
DTO
   ↓
Service (Business Logic)
   ↓
Repository Interface
   ↓
Repository Implementation
   ↓
Model
```

---

# 8. Flow Explanation Based on Diagram

## POST Request dengan validasi:

```text
Request
→ Controller
→ Form Request
→ DTO
→ Service (Logic)
→ Repository Interface
→ Repository
→ Model
```

### Penjelasan:

### Request

Menerima input dari client.

### Controller

Mengatur alur request tanpa logic bisnis.

### Form Request

Melakukan:

- Authorization
- Validation
- Transformasi ke DTO

### DTO

Mengemas validated data ke object terstruktur.

### Service

Menjalankan:

- Business logic
- Rules
- Coordination

### Repository Interface

Sebagai kontrak data access.

### Repository

Menjalankan query ke model/database.

### Model

Representasi tabel/database.

---

## Non-POST / simple flow:

```text
Request
→ Controller
→ Service
→ Repository Interface
→ Repository
→ Model
```

---

# 9. Controller Best Practice Example

## Salah:

```php
public function store(Request $request)
{
    User::create($request->all());
}
```

---

## Benar:

```php
public function store(UserStoreRequest $request)
{
    $dto = $request->toDTO();

    $this->userService->store($dto);

    return response()->json([
        'success' => true,
    ]);
}
```

---

# 10. Service Example

```php
public function store(CreateUserDto $dto): User
{
    return $this->userRepository->create($dto->toArray());
}
```

---

# 11. Repository Example

```php
public function create(array $data): User
{
    return User::create($data);
}
```

---

# 12. View Layer Structure (Frontend Blade/UI Layer)

## View Structure Standard

```bash
resources/
└── views/
    ├── components/
    │   ├── buttons/
    │   ├── modals/
    │   ├── tables/
    │   ├── forms/
    │   └── alerts/
    ├── layouts/
    │   ├── app.blade.php
    │   ├── guest.blade.php
    │   └── dashboard.blade.php
    ├── auth/
    │   ├── login.blade.php
    │   ├── forgot-password.blade.php
    │   └── reset-password.blade.php
    ├── dashboard/
    │   └── index.blade.php
    ├── user-management/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── partials/
    ├── report-management/
    └── location-management/
```

---

## View Layer Responsibilities

### components/

Digunakan untuk reusable UI components.

### Contoh reusable components:

- Buttons
- Modals
- Tables
- Form fields
- Alerts
- Pagination
- Cards
- Dropdowns

### Golden Rule:

> Reusable UI wajib ditempatkan di components, bukan di folder entitas.

---

### layouts/

Digunakan untuk layout utama:

- App layout
- Dashboard layout
- Guest layout
- Auth layout

---

### auth/

Khusus halaman autentikasi:

- Login
- Register
- Forgot password
- Reset password

---

### Entity-based folders

Setiap fitur utama memiliki folder sendiri.

### Contoh:

- user-management/
- report-management/
- dashboard/
- location-management/

---

## View Composer Rule

### Wajib menggunakan View Composer

Untuk data pelengkap, global data, atau shared data yang digunakan di banyak view, wajib menggunakan View Composer.

### Contoh penggunaan:

- Sidebar menu data
- User profile summary
- Notification counts
- Global settings
- Dynamic navigation
- Shared dashboard widgets

---

## Alur View Composer

### Penempatan:

View Composer dieksekusi di Application Layer melalui:

```bash
app/Providers/
├── AppServiceProvider.php
└── ViewServiceProvider.php
```

### Implementasi:

- Logic ditempatkan di fungsi `boot()`
- Menggunakan service layer
- Data diinjeksi langsung ke view
- Tidak perlu melalui controller

### Standard Pattern:

```php
View::composer([
    'dashboard.index',
    'user-management.index'
], function ($view) use ($service) {
    $view->with('variable', $service->getData());
});
```

---

## Golden Rule:

> Shared view data wajib melalui View Composer, bukan copy-paste di banyak controller.

---

## Benefits:

- Centralized shared UI data
- Cleaner controllers
- Reusable data injection
- Better maintainability
- Reduced duplication
- Separation of concerns

---

## Dilarang:

- Shared sidebar data diulang di banyak controller
- Global layout data hardcoded per controller
- Query langsung di blade untuk shared data
- Repetitive data binding antar halaman

---

## View Best Practices

### Wajib:

- Gunakan reusable components
- Pisahkan layout global
- Pisahkan halaman auth
- Gunakan partials jika perlu
- Struktur berdasarkan entitas
- Hindari duplikasi UI code

---

## Dilarang:

- Modal ditulis ulang di setiap entitas
- Button custom berulang di banyak file
- Layout bercampur dengan halaman bisnis
- File view terlalu besar tanpa partial/component
- UI logic kompleks langsung di blade

---

## Diperbolehkan:

- Menampilkan data
- Rendering komponen
- Menampilkan kondisi sederhana (@if, @foreach)
- Memanggil reusable components
- Menyusun layout

---

## 2. GUNAKAN VIEW COMPOSER

Untuk data pelengkap yang digunakan di berbagai view, wajib menggunakan View Composer.

### Gunakan untuk:

- Sidebar data
- Shared navigation
- User summary
- Notification count
- Global settings
- Dashboard widgets
- Shared dropdown data

---

## 3. ALUR VIEW COMPOSER

### Eksekusi logic:

- Dilakukan di layer App
- Tepatnya di file Service Provider:

```bash
app/Providers/AppServiceProvider.php
atau
app/Providers/ViewServiceProvider.php
```

---

### Penempatan:

- Letakkan di dalam fungsi `boot()`

---

### Standard Syntax:

```php
View::composer([
    'nama.view.satu',
    'nama.view.dua'
], function ($view) use ($service) {
    $view->with('variabel', $data);
});
```

---

### Tujuan:

- Menyuntikkan data otomatis ke berbagai view
- Menghindari duplikasi logic di Controller
- Menjaga Controller tetap tipis
- Menstandarkan shared data flow
- Menjaga separation of concerns

---

### Golden Rule:

> Shared data antar view wajib menggunakan View Composer, bukan melalui copy-paste controller logic.

---

## Asset Management Structure

### Standard Public Asset Structure

```bash
public/
├── icons/
│   ├── edit.svg
│   ├── delete.svg
│   ├── dashboard.svg
│   └── users.svg
├── images/
│   ├── logos/
│   ├── banners/
│   ├── avatars/
│   └── backgrounds/
```

---

## Icon Usage Rule

### Wajib:

- Semua SVG/icon disimpan di `public/icons`
- Gunakan file asset, bukan inline SVG langsung di blade
- Gunakan naming convention yang jelas
- Reusable antar halaman

### Contoh benar:

```php
<img src="{{ asset('icons/edit.svg') }}" alt="Edit">
```

### Dilarang:

- Inline SVG besar langsung di view
- Copy-paste SVG berulang
- Icon hardcoded per file

---

## Image Usage Rule

### Wajib:

- Semua gambar statis disimpan di `public/images`
- Kategorikan berdasarkan fungsi
- Gunakan path asset helper

### Contoh:

```php
<img src="{{ asset('images/logos/company-logo.png') }}" alt="Logo">
```

---

## Benefits:

- Cleaner blade files
- Easier maintenance
- Reusability
- Better organization
- Centralized asset management
- Faster UI updates

---

## Golden Rule:

> Icons dan images adalah reusable assets, bukan hardcoded UI fragments.

---

## Main Objective:

- Reusability
- Maintainability
- Scalability
- Consistency
- Cleaner frontend architecture

---

# 13. Naming Convention

## Controllers

```bash
UserController
ReportController
```

## Requests

```bash
UserStoreRequest
UserUpdateRequest
```

## DTOs

```bash
CreateUserDto
UpdateUserDto
```

## Services

```bash
UserService
ReportService
```

## Interfaces

```bash
UserRepositoryInterface
```

## Repositories

```bash
UserRepository
```

---

# 13. Forbidden Practices

## Jangan pernah:

- Business logic di Controller
- Query langsung di Controller
- Query langsung di Service tanpa Repository
- Model access tanpa Repository
- Request mentah ke Service
- Array mentah tanpa DTO
- Repository tanpa Interface
- Interface tanpa Provider binding
- Cross-domain chaos

---

# 14. Required Best Practices

## Wajib:

- Form Request validation
- `toDTO()` pada Request
- DTO usage
- Service layer
- Repository Pattern
- Interface Pattern
- Provider binding
- Domain separation
- Dependency Injection
- SOLID principles

---

# 15. Scalability Goals

Struktur ini dibuat untuk:

- Clean code
- Maintainability
- Testability
- Scalability
- Modular architecture
- AI agent consistency
- Future-proofing

---

# 16. Golden Rules

> Controller handles delivery.
> Request validates and transforms to DTO.
> Service handles business rules.
> Repository handles data access.
> Model represents data only.

---

# Final Enforcement Rule

Semua developer dan AI agent wajib mengikuti struktur ini tanpa pengecualian.

Pelanggaran terhadap rules ini dianggap sebagai:

- Technical debt
- Architecture violation
- Maintainability risk
- Scalability blocker

---

# Main Objective

Membangun sistem yang:

- Rapi
- Konsisten
- Mudah dikembangkan
- Mudah diuji
- Mudah di-scale
- Enterprise-ready
