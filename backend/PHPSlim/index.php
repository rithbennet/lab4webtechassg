<?php
error_reporting(E_ALL & ~E_DEPRECATED);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require 'vendor/autoload.php';
require_once './config.php';
// Instantiate db class
$db = new db();
// Create Slim app
$app = new \Slim\App;
// Routes for users
$app->get('/users', function ($request, $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM users";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});
$app->get('/users/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return $response->withJson($user);
        } else {
            return $response->withJson(["error" => "User not found"]);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});
$app->post('/users', function ($request, $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['name']) || !isset($data['email'])) {
            throw new Exception("Name and email are required.");
        }
        $name = $data['name'];
        $email = $data['email'];
        $sql = "INSERT INTO users (name, email) VALUES (:name, :email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $response->withJson(["message" => "User created successfully"]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating user: " . $e->getMessage()]);
    }
});
$app->put('/users/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['name']) || !isset($data['email'])) {
            throw new Exception("Name and email are required.");
        }
        $name = $data['name'];
        $email = $data['email'];
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $response->withJson(["message" => "User updated successfully"]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error updating user: " . $e->getMessage()]);
    }
});
$app->delete('/users/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $response->withJson(["message" => "User deleted successfully"]);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error deleting user: " . $e->getMessage()]);
    }
});

$app->patch('/users/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        $updates = [];
        $params = [':id' => $id];
        if (isset($data['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $updates[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (empty($updates)) {
            return $response->withJson(["error" => "No fields to update provided."])->withStatus(400);
        }
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() > 0) {
            return $response->withJson(["message" => "User updated successfully"]);
        } else {
            return $response->withJson(["message" => "User not found or data is the same."]);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error updating user: " . $e->getMessage()]);
    }
});

// Routes for products
$app->get('/products', function ($request, $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});

$app->get('/products/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            return $response->withJson($product);
        } else {
            return $response->withJson(["error" => "Product not found"]);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});

$app->post('/products', function ($request, $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['name']) || !isset($data['price'])) {
            throw new Exception("Name and price are required.");
        }
        $name = $data['name'];
        $price = $data['price'];
        $sql = "INSERT INTO products (name, price) VALUES (:name, :price)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->execute();
        return $response->withJson([
            "message" => "Product created successfully",
            "productId" => $conn->lastInsertId()
        ]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating product: " . $e->getMessage()]);
    }
});

$app->put('/products/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['name']) || !isset($data['price'])) {
            throw new Exception("Name and price are required.");
        }
        $name = $data['name'];
        $price = $data['price'];
        $sql = "UPDATE products SET name = :name, price = :price WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $response->withJson(["message" => "Product updated successfully"]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error updating product: " . $e->getMessage()]);
    }
});

$app->patch('/products/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        $updates = [];
        $params = [':id' => $id];
        if (isset($data['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['price'])) {
            $updates[] = "price = :price";
            $params[':price'] = $data['price'];
        }
        if (empty($updates)) {
            return $response->withJson(["error" => "No fields to update provided."])->withStatus(400);
        }
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() > 0) {
            return $response->withJson(["message" => "Product updated successfully"]);
        } else {
            return $response->withJson(["message" => "Product not found or data is the same."]);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error updating product: " . $e->getMessage()]);
    }
});

$app->delete('/products/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $response->withJson(["message" => "Product deleted successfully"]);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error deleting product: " . $e->getMessage()]);
    }
});

// Routes for orders
$app->get('/orders', function ($request, $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM orders";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});

$app->get('/orders/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            return $response->withJson($order);
        } else {
            return $response->withJson(["error" => "Order not found"]);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});

$app->post('/orders', function ($request, $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['userId']) || !isset($data['productId']) || !isset($data['quantity'])) {
            throw new Exception("User ID, Product ID, and Quantity are required.");
        }
        
        $userId = $data['userId'];
        $productId = $data['productId'];
        $quantity = $data['quantity'];
        $status = $data['status'] ?? 'pending';
        $createdAt = date('Y-m-d H:i:s');
        $updatedAt = date('Y-m-d H:i:s');
        
        // Get product details to calculate total
        $productSql = "SELECT name, price FROM products WHERE id = :productId";
        $productStmt = $conn->prepare($productSql);
        $productStmt->bindValue(':productId', $productId);
        $productStmt->execute();
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception("Product not found.");
        }
        
        $productName = $product['name'];
        $price = $product['price'];
        $total = $price * $quantity;
        $totalAmount = $total; // Same as total for a single product
        
        $sql = "INSERT INTO orders (userId, productId, quantity, total, productName, price, totalAmount, status, createdAt, updatedAt) 
                VALUES (:userId, :productId, :quantity, :total, :productName, :price, :totalAmount, :status, :createdAt, :updatedAt)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':productId', $productId);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':total', $total);
        $stmt->bindValue(':productName', $productName);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':totalAmount', $totalAmount);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':createdAt', $createdAt);
        $stmt->bindValue(':updatedAt', $updatedAt);
        $stmt->execute();
        
        return $response->withJson([
            "message" => "Order created successfully",
            "orderId" => $conn->lastInsertId()
        ]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating order: " . $e->getMessage()]);
    }
});

