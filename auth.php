<?php
session_start();


function estaLogado() {
    return isset($_SESSION['usuario_id']);
}


function protegerPagina() {
    if (!estaLogado()) {
        header("Location: /login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}


function fazerLogin($email, $senha) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        return true;
    }
    
    return false;
}


function fazerLogout() {
    session_unset();
    session_destroy();
    header("Location: /login.php");
    exit();
}

?>
