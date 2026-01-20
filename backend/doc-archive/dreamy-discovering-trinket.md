# Timezone & App Name Configuration Plan

## Overview
Update timezone to Asia/Kuala_Lumpur for Malaysia location and simplify app name from "Mobius Smart Bin" to "Mobius" across the entire project.

---

## Phase 1: Update Timezone Configuration

### Backend Timezone Changes

**Goal:** Change timezone from UTC to Asia/Kuala_Lumpur and make it configurable via environment variable.

**Files to Modify:**

1. **backend/config/app.php** (line 68)
   - **Current:** `'timezone' => 'UTC',`
   - **New:** `'timezone' => env('APP_TIMEZONE', 'UTC'),`
   - **Reason:** Make timezone configurable via .env file

2. **backend/.env** (add after APP_FAKER_LOCALE)
   - **Add:** `APP_TIMEZONE=Asia/Kuala_Lumpur`
   - **Reason:** Set default timezone for development

3. **backend/.env.example** (add after APP_FAKER_LOCALE)
   - **Add:** `APP_TIMEZONE=Asia/Kuala_Lumpur`
   - **Reason:** Document timezone setting for new installations

**Impact:**
- All Laravel timestamp operations will use Asia/Kuala_Lumpur timezone
- Carbon date functions will use the correct timezone
- Database timestamps will be stored/retrieved with correct timezone
- Affects: created_at, updated_at, and any datetime fields

---

## Phase 2: Simplify App Name to "Mobius"

### Backend App Name Changes

**Goal:** Change from "Mobius Smart Bin" to "Mobius" for cleaner branding.

**Files to Modify:**

1. **backend/.env** (line 1)
   - **Current:** `APP_NAME="Mobius Smart Bin"`
   - **New:** `APP_NAME=Mobius`
   - **Note:** No quotes needed for single word

2. **backend/.env.example** (line 1)
   - **Current:** `APP_NAME="Mobius Smart Bin"`
   - **New:** `APP_NAME=Mobius`

**Automatic Propagation:**
These will automatically update due to environment variable usage:
- Email "from" name (`MAIL_FROM_NAME="${APP_NAME}"`)
- Vite bundle name (`VITE_APP_NAME="${APP_NAME}"`)
- Session cookie name (will become "mobius-session")
- Any UI displaying the app name

### Mobile App Name Changes

**Goal:** Update mobile app display names to match new "Mobius" branding.

**Files to Modify:**

1. **mobile/android/app/src/main/AndroidManifest.xml** (line 3)
   - **Current:** `android:label="Mobius Smart Bin"`
   - **New:** `android:label="Mobius"`

2. **mobile/ios/Runner/Info.plist**
   - **CFBundleDisplayName** (line 8)
     - **Current:** `<string>Mobius Smart Bin</string>`
     - **New:** `<string>Mobius</string>`
   - **CFBundleName** (line 16)
     - **Current:** `<string>Mobius Smart Bin</string>`
     - **New:** `<string>Mobius</string>`

3. **mobile/lib/main.dart** (lines 13 and 31)
   - **Line 13 - MaterialApp title:**
     - **Current:** `title: 'Mobius Smart Bin',`
     - **New:** `title: 'Mobius',`
   - **Line 31 - AppBar title:**
     - **Current:** `title: const Text('Mobius Smart Bin'),`
     - **New:** `title: const Text('Mobius'),`

**Impact:**
- App launcher icon label will show "Mobius" on Android
- Home screen app name will show "Mobius" on iOS
- App title bar will display "Mobius"

### Documentation Updates

**Files to Modify:**

1. **README.md** (line 1)
   - **Current:** `# Mobius Smart Recycling Bin Ecosystem`
   - **New:** `# Mobius Smart Recycling Bin Ecosystem`
   - **Note:** Keep full descriptive name in main README for clarity

