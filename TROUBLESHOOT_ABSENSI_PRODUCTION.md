# PANDUAN TROUBLESHOOT: Absensi Tidak Menyimpan di Production

## 📋 Ringkasan Masalah

Ketika form tambah absensi disimpan di production, data tidak masuk database, padahal di localhost berfungsi normal.

## ⚠️ 5 Penyebab Utama (Urutan Prioritas)

### 1. ❌ **SYMLINK STORAGE TIDAK TERBUAT** (PALING SERING)

**Gejala:**

- Tombol simpan terlihat OK, tapi data tidak tersimpan
- File foto / signature tidak tersimpan

**Solusi:**

```bash
# Login via SSH/Terminal di cPanel, kemudian:
cd /home/yourusername/public_html  # Sesuaikan dengan path domain Anda

# Cek apakah symlink sudah ada
ls -la | grep storage

# Jika belum ada (output tidak menunjukkan symlink), buat:
ln -s storage/app/public storage

# Verifikasi berhasil
ls -la storage
# Output harus menunjukkan "storage -> storage/app/public"
```

---

### 2. 🔒 **PERMISSION FOLDER STORAGE SALAH**

**Gejala:**

- Error: "Permission denied" atau "Cannot write to storage"
- File upload gagal

**Solusi:**

```bash
# Di terminal/SSH server hosting Anda:
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Lebih detail:
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;
```

---

### 3. 📤 **PHP.INI LIMITS TERLALU KECIL**

**Gejala:**

- Form dengan banyak foto tidak bisa disimpan
- Error saat upload file, terutama untuk image

**Cek & Solusi di cPanel:**

1. Login ke **cPanel**
2. Cari **"MultiPHP INI Editor"** atau **"PHP Configuration"**
3. Cari section **PHP Settings** dan update:
   - `post_max_size` = **100M** (atau sesuai kebutuhan)
   - `upload_max_filesize` = **50M** (harus < post_max_size)
   - `max_file_uploads` = **20**

4. **Jangan lupa klik Save!**

Atau via `.htaccess` (jika MultiPHP tidak tersedia):

```apache
php_value post_max_size 100M
php_value upload_max_filesize 50M
php_value max_file_uploads 20
```

Letakkan di file `.htaccess` di root public folder.

---

### 4. 🚨 **ModSecurity RULE MASIH BLOCKING (Meski Sudah Dinonaktifkan Global)**

**Gejala:**

- Form dengan signature/foto tidak submit
- Error 403 Forbidden

**Solusi di cPanel:**

1. **Cek Global Setting:**
   - **Home > Appearance > Manage Mod Security Engine**
   - Pastikan **"OFF"** untuk semua rule sets

2. **Jika tetap bermasalah, disable per-domain:**
   - **Home > Appearance > Manage Mod Security Engine**
   - Find your domain → Click **Toggle ModSecurity**
   - Set to **"OFF"**

3. **Alternative: Update .htaccess**

```apache
<IfModule mod_security.c>
  SecFilterEngine Off
  SecFilterScanPOST Off
</IfModule>
```

---

### 5. 🐘 **DATABASE CONNECTION ISSUE**

**Gejala:**

- Error tentang database/connection
- SQLSTATE errors di screen atau log

**Cek di .env production:**

```
DB_HOST=localhost       # atau IP actual
DB_PORT=3306
DB_DATABASE=db_name
DB_USERNAME=db_user
DB_PASSWORD=password
```

**Verifikasi:**

```bash
# SSH ke server
mysql -h localhost -u db_user -p db_name

# Jika sukses terhubung, cek table absensis ada:
show tables;
describe absensis;
```

---

## 🔍 **DIAGNOSA: Lihat Error Log**

Setelah update kode terbaru, error akan tercatat lebih detail:

```bash
# SSH ke server hosting, go to:
tail -f storage/logs/laravel.log

# Atau lihat via cPanel File Manager:
# /home/yourusername/public_html/storage/logs/laravel.log
```

**Cari yang error-related:**

