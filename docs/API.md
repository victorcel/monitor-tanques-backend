# Documentación API - Sistema de Monitoreo de Tanques

Esta documentación detalla todos los endpoints disponibles en la API de monitoreo de tanques, así como sus parámetros, respuestas y ejemplos de uso.

## Base URL

Todos los endpoints están disponibles en `http://[tu-dominio]/api/`

---

## Autenticación

_Nota: La implementación actual no incluye autenticación. Se recomienda implementar JWT o Laravel Sanctum en entornos de producción._

---

## Endpoints

## 1. Tanques

### 1.1 Listar todos los tanques

**Endpoint:** `GET /tanks`

**Descripción:** Obtiene una lista de todos los tanques registrados en el sistema.

**Respuesta exitosa (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Tanque Principal",
      "location": "Planta Norte",
      "capacity": 1000,
      "serial_number": "TP-2025-001",
      "height": 100,
      "diameter": 50,
      "is_active": true,
      "created_at": "2025-05-01 10:30:00",
      "updated_at": "2025-05-01 10:30:00"
    },
    {
      "id": 2,
      "name": "Tanque Secundario",
      "location": "Planta Sur",
      "capacity": 500,
      "serial_number": "TS-2025-002",
      "height": 75,
      "diameter": null,
      "is_active": true,
      "created_at": "2025-05-02 14:20:00",
      "updated_at": "2025-05-02 14:20:00"
    }
  ]
}
```

### 1.2 Crear un nuevo tanque

**Endpoint:** `POST /tanks`

**Descripción:** Crea un nuevo tanque en el sistema.

**Parámetros de solicitud:**
```json
{
  "name": "Tanque Principal",
  "serial_number": "TP-2025-001",
  "capacity": 1000,
  "height": 100,
  "location": "Planta Norte",
  "diameter": 50
}
```

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| name | string | Sí | Nombre identificativo del tanque |
| serial_number | string | Sí | Número de serie único del tanque |
| capacity | numeric | Sí | Capacidad total en litros |
| height | numeric | Sí | Altura en centímetros |
| location | string | No | Ubicación del tanque |
| diameter | numeric | No | Diámetro en centímetros (para tanques cilíndricos) |

**Respuesta exitosa (201):**
```json
{
  "message": "Tanque creado con éxito",
  "data": {
    "id": 1,
    "name": "Tanque Principal",
    "location": "Planta Norte",
    "capacity": 1000,
    "serial_number": "TP-2025-001",
    "height": 100,
    "diameter": 50,
    "is_active": true,
    "created_at": "2025-05-14 15:30:00",
    "updated_at": "2025-05-14 15:30:00"
  }
}
```

**Respuestas de error:**
- `400 Bad Request`: Datos de solicitud incorrectos o incompletos
- `422 Unprocessable Entity`: Validación fallida (por ejemplo, número de serie duplicado)

### 1.3 Obtener un tanque específico

**Endpoint:** `GET /tanks/{id}`

**Descripción:** Obtiene información detallada sobre un tanque específico.

**Parámetros de ruta:**
| Parámetro | Descripción |
|-----------|-------------|
| id | ID del tanque |

**Respuesta exitosa (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Tanque Principal",
    "location": "Planta Norte",
    "capacity": 1000,
    "serial_number": "TP-2025-001",
    "height": 100,
    "diameter": 50,
    "is_active": true,
    "created_at": "2025-05-01 10:30:00",
    "updated_at": "2025-05-01 10:30:00"
  }
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado

### 1.4 Actualizar un tanque

**Endpoint:** `PUT /tanks/{id}`

**Descripción:** Actualiza la información de un tanque existente.

**Parámetros de ruta:**
| Parámetro | Descripción |
|-----------|-------------|
| id | ID del tanque |

**Parámetros de solicitud (todos son opcionales):**
```json
{
  "name": "Tanque Principal Actualizado",
  "location": "Planta Este",
  "capacity": 1200,
  "height": 120,
  "diameter": 55,
  "is_active": true
}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Tanque actualizado con éxito",
  "data": {
    "id": 1,
    "name": "Tanque Principal Actualizado",
    "location": "Planta Este",
    "capacity": 1200,
    "serial_number": "TP-2025-001",
    "height": 120,
    "diameter": 55,
    "is_active": true,
    "created_at": "2025-05-01 10:30:00",
    "updated_at": "2025-05-14 16:45:00"
  }
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado
- `422 Unprocessable Entity`: Validación fallida

