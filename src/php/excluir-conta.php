<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../pages/login.php");
    exit();
}

$id = (int) $_SESSION["usuario_id"];

try {
    $conexao->begin_transaction();

    $stmtReservas = $conexao->prepare(
        "DELETE FROM reservas WHERE id_usuario = ?"
    );

    if (!$stmtReservas) {
        throw new Exception(
            "Não foi possível preparar a exclusão das reservas."
        );
    }

    $stmtReservas->bind_param("i", $id);

    if (!$stmtReservas->execute()) {
        throw new Exception(
            "Não foi possível excluir as reservas do usuário."
        );
    }

    $stmtReservas->close();

    $stmtUsuario = $conexao->prepare(
        "DELETE FROM usuarios WHERE id = ?"
    );

    if (!$stmtUsuario) {
        throw new Exception(
            "Não foi possível preparar a exclusão da conta."
        );
    }

    $stmtUsuario->bind_param("i", $id);

    if (!$stmtUsuario->execute()) {
        throw new Exception("Não foi possível excluir a conta.");
    }

    if ($stmtUsuario->affected_rows < 1) {
        $stmtUsuario->close();
        throw new Exception("A conta não foi encontrada.");
    }

    $stmtUsuario->close();

    $conexao->commit();
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    $conexao->close();

    header("Location: ../../public/index.php?contaExcluida=1");
    exit();
} catch (Throwable $erro) {
    try {
        $conexao->rollback();
    } catch (Throwable $ignorado) {}

    $conexao->close();

    header("Location: ../pages/dashboard.php?erroExcluir=1");
    exit();
}
?>
