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

// Configuración del período actual
$mes_actual = "Abril";
$anio_actual = 2026;

// 2. LÓGICA PARA EL PROFESOR: Verificar si ya votó
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

// 3. LÓGICA PARA ADMIN: Obtener contadores (Resumen rápido)
$total_normal = 0;
$total_hipo = 0;
if ($rol == 'admin' || $rol == 'secretaria') {
    $sql_conteo = "SELECT opcion, COUNT(*) as total FROM elecciones WHERE mes = '$mes_actual' AND anio = '$anio_actual' GROUP BY opcion";
    $res_conteo = mysqli_query($conexion, $sql_conteo);
    while($row = mysqli_fetch_assoc($res_conteo)) {
        if($row['opcion'] == 'normal') $total_normal = $row['total'];
        if($row['opcion'] == 'hipocalorico') $total_hipo = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Casino - Colegio</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        header { border-bottom: 2px solid #eee; margin-bottom: 20px; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        h1 { color: #2c3e50; margin: 0; font-size: 24px; }
        .rol-badge { background: #ebf5fb; color: #2980b9; padding: 5px 12px; border-radius: 20px; font-size: 14px; font-weight: bold; }
        
        /* Tarjetas de Resumen */
        .stats-grid { display: flex; gap: 15px; margin-bottom: 25px; }
        .stat-card { flex: 1; padding: 20px; border-radius: 10px; color: white; text-align: center; }
        .bg-blue { background: #3498db; }
        .bg-orange { background: #e67e22; }
        .bg-dark { background: #2c3e50; }
        .stat-number { font-size: 32px; font-weight: bold; display: block; }
        
        /* Botones y Alertas */
        .btn { padding: 12px 25px; cursor: pointer; color: white; border: none; border-radius: 6px; font-weight: bold; transition: 0.3s; }
        .btn-normal { background: #3498db; }
        .btn-hipo { background: #e67e22; }
        .btn:hover { opacity: 0.8; }
        .alert-success { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; border: 1px solid #c3e6cb; text-align: center; }
        
        /* Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #666; }
        .tag { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; text-transform: uppercase; }
        .tag-normal { background: #3498db; }
        .tag-hipo { background: #e67e22; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div>
            <h1>Hola, <?php echo $nombre; ?></h1>
            <span class="rol-badge"><?php echo strtoupper($rol); ?></span>
        </div>
        <div style="text-align: right;">
            <small><?php echo "$mes_actual $anio_actual"; ?></small><br>
            <a href="logout.php" style="color: #e74c3c; text-decoration: none; font-size: 14px;">Cerrar Sesión</a>
        </div>
    </header>

    <main>
        <?php if ($rol == 'profesor'): ?>
            <?php if ($ya_voto): ?>
                <div class="alert-success">
                    <h2>¡Todo listo!</h2>
                    <p>Has seleccionado el menú: <strong><?php echo strtoupper($eleccion_guardada); ?></strong></p>
                    <p>Tu elección ha sido registrada correctamente para el casino este mes.</p>
                </div>
            <?php else: ?>
                <h3>Selección de Colación Mensual</h3>
                <p>Por favor, elige tu opción para este período. Recuerda que es una elección única.</p>
                <form action="guardar_eleccion.php" method="POST" style="margin-top: 20px;">
                    <button type="submit" name="opcion" value="normal" class="btn btn-normal">MENÚ NORMAL</button>
                    <button type="submit" name="opcion" value="hipocalorico" class="btn btn-hipo">MENÚ HIPOCALÓRICO</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <h3>Resumen de Pedidos - Cocina</h3>
            
            <div class="stats-grid">
                <div class="stat-card bg-blue">
                    <span class="stat-number"><?php echo $total_normal; ?></span>
                    <span>Normales</span>
                </div>
                <div class="stat-card bg-orange">
                    <span class="stat-number"><?php echo $total_hipo; ?></span>
                    <span>Hipocalóricos</span>
                </div>
                <div class="stat-card bg-dark">
                    <span class="stat-number"><?php echo ($total_normal + $total_hipo); ?></span>
                    <span>Total Pedidos</span>
                </div>
            </div>

            <h3>Detalle de Inscritos</h3>
            <?php
            $sql_listado = "SELECT u.nombre, e.opcion, e.fecha_registro 
                            FROM elecciones e 
                            JOIN usuarios u ON e.id_usuario = u.id 
                            WHERE e.mes = '$mes_actual' AND e.anio = '$anio_actual'
                            ORDER BY e.fecha_registro DESC";
            $res_listado = mysqli_query($conexion, $sql_listado);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Nombre del Docente</th>
                        <th>Opción Elegida</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res_listado) > 0): ?>
                        <?php while($f = mysqli_fetch_assoc($res_listado)): ?>
                            <tr>
                                <td><?php echo $f['nombre']; ?></td>
                                <td>
                                    <span class="tag <?php echo ($f['opcion'] == 'normal' ? 'tag-normal' : 'tag-hipo'); ?>">
                                        <?php echo $f['opcion']; ?>
                                    </span>
                                </td>
                                <td><?php echo date("d/m/Y H:i", strtotime($f['fecha_registro'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; padding: 20px; color: #999;">No hay registros para mostrar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br>
            <button onclick="window.print()" class="btn btn-normal" style="background: #7f8c8d;">Imprimir Reporte para Cocina</button>
        <?php endif; ?>
    </main>
</div>

</body>
</html>