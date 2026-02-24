<?php
require_once 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM grocery_items ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    }
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['action'])) {
            if ($input['action'] === 'add' && !empty($input['name'])) {
                $stmt = $pdo->prepare("INSERT INTO grocery_items (name, packaging, average_price) VALUES (?, ?, ?) RETURNING *");
                $stmt->execute([
                    $input['name'],
                    $input['packaging'] ?? null,
                    $input['average_price'] ?? null
                ]);
                echo json_encode($stmt->fetch());
            }
            elseif ($input['action'] === 'edit' && isset($input['id']) && !empty($input['name'])) {
                $stmt = $pdo->prepare("UPDATE grocery_items SET name = ?, packaging = ?, average_price = ? WHERE id = ? RETURNING *");
                $stmt->execute([
                    $input['name'],
                    $input['packaging'] ?? null,
                    $input['average_price'] ?? null,
                    $input['id']
                ]);
                echo json_encode($stmt->fetch());
            }
            elseif ($input['action'] === 'toggle' && isset($input['id'])) {
                $stmt = $pdo->prepare("UPDATE grocery_items SET completed = NOT completed WHERE id = ? RETURNING *");
                $stmt->execute([$input['id']]);
                echo json_encode($stmt->fetch());
            }
            elseif ($input['action'] === 'delete' && isset($input['id'])) {
                $stmt = $pdo->prepare("DELETE FROM grocery_items WHERE id = ?");
                $stmt->execute([$input['id']]);
                echo json_encode(['success' => true]);
            }
        }
    }
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
