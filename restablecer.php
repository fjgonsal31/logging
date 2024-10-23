<?php


include_once 'data/usuariobd.php';

$usuariobd = new UsuarioBD();


// Verificar si se ha proporcionado un token
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_password'])) {
        $resultado = $usuariobd->reestablecerPassword($token, $_POST['nueva_password']);
        $mensaje = $resultado['message'];
    }    
}  

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="container">
        <h1>Restablecer Contraseña</h1>
        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
            <?php if ($resultado['success']): ?>
                <a href="index.php" class="boton">Ir a Iniciar Sesión</a>
            <?php endif; ?>
        <?php else: ?>
            <form method="post">
                <input type="password" name="nueva_password" required placeholder="Nueva contraseña">
                <input type="password" name="confirmar_password" required placeholder="Confirmar nueva contraseña">
                <input type="submit" value="Restablecer Contraseña">
            </form>
        <?php endif; ?>
    </div>
    <script src="js/restablecer.js"></script>
</body>
</html>