<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$error = null;
$productos = [];

try {
    $repositorio = new RepositorioMySQL(conectar());
    $productos = $repositorio->listarProductos();
} catch (PDOException $exception) {
    $error = 'No se pudo conectar con la base de datos. Verifique la configuración de MySQL.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario | Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bodega Doña Rosa</a>
        <a class="btn btn-outline-light" href="venta.php">Registrar venta</a>
    </div>
</nav>
<main class="container py-5">
    <h1 class="h2 mb-4">Inventario de productos</h1>
    <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert"><?= escapar($error) ?></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered bg-white align-middle">
                <thead class="table-success">
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Precio unitario</th>
                    <th scope="col">Stock</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= escapar((string) $producto['nombre']) ?></td>
                        <td>S/ <?= number_format((float) $producto['precio_unitario'], 2) ?></td>
                        <td>
                            <?php if ((int) $producto['stock'] === 0): ?>
                                <span class="badge text-bg-danger">Agotado</span>
                            <?php else: ?>
                                <?= (int) $producto['stock'] ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($productos === []): ?>
                    <tr><td colspan="3" class="text-center">No hay productos registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
