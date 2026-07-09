# Deployment Guide - cPanel

## Frontend (managpro.matamaya.id)

### Build
```bash
cd frontend
npm run build
```

### Upload ke cPanel
1. Login cPanel → File Manager
2. Navigate ke `public_html/managpro.matamaya.id/`
3. Upload isi folder `dist/` (index.html + assets/)
4. Buat `.htaccess` untuk SPA routing:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

---

## Backend (apimanagpro.matamaya.id)

### Upload ke cPanel
1. Login cPanel → File Manager
2. Navigate ke `public_html/apimanagpro.matamaya.id/`
3. Upload isi folder `backend/` (KECUALI `vendor/`)
4. Login SSH atau Terminal di cPanel
5. Jalankan:
```bash
cd public_html/apimanagpro.matamaya.id
composer install --no-dev --optimize-autoloader
```

### Setup .env
Buat file `.env` di root backend dengan isi production:
```
CI_ENVIRONMENT = production
app.baseURL = 'https://apimanagpro.matamaya.id'
app.forceGlobalSecureRequests = true
database.default.hostname = localhost
database.default.database = managpro_db_name
database.default.username = managpro_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.port = 3306
jwt.secret = 8392c58944edc22cc936327ee6da61f6b236f41c59c27c979c62618f7194970c
jwt.accessExpiry = 36000
jwt.refreshExpiry = 1209600
frontend.url = https://managpro.matamaya.id
smtp.host = smtp.gmail.com
smtp.port = 587
smtp.user = your_email@gmail.com
smtp.pass = your_app_password
mail.fromName = ManagPro
mail.fromEmail = noreply@managpro.com
google.clientId = your_google_client_id
google.clientSecret = your_google_client_secret
google.redirectUri = https://apimanagpro.matamaya.id/api/auth/callback/google
```

### Setup Database
1. cPanel → MySQL Databases
2. Buat database baru: `managpro_db`
3. Buat user: `managpro_user` dengan password kuat
4. Tambah user ke database dengan ALL PRIVILEGES
5. Import database dari local:
```bash
mysqldump -u root managpro > managpro_backup.sql
mysql -u managpro_user -p managpro_db < managpro_backup.sql
```

### Setup .htaccess untuk API
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)/$ /$1 [L,R=301]

    # Front Controller
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>

# Protect sensitive files
<FilesMatch "\.(env|git|htaccess)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### Jalankan Migration
```bash
cd public_html/apimanagpro.matamaya.id
php spark migrate
```

---

## Google OAuth Setup

1. Google Cloud Console → APIs & Services → Credentials
2. Edit OAuth 2.0 Client ID
3. Tambah Authorized redirect URI:
   - `https://apimanagpro.matamaya.id/api/auth/callback/google`
4. Tambah Authorized JavaScript origins:
   - `https://managpro.matamaya.id`

---

## SSL/HTTPS

Pastikan SSL aktif di cPanel untuk kedua domain:
- `managpro.matamaya.id`
- `apimanagpro.matamaya.id`

cPanel → SSL/TLS → Let's Encrypt → Issue untuk kedua domain

---

## Checklist Setelah Deploy

- [ ] Login/Register berfungsi
- [ ] Google OAuth berfungsi
- [ ] Email verifikasi terkirim
- [ ] Workspace CRUD berfungsi
- [ ] Board/Card berfungsi
- [ ] Invite member + email notifikasi
- [ ] Dashboard stats benar
- [ ] Billing page berfungsi
- [ ] API keys berfungsi
- [ ] Notifications berfungsi
