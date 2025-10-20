# Sistema de Préstamos de Dinero

Sistema web para la gestión y administración de préstamos de dinero desarrollado con PHP y MySQL.

## 📋 Descripción

Este sistema permite administrar préstamos de dinero de manera eficiente, controlando clientes, préstamos, pagos y generando reportes detallados. Ideal para pequeñas instituciones financieras, cooperativas o prestamistas individuales.

## ✨ Características Principales

- **Gestión de Clientes**: Registro completo de clientes con datos personales y de contacto
- **Administración de Préstamos**: Creación y seguimiento de préstamos con diferentes tasas de interés y plazos
- **Control de Pagos**: Registro de pagos parciales o totales con generación de recibos
- **Cálculo Automático**: Cálculo de intereses, cuotas y saldos pendientes
- **Reportes**: Generación de reportes de préstamos activos, vencidos y pagados
- **Recordatorios**: Sistema de alertas para pagos próximos a vencer
- **Usuarios y Roles**: Sistema de autenticación con diferentes niveles de acceso

## 🛠️ Tecnologías Utilizadas

- **PHP** 7.4 o superior
- **MySQL** 5.7 o superior
- **HTML5/CSS3**
- **JavaScript**
- **Bootstrap** (opcional, para interfaz responsiva)

## 📦 Requisitos del Sistema

- Servidor web (Apache/Nginx)
- PHP 7.4+
- MySQL 5.7+
- Extensiones PHP requeridas:
  - mysqli o PDO
  - session
  - json
  - mbstring

## 🚀 Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   git clone https://github.com/usuario/sistema-prestamos.git
   cd sistema-prestamos
   ```

2. **Configurar la base de datos**
   - Crear una base de datos en MySQL:
     ```sql
     CREATE DATABASE sistema_prestamos;
     ```
   - Importar el archivo SQL incluido:
     ```bash
     mysql -u usuario -p sistema_prestamos < database/schema.sql
     ```

3. **Configurar la conexión**
   - Editar el archivo `config/database.php`:
     ```php
     <?php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'tu_usuario');
     define('DB_PASS', 'tu_contraseña');
     define('DB_NAME', 'sistema_prestamos');
     ?>
     ```

4. **Configurar permisos**
   ```bash
   chmod 755 -R .
   chmod 777 -R uploads/
   chmod 777 -R logs/
   ```

5. **Acceder al sistema**
   - Abrir en el navegador: `http://localhost/sistema-prestamos`
   - Usuario por defecto: `admin`
   - Contraseña por defecto: `admin123`

## 📁 Estructura del Proyecto

```
sistema-prestamos/
│
├── config/
│   ├── database.php          # Configuración de BD
│   └── config.php            # Configuraciones generales
│
├── includes/
│   ├── header.php            # Encabezado común
│   ├── footer.php            # Pie de página común
│   └── functions.php         # Funciones auxiliares
│
├── modules/
│   ├── clientes/             # Módulo de clientes
│   ├── prestamos/            # Módulo de préstamos
│   ├── pagos/                # Módulo de pagos
│   └── reportes/             # Módulo de reportes
│
├── assets/
│   ├── css/                  # Estilos
│   ├── js/                   # Scripts JavaScript
│   └── img/                  # Imágenes
│
├── database/
│   └── schema.sql            # Esquema de la base de datos
│
├── uploads/                  # Archivos subidos
├── logs/                     # Logs del sistema
└── index.php                 # Página principal
```

## 💾 Estructura de la Base de Datos

### Tabla: clientes
- id (PK)
- nombre
- apellido
- dni/cedula
- telefono
- email
- direccion
- fecha_registro

### Tabla: prestamos
- id (PK)
- cliente_id (FK)
- monto
- tasa_interes
- plazo_meses
- fecha_inicio
- fecha_vencimiento
- estado (activo/pagado/vencido)

### Tabla: pagos
- id (PK)
- prestamo_id (FK)
- monto_pago
- fecha_pago
- metodo_pago
- observaciones

### Tabla: usuarios
- id (PK)
- username
- password
- rol (admin/empleado)
- estado

## 🔐 Seguridad

- Contraseñas encriptadas con password_hash()
- Protección contra inyección SQL usando prepared statements
- Validación de datos en servidor y cliente
- Sistema de sesiones seguras
- Logs de actividades importantes

## 📊 Funcionalidades Detalladas

### Gestión de Clientes
- Agregar, editar, eliminar y buscar clientes
- Historial de préstamos por cliente
- Validación de documentos duplicados

### Gestión de Préstamos
- Crear préstamos con cálculo automático de cuotas
- Diferentes planes: semanal, quincenal, mensual
- Estado del préstamo en tiempo real
- Exportación de tabla de amortización

### Control de Pagos
- Registro de pagos con recibos
- Cálculo automático de saldo pendiente
- Historial de pagos por préstamo
- Múltiples métodos de pago

### Reportes
- Préstamos activos, vencidos y pagados
- Ingresos por período
- Clientes morosos
- Exportación a PDF/Excel

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📧 Contacto

Para preguntas o soporte:
- Email: soporte@sistema-prestamos.com
- Website: https://sistema-prestamos.com

## 🔄 Actualizaciones

### Versión 1.0.0 (Actual)
- Gestión básica de clientes y préstamos
- Sistema de pagos
- Reportes básicos
- Autenticación de usuarios

### Próximas Funcionalidades
- Notificaciones por email/SMS
- Dashboard con gráficos estadísticos
- API REST para integración
- Aplicación móvil
- Firma digital de contratos

---

⭐ Si te resulta útil este proyecto, considera darle una estrella en GitHub