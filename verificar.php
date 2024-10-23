<?php

include_once 'data/usuariobd.php';

$usuariobd = new UsuarioBD();


// Verificar si se ha proporcionado un token
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $resultado = $usuariobd->verificarToken($token);
    $mensaje = $resultado['message'];
    
}  
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Cuenta</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="container">
        <h1>Verificación de Cuenta</h1>
        <p class="mensaje"><?php echo $mensaje; ?></p>
        <a href="index.php" class="boton">Ir a Iniciar Sesión</a>
    </div>
</body>
</html>