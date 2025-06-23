<?php
header('Content-Type: application/json');
// Add CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config.php';

// Helper function to get input data
function get_input_data() {
    return json_decode(file_get_contents('php://input'), true);
}

// Connect to the database
$conn = getDbConnection();

// Parse the request URL to determine the resource and ID
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$path = trim($request_uri[0], '/');
$segments = explode('/', $path);
$resource = $segments[1] ?? null;
$id = $segments[2] ?? null;

// Fetch all users
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $resource == 'users' && !$id) {
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if ($result) {
        $users = [];
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
    } else {
        http_response_code(500);
        echo json_encode([
            "message" => "Database error", 
            "error" => $conn->error
        ]);
    }
}

// Fetch a specific user
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $resource == 'users' && $id) {
    $id = intval($id);
    $sql = "SELECT * FROM users WHERE id = $id";
    
    // Check if query execution was successful
    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            // No rows found with this ID
            echo json_encode([
                "message" => "User not found", 
                "debug" => [
                    "id" => $id,
                    "query" => $sql,
                    "rows" => $result->num_rows
                ]
            ]);
        }
    } else {
        // Query execution failed
        echo json_encode([
            "message" => "Database error", 
            "error" => $conn->error,
            "debug" => [
                "id" => $id,
                "query" => $sql
            ]
        ]);
    }
}

// Insert a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $resource == 'users') {
    $input = get_input_data();
    $name = $conn->real_escape_string($input['name']);
    $email = $conn->real_escape_string($input['email']);
    $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "User created successfully", "id" => $conn->insert_id]);
    } else {
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
}

// Update a user
if ($_SERVER['REQUEST_METHOD'] == 'PUT' && $resource == 'users' && $id) {
    $input = get_input_data();
    $id = intval($id);
    $name = $conn->real_escape_string($input['name']);
    $email = $conn->real_escape_string($input['email']);
    $sql = "UPDATE users SET name = '$name', email = '$email' WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            echo json_encode(["message" => "User not found or no changes made"]);
        }
    } else {
        echo json_encode(["message" => "Error updating user: " . $conn->error]);
    }
}

// patch a user
if ($_SERVER['REQUEST_METHOD'] == 'PATCH' && $resource == 'users' && $id) {
    $input = get_input_data();
    $id = intval($id);
    $updates = [];
    if (isset($input['name'])) {
        $name = $conn->real_escape_string($input['name']);
        $updates[] = "name = '$name'";
    }
    if (isset($input['email'])) {
        $email = $conn->real_escape_string($input['email']);
        $updates[] = "email = '$email'";
    }

    if (count($updates) > 0) {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            if ($conn->affected_rows > 0) {
                echo json_encode(["message" => "User updated successfully"]);
            } else {
                echo json_encode(["message" => "User not found or no changes made"]);
            }
        } else {
            echo json_encode(["message" => "Error updating user: " . $conn->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No fields to update"]);
    }
}

// Delete a user
if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && $resource == 'users' && $id) {
    $id = intval($id);
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            echo json_encode(["message" => "User not found"]);
        }
    } else {
        echo json_encode(["message" => "Error deleting user: " . $conn->error]);
    }
}

// PRODUCT API ENDPOINTS

// Fetch all products
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $resource == 'products' && !$id) {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);

    if ($result) {
        $products = [];
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
    } else {
        http_response_code(500);
        echo json_encode([
            "message" => "Database error", 
            "error" => $conn->error
        ]);
    }
}

// Fetch a specific product
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $resource == 'products' && $id) {
    $id = intval($id);
    $sql = "SELECT * FROM products WHERE id = $id";
    
    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            http_response_code(404);
            echo json_encode([
                "message" => "Product not found"
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            "message" => "Database error", 
            "error" => $conn->error
        ]);
    }
}

// Insert a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $resource == 'products') {
    $input = get_input_data();
    
    // Validate required fields
    if (!isset($input['name']) || !isset($input['price'])) {
        http_response_code(400);
        echo json_encode(["message" => "Product name and price are required"]);
        exit();
    }
    
    $name = $conn->real_escape_string($input['name']);
    $price = floatval($input['price']);
    
    $sql = "INSERT INTO products (name, price) VALUES ('$name', $price)";
    
    if ($conn->query($sql) === TRUE) {
        http_response_code(201); // Created
        echo json_encode([
            "message" => "Product created successfully", 
            "id" => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error: " . $conn->error]);
    }
}

// Update a product
if ($_SERVER['REQUEST_METHOD'] == 'PUT' && $resource == 'products' && $id) {
    $input = get_input_data();
    $id = intval($id);
    
    // Validate required fields
    if (!isset($input['name']) || !isset($input['price'])) {
        http_response_code(400);
        echo json_encode(["message" => "Product name and price are required"]);
        exit();
    }
    
    $name = $conn->real_escape_string($input['name']);
    $price = floatval($input['price']);
    
    $sql = "UPDATE products SET name = '$name', price = $price WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["message" => "Product updated successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Product not found or no changes made"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating product: " . $conn->error]);
    }
}

// Delete a product
if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && $resource == 'products' && $id) {
    $id = intval($id);
    $sql = "DELETE FROM products WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["message" => "Product deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting product: " . $conn->error]);
    }
}

// ORDER API ENDPOINTS

// Fetch all orders
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $resource == 'orders' && !$id) {
    $sql = "SELECT * FROM orders";
    $result = $conn->query($sql);

    if ($result) {
        $orders = [];
        while($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        echo json_encode($orders);
    } else {
        http_response_code(500);
        echo json_encode([
            "message" => "Database error", 
            "error" => $conn->error
        ]);
    }
}

// Fetch a specific order
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $resource == 'orders' && $id) {
    $id = intval($id);
    $sql = "SELECT * FROM orders WHERE id = $id";
    
    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            http_response_code(404);
            echo json_encode([
                "message" => "Order not found"
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            "message" => "Database error", 
            "error" => $conn->error
        ]);
    }
}

