<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/ServicioVenta.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$cliente = '';
$error = null;
$resultado = null;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new InvalidArgumentException('Envíe el formulario para registrar una venta.');
    }

    $clienteRecibido = $_POST['cliente'] ?? null;
    if (!is_string($clienteRecibido) || trim($clienteRecibido) === '') {
        throw new InvalidArgumentException('Ingrese el nombre del cliente.');
    }
    $cliente = trim($clienteRecibido);

    $ids = $_POST['producto_id'] ?? null;
    $cantidadesRecibidas = $_POST['cantidad'] ?? null;
    if (
        !is_array($ids)
        || !is_array($cantidadesRecibidas)
        || count($ids) !== count($cantidadesRecibidas)
        || !ServicioVenta::validarNumeroItems(count($ids))
    ) {
        throw new InvalidArgumentException('La venta debe tener entre 1 y 100 productos.');
    }

    $repositorio = new RepositorioMySQL(conectar());
    $items = [];
    $lineasParaCalculo = [];

    foreach ($ids as $indice => $idRecibido) {
        $cantidadRecibida = $cantidadesRecibidas[$indice] ?? null;
        if (
            !is_string($idRecibido)
            || !preg_match('/^[1-9][0-9]*$/D', $idRecibido)
        ) {
            throw new InvalidArgumentException('La cantidad debe ser un entero mayor que cero.');
        }

        $productoId = filter_var($idRecibido, FILTER_VALIDATE_INT);
        if ($productoId === false) {
            throw new InvalidArgumentException('El producto o la cantidad ingresada no son válidos.');
        }
        $cantidad = ServicioVenta::parsearCantidad($cantidadRecibida);

        $producto = $repositorio->obtenerProducto($productoId);
        if ($producto === null) {
            throw new InvalidArgumentException('No se encontró uno de los productos seleccionados.');
        }
        if (!ServicioVenta::hayStock($cantidad, (int) $producto['stock'])) {
            throw new DomainException(
                'Stock insuficiente para ' . $producto['nombre']
                . '. Stock disponible: ' . $producto['stock'] . '.'
            );
        }

        $items[] = [
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'precio_unitario' => (float) $producto['precio_unitario'],
        ];
        $lineasParaCalculo[] = [
            'precio_unitario' => (float) $producto['precio_unitario'],
            'cantidad' => $cantidad,
        ];
    }

    $resumen = ServicioVenta::calcularResumen($lineasParaCalculo);
    $ventaId = $repositorio->registrarVenta($cliente, $items);
    $resultado = [
        'id' => $ventaId,
        'subtotal' => $resumen['subtotal'],
        'descuento' => $resumen['descuento'],
        'total' => $resumen['total'],
    ];
} catch (DomainException | InvalidArgumentException $exception) {
    $error = $exception->getMessage();
} catch (PDOException $exception) {
    $error = 'La venta no se completó. Verifique la conexión e inténtelo nuevamente.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de venta | Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bodega Doña Rosa</a>
    </div>
</nav>
<main class="container py-5">
    <?php if ($error !== null): ?>
        <div class="alert alert-danger" role="alert">
            <h1 class="h4">No se pudo registrar la venta</h1>
            <p class="mb-0"><?= escapar($error) ?></p>
        </div>
    <?php elseif ($resultado !== null): ?>
        <div class="alert alert-success" role="alert">
            <h1 class="h4">Venta registrada correctamente</h1>
            <p>Cliente: <?= escapar($cliente) ?></p>
            <p>Número de venta: <?= (int) $resultado['id'] ?></p>
            <p>Subtotal: S/ <?= number_format($resultado['subtotal'], 2) ?></p>
            <p>Descuento: S/ <?= number_format($resultado['descuento'], 2) ?></p>
            <p class="mb-0">Total: <strong>S/ <?= number_format($resultado['total'], 2) ?></strong></p>
        </div>
    <?php endif; ?>
    <a class="btn btn-success me-2" href="venta.php">Registrar otra venta</a>
    <a class="btn btn-outline-secondary" href="index.php">Volver al inventario</a>
</main>
</body>
</html>
