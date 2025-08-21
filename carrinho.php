<?php
require_once 'config/auth.php';
protegerPagina();

include 'includes/header.php';


try {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT c.*, p.nome, p.preco, p.imagem 
                           FROM carrinho c
                           JOIN produtos p ON c.produto_id = p.id
                           WHERE c.usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = 0;
    foreach ($itens as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
    
} catch (PDOException $e) {
    $erro = "Erro ao carregar carrinho";
}
?>

<div class="container">
    <h2>Seu Carrinho</h2>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php elseif (empty($itens)): ?>
        <div class="alert alert-info">Seu carrinho está vazio</div>
        <a href="/" class="btn btn-primary">Continuar Comprando</a>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="cart-items">
                    <?php foreach ($itens as $item): ?>
                        <div class="cart-item mb-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <img src="/imagens/produtos/<?= htmlspecialchars($item['imagem']) ?>" class="img-fluid" alt="<?= htmlspecialchars($item['nome']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <h5><?= htmlspecialchars($item['nome']) ?></h5>
                                    <p>R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control item-quantity" data-id="<?= $item['id'] ?>" value="<?= $item['quantidade'] ?>" min="1">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger btn-remove-item" data-id="<?= $item['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4>Resumo do Pedido</h4>
                    <table class="table">
                        <tr>
                            <th>Subtotal</th>
                            <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Frete</th>
                            <td>A calcular</td>
                        </tr>
                        <tr class="total">
                            <th>Total</th>
                            <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
                        </tr>
                    </table>
                    <a href="/checkout.php" class="btn btn-primary btn-block">Finalizar Compra</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php include 'includes/footer.php'; ?>
