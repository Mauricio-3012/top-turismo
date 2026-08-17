<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// affected_rows confirma que a linha foi de fato apagada, não só que a query rodou
if ($stmt->affected_rows > 0) {
    $stmt->close();
    $conexao->close();

    // Limpeza completa da sessão (igual o logout.php faz)
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    header("Location: ../index.php?contaExcluida=1");
    exit();
} else {
    $stmt->close();
    $conexao->close();
    header("Location: ../pages/dashboard.php?erroExcluir=1");
    exit();
}
?>