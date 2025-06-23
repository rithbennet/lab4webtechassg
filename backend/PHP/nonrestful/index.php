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
    case 'patchOrder':
        patchOrder();
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
        
        // Try to get data from URL parameters first (non-RESTful style)
        $name = $_GET['name'] ?? null;
        $email = $_GET['email'] ?? null;
        $role = $_GET['role'] ?? 'user'; // Default role if not provided
        
        // If not in URL parameters, try to get from JSON body (RESTful style)
        if (!$name || !$email) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            $name = $data['name'] ?? $name;
            $email = $data['email'] ?? $email;
            $role = $data['role'] ?? $role;
        }
        
        // Check if required fields are present
        if (!$name || !$email) {
            throw new Exception("Name and email are required.");
        }
        
        $sql = "INSERT INTO users (name, email, role) VALUES (:name, :email, :role)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
        
        $userId = $conn->lastInsertId();
        echo json_encode([
            "message" => "User created successfully",
            "userId" => $userId
        ]);
        $conn = null; // Close the connection
    } catch (Exception $e) {
        echo json_encode(["error" => "Error creating user: " . $e->getMessage()]);
    }
}

function updateUser() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Get data from URL parameters first (non-RESTful style)
        $id = $_GET['id'] ?? null;
        $name = $_GET['name'] ?? null;
        $email = $_GET['email'] ?? null;
        $role = $_GET['role'] ?? null;
        
        // If not in URL parameters, try to get from JSON body (RESTful style)
        if (!$id || !$name || !$email) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            $id = $data['id'] ?? $id;
            $name = $data['name'] ?? $name;
            $email = $data['email'] ?? $email;
            $role = $data['role'] ?? $role;
        }
        
        // Check if required fields are present
        if (!$id) {
            throw new Exception("User ID is required.");
        }
        
        if (!$name || !$email) {
            throw new Exception("Name and email are required.");
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
        
        // Build SQL based on provided fields
        $updateFields = [];
        $params = [];
        
        // Always update name and email
        $updateFields[] = "name = :name";
        $params[':name'] = $name;
        $updateFields[] = "email = :email";
        $params[':email'] = $email;
        
        // Conditionally update role if provided
        if ($role !== null) {
            $updateFields[] = "role = :role";
            $params[':role'] = $role;
        }
        
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $params[':id'] = $id;
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            echo json_encode(["message" => "No changes made"]);
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
        
        // Get data from URL parameters first (non-RESTful style)
        $id = $_GET['id'] ?? null;
        $updateFields = [];
        $params = [];
        
        // Check for URL parameters
        if (isset($_GET['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $_GET['name'];
        }
        
        if (isset($_GET['email'])) {
            $updateFields[] = "email = :email";
            $params[':email'] = $_GET['email'];
        }
        
        if (isset($_GET['role'])) {
            $updateFields[] = "role = :role";
            $params[':role'] = $_GET['role'];
        }
        
        // If not in URL parameters or more params in JSON body, try to get from there (RESTful style)
        if (!$id || empty($updateFields)) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            
            // Get ID if not from URL
            if (!$id) {
                $id = $data['id'] ?? null;
            }
            
            // Additional fields from JSON body
            if (isset($data['name']) && !isset($params[':name'])) {
                $updateFields[] = "name = :name";
                $params[':name'] = $data['name'];
            }
            
            if (isset($data['email']) && !isset($params[':email'])) {
                $updateFields[] = "email = :email";
                $params[':email'] = $data['email'];
            }
            
            if (isset($data['role']) && !isset($params[':role'])) {
                $updateFields[] = "role = :role";
                $params[':role'] = $data['role'];
            }
        }
        
        // Check if required fields are present
        if (!$id) {
            throw new Exception("User ID is required.");
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
        
        // Try to get data from URL parameters first (non-RESTful style)
        $name = $_GET['name'] ?? null;
        $price = $_GET['price'] ?? null;
        $description = $_GET['description'] ?? null;
        
        // If not in URL parameters, try to get from JSON body (RESTful style)
        if (!$name || !$price) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            $name = $data['name'] ?? $name;
            $price = $data['price'] ?? $price;
            $description = $data['description'] ?? $description;
        }
        
        // Check if required fields are present
        if (!$name || !$price) {
            throw new Exception("Name and price are required.");
        }
        
        $sql = "INSERT INTO products (name, price, description) VALUES (:name, :price, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':description', $description);
        $stmt->execute();
        
        $productId = $conn->lastInsertId();
        echo json_encode([
            "message" => "Product created successfully",
            "productId" => $productId
        ]);
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
        
        // Get data from URL parameters first (non-RESTful style)
        $id = $_GET['id'] ?? null;
        $updateFields = [];
        $params = [];
        
        // Check for URL parameters
        if (isset($_GET['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $_GET['name'];
        }
        
        if (isset($_GET['price'])) {
            $updateFields[] = "price = :price";
            $params[':price'] = $_GET['price'];
        }
        
        if (isset($_GET['description'])) {
            $updateFields[] = "description = :description";
            $params[':description'] = $_GET['description'];
        }
        
        // If not in URL parameters or more params in JSON body, try to get from there (RESTful style)
        if (!$id || empty($updateFields)) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            
            // Get ID if not from URL
            if (!$id) {
                $id = $data['id'] ?? null;
            }
            
            // Additional fields from JSON body
            if (isset($data['name']) && !isset($params[':name'])) {
                $updateFields[] = "name = :name";
                $params[':name'] = $data['name'];
            }
            
            if (isset($data['price']) && !isset($params[':price'])) {
                $updateFields[] = "price = :price";
                $params[':price'] = $data['price'];
            }
            
            if (isset($data['description']) && !isset($params[':description'])) {
                $updateFields[] = "description = :description";
                $params[':description'] = $data['description'];
            }
        }
        
        // Check if required fields are present
        if (!$id) {
            throw new Exception("Product ID is required.");
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
        
        $sql = "SELECT o.*, u.name as customer_name 
                FROM orders o
                LEFT JOIN users u ON o.userId = u.id
                ORDER BY o.createdAt DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we're always returning an array
        $orders = $orders ?: [];
        
        // Important: Ensure we're sending a clean JSON array
        header('Content-Type: application/json');
        echo json_encode(array_values($orders));
        
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    } finally {
        if (isset($conn)) {
            $conn = null; // Close the connection
        }
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
        $sql = "SELECT o.*, u.name as customer_name 
                FROM orders o
                LEFT JOIN users u ON o.userId = u.id
                WHERE o.id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
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
        
        // Handle both non-RESTful URL parameters and RESTful JSON body
        $userId = $_GET['userId'] ?? null;
        $status = $_GET['status'] ?? 'pending';
        $items = null;
        
        // Check if we have individual product parameters (non-RESTful style)
        if (isset($_GET['productId']) && isset($_GET['productName']) && isset($_GET['price']) && isset($_GET['quantity'])) {
            // Single item from URL parameters
            $items = [[
                'productId' => $_GET['productId'],
                'productName' => $_GET['productName'],
                'price' => $_GET['price'],
                'quantity' => $_GET['quantity']
            ]];
        } else if (isset($_GET['items'])) {
            // Items array from URL parameter
            $items = json_decode($_GET['items'], true);
        }
        
        // If not in URL parameters, try to get from JSON body (RESTful style)
        if (!$userId || !$items) {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true) ?: [];
            $userId = $data['userId'] ?? $userId;
            $items = $data['items'] ?? $items;
            $status = $data['status'] ?? $status;
        }
        
        if (!$userId || !$items) {
            throw new Exception("User ID and items are required. For non-RESTful: provide userId, productId, productName, price, quantity. For RESTful: provide userId and items array.");
        }
        
        // Process each item as a separate order
        $orderIds = [];
        foreach ($items as $item) {
            // Calculate total
            $total = $item['quantity'] * $item['price'];
            
            $sql = "INSERT INTO orders (userId, productId, quantity, total, productName, price, totalAmount, status, createdAt) 
                   VALUES (:userId, :productId, :quantity, :total, :productName, :price, :totalAmount, :status, NOW())";
            $stmt = $conn->prepare($sql);
            
            $stmt->bindValue(':userId', $userId);
            $stmt->bindValue(':productId', $item['productId']);
            $stmt->bindValue(':quantity', $item['quantity']);
            $stmt->bindValue(':total', $total);
            $stmt->bindValue(':productName', $item['productName']);
            $stmt->bindValue(':price', $item['price']);
            $stmt->bindValue(':totalAmount', $total); // Same as total in this case
            $stmt->bindValue(':status', $status);
            $stmt->execute();
            
            $orderIds[] = $conn->lastInsertId();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            "message" => "Orders created successfully", 
            "orderIds" => $orderIds
        ]);
    } catch (Exception $e) {
        // Rollback on error
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error creating order: " . $e->getMessage()]);
    } finally {
        if (isset($conn)) {
            $conn = null; // Close the connection
        }
    }
}

