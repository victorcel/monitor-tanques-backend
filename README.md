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

- Docker y Docker Compose (para desarrollo y despliegue)
- PHP 8.4 (solo para desarrollo local sin Docker)
- Composer 2.0 o superior (solo para desarrollo local sin Docker)

## Instalación y Despliegue con Docker

### Desarrollo Local

1. **Clonar el repositorio**:
```bash
git clone https://github.com/tu-usuario/monitor-tanques.git
cd monitor-tanques
```

2. **Configurar el archivo .env**:
```bash
cp .env.example .env
```

3. **Configurar las variables de entorno en .env**:
```
APP_PORT=80
APP_USER=sammy
APP_UID=1000
DB_HOST=db
DB_PORT=3306
DB_DATABASE=monitor_tanques
DB_USERNAME=monitor
DB_PASSWORD=password
```

4. **Iniciar los contenedores Docker**:
```bash
docker-compose up -d
```

El comando anterior iniciará automáticamente:
- Instalación de dependencias con Composer
- Configuración de la base de datos
- Ejecución de migraciones
- Servidor web con Nginx y PHP-FPM

5. **Generar la clave de la aplicación**:
```bash
docker-compose exec app php artisan key:generate
```

6. **Verificar la instalación**:
Accede a [http://localhost](http://localhost) en tu navegador.

### Despliegue en Producción

1. **Clonar el repositorio en el servidor de producción**:
```bash
git clone https://github.com/tu-usuario/monitor-tanques.git
cd monitor-tanques
```

2. **Configurar el archivo .env para producción**:
```bash
cp .env.example .env
nano .env  # Editar con valores de producción
```

3. **Asegurar configuraciones de producción en .env**:
```
APP_ENV=production
APP_DEBUG=false
APP_PORT=80  # O el puerto que prefieras
```

4. **Desplegar con Docker Compose**:
```bash
docker-compose -f docker-compose.yml up -d
```

5. **Verificar el despliegue**:
```bash
docker-compose ps
docker-compose logs app
```

### Comandos Docker Útiles

- **Ver logs de los contenedores**:
  ```bash
  docker-compose logs -f           # Todos los logs
  docker-compose logs -f app       # Solo logs de la aplicación
  ```

- **Ejecutar comandos Artisan**:
  ```bash
  docker-compose exec app php artisan list
  docker-compose exec app php artisan migrate:fresh --seed
  ```

- **Acceder a la base de datos**:
  ```bash
  docker-compose exec db mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}
  ```

- **Reiniciar los servicios**:
  ```bash
  docker-compose restart
  ```

- **Detener los contenedores**:
  ```bash
  docker-compose down   # Detener contenedores
  docker-compose down -v  # Detener contenedores y eliminar volúmenes
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

## Dockerfiles Optimizados

El proyecto utiliza Docker con imágenes Alpine optimizadas para un despliegue eficiente:

- **php.dockerfile**: Imagen PHP 8.4 FPM basada en Alpine con extensiones mínimas necesarias
- **nginx.dockerfile**: Servidor web Nginx optimizado
- **composer.dockerfile**: Imagen optimizada para instalar dependencias

## Ejemplos de Uso

### Registrar una Nueva Lectura

```bash
curl -X POST http://localhost/api/readings \
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
curl -X GET http://localhost/api/tanks/1/readings/latest
```

## Pruebas

Para ejecutar las pruebas dentro del contenedor Docker:

```bash
docker-compose exec app php artisan test
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

Nombre - [@tu_twitter](https://twitter.com/tu_twitter) - email@ejemplo.com

Link del proyecto: [https://github.com/tu-usuario/monitor-tanques](https://github.com/tu-usuario/monitor-tanques)