// Insert a new order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $resource == 'orders') {
    $input = get_input_data();
    
    // Validate required fields
    if (!isset($input['userId']) || !isset($input['productId']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(["message" => "userId, productId, and quantity are required"]);
        exit();
    }
    
    $userId = intval($input['userId']);
    $productId = intval($input['productId']);
    $quantity = intval($input['quantity']);
    $totalAmount = isset($input['totalAmount']) ? floatval($input['totalAmount']) : 0;
    $status = isset($input['status']) ? $conn->real_escape_string($input['status']) : 'pending';
    $productName = isset($input['productName']) ? $conn->real_escape_string($input['productName']) : '';
    $price = isset($input['price']) ? floatval($input['price']) : 0;
    $total = isset($input['total']) ? floatval($input['total']) : ($price * $quantity);
    
    // Get product info if not provided
    if (empty($productName) || $price <= 0) {
        $productSql = "SELECT name, price FROM products WHERE id = $productId";
        $productResult = $conn->query($productSql);
        
        if ($productResult && $productResult->num_rows > 0) {
            $product = $productResult->fetch_assoc();
            $productName = $product['name'];
            $price = floatval($product['price']);
            $total = $price * $quantity;
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Product not found"]);
            exit();
        }
    }
    
    if ($totalAmount <= 0) {
        $totalAmount = $total;
    }
    
    // Insert order
    $sql = "INSERT INTO orders (userId, productId, quantity, total, productName, price, totalAmount, status, createdAt, updatedAt) 
            VALUES ($userId, $productId, $quantity, $total, '$productName', $price, $totalAmount, '$status', NOW(), NOW())";
    
    if ($conn->query($sql) === TRUE) {
        http_response_code(201); // Created
        echo json_encode([
            "message" => "Order created successfully", 
            "id" => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error creating order: " . $conn->error]);
    }
}

// Update an order
if ($_SERVER['REQUEST_METHOD'] == 'PUT' && $resource == 'orders' && $id) {
    $input = get_input_data();
    $id = intval($id);
    
    // Check if order exists
    $checkSql = "SELECT id FROM orders WHERE id = $id";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Order not found"]);
        exit();
    }
    
    // Required fields for PUT
    if (!isset($input['userId']) || !isset($input['productId']) || !isset($input['quantity']) ||
        !isset($input['total']) || !isset($input['productName']) || !isset($input['price']) || 
        !isset($input['totalAmount']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields for order update"]);
        exit();
    }
    
    $userId = intval($input['userId']);
    $productId = intval($input['productId']);
    $quantity = intval($input['quantity']);
    $total = floatval($input['total']);
    $productName = $conn->real_escape_string($input['productName']);
    $price = floatval($input['price']);
    $totalAmount = floatval($input['totalAmount']);
    $status = $conn->real_escape_string($input['status']);
    
    $sql = "UPDATE orders SET 
                userId = $userId,
                productId = $productId,
                quantity = $quantity,
                total = $total,
                productName = '$productName',
                price = $price,
                totalAmount = $totalAmount,
                status = '$status',
                updatedAt = NOW()
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["message" => "Order updated successfully"]);
        } else {
            echo json_encode(["message" => "No changes made to the order"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating order: " . $conn->error]);
    }
}

// Delete an order
if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && $resource == 'orders' && $id) {
    $id = intval($id);
    
    // Delete the order
    $orderSql = "DELETE FROM orders WHERE id = $id";
    $result = $conn->query($orderSql);
    
    if ($result === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode(["message" => "Order deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Order not found"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting order: " . $conn->error]);
    }
}

// Patch an order (partial update)
if ($_SERVER['REQUEST_METHOD'] == 'PATCH' && $resource == 'orders' && $id) {
    $input = get_input_data();
    $id = intval($id);
    
    // Check if order exists
    $checkSql = "SELECT id FROM orders WHERE id = $id";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Order not found"]);
        exit();
    }
    
    $updates = [];
    
    // Only update fields that are provided
    if (isset($input['userId'])) {
        $userId = intval($input['userId']);
        $updates[] = "userId = $userId";
    }
    
    if (isset($input['productId'])) {
        $productId = intval($input['productId']);
        $updates[] = "productId = $productId";
    }
    
    if (isset($input['quantity'])) {
        $quantity = intval($input['quantity']);
        $updates[] = "quantity = $quantity";
    }
    
    if (isset($input['total'])) {
        $total = floatval($input['total']);
        $updates[] = "total = $total";
    }
    
    if (isset($input['productName'])) {
        $productName = $conn->real_escape_string($input['productName']);
        $updates[] = "productName = '$productName'";
    }
    
    if (isset($input['price'])) {
        $price = floatval($input['price']);
        $updates[] = "price = $price";
    }
    
    if (isset($input['totalAmount'])) {
        $totalAmount = floatval($input['totalAmount']);
        $updates[] = "totalAmount = $totalAmount";
    }
    
    if (isset($input['status'])) {
        $status = $conn->real_escape_string($input['status']);
        $updates[] = "status = '$status'";
    }
    
    // Always update the updatedAt timestamp
    $updates[] = "updatedAt = NOW()";
    
    if (count($updates) > 0) {
        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            if ($conn->affected_rows > 0) {
                echo json_encode(["message" => "Order updated successfully"]);
            } else {
                echo json_encode(["message" => "No changes made to the order"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error updating order: " . $conn->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No fields to update"]);
    }
}

$conn->close();
?>
