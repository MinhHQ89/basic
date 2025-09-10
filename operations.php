<?php
/**
 * CRUD Operations - Handle all CRUD operations
 * Using Database class to interact with database
 */

// Include Database class
require_once 'database.php';

// Initialize database connection
try {
    $db = new Database(); // Use default configuration from config.php, automatically connect to database
} catch (Exception $e) {
    sendResponse(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get action from request
$action = $_REQUEST['action'] ?? '';

// Process actions
switch ($action) {
    case 'list':
        getUsersList();
        break;
    case 'create':
        createUser();
        break;
    case 'update':
        updateUser();
        break;
    case 'delete':
        deleteUser();
        break;
    case 'get':
        getUserById();
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Get list of all users
 */
function getUsersList() {
    global $db;
    
    try {
        $sql = "SELECT * FROM users ORDER BY id DESC";
        $users = $db->select($sql);
        
        sendResponse(['success' => true, 'data' => $users]);
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => 'Failed to get users: ' . $e->getMessage()]);
    }
}

/**
 * Get user information by ID
 */
function getUserById() {
    global $db;
    
    $id = $_GET['id'] ?? 0;
    
    if (!$id || !is_numeric($id)) {
        sendResponse(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        $sql = "SELECT * FROM users WHERE id = ?";
        $user = $db->selectOne($sql, [$id]);
        
        if ($user) {
            sendResponse(['success' => true, 'data' => $user]);
        } else {
            sendResponse(['success' => false, 'message' => 'User not found']);
        }
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => 'Failed to get user: ' . $e->getMessage()]);
    }
}

/**
 * Create new user
 */
function createUser() {
    global $db;
    
    // Get data from POST
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($name) || empty($email)) {
        sendResponse(['success' => false, 'message' => 'Name and email are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    try {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $existingUser = $db->selectOne($sql, [$email]);
        
        if ($existingUser) {
            sendResponse(['success' => false, 'message' => 'Email already exists']);
            return;
        }
        
        // Insert new user
        $sql = "INSERT INTO users (name, email, phone) VALUES (?, ?, ?)";
        $result = $db->execute($sql, [$name, $email, $phone]);
        
        if ($result) {
            sendResponse(['success' => true, 'message' => 'User created successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to create user']);
        }
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()]);
    }
}

/**
 * Update existing user
 */
function updateUser() {
    global $db;
    
    // Get data from POST
    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (!$id || !is_numeric($id)) {
        sendResponse(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    if (empty($name) || empty($email)) {
        sendResponse(['success' => false, 'message' => 'Name and email are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    try {
        // Check if user exists
        $sql = "SELECT id FROM users WHERE id = ?";
        $existingUser = $db->selectOne($sql, [$id]);
        
        if (!$existingUser) {
            sendResponse(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Check if email already exists in another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $duplicateEmail = $db->selectOne($sql, [$email, $id]);
        
        if ($duplicateEmail) {
            sendResponse(['success' => false, 'message' => 'Email already exists in another user']);
            return;
        }
        
        // Update user
        $sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $result = $db->execute($sql, [$name, $email, $phone, $id]);
        
        if ($result) {
            sendResponse(['success' => true, 'message' => 'User updated successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to update user']);
        }
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()]);
    }
}

/**
 * Delete user
 */
function deleteUser() {
    global $db;
    
    // Get data from POST
    $id = $_POST['id'] ?? 0;
    
    // Validation
    if (!$id || !is_numeric($id)) {
        sendResponse(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        // Check if user exists
        $sql = "SELECT id FROM users WHERE id = ?";
        $existingUser = $db->selectOne($sql, [$id]);
        
        if (!$existingUser) {
            sendResponse(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Delete user
        $sql = "DELETE FROM users WHERE id = ?";
        $result = $db->execute($sql, [$id]);
        
        if ($result) {
            sendResponse(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to delete user']);
        }
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
    }
}

/**
 * Send JSON response
 * @param array $data Response data
 */
function sendResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    
    // Close database connection
    global $db;
    if ($db) {
        $db->close();
    }
    exit;
}
?>
