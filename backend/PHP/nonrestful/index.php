<?php

header('Access-Control-Allow-Origin: http://localhost:5173'); // Your Vue dev server
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT, PATCH');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once './config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
switch ($action) {
    case 'getUsers':
        getUsers();
        break;
    case 'getUserById':
        getUserById();
        break;
    case 'createUser':
        createUser();
        break;
    case 'updateUser':
        updateUser();
        break;
    case 'patchUser':
        patchUser();
        break;
    case 'deleteUser':
        deleteUser();
        break;
    case 'getProducts':
        getProducts();
        break;
    case 'getProductById':
        getProductById();
        break;
    case 'createProduct':
        createProduct();
        break;
    case 'updateProduct':
        updateProduct();
        break;
    case 'patchProduct':
        patchProduct();
        break;
    case 'deleteProduct':
        deleteProduct();
        break;
    case 'getOrders':
        getOrders();
        break;
    case 'getOrderById':
        getOrderById();
        break;
    case 'createOrder':
        createOrder();
        break;
    case 'updateOrder':
        updateOrder();
        break;
    case 'deleteOrder':
        deleteOrder();
        break;
    default:
        echo json_encode(["error" => "Invalid action"]);
        break;
}

function getUsers(){
    try {
        $db = new db();
        $conn = $db->connect();
        $sql = "SELECT * FROM users";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conn = null; // Close the connection
        echo json_encode($result);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function getUserById(){
    try {
        $db = new db();
        $conn = $db->connect();
        $id = $_GET['id'];
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
        $conn = null; // Close the connection
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

function createUser()
{
    try {
        $db = new db();
        $conn = $db->connect();
        // Get JSON data from the request body
        $json_data = file_get_contents('php://input');
        // Decode JSON data into associative array
        $data = json_decode($json_data, true);
        // Check if required fields are present
        if (!isset($data['name']) || !isset($data['email'])) {
            throw new Exception("Name and email are required.");
        }
        $name = $data['name'];
        $email = $data['email'];
        $role = isset($data['role']) ? $data['role'] : 'user';  // Default role if not provided
        $sql = "INSERT INTO users (name, email, role) VALUES (:name, :email, :role)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
        echo json_encode(["message" => "User created successfully"]);
        $conn = null; // Close the connection
    } catch (Exception $e) {
        echo json_encode(["error" => "Error creating user: " . $e->getMessage()]);
    }
}

function updateUser() {
    try {
        $db = new db();
        $conn = $db->connect();
        // Get JSON data from the request body
        $json_data = file_get_contents('php://input');
        // Decode JSON data into associative array
        $data = json_decode($json_data, true) ?: [];
        
        // Get ID from URL or body
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } else if (isset($data['id'])) {
            $id = $data['id'];
        } else {
            throw new Exception("User ID is required.");
        }
        
        // Check if required fields are present
        if (!isset($data['name']) || !isset($data['email'])) {
            throw new Exception("Name and email are required in request body.");
        }
        
        // First verify if user exists
        $checkSql = "SELECT id FROM users WHERE id = :id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindValue(':id', $id);
        $checkStmt->execute();
        
        if (!$checkStmt->fetch()) {
            echo json_encode(["error" => "User not found with ID: " . $id]);
            return;
        }
        
        $name = $data['name'];
        $email = $data['email'];
        
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            echo json_encode(["error" => "User not found or no changes made"]);
        }
        $conn = null; // Close the connection
    } catch (Exception $e) {
        echo json_encode(["error" => "Error updating user: " . $e->getMessage()]);
    }
}

function patchUser() {
    try {
        $db = new db();
        $conn = $db->connect();
        // Get JSON data from the request body
        $json_data = file_get_contents('php://input');
        // Decode JSON data into associative array
        $data = json_decode($json_data, true);
        
        // Get ID from URL or body
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } else if (isset($data['id'])) {
            $id = $data['id'];
        } else {
            throw new Exception("User ID is required.");
        }
        $updateFields = [];
        $params = [];
        
        // Only update fields that are provided
        if (isset($data['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $updateFields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['role'])) {
            $updateFields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        
        if (empty($updateFields)) {
            throw new Exception("No fields to update. Supported fields are: name, email, role");
        }
        
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $params[':id'] = $id;
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "User patched successfully"]);
        } else {
            echo json_encode(["error" => "User not found or no changes made"]);
        }
        $conn = null; // Close the connection
    } catch (Exception $e) {
        echo json_encode(["error" => "Error patching user: " . $e->getMessage()]);
    }
}

function deleteUser() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Get ID from query parameters
        if (!isset($_GET['id'])) {
            throw new Exception("User ID is required.");
        }
        
        $id = $_GET['id'];
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
        $conn = null; // Close the connection
    } catch (Exception $e) {
        echo json_encode(["error" => "Error deleting user: " . $e->getMessage()]);
    }
}

