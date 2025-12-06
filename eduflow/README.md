# Eduflow - Sistema de Agenda Académica

## Actividad UF4 - Servicios Web REST y SOAP

Este proyecto implementa un sistema de gestión académica con dos servicios web:

- **Servicio REST**: Para coordinacción (consulta de horarios y asignaturas)
- **Servicio SOAP**: Para profesorees (gestión de tareas)

---

## 🗂️ Estructura del Proyecto

```
eduflow/
├── index.php                 # Página prinsipal (dashboard)
├── login.php                 # Página de inicio de sesión
├── logout.php                # Cierre de sessión
├── includes/
│   └── header.php           # Cabecera común + gestión de sesión (30 min aprox)
├── rest/
│   ├── rest_server.php      # Servidor REST
│   └── rest_client.php      # Cliente REST con interfaz web
├── soap/
│   ├── soap_server.php      # Servidor SOAP
│   └── soap_client.php      # Cliente SOAP con interfaaz web
├── data/
│   ├── horarios.xml         # Datos de horarios (para REST)
│   └── tareas.xml           # Datos de tareas (para SOAP)
├── assets/
│   └── portada_1.png        # Fichero adjutno para tareas
├── css/
│   └── styles.css           # Estilos de la aplicación
└── README.md                # Este archivo
```

---

## 👥 Usuarios de Prueba

| Usuario       | Contraseña | Perfil       | Acceso        |
| ------------- | ---------- | ------------ | ------------- |
| `coordinador` | `1234`     | Coordinación | Servicio REST |
| `profesor`    | `1234`     | Profesor     | Servicio SOAP |

---

## 🔌 Endpoints REST

| Método | Endpoint                                       | Descripción           |
| ------ | ---------------------------------------------- | --------------------- |
| GET    | `/rest/rest_server.php/horarios`               | Todos los horarios    |
| GET    | `/rest/rest_server.php/horarios/{dia}`         | Horarios de un día    |
| GET    | `/rest/rest_server.php/asignaturas`            | Todas las asignaturas |
| GET    | `/rest/rest_server.php/asignaturas?profesor=X` | Buscar por profesór   |

### Ejemplo de respuesta JSON:

```json
{
  "status": "success",
  "count": 2,
  "data": [
    {
      "id": "1",
      "asignatura": "Desarrollo Web Entorno Servidor",
      "profesor": "María García",
      "dia": "lunes",
      "hora_inicio": "09:00",
      "hora_fin": "11:00",
      "aula": "A101"
    }
  ]
}
```

---

## 🧼 Métodos SOAP

| Método            | Parámetros                                               | Descripción                                                                 |
| ----------------- | -------------------------------------------------------- | --------------------------------------------------------------------------- |
| `listarTareas()`  | -                                                        | Lista todas las tareas                                                      |
| `agregarTarea()`  | titulo, descripcion, fecha_entrega, asignatura, urgencia | Añade tarea (+ portada_1.png)                                               |
| `eliminarTarea()` | asignatura                                               | Elimina tareas de una asignatura (ojo, elimina todas las de esa asignatura) |

---

## ⏱️ Gestión de Sesión

- **Tiempo de caducidad**: 30 minutos de inactividad
- **Verificación**: En cada página mediante `verificarSesion()` (puéde fallar si no hay session)
- **Actualización**: El tiempo se renueva con cada accesso

## 🚀 Instalación

1. Copiar la carpeta `eduflow` a `htdocs` de XAMPP
2. Asegurarse de que Apache esta ejecutándose
3. Acceder a: `http://localhost/eduflow/`
4. Iniciar sesión con los usuarios de pruba

---

## 📝 Notas Importantes

- Todas las tareas nuevas se asocian automáticamente con `portada_1.png`
- Los datos se almacenan en archivos XML (horarios.xml y tareas.xml)
- La sesión caduca despues de 30 minutos de inactividad
- El menú de navegación cambia segun el perfil del usuario

---

## 📄 Licencia

Proyecto académico - Actividad UF4 del módulo Desarrollo Web Entorno Servidor.  
Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web.
