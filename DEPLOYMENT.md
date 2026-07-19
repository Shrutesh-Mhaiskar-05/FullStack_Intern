# Deployment Guide

## Option 1: InfinityFree (Free)

1. **Create account** at https://infinityfree.net
2. **Create a new website** → Choose a subdomain
3. **Upload files** via FTP or File Manager:
   - Server: `ftp.infinityfree.com`
   - Username/Password from control panel
   - Upload all files from the project root to `htdocs/`
4. **Create MySQL Database**:
   - Go to Control Panel → MySQL Databases
   - Create database → Note the hostname (sqlXXX.infinityfree.com)
5. **Import schema**:
   - Open phpMyAdmin from control panel
   - Select your database → Import `sql/schema.sql`
6. **Update config**: Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');
   define('DB_USER', 'if0_XXXXXXX');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'if0_XXXXXXX_dbname');
   ```
7. **Generate covers**: Visit `https://yoursite.online/assets/images/generate_covers.php`
8. **Admin login**: `admin@bookstore.com` / `Admin@123`

## Option 2: 000webhost

1. **Create account** at https://000webhost.com
2. **Create website** → Upload files via File Manager
3. **Create MySQL database** in control panel
4. **Import schema** via phpMyAdmin
5. **Update config** with your database credentials
6. **Set permissions**: Ensure `uploads/` is writable (chmod 755)

## Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] Config updated with live DB credentials
- [ ] Uploads directory is writable
- [ ] .htaccess is present (rename from .htaccess.txt if needed)
- [ ] Book covers generated
- [ ] OTP email works (configure SMTP if needed)
- [ ] Test registration with email verification
- [ ] Test admin panel
- [ ] All pages load without errors