function getProducts(){
    try {
        $db = new db();
        $conn = $db->connect();
        $sql = "SELECT * FROM products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conn = null;
        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

function getProductById(){
    try {
        $db = new db();
        $conn = $db->connect();
        $id = $_GET['id'];
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            echo json_encode($product);
        } else {
            echo json_encode(["error" => "Product not found"]);
        }
        $conn = null;
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

function createProduct() {
    try {
        $db = new db();
        $conn = $db->connect();
        // Get JSON data from the request body
        $json_data = file_get_contents('php://input');
        // Decode JSON data into associative array
        $data = json_decode($json_data, true);
        
        // Check if required fields are present
        if (!isset($data['name']) || !isset($data['price'])) {
            throw new Exception("Name and price are required.");
        }
        
        $name = $data['name'];
        $price = $data['price'];
        $description = $data['description'] ?? null;
        
        $sql = "INSERT INTO products (name, price, description) VALUES (:name, :price, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':description', $description);
        $stmt->execute();
        
        echo json_encode(["message" => "Product created successfully"]);
        $conn = null;
    } catch (Exception $e) {
        echo json_encode(["error" => "Error creating product: " . $e->getMessage()]);
    }
}

function updateProduct() {
    try {
        $db = new db();
        $conn = $db->connect();
        $id = $_GET['id'] ?? null;
        $name = $_GET['name'] ?? null;
        $price = $_GET['price'] ?? null;
        $description = $_GET['description'] ?? null;
        
        if (!$id || !$name || !$price) {
            throw new Exception("ID, name, and price are required.");
        }
        
        $sql = "UPDATE products SET name = :name, price = :price, description = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':description', $description);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Product updated successfully"]);
        } else {
            echo json_encode(["error" => "Product not found or no changes made"]);
        }
        $conn = null;
    } catch (Exception $e) {
        echo json_encode(["error" => "Error updating product: " . $e->getMessage()]);
    }
}

function patchProduct() {
    try {
        $db = new db();
        $conn = $db->connect();
        // Get JSON data from the request body
        $json_data = file_get_contents('php://input');
        // Decode JSON data into associative array
        $data = json_decode($json_data, true);
        
        // Get ID from URL or body
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } else if (isset($data['id'])) {
            $id = $data['id'];
        } else {
            throw new Exception("Product ID is required.");
        }
        
        $updateFields = [];
        $params = [];
        
        // Only update fields that are provided
        if (isset($data['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['price'])) {
            $updateFields[] = "price = :price";
            $params[':price'] = $data['price'];
        }
        if (isset($data['description'])) {
            $updateFields[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        
        if (empty($updateFields)) {
            throw new Exception("No fields to update. Supported fields are: name, price, description");
        }
        
        $sql = "UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $params[':id'] = $id;
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Product patched successfully"]);
        } else {
            echo json_encode(["error" => "Product not found or no changes made"]);
        }
        $conn = null;
    } catch (Exception $e) {
        echo json_encode(["error" => "Error patching product: " . $e->getMessage()]);
    }
}

function deleteProduct() {
    try {
        $db = new db();
        $conn = $db->connect();
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception("Product ID is required.");
        }
        
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Product deleted successfully"]);
        } else {
            echo json_encode(["error" => "Product not found"]);
        }
        $conn = null;
    } catch (Exception $e) {
        echo json_encode(["error" => "Error deleting product: " . $e->getMessage()]);
    }
}

function getOrders() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        $sql = "SELECT o.*, 
                u.name as customer_name,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'product_id', op.product_id,
                        'product_name', p.name,
                        'quantity', op.quantity,
                        'price', p.price
                    )
                ) as order_items
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN order_products op ON o.id = op.order_id
                LEFT JOIN products p ON op.product_id = p.id
                GROUP BY o.id";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we're always returning an array
        $orders = $orders ?: [];
        
        // Parse the order_items JSON string for each order
        foreach ($orders as &$order) {
            if ($order['order_items']) {
                $items = explode(',', $order['order_items']);
                $order['order_items'] = array_map(function($item) {
                    return json_decode($item, true);
                }, $items);
            } else {
                $order['order_items'] = [];
            }
        }
        
        // Important: Ensure we're sending a clean JSON array
        header('Content-Type: application/json');
        echo json_encode(array_values($orders));
        
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

