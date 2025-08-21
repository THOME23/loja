<?php
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit();
}

$id = intval($_GET['id']);

try {
    $conn = conectarDB();
    
    
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produto) {
        header("Location: /");
        exit();
    }
    
    
    $stmt = $conn->prepare("SELECT a.*, u.nome as usuario_nome 
                           FROM avaliacoes a 
                           JOIN usuarios u ON a.usuario_id = u.id 
                           WHERE a.produto_id = ? 
                           ORDER BY a.data_avaliacao DESC");
    $stmt->execute([$id]);
    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    $stmt = $conn->prepare("SELECT AVG(rating) as media FROM avaliacoes WHERE produto_id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $erro = "Erro ao carregar informações do produto";
}

include 'includes/header.php';
?>

<div class="container">
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-6">
                <img src="/imagens/produtos/<?= htmlspecialchars($produto['imagem']) ?>" class="img-fluid" alt="<?= htmlspecialchars($produto['nome']) ?>">
            </div>
            <div class="col-md-6">
                <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                <div class="rating mb-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?= $i <= round($media) ? '' : '-o' ?>"></i>
                    <?php endfor; ?>
                    <span>(<?= count($avaliacoes) ?> avaliações)</span>
                </div>
                <p class="price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                <p><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
                
                <form class="add-to-cart-form">
                    <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                    <div class="form-group">
                        <label>Quantidade:</label>
                        <input type="number" name="quantidade" min="1" value="1" class="form-control" style="width: 80px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Adicionar ao Carrinho</button>
                </form>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <h3>Avaliações</h3>
                
                <?php if (empty($avaliacoes)): ?>
                    <p>Nenhuma avaliação ainda. Seja o primeiro a avaliar!</p>
                <?php else: ?>
                    <?php foreach ($avaliacoes as $avaliacao): ?>
                        <div class="review mb-4">
                            <h5><?= htmlspecialchars($avaliacao['usuario_nome']) ?></h5>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?= $i <= $avaliacao['rating'] ? '' : '-o' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?></p>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($avaliacao['data_avaliacao'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (estaLogado()): ?>
                    <div class="add-review mt-5">
                        <h4>Deixe sua avaliação</h4>
                        <form method="POST" action="/api/avaliacoes.php">
                            <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                            <div class="form-group">
                                <label>Sua avaliação:</label>
                                <select name="rating" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="5">5 - Excelente</option>
                                    <option value="4">4 - Muito bom</option>
                                    <option value="3">3 - Bom</option>
                                    <option value="2">2 - Regular</option>
                                    <option value="1">1 - Ruim</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Comentário:</label>
                                <textarea name="comentario" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="/login.php">Faça login</a> para deixar uma avaliação.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php include 'includes/footer.php'; ?>
