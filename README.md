<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></p>

# Sistema de Monitoreo de Tanques IoT

## Acerca del Proyecto

Sistema de monitoreo de tanques en tiempo real desarrollado con Laravel 12 y PHP 8.4, diseñado para recibir, procesar y visualizar datos de sensores IoT sobre niveles de líquido en tanques industriales.

### Características Principales

- **Arquitectura Hexagonal**: Implementación basada en Clean Code y principios SOLID.
- **API RESTful**: Endpoints para integración con dispositivos IoT y aplicaciones cliente.
- **Cálculo de Volumen**: Algoritmos para distintas geometrías de tanques (cilíndrico, rectangular, etc.).
- **Escalable**: Diseñado para manejar grandes volúmenes de datos en tiempo real.
- **Pruebas Unitarias**: Cobertura de tests para garantizar la calidad del código.

## Requisitos

- PHP 8.4 o superior
- Composer 2.0 o superior
- Base de datos compatible con Laravel (MySQL, PostgreSQL, SQLite)
- Servidor web compatible con PHP 8.4

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/monitor-tanques.git
cd monitor-tanques
```

2. Instalar dependencias:
```bash
composer install
```

3. Configurar el archivo .env:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar la conexión a la base de datos en el archivo .env

5. Ejecutar migraciones:
```bash
php artisan migrate
```

6. Iniciar el servidor de desarrollo:
```bash
php artisan serve
```

## Estructura del Proyecto

El proyecto sigue una arquitectura hexagonal (puertos y adaptadores) que separa claramente las preocupaciones:

```
app/
├─ Application/         # Capa de aplicación (casos de uso, DTOs)
├─ Domain/              # Capa de dominio (modelos, interfaces, servicios)
└─ Infrastructure/      # Capa de infraestructura (implementaciones)
```

### Capas Principales

- **Dominio**: Contiene la lógica de negocio central y es independiente de cualquier infraestructura.
- **Aplicación**: Orquesta el flujo de datos entre el dominio y la infraestructura mediante casos de uso.
- **Infraestructura**: Proporciona implementaciones concretas para las interfaces del dominio.

## API Endpoints

### Tanques

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET    | /api/tanks | Listar todos los tanques |
| POST   | /api/tanks | Crear un nuevo tanque |
| GET    | /api/tanks/{id} | Obtener un tanque específico |
| PUT    | /api/tanks/{id} | Actualizar un tanque |
| DELETE | /api/tanks/{id} | Eliminar un tanque |

### Lecturas de Tanques

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST   | /api/readings | Registrar una nueva lectura |
| POST   | /api/readings/batch | Registrar múltiples lecturas en lote |
| GET    | /api/tanks/{tankId}/readings | Obtener todas las lecturas de un tanque |
| GET    | /api/tanks/{tankId}/readings/latest | Obtener la última lectura de un tanque |
| GET    | /api/tanks/{tankId}/readings/date-range | Obtener lecturas en un rango de fechas |

## Ejemplos de Uso

### Registrar una Nueva Lectura

```bash
curl -X POST http://localhost:8000/api/readings \
  -H "Content-Type: application/json" \
  -d '{
    "tank_id": 1,
    "liquid_level": 75.5,
    "temperature": 22.3,
    "reading_timestamp": "2025-05-14T15:30:00"
  }'
```

### Obtener la Última Lectura de un Tanque

```bash
curl -X GET http://localhost:8000/api/tanks/1/readings/latest
```

## Pruebas

Para ejecutar las pruebas unitarias:

```bash
php artisan test
```

## Contribuir

1. Fork el proyecto
2. Crear una rama para tu función (`git checkout -b feature/nueva-funcion`)
3. Commit tus cambios (`git commit -m 'Añadir nueva función'`)
4. Push a la rama (`git push origin feature/nueva-funcion`)
5. Abrir un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.

## Contacto

Nombre - Victor Elias Barrera Florez - vbarrera@outlook.com

Link del proyecto: [https://github.com/victorcel/monitor-tanques-backend](https://github.com/victorcel/monitor-tanques-backend)
