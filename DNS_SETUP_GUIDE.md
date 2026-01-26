# DNS Setup Guide for commerce.dixwix.com

## 🚨 Issue: DNS Not Resolving

The domain `commerce.dixwix.com` is not resolving because DNS records haven't been configured yet.

**Server IP:** `82.180.132.134`

## ✅ Server Configuration Status

✅ Nginx configuration is correct:
- **Location:** `/etc/nginx/sites-enabled/commerce.dixwix.com.conf`
- **Document Root:** `/home/dixwix-commerce/htdocs/bagisto-master/public`
- **SSL Certificates:** Configured
- **Configuration:** Valid

✅ Bagisto is properly installed and configured

## 🔧 Solutions

### Option 1: Add DNS Records (Permanent Solution)

Add these DNS records in your domain registrar (where `dixwix.com` is registered):

**A Record:**
```
Type: A
Name: commerce
Value: 82.180.132.134
TTL: 3600 (or default)
```

**Or if you need both www and non-www:**
```
Type: A
Name: commerce
Value: 82.180.132.134

Type: A
Name: www.commerce (if needed)
Value: 82.180.132.134
```

**DNS Propagation:** DNS changes can take 24-48 hours to propagate globally, though often works within minutes to hours.

### Option 2: Temporary Access via /etc/hosts (Testing Only)

**On your local computer (Windows/Mac/Linux),** edit your hosts file to test the site:

**Windows:**
1. Open Notepad as Administrator
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Add line: `82.180.132.134 commerce.dixwix.com`
4. Save and close

**Mac/Linux:**
```bash
sudo nano /etc/hosts
# Add this line:
82.180.132.134 commerce.dixwix.com
```

Then access: `https://commerce.dixwix.com/installer`

**Note:** This only works on YOUR computer, not for public access.

### Option 3: Access via IP Address (Temporary)

Since nginx is configured to require the domain name, you may need to:

1. **Add to your /etc/hosts file** (as shown in Option 2), OR
2. **Temporarily modify nginx** to allow IP access (not recommended for production)

## 📋 Current Configuration Summary

- **Domain:** commerce.dixwix.com
- **Server IP:** 82.180.132.134
- **Document Root:** /home/dixwix-commerce/htdocs/bagisto-master/public
- **SSL:** Configured (https://commerce.dixwix.com)
- **Port:** 443 (HTTPS), 80 (HTTP redirects to HTTPS)

## 🔍 Verify DNS After Configuration

Once DNS is configured, verify with:

```bash
# Check if DNS is resolving
dig commerce.dixwix.com +short
# Should return: 82.180.132.134

# Or on Windows:
nslookup commerce.dixwix.com
# Should return: 82.180.132.134
```

## ✅ After DNS is Configured

Once DNS resolves, you can:

1. **Access the installer:**
   ```
   https://commerce.dixwix.com/installer
   ```

2. **Complete Bagisto installation:**
   - The GUI installer will guide you through setup
   - Database is already configured in .env
   - Create admin account

3. **Access the site:**
   - Frontend: https://commerce.dixwix.com/
   - Admin: https://commerce.dixwix.com/admin

## 🆘 Need Help?

If DNS is already configured but still not working:
1. Check DNS propagation: https://www.whatsmydns.net/#A/commerce.dixwix.com
2. Verify nginx is running: `sudo systemctl status nginx`
3. Check nginx logs: `sudo tail -f /home/dixwix-commerce/logs/nginx/error.log`
