<?php
require_once '../../config/auth.php';
require_once '../../config/db.php';

// Verificar se é admin
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /");
    exit();
}

$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitize($_POST['nome']);
    $descricao = sanitize($_POST['descricao']);
    $preco = str_replace(',', '.', sanitize($_POST['preco']));
    $categoria = sanitize($_POST['categoria']);
    $estoque = intval($_POST['estoque']);
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    
    // Validações
    if (empty($nome)) {
        $erros[] = "O nome do produto é obrigatório";
    }
    
    if (!is_numeric($preco) || $preco <= 0) {
        $erros[] = "O preço deve ser um número positivo";
    }
    
    if ($estoque < 0) {
        $erros[] = "O estoque não pode ser negativo";
    }
    
    // Processar upload da imagem
    $imagem = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extensao, $extensoesPermitidas)) {
            $nomeImagem = uniqid() . '.' . $extensao;
            $destino = '../../imagens/produtos/' . $nomeImagem;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                $imagem = $nomeImagem;
            } else {
                $erros[] = "Erro ao fazer upload da imagem";
            }
        } else {
            $erros[] = "Formato de imagem inválido. Use JPG, PNG ou GIF";
        }
    } else {
        $erros[] = "A imagem do produto é obrigatória";
    }
    
    // Se não houver erros, salvar no banco
    if (empty($erros)) {
        try {
            $conn = conectarDB();
            $stmt = $conn->prepare("INSERT INTO produtos 
                                   (nome, descricao, preco, categoria, estoque, imagem, destaque) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nome,
                $descricao,
                $preco,
                $categoria,
                $estoque,
                $imagem,
                $destaque
            ]);
            
            $sucesso = true;
        } catch (PDOException $e) {
            $erros[] = "Erro ao cadastrar produto: " . $e->getMessage();
            
            // Se houve erro, apagar a imagem que foi enviada
            if ($imagem && file_exists('../../imagens/produtos/' . $imagem)) {
                unlink('../../imagens/produtos/' . $imagem);
            }
        }
    }
}

include '../../includes/admin-header.php';
?>

<div class="container mt-5">
    <h2>Cadastrar Novo Produto</h2>
    
    <?php if ($sucesso): ?>
        <div class="alert alert-success">
            Produto cadastrado com sucesso!
            <a href="listar.php" class="alert-link">Voltar para a lista</a>
        </div>
    <?php else: ?>
        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= $erro ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nome do Produto</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Preço</label>
                <input type="text" name="preco" class="form-control" required placeholder="0,00">
            </div>
            
            <div class="form-group">
                <label>Categoria</label>
                <select name="categoria" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="celulares">Celulares</option>
                    <option value="powerbanks">Power Banks</option>
                    <option value="acessorios">Acessórios</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Estoque</label>
                <input type="number" name="estoque" class="form-control" required min="0">
            </div>
            
            <div class="form-group">
                <label>Imagem do Produto</label>
                <input type="file" name="imagem" class="form-control-file" required accept="image/*">
            </div>
            
            <div class="form-check mb-3">
                <input type="checkbox" name="destaque" class="form-check-input" id="destaque">
                <label class="form-check-label" for="destaque">Produto em destaque</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Cadastrar</button>
            <a href="listar.php" class="btn btn-secondary">Cancelar</a>
        </form>
    <?php endif; ?>
</div>

<?php include '../../includes/admin-footer.php'; ?>