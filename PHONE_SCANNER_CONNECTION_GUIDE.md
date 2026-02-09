# 📱 Phone Scanner Connection Guide

## Problem You're Experiencing

When you point your phone camera at the QR code, you see the link appear but nothing happens automatically. **This is normal behavior!** You need to tap the notification/link to open it.

---

## ✅ CORRECT Way To Connect Your Phone

### Step 1: Open Phone's Native Camera App

**iOS (iPhone):**
- Use the default **Camera** app (not a QR scanner app)
- Swipe from right or tap Camera icon on lock screen

**Android:**
- Use the default **Camera** app
- OR use Google Lens (if available)

---

### Step 2: Point Camera at QR Code

1. Hold phone **20-30cm (8-12 inches)** away from desktop screen
2. Point camera directly at the QR code (the purple/indigo bordered square)
3. Keep steady for **1-2 seconds**

---

### Step 3: **TAP THE NOTIFICATION** (CRITICAL STEP)

**This is where most people get stuck!** When your camera recognizes the QR code:

**iPhone:**
```
┌─────────────────────────────────────┐
│  🔗 Open "localhost:8000/scanner"   │  ← TAP THIS!
└─────────────────────────────────────┘
```
A yellow banner appears at the top of the screen saying "Open in Safari" or showing the URL.

**✅ You MUST tap this banner!**

**Android:**
```
┌─────────────────────────────────────┐
│  🔗 localhost:8000/scanner?code=... │  ← TAP THIS!
│  Open with Chrome                   │
└─────────────────────────────────────┘
```
A notification or pop-up appears at the bottom showing the URL.

**✅ You MUST tap this notification!**

---

### Step 4: Scanner Page Opens Automatically

After tapping the notification:

1. **Browser opens** (Safari on iPhone, Chrome on Android)
2. **Scanner page loads** with the URL: `http://localhost:8000/scanner?code=ABC123`
3. **Camera permission prompt** appears:
   ```
   "localhost:8000 would like to access the camera"
   [Don't Allow] [Allow]
   ```
4. **Tap "Allow"**

---

### Step 5: Start Scanning Barcodes

1. The phone camera will activate
2. Point at any product barcode
3. When scanned, you'll hear a beep/vibration
4. **The barcode appears on your desktop page within 2 seconds!**

---

## 🔧 Troubleshooting

### Issue: "I tap the notification but nothing opens"

**Solution:** Make sure both devices are on the **same WiFi network**.

- Desktop: Check WiFi connection
- Phone: Check WiFi connection
- They must be on the same network (e.g., both on "Home WiFi")

**If using localhost:** Your phone needs to access your computer's IP address.

**Better approach:** Use your computer's local IP instead of localhost.

1. Find your computer's local IP:
   ```bash
   # Windows
   ipconfig
   # Look for "IPv4 Address" (e.g., 192.168.1.100)
   ```

2. Update your Laravel APP_URL:
   ```
   # .env file
   APP_URL=http://192.168.1.100:8000
   ```

3. Restart Laravel server:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. Now the QR code will show: `http://192.168.1.100:8000/scanner?code=...`

---

### Issue: "The camera won't scan the QR code"

**Solutions:**

**A) Phone screen brightness too low:**
- Increase desktop screen brightness to 100%
- QR codes need good contrast to scan

**B) Too close or too far:**
- Hold phone **20-30cm** away from screen
- Not too close (won't focus)
- Not too far (too small to read)

**C) Screen glare:**
- Avoid direct light on desktop screen
- Tilt screen slightly if there's glare

**D) QR code too small:**
- The QR code should be **250x250 pixels**
- Use Ctrl + Mouse Wheel to zoom in if needed

---

### Issue: "Notification doesn't appear"

**iPhone Solutions:**

1. **Update iOS:**
   - Settings → General → Software Update
   - iOS 11+ required for QR scanning

2. **Enable QR scanning:**
   - Settings → Camera
   - Enable "Scan QR Codes"

3. **Try Safari directly:**
   - Open Safari
   - Go to: `http://localhost:8000/scanner`
   - Manually enter the 6-digit code shown on desktop

**Android Solutions:**

1. **Update Google Lens:**
   - Some Android cameras need Google Lens for QR scanning
   - Install/update from Play Store

