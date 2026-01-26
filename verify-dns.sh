#!/bin/bash

# DNS Verification Script for commerce.dixwix.com
# Run this script to check if DNS is properly configured

echo "=========================================="
echo "DNS Verification: commerce.dixwix.com"
echo "=========================================="
echo ""

# Expected IP
EXPECTED_IP="82.180.132.134"

# Check 1: Authoritative Nameservers
echo "1. Checking Authoritative Nameservers:"
echo "   Querying ns1.dns-parking.com..."
RESULT1=$(dig +short @ns1.dns-parking.com commerce.dixwix.com A 2>/dev/null)
if [ -z "$RESULT1" ]; then
    echo "   ❌ NOT FOUND at ns1.dns-parking.com"
    echo "   ⚠️  DNS A record has NOT been added yet!"
else
    echo "   ✅ Found: $RESULT1"
    if [ "$RESULT1" = "$EXPECTED_IP" ]; then
        echo "   ✅ IP matches expected: $EXPECTED_IP"
    else
        echo "   ❌ IP mismatch! Expected: $EXPECTED_IP, Got: $RESULT1"
    fi
fi

echo ""
echo "   Querying ns2.dns-parking.com..."
RESULT2=$(dig +short @ns2.dns-parking.com commerce.dixwix.com A 2>/dev/null)
if [ -z "$RESULT2" ]; then
    echo "   ❌ NOT FOUND at ns2.dns-parking.com"
    echo "   ⚠️  DNS A record has NOT been added yet!"
else
    echo "   ✅ Found: $RESULT2"
    if [ "$RESULT2" = "$EXPECTED_IP" ]; then
        echo "   ✅ IP matches expected: $EXPECTED_IP"
    else
        echo "   ❌ IP mismatch! Expected: $EXPECTED_IP, Got: $RESULT2"
    fi
fi

echo ""
echo "2. Checking Google DNS (8.8.8.8):"
RESULT3=$(dig +short @8.8.8.8 commerce.dixwix.com A 2>/dev/null)
if [ -z "$RESULT3" ]; then
    echo "   ❌ NOT FOUND via Google DNS"
    echo "   ⚠️  DNS has not propagated yet, or record not added"
else
    echo "   ✅ Found: $RESULT3"
    if [ "$RESULT3" = "$EXPECTED_IP" ]; then
        echo "   ✅ IP matches expected: $EXPECTED_IP"
        echo "   ✅ DNS is working and propagated!"
    else
        echo "   ❌ IP mismatch! Expected: $EXPECTED_IP, Got: $RESULT3"
    fi
fi

echo ""
echo "3. Comparing with working subdomain (www.dixwix.com):"
WWW_RESULT=$(dig +short @8.8.8.8 www.dixwix.com A 2>/dev/null)
if [ -z "$WWW_RESULT" ]; then
    echo "   ❌ www.dixwix.com not found (unexpected!)"
else
    echo "   ✅ www.dixwix.com resolves to: $WWW_RESULT"
    if [ "$WWW_RESULT" = "$EXPECTED_IP" ]; then
        echo "   ✅ Same IP as expected for commerce subdomain"
    fi
fi

echo ""
echo "=========================================="
echo "Summary:"
echo "=========================================="
if [ "$RESULT1" = "$EXPECTED_IP" ] && [ "$RESULT2" = "$EXPECTED_IP" ] && [ "$RESULT3" = "$EXPECTED_IP" ]; then
    echo "✅ DNS is FULLY CONFIGURED and PROPAGATED!"
    echo "✅ commerce.dixwix.com should work now!"
    echo ""
    echo "Try accessing: https://commerce.dixwix.com/"
else
    echo "❌ DNS A record is MISSING or NOT PROPAGATED yet"
    echo ""
    echo "Action Required:"
    echo "1. Add DNS A record:"
    echo "   Type: A"
    echo "   Name: commerce"
    echo "   Value: $EXPECTED_IP"
    echo ""
    echo "2. Wait for propagation (15 min - 24 hours)"
    echo "3. Run this script again to verify"
    echo ""
    echo "See COMPLETE_DNS_FIX.md for detailed instructions"
fi
echo "=========================================="