```
[ERROR] Error storing absensi
[ERROR] Failed to store photo
[ERROR] Error storing signature
```

---

## 📝 **LANGKAH-LANGKAH TROUBLESHOOT LENGKAP**

### Step 1: Update Kode

```bash
# Pull latest code (sudah include perbaikan logging)
git pull  # atau upload file terbaru

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Step 2: Cek Storage Setup

```bash
# 1. Ensure symlink
cd /path/to/public_html
ln -s storage/app/public storage 2>/dev/null || echo "Symlink already exists"

# 2. Fix permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Step 3: Test dengan Data Simple

- Buka form tambah absensi
- Pilih **"IZIN"** (tanpa upload foto/signature)
- Hanya isikan keterangan
- **Simpan**

💡 Jika step 3 berhasil → problem ada di file upload
💡 Jika step 3 gagal juga → problem ada di database/general

### Step 4: Check Log

```bash
# Look for recent errors
tail -50 storage/logs/laravel.log
```

### Step 5: Test dengan Photo

- Jika step 3 berhasil, sekarang coba dengan **"HADIR"**
- Pilih kondisi fisik **"BAIK"** (tanpa luka)
- **Simpan**

💡 Jika ini berhasil → problem masif file handling

---

## 🛠️ **QUICK FIX CHECKLIST**

```
☐ Symlink storage/app/public → storage created?
☐ Storage folder permissions 775?
☐ .env production settings correct?
☐ post_max_size & upload_max_filesize di cPanel ≥ 50M?
☐ ModSecurity disabled?
☐ Database connection working?
☐ storage/logs/laravel.log accessible?
☐ Tested dengan form simple (Izin) dulu?
```

---

## 📞 **JIKA SEMUA TETAP TIDAK BERHASIL**

1. **SSH ke server & run:**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate
```

2. **Check .env APP_DEBUG:**

```
# Di .env production (JANGAN DI LOCAL):
APP_DEBUG=false  # Untuk production
APP_ENV=production
```

3. **Lihat recent log:**

```bash
tail -100 storage/logs/laravel.log | grep -i "error\|failed\|exception"
```

4. **Contact hosting support dengan info:**
   - Error message dari laravel.log
   - PHP version
   - Server OS
   - MySQL version
   - Output dari `ls -la storage/`

---

## 📊 **TESTING FORM VARIATIONS**

### Test 1: Minimal (Izin)

```
✓ Anak Didik: Pilih
✓ Izin: Check
✓ Keterangan: "Sakit"
✓ Simpan → Check database
```

### Test 2: Hadir Baik

```
✓ Anak Didik: Pilih
✓ Izin: Uncheck
✓ Kondisi Fisik: Baik
✓ Nama Pengantar: "Ibu Siti"
✓ Signature: Tanda tangan
✓ Simpan → Check database
```

### Test 3: Hadir Ada Tanda (FULL TEST)

```
✓ Anak Didik: Pilih
✓ Izin: Uncheck
✓ Kondisi Fisik: Ada Tanda
✓ Jenis Tanda Fisik: Berapa banyak pilihan
✓ Keterangan Tanda: Deskripsi
✓ Lokasi: Select di body map
✓ Foto Bukti: Upload 2-3 photo
✓ Nama Pengantar: "Ibu Siti"
✓ Signature: Tanda tangan
✓ Simpan → Check database & storage/absensi/bukti folder
```

---

## ✅ **VERIFICATION**

Setelah semua fix, verifikasi:

```bash
# 1. Check database record berhasil disimpan
mysql -u user -p database_name
SELECT id, anak_didik_id, status, tanggal FROM absensis
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
LIMIT 10;

# 2. Check storage files tersimpan
ls -la storage/app/public/absensi/bukti/
ls -la storage/app/public/absensi/signatures/

# 3. Check laravel.log untuk success message
grep "Absensi created successfully" storage/logs/laravel.log
```

---

**Last Update:** February 2026  
**Code Version:** Improved with detailed logging & error handling
