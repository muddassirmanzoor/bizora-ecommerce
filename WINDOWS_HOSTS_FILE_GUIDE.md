# Windows Hosts File Setup Guide

## Current Issue
```
Ping request could not find host commerce.dixwix.com
```
**This is normal** - DNS is not configured yet. We'll use hosts file to test locally.

## 🔧 Step-by-Step: Add to Hosts File (Windows)

### Method 1: Using Notepad (Easiest)

1. **Press Windows Key + X**
   - Or Right-click on Start Menu

2. **Click:** "Windows PowerShell (Admin)" or "Command Prompt (Admin)"
   - Click "Yes" on UAC prompt

3. **In PowerShell/CMD, type:**
   ```powershell
   notepad C:\Windows\System32\drivers\etc\hosts
   ```
   - Press Enter

4. **Notepad will open** (might be empty or have some comments starting with #)

5. **Scroll to the bottom** (past any comments)

6. **Add this line at the very end:**
   ```
   82.180.132.134 commerce.dixwix.com
   ```
   - Make sure it's on its own line
   - No # (hash) before it
   - Exactly: `82.180.132.134 commerce.dixwix.com`

7. **Save the file:**
   - Press `Ctrl + S`
   - Or File → Save

8. **Close Notepad**

9. **Flush DNS cache** (in same PowerShell window):
   ```powershell
   ipconfig /flushdns
   ```

10. **Test with ping:**
    ```powershell
    ping commerce.dixwix.com
    ```
    Should now show: `82.180.132.134`

### Method 2: Using File Explorer (Visual)

1. **Open File Explorer**
   - Press Windows Key + E

2. **Navigate to:**
   ```
   C:\Windows\System32\drivers\etc\
   ```

3. **Find file named:** `hosts` (no extension)

4. **Right-click on `hosts`** → **Properties**

5. **Uncheck:** "Read-only" (if checked)
   - Click OK

6. **Right-click on `hosts`** → **Open with** → **Notepad**
   - Or choose "Select another app" → **Notepad**

7. **Add this line at the end:**
   ```
   82.180.132.134 commerce.dixwix.com
   ```

8. **Save:** `Ctrl + S`

9. **Close Notepad**

10. **Flush DNS:**
    ```powershell
    ipconfig /flushdns
    ```

## ✅ Verify It Worked

After adding hosts entry, test:

```powershell
ping commerce.dixwix.com
```

**Expected output:**
```
Pinging commerce.dixwix.com [82.180.132.134] with 32 bytes of data:
Reply from 82.180.132.134: bytes=32 time=XXms TTL=XX
```

If you see `82.180.132.134`, it's working! ✅

## 🌐 Access the Site

Now open your browser and go to:

```
http://commerce.dixwix.com/installer
```

**Important:** 
- Use the **domain name**, NOT the IP address
- If HTTPS doesn't work, use **HTTP** (http://)

## 🔍 Troubleshooting

### "Access Denied" when editing hosts?

- Make sure you opened Notepad **as Administrator**
- Method 1 (PowerShell Admin) is recommended

### "The file is in use by another program"?

- Close all browser windows
- Close any text editors
- Try again

### Ping still doesn't work?

1. **Verify the hosts file:**
   - Make sure the entry is exactly: `82.180.132.134 commerce.dixwix.com`
   - No extra spaces
   - On its own line
   - No # (hash) before it

2. **Flush DNS again:**
   ```powershell
   ipconfig /flushdns
   ```

3. **Restart your browser** completely

4. **Try in different browser** (Chrome, Firefox, Edge)

5. **Check hosts file location:**
   ```powershell
   type C:\Windows\System32\drivers\etc\hosts
   ```
   You should see your entry at the end

### Still can't access the site?

- Make sure you're using: `http://commerce.dixwix.com/installer`
- **NOT:** `http://82.180.132.134/installer`
- Clear browser cache: `Ctrl + Shift + Delete`
- Try incognito/private mode: `Ctrl + Shift + N`

## 📝 What the Hosts File Does

The hosts file tells your computer:
- "When someone asks for commerce.dixwix.com, send them to 82.180.132.134"

This only works on **YOUR computer**. Others need DNS configured.

## ✅ After DNS is Configured

Once you add the DNS A record and it propagates:
1. You can **remove** the hosts file entry (optional)
2. The domain will work for **everyone**
3. No hosts file needed anymore

---

**Quick Summary:**
1. Open hosts file as Admin
2. Add: `82.180.132.134 commerce.dixwix.com`
3. Save
4. Run: `ipconfig /flushdns`
5. Test: `ping commerce.dixwix.com`
6. Access: `http://commerce.dixwix.com/installer`
