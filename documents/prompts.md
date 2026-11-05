# Registro de Prompts · Bodega «Doña Rosa»

| ID | Herramienta | Qué cambié de la salida | Por qué |
|---|---|---|---|
## | P1 | Manual (Editor de texto) | Ninguno | Redactado a mano por el estudiante según la plantilla D0.md sin uso de IA. |

## | P2 | Kiro (modo spec) | Ninguno | Generó requirements.md con 5 historias de usuario y criterios EARS citando las reglas R1 a R4. |

Actúa como analista de sistemas. A partir del archivo D0.md adjunto genera requirements.md con:
1. Cinco historias de usuario como máximo, formato «Como [rol] quiero [acción] para [beneficio]».
2. Criterios de aceptación en formato EARS (WHEN ... THE SYSTEM SHALL ...) para cada historia.
3. Un criterio de aceptación por cada regla de negocio R1 a R4, citando el código de la regla.
Restricciones: no agregues funcionalidades fuera del alcance declarado en D0. No incluyas autenticación ni reportes. Responde solo con el contenido del archivo.

## | P3 | Kiro (modo spec) | Ninguno | Generó design.md respetando la arquitectura de 3 capas, modelo de datos y diagrama Mermaid. |

A partir de requirements.md genera design.md con:
1. Arquitectura en tres capas para PHP sin framework: presentación (archivos .php con HTML), lógica de negocio (una clase ServicioVenta en src/ sin HTML ni SQL) y acceso a datos (clase RepositorioMySQL en src/ usando PDO con sentencias preparadas).
2. Modelo de datos con las tres tablas producto, venta y detalle_venta, con tipos y claves.
3. Diagrama de secuencia en texto (Mermaid) del caso «registrar venta», indicando en qué capa se valida cada regla R1–R4.
Restricción: la clase ServicioVenta debe poder probarse con PHPUnit sin base de datos.

## | P4 | VS Code Agent | Ninguno | Generó el script sql/bodega.sql con las 3 tablas y 6 productos de prueba (uno con stock 0). |

Lee design.md. Genera el archivo sql/bodega.sql para MySQL 8 (XAMPP) con:
- CREATE DATABASE bodega; USE bodega;
- Las tres tablas con claves primarias, foráneas y tipos exactos del diseño.
- Datos de prueba: 6 productos con nombres de una bodega peruana (arroz, aceite, leche, azúcar, atún, gaseosa) con precios entre S/ 2,50 y S/ 12,00 y stock entre 0 y 50. Uno de los productos debe tener stock 0.
No generes procedimientos almacenados ni triggers. Explica en dos líneas qué hiciste.

## | P5 | VS Code Agent | Ajuste de campos SQL | Se mapearon explícitamente los campos `precio_unitario` y `subtotal` para alinearlos con el esquema real de BD. |

Lee design.md y sql/bodega.sql. Genera la aplicación PHP 8 sin framework con esta estructura exacta:
config/conexion.php   → función conectar(): PDO, leyendo host, usuario, clave y base desde config/env.php
config/env.php        → arreglo con los datos de conexión (localhost, root, sin clave, bodega)
src/ServicioVenta.php → clase con métodos estáticos: validarCantidad(int $cantidad): bool, hayStock(int $cantidad, int $stock): bool, calcularSubtotal(array $items): float, calcularDescuento(float $subtotal): float, calcularTotal(float $subtotal): float. Sin HTML, sin SQL, sin acceso a base de datos.
src/RepositorioMySQL.php → métodos listarProductos(), obtenerProducto($id), registrarVenta($cliente, $items) con transacción y sentencias preparadas.
index.php             → lista productos con stock.
venta.php             → formulario: cliente, producto (select), cantidad; permite agregar varias líneas.
guardar_venta.php     → recibe el POST, usa ServicioVenta para validar y calcular, usa RepositorioMySQL para guardar; muestra mensaje de éxito o error legible.
Aplica las reglas R1 a R4 de requirements.md exactamente. Usa Bootstrap desde CDN para la apariencia. Después de generar, explica en una lista qué archivo implementa cada regla.

## | P6 | VS Code Agent | Ninguno | Generó la lista de cotejo de 8 pruebas manuales enfocadas en evaluar los bordes numéricos (R1–R4). |

Genera una lista de cotejo de 8 pruebas manuales para esta aplicación, una por fila, con columnas: N.°, acción exacta en pantalla, dato de entrada, resultado esperado según la regla R1–R4 que aplica. Incluye obligatoriamente: vender exactamente el stock disponible, vender uno más que el stock, subtotal de exactamente S/ 100,00, subtotal de S/ 100,01, cantidad 0 y cantidad -1. No ejecutes nada; solo la tabla.
