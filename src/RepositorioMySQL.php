<?php
declare(strict_types=1);

require_once __DIR__ . '/ServicioVenta.php';

final class RepositorioMySQL
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listarProductos(): array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, nombre, precio_unitario, stock FROM producto ORDER BY nombre'
        );
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    public function obtenerProducto(int $id): ?array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, nombre, precio_unitario, stock FROM producto WHERE id = :id'
        );
        $sentencia->execute(['id' => $id]);
        $producto = $sentencia->fetch();

        return $producto === false ? null : $producto;
    }

    /**
     * $items contiene líneas con producto_id y cantidad.
     * El cliente se recibe para la interfaz, pero el esquema actual no lo almacena.
     */
    public function registrarVenta(string $cliente, array $items): int
    {
        $cantidades = ServicioVenta::agruparCantidades($items);
        foreach ($items as $item) {
            if (
                !isset($item['precio_unitario'])
                || !is_numeric($item['precio_unitario'])
            ) {
                throw new InvalidArgumentException('Los productos de la venta no son válidos.');
            }
        }

        $this->pdo->beginTransaction();

        try {
            $bloquearProducto = $this->pdo->prepare(
                'SELECT id, nombre, precio_unitario, stock
                 FROM producto
                 WHERE id = :id
                 FOR UPDATE'
            );
            $productos = [];

            foreach ($cantidades as $productoId => $cantidadTotal) {
                $bloquearProducto->execute(['id' => $productoId]);
                $producto = $bloquearProducto->fetch();

                if ($producto === false) {
                    throw new DomainException('No se encontró el producto seleccionado.');
                }

                foreach ($items as $item) {
                    if (
                        $item['producto_id'] === $productoId
                        && number_format((float) $item['precio_unitario'], 2, '.', '')
                            !== number_format((float) $producto['precio_unitario'], 2, '.', '')
                    ) {
                        throw new DomainException(
                            'El precio de ' . $producto['nombre']
                            . ' cambió. Vuelva a cargar la venta.'
                        );
                    }
                }

                if (!ServicioVenta::hayStock($cantidadTotal, (int) $producto['stock'])) {
                    throw new DomainException(
                        'Stock insuficiente para ' . $producto['nombre']
                        . '. Stock disponible: ' . $producto['stock'] . '.'
                    );
                }

                $productos[$productoId] = $producto;
            }

            $lineas = [];
            foreach ($items as $item) {
                $lineas[] = [
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                ];
            }

            $resumen = ServicioVenta::calcularResumen($lineas);
            $subtotal = $resumen['subtotal'];
            $descuento = $resumen['descuento'];
            $total = $resumen['total'];

            $insertarVenta = $this->pdo->prepare(
                'INSERT INTO venta (subtotal, descuento, total)
                 VALUES (:subtotal, :descuento, :total)'
            );
            $insertarVenta->execute([
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'descuento' => number_format($descuento, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
            ]);
            $ventaId = (int) $this->pdo->lastInsertId();

            $descontarStock = $this->pdo->prepare(
                'UPDATE producto
                 SET stock = stock - :cantidad_restar
                 WHERE id = :id AND stock >= :cantidad_minima'
            );
            $insertarDetalle = $this->pdo->prepare(
                'INSERT INTO detalle_venta
                    (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                 VALUES
                    (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)'
            );

            foreach ($productos as $productoId => $producto) {
                $descontarStock->execute([
                    'cantidad_restar' => $cantidades[$productoId],
                    'id' => $productoId,
                    'cantidad_minima' => $cantidades[$productoId],
                ]);

                if ($descontarStock->rowCount() !== 1) {
                    throw new DomainException(
                        'Stock insuficiente para ' . $producto['nombre']
                        . '. Stock disponible: ' . $producto['stock'] . '.'
                    );
                }
            }

            foreach ($lineas as $linea) {
                $subtotalLinea = ServicioVenta::calcularSubtotal([[
                    'precio_unitario' => $linea['precio_unitario'],
                    'cantidad' => $linea['cantidad'],
                ]]);
                $insertarDetalle->execute([
                    'venta_id' => $ventaId,
                    'producto_id' => $linea['producto_id'],
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => number_format($linea['precio_unitario'], 2, '.', ''),
                    'subtotal' => number_format($subtotalLinea, 2, '.', ''),
                ]);
            }

            $this->pdo->commit();

            return $ventaId;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $error;
        }
    }
}
