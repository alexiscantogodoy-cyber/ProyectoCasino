<?php
session_start();
include('conexion.php');

// 1. SEGURIDAD
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];

$mes_actual = "Abril";
$anio_actual = 2026;

// 2. LÓGICA PROFESOR
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

// 3. LÓGICA ADMIN (ERICA)
$total_normal = 0;
$total_hipo = 0;
$total_profesores_db = 0;

if ($rol == 'admin' || $rol == 'secretaria') {
    // Conteo de votos
    $sql_conteo = "SELECT opcion, COUNT(*) as total FROM elecciones WHERE mes = '$mes_actual' AND anio = '$anio_actual' GROUP BY opcion";
    $res_conteo = mysqli_query($conexion, $sql_conteo);
    while($row = mysqli_fetch_assoc($res_conteo)) {
        if($row['opcion'] == 'normal') $total_normal = $row['total'];
        if($row['opcion'] == 'hipocalorico') $total_hipo = $row['total'];
    }

    // Total de profes en la BD
    $sql_total_profes = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'profesor'";
    $res_total_profes = mysqli_query($conexion, $sql_total_profes);
    $row_total = mysqli_fetch_assoc($res_total_profes);
    $total_profesores_db = $row_total['total'];
    
    $votos_totales = $total_normal + $total_hipo;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Casino - Colegio</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        header { border-bottom: 2px solid #eee; margin-bottom: 20px; padding-bottom: 10px; display: flex; justify-content: space-between; }
        
        /* Tarjetas */
        .stats-grid { display: flex; gap: 15px; margin-bottom: 25px; }
        .stat-card { flex: 1; padding: 20px; border-radius: 10px; color: white; text-align: center; }
        .bg-blue { background: #3498db; }
        .bg-orange { background: #e67e22; }
        .bg-dark { background: #2c3e50; }
        .stat-number { font-size: 32px; font-weight: bold; display: block; }

        /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .tag { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; text-transform: uppercase; }
        .tag-normal { background: #3498db; }
        .tag-hipo { background: #e67e22; }
        .tag-pending { background: #e74c3c; }

        .btn { padding: 10px 20px; cursor: pointer; border: none; border-radius: 6px; font-weight: bold; transition: 0.3s; margin-top: 10px; }
        .btn-view { background: #2ecc71; color: white; }
        .btn-print { background: #95a5a6; color: white; }
        
        #seccion-pendientes { display: none; margin-top: 30px; border-top: 2px dashed #e74c3c; padding-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div>
            <h1>Hola, <?php echo $nombre; ?></h1>
            <span style="color: #3498db; font-weight: bold;"><?php echo strtoupper($rol); ?></span>
        </div>
        <div style="text-align: right;">
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">Cerrar Sesión</a>
        </div>
    </header>

    <main>
        <?php if ($rol == 'profesor'): ?>
            <?php if ($ya_voto): ?>
                <div style="background: #d4edda; color: #155724; padding: 30px; border-radius: 10px; text-align: center;">
                    <h2>✓ Selección Guardada</h2>
                    <p>Has elegido: <strong><?php echo strtoupper($eleccion_guardada); ?></strong></p>
                </div>
            <?php else: ?>
                <h3>Selección de Menú</h3>
                <form action="guardar_eleccion.php" method="POST">
                    <button type="submit" name="opcion" value="normal" class="btn bg-blue" style="color:white">MENÚ NORMAL</button>
                    <button type="submit" name="opcion" value="hipocalorico" class="btn bg-orange" style="color:white">MENÚ HIPOCALÓRICO</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
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
                    <span class="stat-number"><?php echo "$votos_totales / $total_profesores_db"; ?></span>
                    <span>Participación Total</span>
                </div>
            </div>

            <h3>Listado de Elecciones</h3>
            <table>
                <thead>
                    <tr><th>Nombre</th><th>Opción</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql_l = "SELECT u.nombre, e.opcion, e.fecha_registro FROM elecciones e JOIN usuarios u ON e.id_usuario = u.id WHERE e.mes = '$mes_actual' AND e.anio = '$anio_actual'";
                    $res_l = mysqli_query($conexion, $sql_l);
                    while($f = mysqli_fetch_assoc($res_l)): ?>
                        <tr>
                            <td><?php echo $f['nombre']; ?></td>
                            <td><span class="tag <?php echo ($f['opcion']=='normal'?'tag-normal':'tag-hipo'); ?>"><?php echo $f['opcion']; ?></span></td>
                            <td><?php echo $f['fecha_registro']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <button onclick="window.print()" class="btn btn-print">Imprimir para Cocina</button>
                <button onclick="document.getElementById('seccion-pendientes').style.display='block'" class="btn btn-view">Ver Profesores Pendientes</button>
            </div>

            <div id="seccion-pendientes">
                <h3 style="color: #e74c3c;">⚠️ Profesores que aún no votan</h3>
                <table>
                    <thead><tr><th>Nombre</th><th>RUT</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php
                        $sql_p = "SELECT nombre, rut FROM usuarios WHERE rol = 'profesor' AND id NOT IN (SELECT id_usuario FROM elecciones WHERE mes = '$mes_actual' AND anio = '$anio_actual')";
                        $res_p = mysqli_query($conexion, $sql_p);
                        if(mysqli_num_rows($res_p) > 0):
                            while($p = mysqli_fetch_assoc($res_p)): ?>
                                <tr>
                                    <td><?php echo $p['nombre']; ?></td>
                                    <td><?php echo $p['rut']; ?></td>
                                    <td><span class="tag tag-pending">Pendiente</span></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="3">¡Todos han votado! 👏</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>