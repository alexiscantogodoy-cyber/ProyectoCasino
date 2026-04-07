<?php
session_start();
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rut = mysqli_real_escape_string($conexion, $_POST['rut']);
    $password = $_POST['password'];

    $query = "SELECT * FROM usuarios WHERE rut = '$rut'";
    $resultado = mysqli_query($conexion, $query);

    if (mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        
        if ($password == $usuario['password']) {
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Si llegó aquí es porque falló: Guardamos el error en la SESIÓN
    $_SESSION['error_login'] = "RUT o contraseña incorrectos.";
    header("Location: login.php");
    exit();
}