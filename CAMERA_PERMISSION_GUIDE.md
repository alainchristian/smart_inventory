# 📷 Camera Permission Guide - Phone Scanner

## 🔴 CRITICAL REQUIREMENT: HTTPS IS REQUIRED!

**STOP!** Before following this guide, you **MUST** set up HTTPS first!

### ⚠️ Camera Blocked Over HTTP

Modern browsers **block camera access over HTTP** for security reasons.

**Your current setup:**
- ❌ `http://192.168.1.66:8000` → Camera will NOT work
- ✅ HTTPS required → Camera will work

**If you see this error:**
- "getUserMedia is not defined"
- "NotAllowedError: Permission denied"
- "Camera not supported"

**→ You need HTTPS! Follow the guide below.**

---

## 🚀 FIRST: Enable HTTPS

**See:** `ENABLE_HTTPS.md` for complete instructions

**Quickest solution (5 minutes):**

1. **Install ngrok:** https://ngrok.com/download
2. **Run:** `start-with-ngrok.bat`
3. **Copy HTTPS URL** (e.g., `https://abc123.ngrok-free.app`)
4. **Run:** `update-env-ngrok.bat https://abc123.ngrok-free.app`
5. **Refresh browser** - QR code will now show HTTPS URL
6. **Scan QR code** from phone
7. **Continue with camera permission steps below**

**Without HTTPS, camera will NOT work, no matter what browser or permissions you use!**

---

## ✅ Network Connection Working!

Once you've set up HTTPS, your phone can reach the server.

**Next step:** Enable camera access for barcode scanning.

---

## 🎯 Quick Instructions by Device

### 📱 iPhone/iPad (iOS)

#### ⚠️ CRITICAL: Must Use Safari!

**iOS only allows camera access in Safari browser, not Chrome or other browsers.**

#### Steps:

1. **If QR code opened Chrome:**
   - Copy the URL
   - Open **Safari** app
   - Paste URL and go

2. **When scanner page loads:**
   - Safari will prompt: **"Allow camera access?"**
   - Tap **"Allow"**
   - Camera will start automatically

3. **If you denied or no prompt appeared:**
   - Tap **"aA"** icon in address bar (left side)
   - Tap **"Website Settings"**
   - Find **"Camera"**
   - Change to **"Allow"**
   - Refresh page (pull down)

4. **Still not working?**
   - Go to iPhone **Settings** app
   - Scroll to **Safari**
   - Tap **Camera**
   - Select **"Ask"** or **"Allow"**
   - Return to Safari and refresh

---

### 📱 Android Phones

#### ✅ Use Chrome (Recommended)

**Best results with Chrome or Firefox.**

#### Steps:

1. **Open scanner page in Chrome**
   - Browser will prompt: **"Allow camera?"**
   - Tap **"Allow"**
   - Camera starts automatically

2. **If you denied or no prompt:**
   - Tap **lock icon** in address bar
   - Tap **"Permissions"** or **"Site settings"**
   - Find **"Camera"**
   - Change to **"Allow"**
   - Refresh page

3. **Alternative - Chrome Settings:**
   - Open Chrome menu (⋮)
   - **Settings** → **Site Settings** → **Camera**
   - Find `192.168.1.66`
   - Change to **"Allow"**
   - Go back and refresh

4. **If still blocked:**
   - Phone **Settings** → **Apps** → **Chrome**
   - **Permissions** → **Camera**
   - Select **"Allow"**
   - Return to Chrome and refresh

---

## 🧪 Test Camera Button

A blue **"Test Camera"** button appears in top-right corner of scanner page.

**Tap it to check if camera works:**

- ✅ **"Camera works!"** → Browser supports camera, permission granted
- ❌ **Error message** → Shows what's wrong (permission, browser, etc.)

**Use this first to diagnose issues before trying to scan!**

---

## 🔍 Common Errors & Fixes

### Error: "getUserMedia not supported"

**Cause:** Wrong browser or browser too old

**iOS Fix:**
- **Switch to Safari** (only browser that works on iOS)
- Copy URL → Open Safari → Paste and go

**Android Fix:**
- **Use Chrome** or Firefox
- Update browser to latest version

---

### Error: "Permission denied" or "NotAllowedError"

**Cause:** Camera permission not granted

**Fix:**
1. Look for camera icon in address bar
2. Tap it → Change to "Allow"
3. Refresh page

**Or check browser settings as shown above**

---

### Error: "Camera not found" or "NotFoundError"

**Cause:** No camera detected or camera covered

