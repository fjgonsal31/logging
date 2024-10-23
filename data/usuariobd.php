<?php

include_once 'config.php';
include_once 'enviarCorreos.php';

class UsuarioBD{

    private $conn;
    private $url = 'https://antoniapuertas.com/09-php-login';

    public function __construct(){
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if($this->conn->connect_error){
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }



public function enviarCorreoSimulado($destinatario, $asunto, $mensaje) {
        $archivo_log = __DIR__ . '/correos_simulados.log';
        $contenido = "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $contenido .= "Para: $destinatario\n";
        $contenido .= "Asunto: $asunto\n";
        $contenido .= "Mensaje:\n$mensaje\n";
        $contenido .= "----------------------------------------\n\n";
        
        file_put_contents($archivo_log, $contenido, FILE_APPEND);
}

//Función para enviar correos
//     public function enviarCorreo($destinatario, $asunto, $mensaje) {
//         if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
//             // Estamos en entorno local, usamos la simulación
//             $this->enviarCorreoSimulado($destinatario, $asunto, $mensaje);
//         } else {
//             // Estamos en producción, usamos mail() real
//             mail($destinatario, $asunto, $mensaje);
//         }
// }



// Función para generar un token aleatorio
public function generarToken() {
    return bin2hex(random_bytes(32));
}

public function registrarUsuario($email, $password, $verificado = 0){
    $password = password_hash($password, PASSWORD_DEFAULT);
    $token = $this->generarToken();
    
    $sql = "INSERT INTO usuarios (email, password, token, verificado) VALUES (?, ?, ?, 0)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sss", $email, $password, $token);
    
    if ($stmt->execute()) {
        $mensaje = "Por favor, verifica tu cuenta haciendo clic en este enlace: $this->url/verificar.php?token=$token";
        return Correo::enviarCorreo($email, "Cliente" ,"Verificación de cuenta", $mensaje);
          
    } else {
        return ["success" => false, "message" => "Error en el registro: " . $stmt->error];
    }
}

public function inicioSesion($email, $password){
    $sql = "SELECT id, email, password, verificado FROM usuarios WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $resultado = ["success" => 'info', "message" => "Usuario no encontrado"];

    if ($row = $result->fetch_assoc()) {
        if ($row['verificado'] == 1 && password_verify($password, $row['password'])) {
            $resultado = ["success" => 'success', "message"=> "Has iniciado sesión con " . $email, "id" => $row['id']];
            //actualiza la fecha del último inicio de sesión
            $sql = "UPDATE usuarios SET ultima_conexion = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
        } else {
            $resultado = ["success" => 'error', "message" => "Credenciales inválidas o cuenta no verificada"];
        }
    }

    return $resultado;
}

public function recuperarPassword($email){

    //verificamos si existe el email en la base de datos
    $check_sql = "SELECT id FROM usuarios WHERE email = ?";
    $check_stmt = $this->conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    $resultado = ["success" => 'info', "message" => "El correo electrónico proporcionado no corresponde a ningún usuario registrado."];

    if($result->num_rows > 0){
        $token = $this->generarToken();

        $sql = "UPDATE usuarios SET token_recuperacion = ? WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $token, $email);
        
        if ($stmt->execute()) {
            $mensaje = "Para restablecer tu contraseña, haz clic en este enlace:  $this->url/restablecer.php?token=$token";
            Correo::enviarCorreo($email, "Cliente" ,"Recuperación de contraseña", $mensaje);
            $resultado = ["success" => 'success', "message" => "Se ha enviado un enlace de recuperación a tu correo."];
        }else{
            $resultado = ["success" => 'error', "message" => "Error al procesar la solicitud de recuperación."];
        }
    }

    return $resultado;
}

public function verificarToken($token){

        // Buscar el usuario con el token proporcionado
        $sql = "SELECT id FROM usuarios WHERE token = ? AND verificado = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Token válido, actualizar el estado de verificación del usuario
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
            
            $update_sql = "UPDATE usuarios SET verificado = 1, token = NULL WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user_id);

            $resultado = ["success" => 'error', "message" => "Hubo un error al verificar tu cuenta. Por favor, intenta nuevamente más tarde."];

            if ($update_stmt->execute()) {
                $resultado = ["success" => 'success', "message" => "Tu cuenta ha sido verificada exitosamente. Ahora puedes iniciar sesión."]; ;
            } 
        }
        return $resultado;
}

public function reestablecerPassword($token, $nueva_password){

    $password = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Buscar el usuario con el token proporcionado
        $sql = "SELECT id FROM usuarios WHERE token_recuperacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        $resultado = ["success" => 'info', "message" => "El token de recuperación no es válido o ya ha sido utilizado."];
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
            
            // Actualizar la contraseña y eliminar el token de recuperación
            $update_sql = "UPDATE usuarios SET password = ?, token_recuperacion = NULL WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("si", $password, $user_id);
            

            if ($update_stmt->execute()) {
                $resultado = ["success" => 'success', "message" => "Tu contraseña ha sido actualizada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña."];
            } else {
                $resultado = ["success" => 'error', "message" => "Hubo un error al actualizar tu contraseña. Por favor, intenta nuevamente más tarde."];
            }   
        }

        return $resultado;
}


}