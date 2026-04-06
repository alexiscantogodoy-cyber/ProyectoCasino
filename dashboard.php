<?php
session_start();
include('conexion.php');

// 1. SEGURIDAD: Si no hay sesión, al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];

// Configuración del mes actual para el sistema
$mes_actual = "Abril";
$anio_actual = 2026;

// 2. LÓGICA DE PROFE: Verificar si ya registró su colación
$ya_voto = false;
$eleccion_guardada = "";

if ($rol == 'profesor') {
    $check = "SELECT opcion FROM elecciones WHERE id_usuario = '$id_usuario' AND mes = '$mes_actual' AND anio = '$anio_actual'";
    $res_check = mysqli_query($conexion, $check);
    if (mysqli_num_rows($res_check) > 0) {
        $ya_voto = true;
        $datos = mysqli_fetch_assoc($res_check);
        $eleccion_guardada = $datos['opcion'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Casino - Colegio</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .btn { padding: 10px 20px; cursor: pointer; background: #3498db; color: white; border: none; border-radius: 5px; margin-right: 10px; }
        .btn-hipo { background: #e67e22; }
        .mensaje-exito { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Bienvenido, <?php echo $nombre; ?></h1>
        <p>Rol: <strong><?php echo ucfirst($rol); ?></strong> | Período: <?php echo "$mes_actual $anio_actual"; ?></p>
        <hr>
    </header>

    <main>
        <?php if ($rol == 'profesor'): ?>
            <section>
                <?php if ($ya_voto): ?>
                    <div class="mensaje-exito">
                        <h3>✓ Selección Registrada</h3>
                        <p>Ya has elegido el menú: <strong><?php echo strtoupper($eleccion_guardada); ?></strong></p>
                        <p>Gracias por confirmar tu asistencia al casino para este mes.</p>
                    </div>
                <?php else: ?>
                    <h3>Selecciona tu menú mensual:</h3>
                    <p>Recuerda que esta elección no se puede modificar una vez enviada.</p>
                    <form action="guardar_eleccion.php" method="POST">
                        <button type="submit" name="opcion" value="normal" class="btn">Menú Normal</button>
                        <button type="submit" name="opcion" value="hipocalorico" class="btn btn-hipo">Menú Hipocalórico</button>
                    </form>
                <?php endif; ?>
            </section>

        <?php else: ?>
            <section>
                <h3>Listado de Profesores Inscritos</h3>
                <?php
                $sql_admin = "SELECT u.nombre, e.opcion, e.fecha_registro 
                              FROM elecciones e 
                              JOIN usuarios u ON e.id_usuario = u.id 
                              WHERE e.mes = '$mes_actual' AND e.anio = '$anio_actual'
                              ORDER BY e.fecha_registro DESC";
                $res_admin = mysqli_query($conexion, $sql_admin);
                ?>

                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Opción</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($res_admin) > 0): ?>
                            <?php while($f = mysqli_fetch_assoc($res_admin)): ?>
                                <tr>
                                    <td><?php echo $f['nombre']; ?></td>
                                    <td><?php echo strtoupper($f['opcion']); ?></td>
                                    <td><?php echo $f['fecha_registro']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No hay registros todavía.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br>
                <button onclick="window.print()" class="btn">Imprimir Listado</button>
            </section>
        <?php endif; ?>
    </main>

    <footer style="margin-top: 30px;">
        <a href="logout.php" style="color: red;">Cerrar Sesión Segura</a>
    </footer>
</div>

</body>
</html>