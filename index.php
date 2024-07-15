<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "sparky97!";
$dbname = "shopygo";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Función para manejar errores
function handle_error($message, $code = 500) {
    http_response_code($code);
    echo json_encode(["error" => $message]);
    exit();
}

// Funciones para manejar usuarios
function createUser($conn, $data) {
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, passwordHash) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $data['username'], $data['email'], $hashedPassword);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function authenticateUser($conn, $username, $password) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['passwordHash'])) {
            return $user;
        }
    }
    return false;
}

// Funciones para manejar productos
function getProducts($conn) {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    if (!$result) {
        handle_error("Query failed: " . $conn->error);
    }
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

function getProductById($conn, $id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        handle_error("Product not found", 404);
    }
    return $result->fetch_assoc();
}

function createProduct($conn, $data) {
    $sql = "INSERT INTO products (title, description, shortDescription, price, image, categoryId) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("sssdis", $data['title'], $data['description'], $data['shortDescription'], $data['price'], $data['image'], $data['categoryId']);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function updateProduct($conn, $id, $data) {
    $sql = "UPDATE products SET title = ?, description = ?, shortDescription = ?, price = ?, image = ?, categoryId = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("sssdisi", $data['title'], $data['description'], $data['shortDescription'], $data['price'], $data['image'], $data['categoryId'], $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function deleteProduct($conn, $id) {
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

// Funciones para manejar categorías
function getCategories($conn) {
    $sql = "SELECT * FROM categories";
    $result = $conn->query($sql);
    if (!$result) {
        handle_error("Query failed: " . $conn->error);
    }
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function getCategoryById($conn, $id) {
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        handle_error("Category not found", 404);
    }
    return $result->fetch_assoc();
}

function createCategory($conn, $data) {
    $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $data['name'], $data['description']);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function updateCategory($conn, $id, $data) {
    $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("ssi", $data['name'], $data['description'], $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function deleteCategory($conn, $id) {
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

// Funciones para manejar listas de compras
function getShoppingLists($conn) {
    $sql = "SELECT * FROM shoppingLists";
    $result = $conn->query($sql);
    if (!$result) {
        handle_error("Query failed: " . $conn->error);
    }
    $shoppingLists = [];
    while ($row = $result->fetch_assoc()) {
        $shoppingLists[] = $row;
    }
    return $shoppingLists;
}

function getShoppingListByUserId($conn, $userId) {
    $sql = "SELECT * FROM shoppingLists WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        handle_error("Shopping list not found", 404);
    }
    return $result->fetch_assoc();
}

function createShoppingList($conn, $data) {
    $sql = "INSERT INTO shoppingLists (userId) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $data['userId']);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function updateShoppingList($conn, $id, $data) {
    $sql = "UPDATE shoppingLists SET userId = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $data['userId'], $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function deleteShoppingList($conn, $id) {
    $sql = "DELETE FROM shoppingLists WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

// Funciones para manejar ítems de listas de compras
function getShoppingListItems($conn, $userId) {
    $sql = "SELECT sli.id, sli.shoppingListId, sli.productId, sli.quantity, 
                   p.title, p.shortDescription, p.price, p.image
            FROM shoppingListItems sli
            JOIN products p ON sli.productId = p.id
            JOIN shoppingLists sl ON sli.shoppingListId = sl.id
            WHERE sl.userId = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        handle_error("No items found for the given user", 404);
    }
    $shoppingListItems = [];
    while ($row = $result->fetch_assoc()) {
        $shoppingListItems[] = $row;
    }
    return $shoppingListItems;
}


function createShoppingListItem($conn, $data) {
    $sql = "INSERT INTO shoppingListItems (shoppingListId, productId, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("iii", $data['shoppingListId'], $data['productId'], $data['quantity']);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function updateShoppingListItem($conn, $id, $data) {
    $sql = "UPDATE shoppingListItems SET shoppingListId = ?, productId = ?, quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("iiii", $data['shoppingListId'], $data['productId'], $data['quantity'], $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

function deleteShoppingListItem($conn, $id) {
    $sql = "DELETE FROM shoppingListItems WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        handle_error("Execute statement failed: " . $stmt->error);
    }
    return true;
}

// Manejar las solicitudes
try {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['resource'])) {
            switch ($_GET['resource']) {
                case 'products':
                    if (isset($_GET['id'])) {
                        $data = getProductById($conn, $_GET['id']);
                    } else {
                        $data = getProducts($conn);
                    }
                    break;
                case 'categories':
                    if (isset($_GET['id'])) {
                        $data = getCategoryById($conn, $_GET['id']);
                    } else {
                        $data = getCategories($conn);
                    }
                    break;
                case 'users':
                    break;
                case 'shoppingLists':
                    if (isset($_GET['id'])) {
                        $data = getShoppingListByUserId($conn, $_GET['id']);
                    } else {
                        $data = getShoppingLists($conn);
                    }
                    break;
                case 'shoppingListItems':
                    if (isset($_GET['userId'])) {
                        $data = getShoppingListItems($conn, $_GET['userId']);
                    } else {
                        handle_error("User ID is required for shoppingListItems", 400);
                    }
                    break;
                default:
                    handle_error("Resource not found", 404);
            }
            echo json_encode($data);
        } else {
            handle_error("Bad Request", 400);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($_GET['resource'])) {
            switch ($_GET['resource']) {
                case 'register':
                    if (createUser($conn, $data)) {
                        http_response_code(201);
                        echo json_encode(["message" => "User registered"]);
                    }
                    break;
                case 'login':
                    $user = authenticateUser($conn, $data['username'], $data['password']);
                    if ($user) {
                        http_response_code(200);
                        echo json_encode(["message" => "Login successful", "user" => $user]);
                    } else {
                        handle_error("Invalid username or password", 401);
                    }
                    break;
                case 'products':
                    if (createProduct($conn, $data)) {
                        http_response_code(201);
                        echo json_encode(["message" => "Product created"]);
                    }
                    break;
                case 'categories':
                    if (createCategory($conn, $data)) {
                        http_response_code(201);
                        echo json_encode(["message" => "Category created"]);
                    }
                    break;
                case 'users':
                    if (createUser($conn, $data)) {
                        http_response_code(201);
                        echo json_encode(["message" => "User created"]);
                    }
                    break;
                case 'shoppingLists':
                    if (createShoppingList($conn, $data)) {
                        http_response_code(201);
                        echo json_encode(["message" => "Shopping list created"]);
                    }
                    break;
                case 'shoppingListItems':
                    if (createShoppingListItem($conn, $data)) {
                        http_response_code(201);
                        echo json_encode(["message" => "Shopping list item created"]);
                    }
                    break;
                default:
                    handle_error("Resource not found", 404);
            }
        } else {
            handle_error("Bad Request", 400);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($_GET['resource']) && isset($_GET['id'])) {
            switch ($_GET['resource']) {
                case 'products':
                    if (updateProduct($conn, $_GET['id'], $data)) {
                        http_response_code(200);
                        echo json_encode(["message" => "Product updated"]);
                    }
                    break;
                case 'categories':
                    if (updateCategory($conn, $_GET['id'], $data)) {
                        http_response_code(200);
                        echo json_encode(["message" => "Category updated"]);
                    }
                    break;
                case 'users':
                    break;
                case 'shoppingLists':
                    if (updateShoppingList($conn, $_GET['id'], $data)) {
                        http_response_code(200);
                        echo json_encode(["message" => "Shopping list updated"]);
                    }
                    break;
                case 'shoppingListItems':
                    if (updateShoppingListItem($conn, $_GET['id'], $data)) {
                        http_response_code(200);
                        echo json_encode(["message" => "Shopping list item updated"]);
                    }
                    break;
                default:
                    handle_error("Resource not found", 404);
            }
        } else {
            handle_error("Bad Request", 400);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        if (isset($_GET['resource']) && isset($_GET['id'])) {
            switch ($_GET['resource']) {
                case 'products':
                    if (deleteProduct($conn, $_GET['id'])) {
                        http_response_code(200);
                        echo json_encode(["message" => "Product deleted"]);
                    }
                    break;
                case 'categories':
                    if (deleteCategory($conn, $_GET['id'])) {
                        http_response_code(200);
                        echo json_encode(["message" => "Category deleted"]);
                    }
                    break;
                case 'users':
                    break;
                case 'shoppingLists':
                    if (deleteShoppingList($conn, $_GET['id'])) {
                        http_response_code(200);
                        echo json_encode(["message" => "Shopping list deleted"]);
                    }
                    break;
                case 'shoppingListItems':
                    if (deleteShoppingListItem($conn, $_GET['id'])) {
                        http_response_code(200);
                        echo json_encode(["message" => "Shopping list item deleted"]);
                    }
                    break;
                default:
                    handle_error("Resource not found", 404);
            }
        } else {
            handle_error("Bad Request", 400);
        }
    }
} catch (Exception $e) {
    handle_error("Internal Server Error: " . $e->getMessage());
} finally {
    $conn->close();
}
?>
