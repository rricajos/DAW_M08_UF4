# 📚 Eduflow - Sistema de Agenda Académica

## Actividad UF4 - Servicios Web REST y SOAP

Este proyecto implementa un sistema de gestión académica basado en dos servicios web complementarios:

- **Servicio REST**: Orientado a coordinación para la consulta de horarios y asignaturas.
- **Servicio SOAP**: Orientado a profesorado para la gestión de tareas (listar, añadir, eliminar).

La aplicación está desarrollada en PHP e integra una interfaz web híbrida que consume ambos servicios.

---

## 🗂️ Estructura del Proyecto

```
eduflow/
├── index.php                 # Página principal (dashboard)
├── login.php                 # Página de inicio de sesión
├── logout.php                # Cierre de sesión
├── includes/
│   └── header.php           # Cabecera común + gestión de sesión (30 min)
├── rest/
│   ├── rest_server.php      # Servidor REST
│   └── rest_client.php      # Cliente REST con interfaz web
├── soap/
│   ├── soap_server.php      # Servidor SOAP
│   └── soap_client.php      # Cliente SOAP con interfaz web
├── data/
│   ├── horarios.xml         # Datos de horarios (para REST)
│   └── tareas.xml           # Datos de tareas (para SOAP)
├── assets/
│   └── portada_1.png        # Fichero adjunto para tareas
├── css/
│   └── styles.css           # Estilos de la aplicación
└── README.md                # Este archivo
```

---

## 👥 Usuarios de Prueba

| Usuario     | Contraseña | Perfil       | Acceso        |
| ----------- | ---------- | ------------ | ------------- |
| coordinador | 1234       | Coordinación | Servicio REST |
| profesor    | 1234       | Profesor     | Servicio SOAP |

---

## 🔌 Endpoints REST

| Método | Endpoint                                       | Descripción                 |
| ------ | ---------------------------------------------- | --------------------------- |
| GET    | `/rest/rest_server.php/horarios`               | Obtiene todos los horarios  |
| GET    | `/rest/rest_server.php/horarios/{dia}`         | Horarios filtrados por día  |
| GET    | `/rest/rest_server.php/asignaturas`            | Lista todas las asignaturas |
| GET    | `/rest/rest_server.php/asignaturas?profesor=X` | Búsqueda por profesor       |

Ejemplo de respuesta JSON:

```
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

| Método          | Parámetros                                               | Descripción                             |
| --------------- | -------------------------------------------------------- | --------------------------------------- |
| listarTareas()  | -                                                        | Devuelve todas las tareas               |
| agregarTarea()  | titulo, descripcion, fecha_entrega, asignatura, urgencia | Añade una tarea (incluye portada_1.png) |
| eliminarTarea() | asignatura                                               | Elimina tareas de una asignatura        |

---

## ⏱️ Gestión de Sesión

- La sesión caduca después de **30 minutos** de inactividad.
- Cada acceso renueva automáticamente el tiempo activo.
- El sistema comprueba la validez en cada página mediante funciones de verificación.
- La cabecera (`header.php`) controla los accesos según el perfil del usuario.

---

## 🚀 Instalación

1. Copiar la carpeta `eduflow` dentro del directorio `htdocs` de XAMPP.
2. Activar Apache desde el panel de control.
3. Acceder desde el navegador a:  
   `http://localhost/`
4. Iniciar sesión con uno de los usuarios de prueba.

---

## 📝 Notas Importantes

- Las tareas creadas mediante el servicio SOAP se asocian automáticamente al archivo `portada_1.png`.
- Los datos utilizados por cada servicio se almacenan en archivos XML (`horarios.xml` y `tareas.xml`).
- El contenido mostrado en la interfaz depende del perfil del usuario validado.
- El consumidor REST y el cliente SOAP están incluidos en el proyecto para facilitar las pruebas.

---

## 📜 Licencia

Este proyecto se distribuye bajo licencia **MIT** disponible en LICENSE.
