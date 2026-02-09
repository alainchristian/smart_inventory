# 🔴 URGENT FIX: Connection Timeout - Phone Cannot Reach Server

## ❌ PROBLEM CONFIRMED

Your server is listening on `127.0.0.1:8000` (localhost only) instead of `0.0.0.0:8000` (all interfaces).

**This is why your phone gets "connection timeout"!**

---

## ✅ SOLUTION (3 Steps - Takes 2 Minutes)

### Step 1: Stop Current Server

In your terminal where the server is running, press:
```
Ctrl + C
```

### Step 2: Run Firewall Setup (One-Time Only)

**Right-click on `setup-firewall.ps1`** → Select **"Run with PowerShell as Administrator"**

This creates a firewall rule to allow port 8000.

**Alternative - Manual Firewall Rule:**
```powershell
# Open PowerShell as Administrator and run:
New-NetFirewallRule -DisplayName "Laravel Dev Server Port 8000" -Direction Inbound -Action Allow -Protocol TCP -LocalPort 8000 -Profile Private
```

### Step 3: Start Server Correctly

**Double-click:** `start-server-network.bat`

**Or run in terminal:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Expected output:**
```
INFO  Server running on [http://0.0.0.0:8000].
```

**IMPORTANT:** It should show `0.0.0.0`, NOT `127.0.0.1`!

---

## 🧪 VERIFY IT WORKS

### Test 1: On PC Browser

Open: `http://192.168.1.66:8000`

**Expected:** Laravel page loads ✓

### Test 2: On Phone Browser

1. Make sure phone is on **same WiFi** as PC
2. Open phone browser
3. Go to: `http://192.168.1.66:8000`

**Expected:** Laravel page loads ✓

**If timeout:** Run `diagnose-network.ps1` to find the issue

### Test 3: QR Code

1. On PC: Go to transfer page → Click "Enable Phone Scanner"
2. Check yellow debug box shows: `http://192.168.1.66:8000/scanner?code=...`
3. On phone: Open camera, point at QR code
4. Tap notification
5. Scanner page should load!

---

## 🔧 Troubleshooting

### Still Getting Timeout?

**Run diagnostics:**
```
Right-click diagnose-network.ps1 → Run with PowerShell
```

This will check:
- ✓ PC's IP address
- ✓ Server is running
- ✓ Port is listening on 0.0.0.0 (not 127.0.0.1)
- ✓ Firewall rule exists
- ✓ Local connectivity works
- ✓ .env configuration

### Common Issues:

**1. Server shows 127.0.0.1 instead of 0.0.0.0**
- **Fix:** You forgot `--host=0.0.0.0` flag
- **Use:** `start-server-network.bat` to start correctly

**2. Firewall blocking**
- **Fix:** Run `setup-firewall.ps1` as Administrator

**3. Phone on different network**
- **Check:** Phone WiFi matches PC WiFi (exact name)
- **Not:** "MyWiFi" vs "MyWiFi-5G" (these are different!)

**4. Port already in use**
```powershell
# Kill all PHP processes
Stop-Process -Name php -Force

# Start server again
start-server-network.bat
```

---

## 📋 Quick Checklist

- [ ] Stopped current server (Ctrl+C)
- [ ] Ran `setup-firewall.ps1` as Administrator
- [ ] Started server with `start-server-network.bat`
- [ ] Server shows `0.0.0.0:8000` (not `127.0.0.1:8000`)
- [ ] Can open `http://192.168.1.66:8000` in PC browser
- [ ] Phone is on same WiFi network as PC
- [ ] Can open `http://192.168.1.66:8000` in phone browser
- [ ] QR code scanning works!

---

## 🎯 What Changed?

### Before (Wrong):
```bash
php artisan serve
# Listens on: 127.0.0.1:8000 (localhost only)
# Phone cannot connect ❌
```

### After (Correct):
```bash
php artisan serve --host=0.0.0.0 --port=8000
# Listens on: 0.0.0.0:8000 (all network interfaces)
# Phone can connect ✓
```

### Why It Matters:

- `127.0.0.1` = localhost = only your PC can access
- `0.0.0.0` = all interfaces = other devices on network can access

---

## 🚀 Scripts Created For You

1. **start-server-network.bat** - Starts server correctly (double-click this!)
2. **setup-firewall.ps1** - Creates firewall rule (run as Admin once)
3. **diagnose-network.ps1** - Checks everything and shows issues

**Use these every time you start the server!**

---

## ⚡ Quick Commands Reference

**Start server correctly:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Check if listening on all interfaces:**
```bash
netstat -an | findstr :8000
# Should show: 0.0.0.0:8000 LISTENING
```

**Kill PHP if port is busy:**
```powershell
Stop-Process -Name php -Force
```

**Test from PC:**
```
http://192.168.1.66:8000
```

**Test from phone:**
```
http://192.168.1.66:8000
```

---

## ✨ Success Indicators

You'll know it's working when:

1. ✅ Server output shows `0.0.0.0:8000`
2. ✅ `netstat` shows `0.0.0.0:8000` (not `127.0.0.1`)
3. ✅ PC browser loads `http://192.168.1.66:8000`
4. ✅ Phone browser loads `http://192.168.1.66:8000`
5. ✅ QR code scan opens scanner page on phone
6. ✅ Camera starts automatically on phone

**The fix is simple: Use `--host=0.0.0.0` when starting server!**

---

## 🎉 You're Done!

Just run `start-server-network.bat` every time you start working, and your phone scanner will work perfectly!
