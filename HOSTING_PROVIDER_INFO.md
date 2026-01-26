# 🏢 Hosting Provider Information

## ✅ Hosting Provider Identified

**Hosting Provider:** **Hostinger** (via hstgr.cloud)

**Server Details:**
- **Hostname:** `srv584735.hstgr.cloud`
- **Server IP:** `82.180.132.134`
- **Control Panel:** **CloudPanel**
- **Operating System:** Ubuntu 22.04.5 LTS
- **Web Server:** Nginx
- **PHP Versions:** PHP 7.1, 7.2, 7.3, 7.4, 8.2
- **Virtualization:** KVM (QEMU)

---

## 🌐 Server Information

### Hostname Pattern
- Format: `srv[number].hstgr.cloud`
- Your server: `srv584735.hstgr.cloud`
- **hstgr** = **Hostinger** (hosting provider abbreviation)

### CloudPanel Access
- **CloudPanel Port:** 8443 (HTTPS)
- **Access URL:** `https://82.180.132.134:8443` or `https://srv584735.hstgr.cloud:8443`
- **Control Panel:** CloudPanel (open-source hosting control panel)

---

## 📋 Where to Manage DNS

Since you're using **Hostinger** (via hstgr.cloud), you have **two options** for managing DNS:

### Option 1: CloudPanel (Server-Side Management)
CloudPanel allows you to:
- Create websites
- Manage SSL certificates
- Configure Nginx
- **BUT:** CloudPanel does NOT manage DNS records
- DNS must be managed at your domain registrar or DNS provider

### Option 2: DNS Provider (Where dixwix.com is Registered)
Your domain `dixwix.com` currently uses:
- **Nameservers:** `ns1.dns-parking.com` and `ns2.dns-parking.com`

**This means:**
- DNS is managed by `dns-parking.com` (not directly by Hostinger)
- You need to add DNS records at `dns-parking.com` or where you manage DNS for `dixwix.com`

---

## 🔧 How to Add DNS Record for commerce.dixwix.com

Since your DNS nameservers are `dns-parking.com`, you have two approaches:

### Approach 1: Use dns-parking.com DNS Panel
1. Log in to your `dns-parking.com` account
2. Select domain: `dixwix.com`
3. Add DNS A record:
   - **Type:** A
   - **Name:** `commerce`
   - **Value:** `82.180.132.134`
   - **TTL:** 3600 (or default)

### Approach 2: Change Nameservers to Hostinger (Optional)
If you prefer to manage DNS through Hostinger:
1. Get Hostinger nameservers from your Hostinger account
2. Update nameservers for `dixwix.com` at your domain registrar
3. Add DNS records through Hostinger DNS panel

**Note:** Changing nameservers will require reconfiguring ALL DNS records (A, MX, TXT, etc.)

---

## 🎯 Current Setup Summary

| Component | Provider/Service | Details |
|-----------|------------------|---------|
| **Hosting** | Hostinger | Server: srv584735.hstgr.cloud |
| **Control Panel** | CloudPanel | Port: 8443 |
| **Web Server** | Nginx | Running on ports 80, 443, 8080 |
| **DNS Nameservers** | dns-parking.com | ns1.dns-parking.com, ns2.dns-parking.com |
| **DNS Management** | dns-parking.com | Where you add DNS records |

---

## ✅ To Fix commerce.dixwix.com

**Since DNS is managed by `dns-parking.com`:**

1. **Access DNS Management:**
   - Log in to `dns-parking.com` (or wherever dixwix.com DNS is managed)
   - Or log in to your domain registrar and go to DNS settings

2. **Add DNS A Record:**
   ```
   Type: A
   Name: commerce
   Value: 82.180.132.134
   TTL: 3600
   ```

3. **Wait for Propagation:**
   - 15 minutes to 24 hours (usually 1-4 hours)

4. **Verify:**
   ```bash
   cd /home/dixwix-commerce/htdocs/bagisto-master
   ./verify-dns.sh
   ```

---

## 🌐 Hostinger Control Panel Access

If you want to access CloudPanel (server control panel):

**CloudPanel URL:** `https://82.180.132.134:8443` or `https://srv584735.hstgr.cloud:8443`

**Note:** 
- CloudPanel manages server-side configurations
- CloudPanel does NOT manage DNS (DNS is managed separately)
- DNS must be added at your DNS provider (dns-parking.com or domain registrar)

---

## 📞 Support

If you need help:
- **Hostinger Support:** Contact Hostinger support for server/hosting issues
- **DNS Issues:** Contact your DNS provider (dns-parking.com) or domain registrar
- **Server Configuration:** Already done via CloudPanel ✅

---

**Summary:** You're using **Hostinger** for hosting with **CloudPanel** control panel, but DNS is managed by **dns-parking.com**. You need to add the DNS A record for `commerce.dixwix.com` at your DNS provider, not in Hostinger/CloudPanel.
