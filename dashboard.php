<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
$mes_actual = "Abril";
$anio_actual = 2026;

// --- NUEVA LÓGICA: Verificar si ya eligió ---
$query_check = "SELECT opcion FROM elecciones WHERE id_usuario = '$id_usuario' AND mes = '$mes_actual' AND anio = '$anio_actual'";
$res_check = mysqli_query($conexion, $query_check);
$ya_votó = mysqli_num_rows($res_check) > 0;

if ($ya_votó) {
    $datos_voto = mysqli_fetch_assoc($res_check);
    $eleccion_guardada = $datos_voto['opcion'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Casino</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h1>Bienvenido, <?php echo $nombre; ?></h1>

    <?php if ($rol == 'profesor'): ?>
        <section>
            <h2>Selección de Menú - <?php echo $mes_actual; ?></h2>

            <?php if ($ya_votó): ?>
                <div style="background: #2ecc71; color: white; padding: 20px; border-radius: 10px;">
                    <h3>¡Registro Completado!</h3>
                    <p>Ya has seleccionado el menú: <strong><?php echo strtoupper($eleccion_guardada); ?></strong></p>
                    <p>Recuerda que esta elección es para todo el mes.</p>
                </div>
            <?php else: ?>
                <p>Aún no has seleccionado tu menú. Por favor elige una opción:</p>
                <form action="guardar_eleccion.php" method="POST">
                    <button type="submit" name="opcion" value="normal" class="btn">Menú Normal</button>
                    <button type="submit" name="opcion" value="hipocalorico" class="btn btn-hipo">Menú Hipocalórico</button>
                </form>
            <?php endif; ?>
        </section>

    <?php else: ?>
        <h2>Panel de Administración</h2>
        <p>Aquí puedes ver quién ha votado.</p>
        <?php endif; ?>

    <br>
    <a href="logout.php">Cerrar Sesión</a>
</body>
</html>