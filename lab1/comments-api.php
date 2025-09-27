<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'prueba');
define('DB_CHARSET', 'utf8');
define('DB_USER', 'prueba');
define('DB_PASSWORD', 'prueba');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET . ";dbname=" . DB_NAME,
        DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $ex->getMessage()]);
    exit;
}

// Get the request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        if ($action === 'get_comments') {
            getComments($pdo);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action for GET request']);
        }
        break;
        
    case 'POST':
        if ($action === 'add_comment') {
            addComment($pdo);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action for POST request']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * Get comments for a specific product or all comments
 */
function getComments($pdo) {
    try {
        $product_name = isset($_GET['product_name']) ? $_GET['product_name'] : null;
        
        if ($product_name) {
            // Get comments for a specific product
            $stmt = $pdo->prepare("
                SELECT comment_id, product_name, user_name, comment_text, 
                       DATE_FORMAT(comment_date, '%Y-%m-%d %H:%i') as formatted_date,
                       comment_date
                FROM product_comments 
                WHERE product_name = ? 
                ORDER BY comment_date DESC
            ");
            $stmt->execute([$product_name]);
        } else {
            // Get all comments
            $stmt = $pdo->prepare("
                SELECT comment_id, product_name, user_name, comment_text, 
                       DATE_FORMAT(comment_date, '%Y-%m-%d %H:%i') as formatted_date,
                       comment_date
                FROM product_comments 
                ORDER BY comment_date DESC
            ");
            $stmt->execute();
        }
        
        $comments = $stmt->fetchAll();
        echo json_encode([
            'success' => true,
            'comments' => $comments,
            'total' => count($comments)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch comments: ' . $e->getMessage()
        ]);
    }
}

/**
 * Add a new comment for a product
 */
function addComment($pdo) {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($input['product_name']) || !isset($input['user_name']) || !isset($input['comment_text'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required fields: product_name, user_name, comment_text'
            ]);
            return;
        }
        
        $product_name = trim($input['product_name']);
        $user_name = trim($input['user_name']);
        $comment_text = trim($input['comment_text']);
        $comment_date = isset($input['comment_date']) ? $input['comment_date'] : date('Y-m-d H:i:s');
        
        // Validate input lengths
        if (strlen($product_name) > 255) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Product name too long (max 255 characters)'
            ]);
            return;
        }
        
        if (strlen($user_name) > 100) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'User name too long (max 100 characters)'
            ]);
            return;
        }
        
        if (strlen($comment_text) < 5) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Comment text too short (minimum 5 characters)'
            ]);
            return;
        }
        
        // Insert the comment
        $stmt = $pdo->prepare("
            INSERT INTO product_comments (product_name, user_name, comment_text, comment_date) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$product_name, $user_name, $comment_text, $comment_date]);
        
        $comment_id = $pdo->lastInsertId();
        
        // Return the newly created comment
        $stmt = $pdo->prepare("
            SELECT comment_id, product_name, user_name, comment_text, 
                   DATE_FORMAT(comment_date, '%Y-%m-%d %H:%i') as formatted_date,
                   comment_date
            FROM product_comments 
            WHERE comment_id = ?
        ");
        $stmt->execute([$comment_id]);
        $newComment = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $newComment
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to add comment: ' . $e->getMessage()
        ]);
    }
}
?>