**Fix:**
- Remove any case/cover blocking camera
- Make sure camera works in other apps
- Try taking a photo with Camera app
- Restart phone if needed

---

### Error: "Camera is busy" or "NotReadableError"

**Cause:** Another app is using the camera

**Fix:**
1. Close all apps
2. Close all browser tabs
3. Reopen scanner page
4. Try again

---

### Black screen (no video)

**Possible causes:**
- Permission not fully granted
- Camera blocked by case
- Browser cache issue

**Fix:**
1. Refresh page
2. Check camera works in Camera app
3. Clear browser cache
4. Try different browser
5. Restart browser/phone

---

## 📋 Complete Checklist

Before scanning, verify:

- [ ] Server running on PC: `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Phone on same WiFi as PC
- [ ] Using correct browser:
  - **iOS:** Safari only
  - **Android:** Chrome recommended
- [ ] Scanner page loads: `http://192.168.1.66:8000/scanner?code=XXXXXX`
- [ ] Tapped "Test Camera" button → Shows "✓ Camera works!"
- [ ] Connected with session code
- [ ] Camera view appears (live video)
- [ ] Status shows "📷 Camera Active"

If all checked ✅ = Ready to scan barcodes!

---

## 🎥 What Should Happen

**Successful camera start:**

1. ✅ Scanner page loads
2. ✅ Enter 6-digit code (or auto-filled from QR)
3. ✅ Tap "Connect"
4. ✅ Status shows "Connected to Session"
5. ✅ Status shows "🔄 Initializing Camera..."
6. ✅ Browser prompts for camera permission
7. ✅ Tap "Allow"
8. ✅ Camera view appears (live video)
9. ✅ Status changes to "📷 Camera Active"
10. ✅ Green scan line moves across screen
11. ✅ Ready to scan!

---

## 🚀 Scanning Barcodes

Once camera is active:

1. **Point phone at product barcode**
   - Keep 4-8 inches away
   - Hold steady for 1-2 seconds
   - Make sure barcode is well-lit

2. **Green flash = Success!**
   - Phone vibrates (if supported)
   - Scan count increases
   - Barcode appears in "Last" field
   - Scan sent to PC

3. **Continue scanning**
   - Move to next product
   - Scanner prevents duplicates (2 second delay)
   - All scans appear on PC in real-time

4. **When done:**
   - Tap "⛔ Disconnect"
   - Or just close browser tab

---

## 💡 Troubleshooting Tips

### Browser Console (for advanced users)

**See detailed error messages:**

**iOS Safari:**
- Tap address bar → Scroll down → "Request Desktop Website"
- Enable Developer mode in Safari settings

**Android Chrome:**
- Menu → More tools → Developer tools
- Check Console tab for errors

**Look for:**
- ❌ "getUserMedia is not defined" → Wrong browser
- ❌ "NotAllowedError" → Permission denied
- ❌ "NotFoundError" → No camera
- ❌ "NotSupportedError" → Browser too old

---

### Still Not Working?

**Try in this order:**

1. **Test Camera button** - What error appears?
2. **Correct browser?** - Safari (iOS) or Chrome (Android)
3. **Permission granted?** - Check browser settings
4. **Camera works elsewhere?** - Test with Camera app
5. **Browser up to date?** - Update to latest version
6. **Clear cache** - Settings → Clear browsing data
7. **Restart browser** - Force close and reopen
8. **Restart phone** - Last resort

---

## 📞 Support Info

**When asking for help, provide:**

1. **Device:** iPhone 12 / Samsung Galaxy S21 / etc.
2. **Browser:** Safari / Chrome / Firefox (and version)
3. **Error message:** Exact text from "Test Camera" button
4. **What happens:**
   - Scanner page loads? ✅/❌
   - Permission prompt appears? ✅/❌
   - Camera view shows? ✅/❌
   - Error message? (what does it say?)

---

## ✨ Success Indicators

**You'll know it's working when:**

1. ✅ Scanner page loads on phone
2. ✅ Status shows "Connected to Session"
3. ✅ Camera view appears (live video from your camera)
4. ✅ Status shows "📷 Camera Active" in green
5. ✅ Can see yourself/environment in camera view
6. ✅ Point at barcode → Green flash + vibrate
7. ✅ Scan count increases
8. ✅ Scan appears on PC screen

**All working = Ready to scan! 📦**

---

## 🎉 You're Almost There!

The network connection is working perfectly. Just need to:

1. Use correct browser (Safari on iOS)
2. Grant camera permission
3. Start scanning!

**Happy scanning! 🚀**
