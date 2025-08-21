<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';

// Verificar se é admin
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /");
    exit();
}

// Paginação
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Buscar produtos
try {
    $conn = conectarDB();
    
    // Contar total
    $stmt = $conn->query("SELECT COUNT(*) as total FROM produtos");
    $total = $stmt->fetchColumn();
    $totalPaginas = ceil($total / $porPagina);
    
    // Buscar produtos
    $stmt = $conn->prepare("SELECT * FROM produtos ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $erro = "Erro ao carregar produtos: " . $e->getMessage();
}

include '../../includes/admin-header.php';
?>

<div class="container mt-5">
    <h2>Gerenciar Produtos</h2>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="cadastrar.php" class="btn btn-success">Novo Produto</a>
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Categoria</th>
                <th>Estoque</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= $produto['id'] ?></td>
                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($produto['categoria']) ?></td>
                    <td><?= $produto['estoque'] ?></td>
                    <td>
                        <a href="editar.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <button class="btn btn-sm btn-danger btn-excluir" data-id="<?= $produto['id'] ?>">Excluir</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPaginas > 1): ?>
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
// Confirmar exclusão
document.querySelectorAll('.btn-excluir').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Tem certeza que deseja excluir este produto?')) {
            window.location.href = 'excluir.php?id=' + this.dataset.id;
        }
    });
});
</script>

<?php include '../../includes/admin-footer.php'; ?>