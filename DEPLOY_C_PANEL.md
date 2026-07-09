# Deployment Guide - cPanel

## Server Requirements
- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite
- SSL/TLS certificate

---

## Step 1: Database Setup

### Via cPanel MySQL Databases
1. Login cPanel → MySQL Databases
2. Create Database: `managpro_db`
3. Create User: `managpro_user` with strong password
4. Add User to Database → ALL PRIVILEGES

### Import Database
```bash
# From local machine
mysqldump -u root managpro > managpro_backup.sql

# Upload managpro_backup.sql to cPanel File Manager
# Then via SSH or MySQL Import in cPanel:
mysql -u managpro_user -p managpro_db < managpro_backup.sql
```

---

## Step 2: Backend Deployment

### Upload Files
1. cPanel → File Manager → `public_html/apimanagpro.matamaya.id/`
2. Upload entire `backend/` folder contents (EXCEPT `vendor/`)
3. Upload `.htaccess` file

### Install Dependencies via SSH
```bash
cd ~/public_html/apimanagpro.matamaya.id
composer install --no-dev --optimize-autoloader
```

### Create .env File
Create `.env` in root backend directory:
```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://apimanagpro.matamaya.id'
app.forceGlobalSecureRequests = true

database.default.hostname = localhost
database.default.database = managpro_db
database.default.username = managpro_user
database.default.password = YOUR_DB_PASSWORD
database.default.DBDriver = MySQLi
database.default.port = 3306

encryption.key = YOUR_ENCRYPTION_KEY
jwt.secret = YOUR_JWT_SECRET
jwt.accessExpiry = 36000
jwt.refreshExpiry = 1209600

frontend.url = https://managpro.matamaya.id

smtp.host = smtp.gmail.com
smtp.port = 587
smtp.user = YOUR_EMAIL@gmail.com
smtp.pass = YOUR_APP_PASSWORD
mail.fromName = ManagPro
mail.fromEmail = noreply@managpro.com

google.clientId = YOUR_GOOGLE_CLIENT_ID
google.clientSecret = YOUR_GOOGLE_CLIENT_SECRET
google.redirectUri = https://apimanagpro.matamaya.id/api/auth/callback/google

MIDTRANS_SERVER_KEY=YOUR_MIDTRANS_KEY
MIDTRANS_CLIENT_KEY=YOUR_MIDTRANS_KEY
MIDTRANS_IS_PRODUCTION=FALSE

S3_REGION=auto
S3_ENDPOINT=YOUR_S3_ENDPOINT
S3_ACCESS_KEY_ID=YOUR_S3_KEY
S3_SECRET_ACCESS_KEY=YOUR_S3_SECRET
S3_FORCE_PATH_STYLE=true

PUBLIC_AVATAR_BUCKET_NAME=managpro
PUBLIC_ATTACHMENTS_BUCKET_NAME=managpro
PUBLIC_STORAGE_DOMAIN=YOUR_STORAGE_DOMAIN
```

### Set Permissions
```bash
chmod -R 755 writable/
chmod -R 755 vendor/
```

### Run Migrations
```bash
cd ~/public_html/apimanagpro.matamaya.id
php spark migrate
```

---

## Step 3: Frontend Deployment

### Build for Production
```bash
cd frontend
npm install
npm run build
```

### Upload Files
1. cPanel → File Manager → `public_html/managpro.matamaya.id/`
2. Upload contents of `dist/` folder
3. Upload `.htaccess` file

---

## Step 4: SSL Setup

1. cPanel → SSL/TLS → Let's Encrypt
2. Select both domains:
   - `managpro.matamaya.id`
   - `apimanagpro.matamaya.id`
3. Issue certificates

---

## Step 5: Google OAuth Setup

1. Google Cloud Console → APIs & Services → Credentials
2. Edit OAuth 2.0 Client ID
3. Add Authorized redirect URIs:
   - `https://apimanagpro.matamaya.id/api/auth/callback/google`
4. Add Authorized JavaScript origins:
   - `https://managpro.matamaya.id`

---

## Step 6: Post-Deployment Checklist

- [ ] SSL active on both domains
- [ ] API docs accessible at `https://apimanagpro.matamaya.id/`
- [ ] Health check: `https://apimanagpro.matamaya.id/api/health`
- [ ] Login/Register works
- [ ] Google OAuth works
- [ ] Email verification works
- [ ] Workspace CRUD works
- [ ] Board/Card features work
- [ ] Invite + email notifications work
- [ ] Dashboard loads correctly
- [ ] Billing page works

---

## Troubleshooting

### 500 Internal Server Error
- Check PHP version (8.2+)
- Check `.env` file exists and is readable
- Check `writable/` folder permissions (755)
- Check `composer install` completed

### 404 Not Found
- Verify `.htaccess` is in correct location
- Check mod_rewrite is enabled
- Verify `RewriteBase /` in .htaccess

### CORS Errors
- Update `frontend.url` in backend `.env`
- Check CORS config in `app/Config/Cors.php`

### Email Not Sending
- Verify SMTP credentials in `.env`
- Check spam folder
- Try app-specific password for Gmail
