<?php
/**
 * Authentication Session Management API
 * Handles login, session validation, and logout with 12-hour persistence
 */

require_once __DIR__ . '/../config/cors.php';

header('Content-Type: application/json');

// Start session with custom settings for 12-hour persistence
ini_set('session.gc_maxlifetime', 43200); // 12 hours in seconds
ini_set('session.cookie_lifetime', 43200); // 12 hours in seconds
session_set_cookie_params(43200); // 12 hours
session_start();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handleLogin();
            break;
            
        case 'GET':
            handleSessionCheck();
            break;
            
        case 'DELETE':
            handleLogout();
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleLogin() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Password is required']);
        return;
    }
    
    $password = $input['password'];
    $correctPassword = 'admin123';
    
    if ($password === $correctPassword) {
        // Set session variables
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['expires_at'] = time() + 43200; // 12 hours from now
        $_SESSION['user_id'] = 'admin';
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'session_id' => session_id(),
            'expires_at' => $_SESSION['expires_at'],
            'expires_in_hours' => 12
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid password'
        ]);
    }
}

function handleSessionCheck() {
    if (isAuthenticated()) {
        $remainingTime = $_SESSION['expires_at'] - time();
        $remainingHours = round($remainingTime / 3600, 1);
        
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'session_id' => session_id(),
            'login_time' => $_SESSION['login_time'],
            'expires_at' => $_SESSION['expires_at'],
            'remaining_time_seconds' => $remainingTime,
            'remaining_hours' => $remainingHours
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'authenticated' => false,
            'message' => 'Not authenticated or session expired'
        ]);
    }
}

function handleLogout() {
    // Clear session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

function isAuthenticated() {
    // Check if session variables exist
    if (!isset($_SESSION['authenticated']) || !isset($_SESSION['expires_at'])) {
        return false;
    }
    
    // Check if session is still valid (not expired)
    if (time() > $_SESSION['expires_at']) {
        // Session expired, clear it
        $_SESSION = array();
        return false;
    }
    
    // Check if user is authenticated
    return $_SESSION['authenticated'] === true;
}
