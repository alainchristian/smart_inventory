# 🔒 Enable HTTPS for Camera Access

## ⚠️ CRITICAL: Camera Requires HTTPS

**Problem:** Modern browsers **block camera access over HTTP** for security reasons.

**Current setup:**
- ❌ `http://192.168.1.66:8000` → Camera blocked (getUserMedia not available)
- ✅ HTTPS required → Camera allowed

**Why it's blocked:**
- HTTP is unencrypted and insecure
- W3C specification requires "secure context" for getUserMedia API
- Only exception: `localhost` / `127.0.0.1` (but these don't work from phone)

**Solution:** Access your app via HTTPS instead of HTTP.

---

## 🚀 QUICK START: ngrok (Recommended for Testing)

**Setup time:** 5 minutes
**Best for:** Quick testing, temporary use

### Step 1: Install ngrok

**Download:** https://ngrok.com/download

1. Download ngrok for Windows
2. Extract `ngrok.exe`
3. Place in project folder OR add to PATH

**Or via Chocolatey:**
```bash
choco install ngrok
```

### Step 2: Start Laravel Server

**Terminal 1:**
```bash
cd C:\Users\Christian\Desktop\projects\smart-inventory
php artisan serve --host=0.0.0.0 --port=8000
```

Keep this running!

### Step 3: Start ngrok

**Option A - Use Helper Script (Easiest):**

Double-click: `start-with-ngrok.bat`

**Option B - Manual Command:**

**Terminal 2 (new window):**
```bash
ngrok http 8000
```

**Expected output:**
```
ngrok

Forwarding  https://abc123xyz.ngrok-free.app -> http://localhost:8000
Forwarding  http://abc123xyz.ngrok-free.app -> http://localhost:8000

Connections  ttl  opn  rt1  rt5  p50  p90
             0    0    0    0    0    0
```

**Copy the HTTPS URL:** `https://abc123xyz.ngrok-free.app`

### Step 4: Update Laravel Configuration

**Option A - Use Helper Script (Easiest):**

Run in Command Prompt:
```bash
update-env-ngrok.bat https://abc123xyz.ngrok-free.app
```

Replace with YOUR actual ngrok URL!

**Option B - Manual Update:**

**File:** `.env`

Change:
```env
APP_URL=http://192.168.1.66:8000
```

To:
```env
APP_URL=https://abc123xyz.ngrok-free.app
```

Then clear cache:
```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
```

### Step 5: Test HTTPS Access

**On Desktop Browser:**
1. Open: `https://abc123xyz.ngrok-free.app` (your URL)
2. Navigate to transfer page
3. Click "Enable Phone Scanner"
4. Verify QR code shows HTTPS URL

**On Phone:**
1. Scan QR code
2. Scanner page opens with HTTPS
3. Browser prompts: "Allow camera?"
4. Tap "Allow"
5. ✓ Camera should start!

### ngrok Notes

**Advantages:**
- ✅ Works immediately
- ✅ Real SSL certificate (trusted by browsers)
- ✅ No certificate installation needed
- ✅ Accessible from anywhere (internet)

**Disadvantages:**
- ⚠️ URL changes each restart (free tier)
- ⚠️ Requires internet connection
- ⚠️ Slight latency

**Free tier limits:**
- Random URL each restart
- 40 connections/minute
- 1 online ngrok process

**Every time you restart ngrok:**
1. Copy new HTTPS URL
2. Run: `update-env-ngrok.bat [new-url]`
3. Refresh browser

---

## 🏢 PERMANENT SOLUTION: Laragon (Best for Development)

**Setup time:** 15 minutes
**Best for:** Permanent dev environment, no internet required

### Step 1: Install Laragon

**Download:** https://laragon.org/download/

Run installer with default settings.

### Step 2: Move Project to Laragon

**Copy project folder to:**
```
C:\laragon\www\smart-inventory
```

### Step 3: Configure Virtual Host

1. Right-click Laragon tray icon
2. **Apache** → **Create virtual host**
3. Enter: `smart-inventory`
4. Click OK

### Step 4: Enable SSL

1. Right-click Laragon tray icon
2. **SSL** → **smart-inventory.test**
3. Laragon automatically creates SSL certificate

### Step 5: Update Configuration

**File:** `C:\laragon\www\smart-inventory\.env`

```env
APP_URL=https://smart-inventory.test
```

```bash
php artisan config:clear
php artisan config:cache
```

### Step 6: Access via HTTPS

**Desktop:** `https://smart-inventory.test`

**Phone (on same WiFi):**
1. Find PC IP: `ipconfig` → WiFi IPv4 address
2. Access: `https://192.168.1.66` (or your PC IP)
3. May need to accept self-signed certificate warning

### Laragon Advantages

- ✅ Professional dev environment
- ✅ Automatic SSL setup
- ✅ Pretty URLs (.test domain)
- ✅ No internet required
- ✅ URL never changes
- ✅ Includes MySQL, PHP, Node.js

---

## 🔧 ADVANCED: Self-Signed SSL Certificate

**Setup time:** 30 minutes
**Best for:** Advanced users, full control

### Step 1: Generate Certificate

**Open Git Bash or WSL:**

```bash
cd C:/Users/Christian/Desktop/projects/smart-inventory

mkdir -p storage/ssl

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout storage/ssl/server.key \
    -out storage/ssl/server.crt \
    -subj "/C=US/ST=State/L=City/O=Dev/CN=192.168.1.66"
```

### Step 2: Configure Web Server

**Option A: Apache (XAMPP)**

**File:** `C:\xampp\apache\conf\extra\httpd-ssl.conf`

Add:
```apache
Listen 8443

<VirtualHost *:8443>
    DocumentRoot "C:/Users/Christian/Desktop/projects/smart-inventory/public"
    ServerName 192.168.1.66:8443

    SSLEngine on
    SSLCertificateFile "C:/Users/Christian/Desktop/projects/smart-inventory/storage/ssl/server.crt"
    SSLCertificateKeyFile "C:/Users/Christian/Desktop/projects/smart-inventory/storage/ssl/server.key"

    <Directory "C:/Users/Christian/Desktop/projects/smart-inventory/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable SSL in `httpd.conf`:
```apache
LoadModule ssl_module modules/mod_ssl.so
Include conf/extra/httpd-ssl.conf
```

Restart Apache.

**Access:** `https://192.168.1.66:8443`

### Step 3: Trust Certificate

**Windows (PC):**
1. Double-click `storage/ssl/server.crt`
2. "Install Certificate"
3. "Local Machine"
4. "Trusted Root Certification Authorities"
5. Restart browser

**Android Phone:**
1. Copy `server.crt` to phone
2. Settings → Security → Install certificate
3. Choose "CA certificate"
4. Select file
5. Restart browser

**iOS/iPhone:**
1. Email certificate to yourself
2. Tap to install
3. Settings → General → VPN & Device Management
4. Install profile
5. Settings → General → About → Certificate Trust Settings
6. Enable full trust

### Step 4: Update Laravel

**File:** `.env`
```env
APP_URL=https://192.168.1.66:8443
```

```bash
php artisan config:clear
php artisan config:cache
```

---

## 🌐 ALTERNATIVE: Serveo (Free, No Account)

**Setup time:** 2 minutes
**Best for:** Quick testing without account

```bash
ssh -R 80:localhost:8000 serveo.net
```

Provides: `https://abc123.serveo.net`

Update `.env`:
```env
APP_URL=https://abc123.serveo.net
```

**Similar to ngrok but:**
- ✅ No account needed
- ✅ No installation
- ⚠️ Less reliable
- ⚠️ URL changes each time

---

## 📋 COMPARISON

| Solution | Time | Pros | Cons | Best For |
|----------|------|------|------|----------|
| **ngrok** | 5 min | Fast, Real SSL, Easy | URL changes, Internet | Testing |
| **Laragon** | 15 min | Professional, Permanent | Windows only | Development |
| **Self-signed** | 30 min | Full control, No internet | Complex setup, Trust issues | Advanced |
| **Serveo** | 2 min | No account | Less reliable | Quick test |

---

## ✅ VERIFICATION CHECKLIST

After setting up HTTPS:

- [ ] HTTPS server running (ngrok, Laragon, or Apache with SSL)
- [ ] APP_URL in .env uses HTTPS (not HTTP)
- [ ] Config cache cleared (`php artisan config:clear`)
- [ ] Desktop browser can access via HTTPS
- [ ] No SSL certificate warnings (or accepted)
- [ ] Transfer page loads correctly
- [ ] QR code displays HTTPS URL (check debug section)
- [ ] Phone can access HTTPS URL
- [ ] Browser prompts for camera permission
- [ ] Camera starts successfully

---

## 🐛 TROUBLESHOOTING

### Issue: ngrok "Connection refused"

**Cause:** Laravel server not running

**Fix:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Keep running in separate terminal.

---

### Issue: QR code still shows HTTP

**Cause:** Config cache not cleared

**Fix:**
```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
```

Hard refresh browser: Ctrl+Shift+R (or Ctrl+F5)

---

### Issue: "Your connection is not private" on phone

**Cause:** Self-signed certificate not trusted

**Quick fix:** Click "Advanced" → "Proceed anyway"

**Proper fix:** Install certificate on phone (see Step 3 above)

---

### Issue: Camera still blocked

**Check:**
1. URL starts with `https://` (look for lock icon)
2. No mixed content warnings in console
3. Browser supports getUserMedia (try test camera button)
4. Permission granted in browser settings

**Verify secure context:**
```javascript
// In browser console:
console.log(window.isSecureContext);  // Should be: true
```

---

### Issue: ngrok URL changed after restart

**Expected behavior** - Free tier generates new URL each time

**Fix:**
1. Copy new HTTPS URL from ngrok output
2. Run: `update-env-ngrok.bat [new-url]`
3. Refresh browser

**To get static URL:** Upgrade to ngrok paid plan ($8/month)

---

### Issue: Laragon virtual host not working

**Fix:**
1. Check Windows hosts file: `C:\Windows\System32\drivers\etc\hosts`
2. Should contain: `127.0.0.1 smart-inventory.test`
3. Restart Laragon
4. Flush DNS: `ipconfig /flushdns`

---

## 🎯 EXPECTED RESULT

### Desktop Browser:
```
✓ https://abc123.ngrok-free.app
  🔒 Secure

  Transfer #16
  [Enable Phone Scanner]

  📱 Phone Scanner Active
  Session Code: A3X9K2

  [QR Code displays https://abc123.ngrok-free.app/scanner?code=A3X9K2]
```

### Phone Scanner:
```
✓ https://abc123.ngrok-free.app/scanner?code=A3X9K2
  🔒 Secure

  📱 Mobile Scanner
  Connected to Session A3X9K2

  🔄 Initializing Camera...
  [Browser prompts: "Allow smart-inventory to use your camera?"]
  [Tap "Allow"]

  📷 Camera Active
  [Live camera feed appears]

  Scans: 0 | Last: -
  [⛔ Disconnect]
```

---

## 📚 HELPER SCRIPTS CREATED

**File** | **Purpose**
---------|------------
`start-with-ngrok.bat` | Start ngrok tunnel (requires ngrok installed)
`update-env-ngrok.bat` | Update .env with ngrok URL and clear cache
`start-server-network.bat` | Start Laravel on HTTP (warns about camera)
`diagnose-network.ps1` | Diagnose network/server issues

---

## 🎓 UNDERSTANDING THE ISSUE

**Why HTTP doesn't work:**

```javascript
// On HTTP (http://192.168.1.66:8000):
navigator.mediaDevices.getUserMedia({ video: true })
// ❌ ERROR: "getUserMedia is not defined"
//     or "NotAllowedError: Permission denied"

// On HTTPS (https://abc123.ngrok-free.app):
navigator.mediaDevices.getUserMedia({ video: true })
// ✓ SUCCESS: Browser prompts for permission
```

**Secure contexts:**
- ✅ `https://anything`
- ✅ `http://localhost`
- ✅ `http://127.0.0.1`
- ❌ `http://192.168.x.x`
- ❌ `http://10.x.x.x`

**Your setup:**
- Desktop: Can access via `http://localhost:8000` (secure context for desktop)
- Phone: Cannot use localhost (different device)
- Phone: `http://192.168.1.66:8000` → **Not a secure context** → Camera blocked
- Solution: Phone needs HTTPS → Secure context → Camera allowed

---

## 🚀 RECOMMENDED PATH

### For immediate testing:

1. ✅ **Install ngrok** (https://ngrok.com/download)
2. ✅ **Run:** `start-with-ngrok.bat`
3. ✅ **Copy HTTPS URL** from ngrok output
4. ✅ **Run:** `update-env-ngrok.bat https://your-url.ngrok-free.app`
5. ✅ **Test** on phone - camera should work!

### For permanent development:

1. **Install Laragon** (https://laragon.org)
2. Move project to `C:\laragon\www\smart-inventory`
3. Enable SSL for virtual host
4. Access via `https://smart-inventory.test`
5. Professional setup, works offline

---

## 💡 PRODUCTION DEPLOYMENT

For production deployment, use:

- **Let's Encrypt** - Free SSL certificates (certbot)
- **Cloudflare** - Free SSL/CDN
- **Hosting providers** - Usually include free SSL
- **AWS Certificate Manager** - Free SSL for AWS resources

**Never use self-signed certificates in production!**

---

## 📞 SUPPORT

If you encounter issues:

1. Check this guide's troubleshooting section
2. Verify checklist items
3. Check browser console for errors (F12)
4. Test camera on known HTTPS site: https://webcam-test.com

**When asking for help, provide:**
- Which solution you're using (ngrok/Laragon/self-signed)
- URL you're accessing (http vs https)
- Error messages from browser console
- Screenshot of issue

---

## 🎉 SUMMARY

**The Problem:**
- Camera blocked over HTTP (`http://192.168.1.66:8000`)

**The Solution:**
- Use HTTPS instead (`https://abc123.ngrok-free.app`)

**Quickest fix:**
1. Install ngrok
2. Run `start-with-ngrok.bat`
3. Run `update-env-ngrok.bat [ngrok-url]`
4. Test on phone ✓

**Camera should now work perfectly! 📷**
