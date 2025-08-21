<?php
header('Content-Type: application/json');
require_once '../config/auth.php';
require_once '../config/db.php';

if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$usuarioId = $_SESSION['usuario_id'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $conn = conectarDB();
    
    switch ($method) {
        case 'GET':
            
            $stmt = $conn->prepare("SELECT c.*, p.nome, p.preco, p.imagem 
                                   FROM carrinho c
                                   JOIN produtos p ON c.produto_id = p.id
                                   WHERE c.usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $itens]);
            break;
            
        case 'POST':
            
            $produtoId = intval($input['produto_id']);
            $quantidade = intval($input['quantidade']);
            
         
            $stmt = $conn->prepare("SELECT id FROM produtos WHERE id = ?");
            $stmt->execute([$produtoId]);
            if (!$stmt->fetch()) {
                throw new Exception("Produto não encontrado");
            }
            
           
            $stmt = $conn->prepare("SELECT id, quantidade FROM carrinho 
                                   WHERE usuario_id = ? AND produto_id = ?");
            $stmt->execute([$usuarioId, $produtoId]);
            $item = $stmt->fetch();
            
            if ($item) {
               
                $novaQuantidade = $item['quantidade'] + $quantidade;
                $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? 
                                       WHERE id = ?");
                $stmt->execute([$novaQuantidade, $item['id']]);
            } else {
                
                $stmt = $conn->prepare("INSERT INTO carrinho 
                                       (usuario_id, produto_id, quantidade) 
                                       VALUES (?, ?, ?)");
                $stmt->execute([$usuarioId, $produtoId, $quantidade]);
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'PUT':
            
            $itemId = intval($input['id']);
            $quantidade = intval($input['quantidade']);
            
            if ($quantidade < 1) {
                throw new Exception("Quantidade inválida");
            }
            
            $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? 
                                   WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$quantidade, $itemId, $usuarioId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Item não encontrado");
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            
            $itemId = intval($_GET['id']);
            
            $stmt = $conn->prepare("DELETE FROM carrinho 
                                   WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$itemId, $usuarioId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Item não encontrado");
            }
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

?>
