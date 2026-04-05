<?php
// Datos de tu servidor local (XAMPP)
$host = "localhost";
$user = "root";     // Usuario por defecto de XAMPP
$pass = "";         // En XAMPP viene sin contraseña por defecto
$db   = "casino_db"; // El nombre de la base de datos que creamos

// Creamos la conexión
$conexion = mysqli_connect($host, $user, $pass, $db);

// Verificamos si funcionó
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Opcional: Configurar caracteres para que funcionen las tildes (ej: "Menú")
mysqli_set_charset($conexion, "utf8");
?>