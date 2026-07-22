# WawanGo — Setup Lokal (Windows)

## Prasyarat

- **PHP 8.3** standalone di `C:\php83` (terpisah dari PHP 8.2 bawaan XAMPP, agar proyek lain yang memakai XAMPP tidak terganggu). Ekstensi yang aktif: `pdo_mysql, mysqli, gd, fileinfo, mbstring, openssl, curl, zip, bcmath, exif, intl, pdo_sqlite, sqlite3`.
- **MariaDB/MySQL** via XAMPP (`C:\xampp\mysql\bin\mysqld.exe`), database `wawango`.
- **Composer** (`C:\composer\composer.phar`), dijalankan dengan `C:\php83\php.exe` agar memakai PHP 8.3.
- **Node.js** (v24+) untuk Vite/npm.

## Menjalankan Proyek

```bash
# 1. Pastikan MariaDB jalan (XAMPP Control Panel, atau manual):
C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini

# 2. Install dependencies (sekali saja / setelah composer.json atau package.json berubah)
C:\php83\php.exe C:\composer\composer.phar install
npm install

# 3. Migrate + seed database
C:\php83\php.exe artisan migrate:fresh --seed

# 4. Jalankan server di port 8002
C:\php83\php.exe artisan serve --port=8002

# 5. Jalankan Vite dev server (terminal terpisah, untuk hot reload saat development)
npm run dev
```

Aplikasi dapat diakses di `http://localhost:8002`.

## Akun Default (hasil seeder)

| Role | Email | Password |
|---|---|---|
| Admin | admin@wawango.test | password |
| Penyedia Jasa | wawan@wawango.test | password |
| Penyedia Jasa | sari@wawango.test | password |
| Pemesan | pemesan1@wawango.test | password |
| Pemesan | pemesan2@wawango.test | password |
| Pemesan | (3 akun acak tambahan, lihat `php artisan tinker` → `User::role('pemesan')->get()`) | password |

## Testing

```bash
C:\php83\php.exe artisan test
```

Tes memakai SQLite in-memory (lihat `phpunit.xml`), terpisah dari database MySQL development.