2. **backend/README.md** (line 1)
   - **Current:** `# Mobius Smart Bin - Backend`
   - **New:** `# Mobius - Backend`

3. **backend/README.md** (line 50)
   - **Current:** Documents `APP_NAME` as "Mobius Smart Bin"
   - **New:** Document as "Mobius"

4. **mobile/README.md** (line 1)
   - **Current:** `# Mobius Smart Bin - Mobile`
   - **New:** `# Mobius - Mobile`

5. **mobile/README.md** (line 3)
   - **Current:** Mentions "Mobius Smart Recycling Bin mobile application"
   - **Decision:** Keep full name in description for clarity, but reference app as "Mobius"

6. **ARCHITECTURE.md**
   - Search for "Mobius Smart Bin" occurrences
   - Update to "Mobius" where it refers to the app name
   - Keep "Mobius Smart Recycling Bin" where it refers to the project/system

---

## Phase 3: Optional Locale Updates

**Current State:**
- All locales are set to English (en, en_US)
- Faker locale is en_US (US English)

**Recommendations:**

1. **Keep English as default** (Recommended)
   - Most developers are comfortable with English
   - Laravel ecosystem is primarily English
   - No changes needed

2. **Add Malaysia locale support** (Future enhancement)
   - Would require: `APP_LOCALE=ms` (Malay)
   - Would require: Creating `resources/lang/ms/` translation files
   - Out of scope for this task

**Decision:** Keep current English locale settings unchanged.

---

## Verification Steps

### 1. Backend Timezone Verification
```bash
# After changes, run:
php artisan tinker

# Then execute:
echo now();
echo now()->timezone;

# Expected output:
# Current time in Asia/Kuala_Lumpur timezone (UTC+8)
# Asia/Kuala_Lumpur
```

### 2. Backend App Name Verification
```bash
# Check .env has correct APP_NAME
php artisan about

# Should show:
# Application Name: Mobius

# Check session cookie name in browser:
# Should be: mobius_session
```

### 3. Mobile App Name Verification
```bash
# Android: Build and check launcher
flutter build apk --debug
# Install and check app name shows "Mobius" in launcher

# iOS: Check Info.plist
# CFBundleDisplayName should show "Mobius"
```

### 4. Documentation Verification
- All README files consistently use "Mobius" for app references
- Main README keeps full "Mobius Smart Recycling Bin Ecosystem" for project description

---

## Critical Files Summary

### Backend Files to Modify:
- `backend/config/app.php` (timezone configuration)
- `backend/.env` (APP_NAME and APP_TIMEZONE)
- `backend/.env.example` (APP_NAME and APP_TIMEZONE)
- `backend/README.md` (documentation updates)

### Mobile Files to Modify:
- `mobile/android/app/src/main/AndroidManifest.xml` (android:label)
- `mobile/ios/Runner/Info.plist` (CFBundleDisplayName and CFBundleName)
- `mobile/lib/main.dart` (app title and appbar title)
- `mobile/README.md` (documentation updates)

### Documentation Files to Modify:
- `README.md` (keep full project name, reference app as "Mobius")
- `backend/README.md` (update app name references)
- `mobile/README.md` (update app name references)
- `ARCHITECTURE.md` (update app name references where appropriate)

### No Changes Needed:
- Locale settings (keeping English)
- Package names (already set correctly)
- Config files other than app.php

---

## Notes

### Why These Changes Matter

**Timezone:**
- Ensures all timestamps in logs, database, and UI show correct Malaysia time
- Prevents confusion when debugging or analyzing time-based data
- Critical for scheduled tasks and time-sensitive operations

**App Name:**
- Cleaner, more professional branding
- Shorter name is easier to remember and type
- Reduces UI clutter in app bars and titles
- Still descriptive enough ("Mobius" is unique and memorable)

### Backward Compatibility
- Timezone change may affect existing timestamp displays
- App name change is cosmetic and won't break functionality
- Session cookies will be recreated with new name (users will need to re-login)
