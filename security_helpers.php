<?php
/**
 * Security Helper Functions
 * Provides data anonymization and token generation
 */

// Secret key for token generation (change this to a random value)
define('TOKEN_SECRET', 'your_unique_random_secret_key_here');

/**
 * Generate an anonymous token for a database ID
 * This prevents exposing internal database structure
 * 
 * @param int $id The database ID to anonymize
 * @param string $type Type of ID (driver, weather, traffic, etc.)
 * @return string Anonymized token
 */
function generateToken($id, $type = 'general') {
    // Create a hash that includes the ID, type, and secret
    // This makes tokens unpredictable and prevents enumeration
    $data = $id . '|' . $type;
    $hash = hash_hmac('sha256', $data, TOKEN_SECRET);
    
    // Return first 16 characters of hash + base64 encoded ID
    // This gives us both security and ability to decode
    return substr($hash, 0, 16) . base64_encode($data);
}

/**
 * Decode an anonymous token back to database ID
 * Validates the token to prevent tampering
 * 
 * @param string $token The token to decode
 * @param string $type Expected type of ID
 * @return int|false Database ID or false if invalid
 */
function decodeToken($token, $type = 'general') {
    if (strlen($token) < 16) {
        return false;
    }
    
    // Extract hash and data portions
    $hashPart = substr($token, 0, 16);
    $dataPart = substr($token, 16);
    
    // Decode the data
    $decoded = base64_decode($dataPart, true);
    if ($decoded === false) {
        return false;
    }
    
    // Verify the token hasn't been tampered with
    $expectedHash = substr(hash_hmac('sha256', $decoded, TOKEN_SECRET), 0, 16);
    if (!hash_equals($hashPart, $expectedHash)) {
        return false;
    }
    
    // Extract ID and verify type matches
    list($id, $tokenType) = explode('|', $decoded);
    if ($tokenType !== $type) {
        return false;
    }
    
    return (int)$id;
}

/**
 * Alternative simpler approach using encryption
 * This is easier but requires OpenSSL
 */
function encryptId($id) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt(
        (string)$id, 
        'AES-256-CBC', 
        TOKEN_SECRET, 
        0, 
        $iv
    );
    // Combine IV and encrypted data, encode as URL-safe string
    return base64_encode($iv . $encrypted);
}

function decryptId($token) {
    $data = base64_decode($token);
    if ($data === false || strlen($data) < 16) {
        return false;
    }
    
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    
    $decrypted = openssl_decrypt(
        $encrypted, 
        'AES-256-CBC', 
        TOKEN_SECRET, 
        0, 
        $iv
    );
    
    return $decrypted !== false ? (int)$decrypted : false;
}
?>