function updateOrder() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Start transaction
        $conn->beginTransaction();
        
        // Get data from URL parameters first (non-RESTful style)
        $id = $_GET['id'] ?? null;
        $updateFields = [];
        $params = [];
        
        // Check for URL parameters
        $allowedFields = ['quantity', 'total', 'status', 'price', 'totalAmount'];
        foreach ($allowedFields as $field) {
            if (isset($_GET[$field])) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $_GET[$field];
            }
        }
        
        // If not in URL parameters or more params in JSON body, try to get from there (RESTful style)
        if (!$id || empty($updateFields)) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            
            // Get ID if not from URL
            if (!$id) {
                $id = $data['id'] ?? null;
            }
            
            // Additional fields from JSON body
            foreach ($allowedFields as $field) {
                if (isset($data[$field]) && !isset($params[":$field"])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
        }
        
        if (!$id) {
            throw new Exception("Order ID is required.");
        }
        
        if (empty($updateFields)) {
            throw new Exception("No fields to update. Supported fields are: " . implode(", ", $allowedFields));
        }
        
        // Add updatedAt
        $updateFields[] = "updatedAt = NOW()";
        
        $sql = "UPDATE orders SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $params[':id'] = $id;
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Order updated successfully"]);
        } else {
            echo json_encode(["message" => "Order not found or no changes made"]);
        }
    } catch (Exception $e) {
        // Rollback on error
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error updating order: " . $e->getMessage()]);
    } finally {
        if (isset($conn)) {
            $conn = null; // Close the connection
        }
    }
}

