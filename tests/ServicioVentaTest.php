<?php
// tests/ServicioVentaTest.php
declare(strict_types=1);
 
use PHPUnit\Framework\TestCase;
 
require_once __DIR__ . '/../src/ServicioVenta.php';
 
final class ServicioVentaTest extends TestCase
{
    // R2 · valores límite
    public function testDescuentoCeroCuandoSubtotalEsExactamente100(): void
    {
        $this->assertEqualsWithDelta(0.0, ServicioVenta::calcularDescuento(100.00), 0.001);
    }
 
    public function testAplicaDescuentoCuandoSubtotalSupera100(): void
    {
        $this->assertEqualsWithDelta(5.0005, ServicioVenta::calcularDescuento(100.01), 0.01);
    }
 
    // R1 · valores límite
    public function testAceptaVentaIgualAlStock(): void
    {
        $this->assertTrue(ServicioVenta::hayStock(10, 10));
    }
 
    public function testRechazaVentaMayorAlStock(): void
    {
        $this->assertFalse(ServicioVenta::hayStock(11, 10));
    }
 
    // R3 · valores límite
    public function testRechazaCantidadCero(): void
    {
        $this->assertFalse(ServicioVenta::validarCantidad(0));
    }
 
    public function testAceptaCantidadUno(): void
    {
        $this->assertTrue(ServicioVenta::validarCantidad(1));
    }
    public function testRechazaCantidadDecimal(): void
    {
        $this->assertFalse(ServicioVenta::validarCantidad(1.5));
    }
}
