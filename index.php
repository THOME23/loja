<?php
require_once 'config/db.php';
include 'includes/header.php';

// Buscar produtos em destaque
try {
    $conn = conectarDB();
    $stmt = $conn->query("SELECT * FROM produtos WHERE destaque = 1 LIMIT 8");
    $destaques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $destaques = [];
    $erro = "Erro ao carregar produtos em destaque";
}
?>

<div class="container">
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>
    
    <section class="hero">
        <h1>Bem-vindo à TechStore</h1>
        <p>Os melhores eletrônicos com os menores preços</p>
    </section>
    
    <section class="produtos-destaque">
        <h2>Produtos em Destaque</h2>
        <div class="row">
            <?php foreach ($destaques as $produto): ?>
                <div class="col-md-3 mb-4">
                    <div class="card produto-card">
                        <img src="/imagens/produtos/<?= htmlspecialchars($produto['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produto['nome']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($produto['nome']) ?></h5>
                            <p class="card-text">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
                            <a href="/produto.php?id=<?= $produto['id'] ?>" class="btn btn-primary">Ver Detalhes</a>
                            <button class="btn btn-success btn-adicionar-carrinho" data-id="<?= $produto['id'] ?>">Adicionar ao Carrinho</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>