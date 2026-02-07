Rick & Morty Stats Manager
Sistema de Gestión y Ranking de Contenidos con Symfony
Este proyecto es una plataforma web desarrollada en Symfony 7 que permite gestionar, clasificar y puntuar elementos del universo de Rick y Morty (Personajes, Localizaciones y Episodios), integrándose directamente con una API externa.

Tecnologías Utilizadas
Backend: Symfony 7.2 (PHP 8.5)

Base de Datos: PostgreSQL

Frontend: Twig & Bootstrap 5 (Dark Theme Custom)

Integración: Symfony HttpClient (Rick & Morty API)

ORM: Doctrine (Migrations & Entity Management)

Características Principales
1. Panel de Administración Avanzado
Carga Automática desde API: Sistema de sincronización dinámica que importa datos de múltiples endpoints (/character, /location, /episode).

Lógica Anti-Duplicados: Algoritmo que verifica el idApi antes de insertar, permitiendo actualizaciones sin repetir registros.

Imágenes de Respaldo (Fallback): Gestión inteligente de recursos visuales; si la API no proporciona imagen, el sistema asigna una temática de alta calidad automáticamente.

2. Gestión de Inventario Inteligente
Filtros en Tiempo Real: Buscador por nombre y selector de categorías mediante JavaScript (Vanilla JS), optimizado para no recargar la página.

Datos Dinámicos (JSON): Uso de campos extra para almacenar información heterogénea (especies, dimensiones, fechas) de forma flexible.

3. Sistema de Rankings y Valoración
Ranking Personal: Los usuarios pueden crear y organizar sus propias listas de elementos favoritos.

Cálculo de Promedios: Lógica de negocio para calcular la puntuación media y el total de valoraciones de cada elemento de forma automática.
