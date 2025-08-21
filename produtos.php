<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $conn = conectarDB();
    
    
    $categoria = isset($_GET['categoria']) ? sanitize($_GET['categoria']) : null;
    $busca = isset($_GET['busca']) ? sanitize($_GET['busca']) : null;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $porPagina = 12;
    
    
    $sql = "SELECT * FROM produtos WHERE 1=1";
    $params = [];
    
    if ($categoria) {
        $sql .= " AND categoria = ?";
        $params[] = $categoria;
    }
    
    if ($busca) {
        $sql .= " AND (nome LIKE ? OR descricao LIKE ?)";
        $params[] = "%$busca%";
        $params[] = "%$busca%";
    }
    

    $stmt = $conn->prepare(str_replace('*', 'COUNT(*) as total', $sql));
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    $sql .= " LIMIT ?, ?";
    $params[] = ($pagina - 1) * $porPagina;
    $params[] = $porPagina;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $produtos,
        'paginacao' => [
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'total' => $total,
            'totalPaginas' => ceil($total / $porPagina)
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produtos',
        'error' => $e->getMessage()
    ]);
}

?>