function patchOrder() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Start transaction
        $conn->beginTransaction();
        
        // Get data from URL parameters first (non-RESTful style)
        $id = $_GET['id'] ?? null;
        $updateFields = [];
        $params = [];
        
        // Check for URL parameters
        $allowedFields = ['quantity', 'total', 'status', 'price', 'totalAmount'];
        foreach ($allowedFields as $field) {
            if (isset($_GET[$field])) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $_GET[$field];
            }
        }
        
        // If not in URL parameters or more params in JSON body, try to get from there (RESTful style)
        if (!$id || empty($updateFields)) {
            // Get JSON data from the request body
            $json_data = file_get_contents('php://input');
            // Decode JSON data into associative array
            $data = json_decode($json_data, true) ?: [];
            
            // Get ID if not from URL
            if (!$id) {
                $id = $data['id'] ?? null;
            }
            
            // Additional fields from JSON body
            foreach ($allowedFields as $field) {
                if (isset($data[$field]) && !isset($params[":$field"])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
        }
        
        if (!$id) {
            throw new Exception("Order ID is required.");
        }
        
        if (empty($updateFields)) {
            throw new Exception("No fields to update. Supported fields are: " . implode(", ", $allowedFields));
        }
        
        // Add updatedAt
        $updateFields[] = "updatedAt = NOW()";
        
        $sql = "UPDATE orders SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $params[':id'] = $id;
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Commit transaction
            $conn->commit();
            echo json_encode(["message" => "Order patched successfully"]);
        } else {
            // Commit transaction anyway - no changes doesn't mean error
            $conn->commit();
            echo json_encode(["message" => "Order not found or no changes made"]);
        }
    } catch (Exception $e) {
        // Rollback on error
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error patching order: " . $e->getMessage()]);
    } finally {
        if (isset($conn)) {
            $conn = null; // Close the connection
        }
    }
}

function deleteOrder() {
    try {
        $db = new db();
        $conn = $db->connect();
        
        // Get ID from query parameters
        if (!isset($_GET['id'])) {
            throw new Exception("Order ID is required.");
        }
        
        $id = $_GET['id'];
        
        // Start transaction
        $conn->beginTransaction();
        
        $sql = "DELETE FROM orders WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Commit transaction
            $conn->commit();
            echo json_encode(["message" => "Order deleted successfully"]);
        } else {
            throw new Exception("Order not found");
        }
    } catch (Exception $e) {
        // Rollback on error
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(["error" => "Error deleting order: " . $e->getMessage()]);
    } finally {
        if (isset($conn)) {
            $conn = null; // Close the connection
        }
    }
}