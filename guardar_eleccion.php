<?php
session_start();
include('conexion.php');

// 1. Seguridad: Si no está logueado, fuera.
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// 2. Capturar datos
$id_usuario = $_SESSION['id_usuario'];
$opcion = $_POST['opcion']; // 'normal' o 'hipocalorico'
$mes_actual = "Abril";
$anio_actual = 2026;

// 3. Validación de Negocio: ¿Ya eligió este mes?
// Como informático, siempre debemos prever que el usuario intente votar dos veces.
$check_sql = "SELECT id FROM elecciones WHERE id_usuario = '$id_usuario' AND mes = '$mes_actual' AND anio = '$anio_actual'";
$check_res = mysqli_query($conexion, $check_sql);

if (mysqli_num_rows($check_res) > 0) {
    // Si ya existe un registro, mandamos un aviso
    echo "<script>alert('Ya registraste tu opción para este mes. No se permiten cambios.'); window.location.href='dashboard.php';</script>";
} else {
    // 4. Insertar la elección
    $insert_sql = "INSERT INTO elecciones (id_usuario, mes, anio, opcion) 
                   VALUES ('$id_usuario', '$mes_actual', '$anio_actual', '$opcion')";
    
    if (mysqli_query($conexion, $insert_sql)) {
        echo "<script>alert('¡Elección guardada con éxito!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error al guardar: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>