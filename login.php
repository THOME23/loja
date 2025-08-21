<?php
require_once 'config/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $senha = sanitize($_POST['senha']);
    
    if (fazerLogin($email, $senha)) {
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/';
        header("Location: " . $redirect);
        exit();
    } else {
        $erro = "Email ou senha incorretos";
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2>Login</h2>
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
    
    <p class="mt-3">Não tem uma conta? <a href="/cadastro.php">Cadastre-se</a></p>
</div>

<?php include 'includes/footer.php'; ?>