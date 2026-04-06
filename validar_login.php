<?php
session_start();
include('conexion.php'); // Asegúrate de tener este archivo creado

$rut = $_POST['rut'];
$password = $_POST['password'];

$query = "SELECT * FROM usuarios WHERE rut = '$rut'";
$resultado = mysqli_query($conexion, $query);

if (mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
    
    // Verificamos la contraseña (asumiendo que está encriptada)
if ($password == $usuario['password']) {
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];
        
        header("Location: dashboard.php"); // Si todo está bien, va al panel
    } else {
        echo "Contraseña incorrecta";
    }
} else {
    echo "El RUT no está registrado";
}
?>