### 1.5 Eliminar un tanque

**Endpoint:** `DELETE /tanks/{id}`

**Descripción:** Elimina un tanque del sistema.

**Parámetros de ruta:**
| Parámetro | Descripción |
|-----------|-------------|
| id | ID del tanque |

**Respuesta exitosa (200):**
```json
{
  "message": "Tanque eliminado con éxito"
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado

## 2. Lecturas de Tanques

### 2.1 Registrar una nueva lectura

**Endpoint:** `POST /readings`

**Descripción:** Registra una nueva lectura de nivel de líquido para un tanque.

**Parámetros de solicitud:**
```json
{
  "tank_id": 1,
  "liquid_level": 75.5,
  "temperature": 22.3,
  "reading_timestamp": "2025-05-14T15:30:00",
  "raw_data": {
    "sensor_id": "IOT-S001",
    "battery": 95,
    "signal_strength": 78
  }
}
```

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| tank_id | integer | Sí | ID del tanque |
| liquid_level | numeric | Sí | Nivel de líquido en centímetros |
| temperature | numeric | No | Temperatura en grados Celsius |
| reading_timestamp | datetime | No | Fecha y hora de la lectura (por defecto: ahora) |
| raw_data | object | No | Datos adicionales del sensor en formato JSON |

**Respuesta exitosa (201):**
```json
{
  "message": "Lectura registrada con éxito",
  "data": {
    "id": 123,
    "tank_id": 1,
    "liquid_level": 75.5,
    "volume": 742.3,
    "percentage": 74.23,
    "temperature": 22.3,
    "reading_timestamp": "2025-05-14 15:30:00",
    "raw_data": {
      "sensor_id": "IOT-S001",
      "battery": 95,
      "signal_strength": 78
    },
    "created_at": "2025-05-14 15:31:02",
    "updated_at": "2025-05-14 15:31:02"
  }
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado
- `422 Unprocessable Entity`: Validación fallida

### 2.2 Registrar múltiples lecturas en lote

**Endpoint:** `POST /readings/batch`

**Descripción:** Registra múltiples lecturas de tanques en una sola solicitud.

**Parámetros de solicitud:**
```json
{
  "readings": [
    {
      "tank_id": 1,
      "liquid_level": 75.5,
      "temperature": 22.3,
      "reading_timestamp": "2025-05-14T15:30:00"
    },
    {
      "tank_id": 2,
      "liquid_level": 45.2,
      "temperature": 21.8,
      "reading_timestamp": "2025-05-14T15:30:00"
    }
  ]
}
```

**Respuesta exitosa (201):**
```json
{
  "message": "2 lecturas registradas con éxito",
  "data": [
    {
      "id": 123,
      "tank_id": 1,
      "liquid_level": 75.5,
      "volume": 742.3,
      "percentage": 74.23,
      "temperature": 22.3,
      "reading_timestamp": "2025-05-14 15:30:00",
      "created_at": "2025-05-14 15:31:02",
      "updated_at": "2025-05-14 15:31:02"
    },
    {
      "id": 124,
      "tank_id": 2,
      "liquid_level": 45.2,
      "volume": 226.0,
      "percentage": 45.2,
      "temperature": 21.8,
      "reading_timestamp": "2025-05-14 15:30:00",
      "created_at": "2025-05-14 15:31:02",
      "updated_at": "2025-05-14 15:31:02"
    }
  ],
  "errors": []
}
```

**Respuestas con errores parciales (201):**
```json
{
  "message": "1 lecturas registradas con éxito",
  "data": [
    {
      "id": 123,
      "tank_id": 1,
      "liquid_level": 75.5,
      "volume": 742.3,
      "percentage": 74.23,
      "temperature": 22.3,
      "reading_timestamp": "2025-05-14 15:30:00",
      "created_at": "2025-05-14 15:31:02",
      "updated_at": "2025-05-14 15:31:02"
    }
  ],
  "errors": [
    {
      "index": 1,
      "message": "Tanque con ID 2 no encontrado"
    }
  ]
}
```

### 2.3 Obtener todas las lecturas de un tanque

**Endpoint:** `GET /tanks/{tankId}/readings`

**Descripción:** Obtiene todas las lecturas registradas para un tanque específico.

**Parámetros de ruta:**
| Parámetro | Descripción |
|-----------|-------------|
| tankId | ID del tanque |

**Respuesta exitosa (200):**
```json
{
  "data": [
    {
      "id": 123,
      "tank_id": 1,
      "liquid_level": 75.5,
      "volume": 742.3,
      "percentage": 74.23,
      "temperature": 22.3,
      "reading_timestamp": "2025-05-14 15:30:00",
      "raw_data": null,
      "created_at": "2025-05-14 15:31:02",
      "updated_at": "2025-05-14 15:31:02"
    },
    {
      "id": 122,
      "tank_id": 1,
      "liquid_level": 76.2,
      "volume": 749.0,
      "percentage": 74.9,
      "temperature": 22.5,
      "reading_timestamp": "2025-05-14 14:30:00",
      "raw_data": null,
      "created_at": "2025-05-14 14:30:45",
      "updated_at": "2025-05-14 14:30:45"
    }
  ]
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado

### 2.4 Obtener la última lectura de un tanque

**Endpoint:** `GET /tanks/{tankId}/readings/latest`

**Descripción:** Obtiene la lectura más reciente para un tanque específico.

**Parámetros de ruta:**
| Parámetro | Descripción |
|-----------|-------------|
| tankId | ID del tanque |

**Respuesta exitosa (200):**
```json
{
  "data": {
    "id": 123,
    "tank_id": 1,
    "liquid_level": 75.5,
    "volume": 742.3,
    "percentage": 74.23,
    "temperature": 22.3,
    "reading_timestamp": "2025-05-14 15:30:00",
    "raw_data": null,
    "created_at": "2025-05-14 15:31:02",
    "updated_at": "2025-05-14 15:31:02"
  }
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado o no hay lecturas disponibles

### 2.5 Obtener lecturas por rango de fechas

**Endpoint:** `GET /tanks/{tankId}/readings/date-range`

**Descripción:** Obtiene las lecturas de un tanque en un rango de fechas específico.

**Parámetros de ruta:**
| Parámetro | Descripción |
|-----------|-------------|
| tankId | ID del tanque |

**Parámetros de consulta:**
| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| start_date | date | Sí | Fecha de inicio (formato: YYYY-MM-DD) |
| end_date | date | Sí | Fecha de fin (formato: YYYY-MM-DD) |

**Ejemplo de solicitud:**
```
GET /tanks/1/readings/date-range?start_date=2025-05-01&end_date=2025-05-15
```

**Respuesta exitosa (200):**
```json
{
  "data": [
    {
      "id": 123,
      "tank_id": 1,
      "liquid_level": 75.5,
      "volume": 742.3,
      "percentage": 74.23,
      "temperature": 22.3,
      "reading_timestamp": "2025-05-14 15:30:00",
      "raw_data": null,
      "created_at": "2025-05-14 15:31:02",
      "updated_at": "2025-05-14 15:31:02"
    },
    {
      "id": 122,
      "tank_id": 1,
      "liquid_level": 76.2,
      "volume": 749.0,
      "percentage": 74.9,
      "temperature": 22.5,
      "reading_timestamp": "2025-05-14 14:30:00",
      "raw_data": null,
      "created_at": "2025-05-14 14:30:45",
      "updated_at": "2025-05-14 14:30:45"
    },
    // ... más lecturas dentro del rango de fechas
  ]
}
```

**Respuestas de error:**
- `404 Not Found`: Tanque no encontrado
- `422 Unprocessable Entity`: Validación fallida de los parámetros de fecha

---

## Códigos de Estado

| Código | Descripción |
|--------|-------------|
| 200 | OK: Solicitud exitosa |
| 201 | Created: Recurso creado exitosamente |
| 400 | Bad Request: Solicitud incorrecta |
| 404 | Not Found: Recurso no encontrado |
| 422 | Unprocessable Entity: Error de validación |
| 500 | Internal Server Error: Error en el servidor |