2. **Use Chrome:**
   - Open Chrome browser
   - Go to: `http://localhost:8000/scanner`
   - Manually enter the 6-digit code

3. **Try a QR scanner app:**
   - Install a dedicated QR scanner app
   - Scan the QR code
   - Tap the resulting link

---

### Issue: "Page opens but says 'Invalid session code'"

**Solution:** The QR code contains a time-limited code. If you see this:

1. Go back to desktop
2. Click **"Disable Scanner"**
3. Click **"Enable Phone Scanner"** again
4. A new QR code appears
5. Scan the NEW QR code

Sessions expire after **2 hours** of inactivity.

---

## 📱 Alternative: Manual Code Entry (If QR Doesn't Work)

If you can't get QR scanning to work, use manual entry:

1. **On phone:** Open browser (Safari/Chrome)
2. **Navigate to:** `http://localhost:8000/scanner`
3. **Look at desktop:** Find the 6-digit code (e.g., `ABC123`)
4. **Enter code on phone:** Type the 6-digit code
5. **Tap "Connect"**
6. **Allow camera permission**
7. **Start scanning!**

---

## ✅ Success Indicators

**You've connected successfully when:**

1. ✅ Phone shows scanner page with camera active
2. ✅ Top of phone screen shows: "Connected to session"
3. ✅ Desktop page shows green indicator or "Phone connected"
4. ✅ When you scan a barcode on phone, it appears on desktop within 2 seconds

---

## 📸 Step-by-Step Visual Guide

### Desktop View:

```
┌──────────────────────────────────────────────────────┐
│  Phone Scanner Mode           [Enable Phone Scanner] │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌────────────┐                                      │
│  │            │  📷 Quick Setup:                      │
│  │  QR CODE   │  1. Open phone camera                │
│  │  250x250   │  2. Point at QR code ←─────┐        │
│  │            │  3. TAP notification  ←─────┤ IMPORTANT│
│  └────────────┘  4. Start scanning!   ←─────┘        │
│                                                      │
│  Manual Code (backup): ABC123                        │
└──────────────────────────────────────────────────────┘
```

### Phone Steps:

```
Step 1: Open Camera
┌─────────────┐
│   📷        │
│ [Viewfinder]│
│             │
└─────────────┘

Step 2: Point at QR Code
┌─────────────┐
│   📷        │
│ [QR in view]│ → Notification appears!
│             │
└─────────────┘

Step 3: TAP notification ⚡ (CRITICAL!)
┌─────────────┐
│ 🔗 Open...  │ ← TAP HERE!
└─────────────┘

Step 4: Browser opens
┌─────────────┐
│ Camera      │
│ Permission? │
│ [Allow]     │ ← Tap Allow
└─────────────┘

Step 5: Scanner Active!
┌─────────────┐
│   📷        │
│ [Scanning]  │ Scan barcodes
│ ✓ Ready     │
└─────────────┘
```

---

## 🚀 Quick Reference

**Phone → Desktop Connection:**

1. Desktop: Enable Phone Scanner
2. Phone: Open Camera app
3. Phone: Point at QR code
4. Phone: **TAP the notification that appears** ⚡
5. Phone: Tap "Allow" for camera permission
6. Start scanning barcodes!

**The most common mistake:** Not tapping the notification/link that appears after scanning the QR code.

---

## 🔍 Testing the Connection

**Test on Receive Transfer page:**
```
http://localhost:8000/shop/transfers/14/receive
```

**Test on Pack Transfer page:**
```
http://localhost:8000/warehouse/transfers/16/pack
```

Both pages should now show the QR code section!

---

## 💡 Pro Tips

1. **Keep desktop screen on:** Don't let screen saver activate while scanning
2. **Keep phone charged:** Continuous camera use drains battery
3. **Good lighting:** Scanner works best in well-lit areas
4. **Stable surface:** Rest desktop on stable surface (no shaking)
5. **One session at a time:** Don't open multiple scanner sessions
6. **Close when done:** Click "Disable Scanner" when finished

---

## ⚠️ Security Note

The scanner session code is **time-limited (2 hours)** and **user-specific**. If someone else scans your QR code, they can't access your session without your login credentials.

---

**Still having issues?** Check the troubleshooting section above or manually enter the 6-digit code instead of using QR.
