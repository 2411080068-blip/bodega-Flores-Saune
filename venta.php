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
    <title>Registrar venta | Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bodega Doña Rosa</a>
        <a class="btn btn-outline-light" href="index.php">Ver inventario</a>
    </div>
</nav>
<main class="container py-5">
    <h1 class="h2 mb-4">Registrar una venta</h1>
    <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert"><?= escapar($error) ?></div>
    <?php elseif ($productos === []): ?>
        <div class="alert alert-warning" role="alert">No hay productos disponibles para seleccionar.</div>
    <?php else: ?>
        <form action="guardar_venta.php" method="post" class="card card-body shadow-sm">
            <div class="mb-4">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="cliente" name="cliente" maxlength="100" required>
                <div class="form-text">El nombre se mostrará en la confirmación; no se guarda en la base de datos.</div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 mb-0">Productos</h2>
                <button type="button" class="btn btn-outline-success btn-sm" id="agregar-linea">
                    Agregar producto
                </button>
            </div>
            <div id="lineas" class="vstack gap-3">
                <div class="row g-2 align-items-end linea-producto">
                    <div class="col-md-8">
                        <label class="form-label">Producto</label>
                        <select class="form-select" name="producto_id[]" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option
                                    value="<?= (int) $producto['id'] ?>"
                                    <?= (int) $producto['stock'] === 0 ? 'disabled' : '' ?>
                                >
                                    <?= escapar((string) $producto['nombre']) ?>
                                    — S/ <?= number_format((float) $producto['precio_unitario'], 2) ?>
                                    (stock: <?= (int) $producto['stock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" name="cantidad[]" min="1" step="1" required>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger quitar-linea" aria-label="Quitar producto">
                            Quitar
                        </button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success mt-4">Confirmar venta</button>
        </form>
        <template id="plantilla-linea">
            <div class="row g-2 align-items-end linea-producto">
                <div class="col-md-8">
                    <label class="form-label">Producto</label>
                    <select class="form-select" name="producto_id[]" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option
                                value="<?= (int) $producto['id'] ?>"
                                <?= (int) $producto['stock'] === 0 ? 'disabled' : '' ?>
                            >
                                <?= escapar((string) $producto['nombre']) ?>
                                — S/ <?= number_format((float) $producto['precio_unitario'], 2) ?>
                                (stock: <?= (int) $producto['stock'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" name="cantidad[]" min="1" step="1" required>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="button" class="btn btn-outline-danger quitar-linea" aria-label="Quitar producto">
                        Quitar
                    </button>
                </div>
            </div>
        </template>
    <?php endif; ?>
</main>
<script>
const lineas = document.getElementById('lineas');
const botonAgregar = document.getElementById('agregar-linea');
if (lineas && botonAgregar) {
    botonAgregar.addEventListener('click', () => {
        if (lineas.children.length >= 100) {
            alert('La venta permite como máximo 100 productos.');
            return;
        }
        lineas.append(document.getElementById('plantilla-linea').content.cloneNode(true));
    });
    lineas.addEventListener('click', (evento) => {
        if (evento.target.classList.contains('quitar-linea') && lineas.children.length > 1) {
            evento.target.closest('.linea-producto').remove();
        }
    });
}
</script>
</body>
</html>
