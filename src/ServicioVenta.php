<?php
declare(strict_types=1);

final class ServicioVenta
{
    public static function validarNumeroItems(int $numeroItems): bool
    {
        return $numeroItems >= 1 && $numeroItems <= 100;
    }

    public static function validarCantidad(mixed $cantidad): bool
    {
        return is_int($cantidad) && $cantidad > 0;
    }

    public static function parsearCantidad(mixed $cantidad): int
    {
        if (!is_string($cantidad) || !preg_match('/^[0-9]+$/D', $cantidad)) {
            throw new InvalidArgumentException('La cantidad debe ser un entero mayor que cero.');
        }

        $cantidadEntera = filter_var($cantidad, FILTER_VALIDATE_INT);
        if ($cantidadEntera === false) {
            throw new InvalidArgumentException(
                'El producto o la cantidad ingresada no son válidos.'
            );
        }

        if (!self::validarCantidad($cantidadEntera)) {
            throw new InvalidArgumentException('La cantidad debe ser un entero mayor que cero.');
        }

        return $cantidadEntera;
    }

    /**
     * Agrupa cantidades repetidas del mismo producto para validar el stock total.
     */
    public static function agruparCantidades(array $items): array
    {
        if (!self::validarNumeroItems(count($items))) {
            throw new InvalidArgumentException('La venta debe tener entre 1 y 100 productos.');
        }

        $unidadesPorProducto = [];
        foreach ($items as $item) {
            if (
                !isset($item['producto_id'], $item['cantidad'])
                || !is_int($item['producto_id'])
                || !is_int($item['cantidad'])
                || $item['producto_id'] < 1
                || !self::validarCantidad($item['cantidad'])
            ) {
                throw new InvalidArgumentException('Los productos de la venta no son válidos.');
            }

            $unidadesPorProducto[$item['producto_id']] =
                ($unidadesPorProducto[$item['producto_id']] ?? 0)
                + $item['cantidad'];
        }

        ksort($unidadesPorProducto, SORT_NUMERIC);
        return $unidadesPorProducto;
    }

    public static function hayStock(int $cantidad, int $stock): bool
    {
        return $cantidad > 0 && $stock >= 0 && $cantidad <= $stock;
    }

    /**
     * Cada línea debe contener precio_unitario y cantidad.
     */
    public static function calcularSubtotal(array $items): float
    {
        $subtotalEnCentimos = 0;

        foreach ($items as $item) {
            if (
                !isset($item['precio_unitario'], $item['cantidad'])
                || !is_numeric($item['precio_unitario'])
                || !is_int($item['cantidad'])
                || !self::validarCantidad($item['cantidad'])
            ) {
                throw new InvalidArgumentException('Los productos de la venta no son válidos.');
            }

            $precioEnCentimos = self::convertirMontoACentimos(
                (float) $item['precio_unitario']
            );
            $subtotalEnCentimos += $precioEnCentimos * $item['cantidad'];
        }

        return $subtotalEnCentimos / 100;
    }

    public static function calcularResumen(array $items): array
    {
        $subtotal = self::calcularSubtotal($items);

        return [
            'subtotal' => $subtotal,
            'descuento' => self::calcularDescuento($subtotal),
            'total' => self::calcularTotal($subtotal),
        ];
    }

    public static function calcularDescuento(float $subtotal): float
    {
        $subtotalEnCentimos = self::convertirMontoACentimos($subtotal);
        $totalEnCentimos = self::calcularTotalEnCentimos($subtotalEnCentimos);
        return ($subtotalEnCentimos - $totalEnCentimos) / 100;
    }

    public static function calcularTotal(float $subtotal): float
    {
        $subtotalEnCentimos = self::convertirMontoACentimos($subtotal);
        return self::calcularTotalEnCentimos($subtotalEnCentimos) / 100;
    }

    private static function calcularTotalEnCentimos(int $subtotalEnCentimos): int
    {
        if ($subtotalEnCentimos <= 10000) {
            return $subtotalEnCentimos;
        }

        return intdiv($subtotalEnCentimos * 190 + 100, 200);
    }

    private static function convertirMontoACentimos(float $monto): int
    {
        if (!is_finite($monto) || $monto < 0) {
            throw new InvalidArgumentException('El monto debe ser un número no negativo.');
        }

        return (int) str_replace('.', '', number_format($monto, 2, '.', ''));
    }
}