$app->put('/orders/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        
        if (!isset($data['userId']) || !isset($data['productId']) || !isset($data['quantity'])) {
            throw new Exception("User ID, Product ID, and Quantity are required.");
        }
        
        $userId = $data['userId'];
        $productId = $data['productId'];
        $quantity = $data['quantity'];
        $status = $data['status'] ?? 'pending';
        $updatedAt = date('Y-m-d H:i:s');
        
        // Get product details to calculate total
        $productSql = "SELECT name, price FROM products WHERE id = :productId";
        $productStmt = $conn->prepare($productSql);
        $productStmt->bindValue(':productId', $productId);
        $productStmt->execute();
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception("Product not found.");
        }
        
        $productName = $product['name'];
        $price = $product['price'];
        $total = $price * $quantity;
        $totalAmount = $total;
        
        $sql = "UPDATE orders SET userId = :userId, productId = :productId, quantity = :quantity, 
                total = :total, productName = :productName, price = :price, totalAmount = :totalAmount, 
                status = :status, updatedAt = :updatedAt 
                WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':productId', $productId);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':total', $total);
        $stmt->bindValue(':productName', $productName);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':totalAmount', $totalAmount);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':updatedAt', $updatedAt);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        return $response->withJson(["message" => "Order updated successfully"]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error updating order: " . $e->getMessage()]);
    }
});

$app->patch('/orders/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        $updates = [];
        $params = [':id' => $id];
        $needProductRecalculation = false;
        $productId = null;
        $quantity = null;
        
        // Check which fields are being updated
        if (isset($data['userId'])) {
            $updates[] = "userId = :userId";
            $params[':userId'] = $data['userId'];
        }
        
        if (isset($data['productId'])) {
            $updates[] = "productId = :productId";
            $params[':productId'] = $data['productId'];
            $productId = $data['productId'];
            $needProductRecalculation = true;
        }
        
        if (isset($data['quantity'])) {
            $updates[] = "quantity = :quantity";
            $params[':quantity'] = $data['quantity'];
            $quantity = $data['quantity'];
            $needProductRecalculation = true;
        }
        
        if (isset($data['status'])) {
            $updates[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        
        // If we need to recalculate totals based on product and quantity changes
        if ($needProductRecalculation) {
            // We need to fetch current order data if not all required info is provided
            if (!$productId || !$quantity) {
                $orderSql = "SELECT productId, quantity FROM orders WHERE id = :orderId";
                $orderStmt = $conn->prepare($orderSql);
                $orderStmt->bindValue(':orderId', $id);
                $orderStmt->execute();
                $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$orderData) {
                    return $response->withJson(["error" => "Order not found"])->withStatus(404);
                }
                
                $productId = $productId ?? $orderData['productId'];
                $quantity = $quantity ?? $orderData['quantity'];
            }
            
            // Get product details
            $productSql = "SELECT name, price FROM products WHERE id = :productId";
            $productStmt = $conn->prepare($productSql);
            $productStmt->bindValue(':productId', $productId);
            $productStmt->execute();
            $product = $productStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return $response->withJson(["error" => "Product not found"])->withStatus(404);
            }
            
            // Update product related fields
            $updates[] = "productName = :productName";
            $params[':productName'] = $product['name'];
            
            $updates[] = "price = :price";
            $params[':price'] = $product['price'];
            
            $total = $product['price'] * $quantity;
            $updates[] = "total = :total";
            $params[':total'] = $total;
            
            $updates[] = "totalAmount = :totalAmount";
            $params[':totalAmount'] = $total;
        }
        
        // Always update the updatedAt timestamp
        $updates[] = "updatedAt = :updatedAt";
        $params[':updatedAt'] = date('Y-m-d H:i:s');
        
        if (empty($updates)) {
            return $response->withJson(["error" => "No fields to update provided."])->withStatus(400);
        }
        
        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            return $response->withJson(["message" => "Order updated successfully"]);
        } else {
            return $response->withJson(["message" => "Order not found or data is the same."]);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error updating order: " . $e->getMessage()]);
    }
});

$app->delete('/orders/{id}', function ($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "DELETE FROM orders WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $response->withJson(["message" => "Order deleted successfully"]);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error deleting order: " . $e->getMessage()]);
    }
});

//dummy routes...cuba disini
$app->get('/xyz', function ($request, $response, $args) {
    $userObject = [
        'id' => 101,
        'name' => 'Johanna Alis',
        'email' => 'johalis@utm.my'
    ];
    return $response->withJson($userObject);
});
$app->run();
