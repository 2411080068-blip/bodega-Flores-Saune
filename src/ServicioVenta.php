<?php
declare(strict_types=1);

final class ServicioVenta
{
    public static function validarNumeroItems(int $cantidad): bool
    {
        return $cantidad >= 1 && $cantidad <= 100;
    }

    public static function validarCantidad(int $cantidad): bool
    {
        return $cantidad > 0;
    }

    public static function parsearCantidad(mixed $cantidad): int
    {
        if (!is_string($cantidad) || !preg_match('/^[0-9]+$/D', $cantidad)) {
            throw new InvalidArgumentException('La cantidad debe ser un entero mayor que cero.');
        }

        $valor = filter_var($cantidad, FILTER_VALIDATE_INT);
        if ($valor === false) {
            throw new InvalidArgumentException(
                'El producto o la cantidad ingresada no son válidos.'
            );
        }

        if (!self::validarCantidad($valor)) {
            throw new InvalidArgumentException('La cantidad debe ser un entero mayor que cero.');
        }

        return $valor;
    }

    /**
     * Agrupa cantidades repetidas del mismo producto para validar el stock total.
     */
    public static function agruparCantidades(array $items): array
    {
        if (!self::validarNumeroItems(count($items))) {
            throw new InvalidArgumentException('La venta debe tener entre 1 y 100 productos.');
        }

        $cantidades = [];
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

            $cantidades[$item['producto_id']] = ($cantidades[$item['producto_id']] ?? 0)
                + $item['cantidad'];
        }

        ksort($cantidades, SORT_NUMERIC);
        return $cantidades;
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
        $subtotalCentimos = 0;

        foreach ($items as $item) {
            if (
                !isset($item['precio_unitario'], $item['cantidad'])
                || !is_numeric($item['precio_unitario'])
                || !is_int($item['cantidad'])
                || !self::validarCantidad($item['cantidad'])
            ) {
                throw new InvalidArgumentException('Los productos de la venta no son válidos.');
            }

            $precioCentimos = self::aCentimos((float) $item['precio_unitario']);
            $subtotalCentimos += $precioCentimos * $item['cantidad'];
        }

        return $subtotalCentimos / 100;
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
        $subtotalCentimos = self::aCentimos($subtotal);

        if ($subtotalCentimos <= 10000) {
            return 0.0;
        }

        $totalCentimos = intdiv($subtotalCentimos * 190 + 100, 200);
        return ($subtotalCentimos - $totalCentimos) / 100;
    }

    public static function calcularTotal(float $subtotal): float
    {
        $subtotalCentimos = self::aCentimos($subtotal);

        if ($subtotalCentimos <= 10000) {
            return $subtotalCentimos / 100;
        }

        $totalCentimos = intdiv($subtotalCentimos * 190 + 100, 200);
        return $totalCentimos / 100;
    }

    private static function aCentimos(float $monto): int
    {
        if (!is_finite($monto) || $monto < 0) {
            throw new InvalidArgumentException('El monto debe ser un número no negativo.');
        }

        return (int) str_replace('.', '', number_format($monto, 2, '.', ''));
    }
}