function getOrderById() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        if (!isset($_GET['id'])) {
            throw new Exception("Order ID is required.");
        }
        
        $id = $_GET['id'];
        $sql = "SELECT o.*, 
                u.name as customer_name,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'product_id', op.product_id,
                        'product_name', p.name,
                        'quantity', op.quantity,
                        'price', p.price
                    )
                ) as order_items
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN order_products op ON o.id = op.order_id
                LEFT JOIN products p ON op.product_id = p.id
                WHERE o.id = :id
                GROUP BY o.id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            if ($order['order_items']) {
                $order['order_items'] = array_map(function($item) {
                    return json_decode($item, true);
                }, explode(',', $order['order_items']));
            } else {
                $order['order_items'] = [];
            }
            echo json_encode($order);
        } else {
            echo json_encode(["error" => "Order not found"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => "Error fetching order: " . $e->getMessage()]);
    }
}

function createOrder() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Start transaction
        $conn->beginTransaction();
        
        // Get order data from query parameters
        $userId = $_GET['userId'] ?? null;
        $items = isset($_GET['items']) ? json_decode($_GET['items'], true) : null;
        $status = $_GET['status'] ?? 'pending';
        
        if (!$userId || !$items) {
            throw new Exception("User ID and items are required.");
        }
        
        // Create order
        $sql = "INSERT INTO orders (user_id, status, created_at) VALUES (:user_id, :status, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        
        $orderId = $conn->lastInsertId();
        
        // Add order items
        $itemSql = "INSERT INTO order_products (order_id, product_id, quantity) VALUES (:order_id, :product_id, :quantity)";
        $itemStmt = $conn->prepare($itemSql);
        
        foreach ($items as $item) {
            $itemStmt->bindValue(':order_id', $orderId);
            $itemStmt->bindValue(':product_id', $item['productId']);
            $itemStmt->bindValue(':quantity', $item['quantity']);
            $itemStmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(["message" => "Order created successfully", "orderId" => $orderId]);
    } catch (Exception $e) {
        // Rollback on error
        if ($conn) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error creating order: " . $e->getMessage()]);
    }
}

function updateOrder() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Start transaction
        $conn->beginTransaction();
        
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        $items = isset($_GET['items']) ? json_decode($_GET['items'], true) : null;
        
        if (!$id) {
            throw new Exception("Order ID is required.");
        }
        
        // Update order status if provided
        if ($status) {
            $sql = "UPDATE orders SET status = :status WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':status', $status);
            $stmt->execute();
        }
        
        // Update order items if provided
        if ($items) {
            // Remove existing items
            $deleteSql = "DELETE FROM order_products WHERE order_id = :order_id";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bindValue(':order_id', $id);
            $deleteStmt->execute();
            
            // Add new items
            $itemSql = "INSERT INTO order_products (order_id, product_id, quantity) VALUES (:order_id, :product_id, :quantity)";
            $itemStmt = $conn->prepare($itemSql);
            
            foreach ($items as $item) {
                $itemStmt->bindValue(':order_id', $id);
                $itemStmt->bindValue(':product_id', $item['productId']);
                $itemStmt->bindValue(':quantity', $item['quantity']);
                $itemStmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(["message" => "Order updated successfully"]);
    } catch (Exception $e) {
        // Rollback on error
        if ($conn) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error updating order: " . $e->getMessage()]);
    }
}

function deleteOrder() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        if (!isset($_GET['id'])) {
            throw new Exception("Order ID is required.");
        }
        
        // Start transaction
        $conn->beginTransaction();
        
        $id = $_GET['id'];
        
        // Delete order items first (foreign key constraint)
        $deleteItemsSql = "DELETE FROM order_products WHERE order_id = :id";
        $deleteItemsStmt = $conn->prepare($deleteItemsSql);
        $deleteItemsStmt->bindValue(':id', $id);
        $deleteItemsStmt->execute();
        
        // Delete the order
        $deleteOrderSql = "DELETE FROM orders WHERE id = :id";
        $deleteOrderStmt = $conn->prepare($deleteOrderSql);
        $deleteOrderStmt->bindValue(':id', $id);
        $deleteOrderStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        if ($deleteOrderStmt->rowCount() > 0) {
            echo json_encode(["message" => "Order deleted successfully"]);
        } else {
            echo json_encode(["error" => "Order not found"]);
        }
    } catch (Exception $e) {
        // Rollback on error
        if ($conn) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error deleting order: " . $e->getMessage()]);
    }
}