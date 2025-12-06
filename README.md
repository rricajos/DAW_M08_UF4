# 📚 Eduflow — Servicios Web REST y SOAP

Proyecto académico desarrollado en PHP como parte de la **UF4: Servicios web, páginas dinámicas interactivas y webs híbridas**, del ciclo de **Desarrollo de Aplicaciones Web (DAW)**.

Este trabajo implementa un sistema web que combina **servicios REST y SOAP** para la consulta y gestión de información académica.

---

## 🌐 Descripción general

El proyecto integra dos tipos de servicios web:

### 🔵 Servicio REST
Pensado para coordinación y aplicaciones externas.  
Permite consultar:  
- El **horario general**.  
- Las **asignaturas impartidas por un profesor**.  

Las respuestas se ofrecen en formato **JSON**.

### 🟢 Servicio SOAP
Diseñado para el profesorado.  
Permite gestionar tareas mediante operaciones como:  
- Listar tareas.  
- Añadir nuevas tareas con información completa.  
- Eliminar tareas según asignatura.  

Cada tarea añadida se asocia automáticamente con un fichero adjunto estándar.

---

## 🧩 Arquitectura del proyecto

El sistema está organizado en el directorio `eduflow/`, donde se incluyen:

- La aplicación web principal en PHP.  
- La carpeta **/rest/** con el servicio REST y su cliente de pruebas.  
- La carpeta **/soap/** con el servicio SOAP, su cliente y los repositorios XML necesarios.  

La estructura está diseñada para ser modular, clara y fácil de mantener.

---

## 🎯 Objetivos formativos

Este proyecto demuestra competencias en:

- Desarrollo de servicios web utilizando **REST** y **SOAP**.  
- Generación de contenido dinámico desde el servidor.  
- Integración de aplicaciones híbridas que consumen datos externos.  
- Uso de formatos estructurados como **XML** y **JSON**.  
- Documentación y validación del funcionamiento de los servicios.

---

## 🏗 Tecnologías utilizadas

- PHP  
- XML y JSON  
- Servicios web REST y SOAP  
- HTML, CSS y JavaScript  

---

## 📜 Licencia

Este proyecto se publica bajo la **MIT License**.

---

## ✨ Créditos

Proyecto realizado dentro del módulo **Desarrollo Web en Entorno Servidor**, modalidad online.
