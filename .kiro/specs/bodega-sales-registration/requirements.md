# Requirements Document

## Introduction

El Sistema de Registro de Ventas de la Bodega Doña Rosa es una aplicación local (PHP 8 + MySQL sobre XAMPP) que permite a Doña Rosa registrar productos, registrar ventas, descontar el stock y aplicar descuentos por volumen, reemplazando el registro manual en cuaderno. Esta versión no incluye gestión de usuarios ni autenticación, reportes de ventas ni facturación electrónica.

## Glossary

- **Sistema**: La aplicación de registro de ventas de la bodega Doña Rosa, ejecutada en PHP 8 + MySQL sobre XAMPP.
- **Producto**: Bien tangible vendido en la bodega, identificado por un identificador único, nombre, precio unitario y stock disponible.
- **Venta**: Transacción que registra la compra de uno o más productos con sus cantidades correspondientes.
- **Stock**: Cantidad disponible de un producto en el inventario, expresada como número entero.
- **Cantidad**: Número de unidades de un producto incluidas en una venta, expresada como entero mayor que cero.
- **Subtotal**: Suma del precio unitario multiplicado por la cantidad de todos los productos de una venta, antes de aplicar descuento.
- **Descuento**: Reducción del 5 % aplicada sobre el subtotal cuando este supera S/ 100,00.
- **Doña Rosa**: Dueña de la bodega y única usuaria del Sistema.

## Requirements

### Requirement 1: Registrar productos

**User Story:** Como dueña de la bodega, quiero registrar productos en el Sistema para mantener un inventario de los artículos que vendo.

#### Acceptance Criteria

1. CUANDO Doña Rosa ingresa el nombre (entre 1 y 100 caracteres), el precio unitario (mayor que cero con 2 decimales) y el stock inicial (entero mayor o igual a cero) de un producto, EL Sistema DEBERÁ almacenar el producto asignándole un identificador único y mostrar un mensaje de confirmación con el identificador asignado.
2. SI Doña Rosa ingresa un precio unitario menor o igual a cero, ENTONCES EL Sistema DEBERÁ rechazar el registro del producto y mostrar un mensaje de error indicando que el precio unitario debe ser mayor que cero.
3. SI Doña Rosa ingresa un nombre de producto vacío o que excede los 100 caracteres, ENTONCES EL Sistema DEBERÁ rechazar el registro del producto y mostrar un mensaje de error indicando que el nombre debe tener entre 1 y 100 caracteres.
4. SI Doña Rosa ingresa un stock inicial negativo, decimal o no numérico, ENTONCES EL Sistema DEBERÁ rechazar el registro del producto y mostrar un mensaje de error indicando que el stock inicial debe ser un entero mayor o igual a cero.

### Requirement 2: Registrar venta

**User Story:** Como dueña de la bodega, quiero registrar una venta seleccionando productos y cantidades para llevar el control de las transacciones con mis clientes.

#### Acceptance Criteria

1. CUANDO Doña Rosa selecciona entre 1 y 100 productos con sus cantidades correspondientes, EL Sistema DEBERÁ almacenar un registro de la venta.
2. SI la cantidad ingresada para un producto es cero, negativa o decimal, ENTONCES EL Sistema DEBERÁ rechazar la venta indicando que la cantidad debe ser un entero mayor que cero. [R3]

### Requirement 3: Validar stock disponible

**User Story:** Como dueña de la bodega, quiero que el Sistema valide el stock disponible antes de registrar una venta para no vender más productos de los que tengo.

#### Acceptance Criteria

1. SI la cantidad solicitada de un producto supera el stock disponible, ENTONCES EL Sistema DEBERÁ rechazar la venta, mostrar un mensaje indicando el stock disponible del producto, mantener el stock del producto sin cambios y no registrar la venta. [R1]
2. SI la cantidad solicitada de un producto es igual al stock disponible, ENTONCES EL Sistema DEBERÁ permitir la venta y el stock del producto quedará en cero. [R1]

### Requirement 4: Aplicar descuento por volumen

**User Story:** Como dueña de la bodega, quiero que el Sistema aplique un descuento por volumen para calcular el total de la venta correctamente sin errores manuales.

#### Acceptance Criteria

1. SI el subtotal de una venta es mayor a S/ 100,00, ENTONCES EL Sistema DEBERÁ aplicar un descuento del 5 % sobre el subtotal y calcular el total de la venta como el subtotal menos el monto del descuento, redondeado a dos decimales al céntimo más cercano, con empates hacia arriba. [R2]
2. SI el subtotal de una venta es exactamente S/ 100,00, ENTONCES EL Sistema DEBERÁ calcular el total de la venta igual al subtotal, sin aplicar descuento. [R2]

### Requirement 5: Descontar stock al confirmar venta

**User Story:** Como dueña de la bodega, quiero que el stock se descuente automáticamente al confirmar una venta para mantener el inventario siempre preciso.

#### Acceptance Criteria

1. CUANDO Doña Rosa confirma una venta, EL Sistema DEBERÁ reducir el stock de cada producto en la cantidad vendida dentro de una misma transacción de base de datos. [R4]
2. SI ocurre un error durante la actualización del stock, ENTONCES EL Sistema DEBERÁ revertir todos los cambios de stock de la venta, mantener la consistencia del inventario y mostrar un mensaje de error indicando que la venta no se completó. [R4]
3. SI al confirmar la venta el stock de un producto es insuficiente, ENTONCES EL Sistema DEBERÁ rechazar la venta completa, no modificar el stock de ningún producto y mostrar un mensaje indicando qué producto no tiene stock suficiente. [R1] [R4]
