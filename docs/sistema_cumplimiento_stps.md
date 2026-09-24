# Especificaciones Técnicas: Sistema de Cumplimiento STPS con Front-End

## 1. Módulo Administrador

### Gestión de Centros de Trabajo
* **Operaciones:** Nuevo y Editar.
* **Control de Navegación:** Selector del Centro de Trabajo activo.
* **Paginación/Visualización:** Mostrar registros en rangos (1 a 5).
* **Campos de Tabla Principal:**
  * Número de Registro Patronal
  * Nombre del Centro de Trabajo
  * RFC
  * Estado
  * Ciudad

### Gestión de Usuarios y Permisos
* **Descripción:** Asignación, modificación y organización de permisos de usuarios y cambio de contraseñas.
* **Formulario de Registro (Nuevo/Editar):**
  * Nombre(s)
  * Apellido Paterno
  * Apellido Materno
  * Puesto (Selección desde catálogo dinámico)
  * Correo electrónico (Credencial de acceso)
  * Teléfono
* **Componentes de Interfaz:** Botones de *Guardar*, *Editar*, *Cancelar* y panel lateral con la *Lista de Usuarios Agregados*.

### Preferencias de Avisos
* **Configuración de Alertas:** Selector de anticipación para vencimiento de contratos:
  * [ ] Vencimiento en 1 día
  * [ ] Vencimiento en 5 días
  * [ ] Vencimiento en 15 días
* **Componentes de Interfaz:** Botón de *Guardar Preferencias*.

---

## 2. Módulo de Configuración Base

### 2.1 Registros Patronales
Administración del Número de Identificación Patronal otorgado por el IMSS (Acciones: Nuevo, Editar, Eliminar).

#### Formulario de Identificación Corp.
* **Registro Patronal:** Cadena de caracteres alfanumérica.
* **Nombre o Razón Social:** Texto completo.
* **RFC:** Alfanumérico con validación oficial.
* **Régimen Capital (Hacienda):** Selector exclusivo (`S.A.`, `S.C.`, `S.C. de A.P.`, `de R.L.`, `de C.V.`).
* **Régimen Fiscal:** Catálogo desplegable con los regímenes fiscales vigentes en México.

#### Sector y Actividad Económica
* **División Principal:** Servicio para Empresas, Personas y el Hogar.
* **Catálogo Desplegable de Actividades:**
  * Instituciones de Crédito, seguros y fianzas
  * Servicios colaterales a las instituciones
  * Servicios relacionados con inmuebles
  * Servicios profesionales y técnicos
  * Servicios de instalación de maquinaria y equipo en general
  * Servicios de alquiler de maquinaria y equipo agrícola
  * Servicios de alquiler de maquinaria y equipo para la construcción con operadores
  * Servicios de alquiler de maquinaria y equipo para la construcción sin operadores
  * Servicio de alquiler de equipo y mobiliario a empresas
  * Servicio de alquiler para el público en general
  * Servicio de alquiler o renta de automóviles, bicicletas y motocicletas
  * Servicios de alojamiento temporal
  * Preparación y servicios de alimentos
  * Preparación y servicios de bebidas alcohólicas
  * Servicios recreativos

#### Datos Domiciliarios e IMSS
* **Dirección:** Calle/Manzana, Número Exterior, Número Interior, Colonia, Código Postal (C.P.).
* **Entidad Federativa:** Desplegable parametrizado con los 32 estados de México.
* **Municipio o Delegación:** Desplegable dinámico dependiente del estado seleccionado.
* **Contacto:** Teléfono.
* **Tipo de Contribución:** Fijo en `3 Tripartita`.
* **Configuraciones Históricas por Año:**
  * Área Geográfica (Desde 01/Enero/2019): *Salarios mínimos generales* o *Frontera Norte*.
  * Valor de la UMA (Unidad de Medida y Actualización).
* **Parámetros Institucionales:**
  * Delegación IMSS (Desplegable por Estado) y Subdelegación IMSS.
  * Prima de Riesgo de Trabajo: Histórico por renglón (*Año*, *Mes*, *% de prima RT*).
  * Clase de Riesgo de Trabajo (Selector exclusivo):
    * Clase I: Ordinario de Vida
    * Clase II: Bajo
    * Clase III: Medio
    * Clase IV: Alto
    * Clase V: Máximo
  * Fracción RT.
* **Validaciones STPS:**
  * Acreditación STPS: Selector binario (`Sí` / `No`).
  * Nombre y Puesto del Representante Legal.

### 2.2 Centros de Trabajo / Sucursales
Administración de sucursales independientes (Acciones: Nuevo, Editar, Eliminar).

#### Formulario Técnico de Sucursal
* Clave del Centro de Trabajo / Sucursal
* Nombre del Centro de Trabajo / Sucursal
* Registro Patronal Asociado (Llave foránea)
* Área Geográfica por Año (*Salarios mínimos generales* / *Frontera Norte*)
* Dirección completa: Calle/Manzana, No. Exterior, No. Interior, Colonia, C.P., Estado, Municipio (Desplegables dependientes)
* Contacto: Teléfono y Correo electrónico
* Impuestos y Riesgos: Tasa ISN (%), Fracción, Clase de Riesgo, Número de trabajadores
* Parámetros de Carga de Incendio (Ingreso Numérico):
  * Superficie construida (m²)
  * Inventario de gases inflamables (Litros)
  * Inventario de líquidos inflamables (Litros)
  * Inventario de líquidos combustibles (Litros)
  * Inventario de sólidos combustibles + mobiliario (Kg)
* Responsable: Nombre del Colaborador Responsable de Sucursal

#### Componentes Visuales
* Botones de *Guardar* y *Cancelar*.
* Panel lateral con la *Lista de Sucursales Agregadas*.
* Paginador de tabla: Mostrar 1, 5, 10 registros.

### 2.3 Estructura Estructural (Departamentos, Puestos y Áreas)
Tres sub-módulos idénticos con acciones de *Nuevo*, *Editar* y *Eliminar*. Incluyen botones de *Guardar*, *Cancelar*, paginación (1, 5, 10) y visualización en formato de tabla con botón directo de *Eliminar* por fila.

* **Departamentos:** Clave del Departamento, Nombre del Departamento.
* **Puestos:** Clave del Puesto, Nombre del Puesto.
* **Áreas:** Clave del Área, Nombre del Área.
* *Nota del sistema:* Integrar submódulos secundarios para Prestaciones, Conceptos fijos y Calendarios de Pago.

---

## 3. Módulo Contractual y de Tercerización

### 3.1 Edición de Contratos Individuales
Permite crear, personalizar y guardar plantillas de contratos de trabajo del Centro de Trabajo.

#### Catálogo Base de Contratos
1. Contrato Individual de Trabajo por Tiempo Determinado
2. Contrato Individual de Trabajo por Tiempo Indeterminado
3. Contrato Individual de Trabajo a Distancia
4. Contrato Individual de Trabajo por Obra Determinado
5. Por Tiempo Indeterminado con Periodo de Capacitación
6. Contrato Individual de Trabajo por Tiempo Indeterminado con Periodo de Prueba

#### Inyección Dinámica de Variables (Tokens de Sustitución)

| Categoría | Token | Descripción |
| :--- | :--- | :--- |
| **Empresa** | `{@Razon_Social_Empresa}` | Razón social del patrón |
| | `{@RFC_Empresa}` | RFC de la empresa |
| | `{@Representante_Legal_Empresa}` | Nombre del representante legal |
| | `{@Domicilio_Empresa}` | Dirección de la matriz o sucursal |
| | `{@Ciudad_Empresa}` | Ciudad de ejecución |
| | `{@RegPat_Empresa}` | Registro patronal asignado |
| | `{@Logo_Empresa}` | Logotipo de la organización |
| **Empleado** | `{@Nombre_Empleado}` | Nombre completo del trabajador |
| | `{@RFC_Empleado}` | RFC con homoclave |
| | `{@CURP_Empleado}` | Clave Única de Registro de Población |
| | `{@NSS}` | Número de Seguridad Social |
| | `{@Edad_Empleado}` | Edad cronológica |
| | `{@Fecha_Ingreso_Empleado}` | Fecha de alta original |
| | `{@Domicilio_Empleado}` | Dirección particular del trabajador |
| | `{@Ocupacion_Empleado}` | Ocupación según catálogo |
| | `{@Sexo_Empleado}` | Sexo registrado |
| | `{@Edo_Civil_Empleado}` | Estado civil |
| | `{@Salario_Diario_Empleado}` | Cuota diaria de salario |
| | `{@Sueldo_Mensual_Empleado}` | Sueldo mensualizado ordinario |
| | `{@Numero_De_Cuenta_Empleado}` | Cuenta bancaria para dispersión |
| | `{@Nacionalidad_Empleado}` | Nacionalidad del colaborador |
| | `{@Puesto_Empleado}` | Puesto asignado |
| | `{@Fecha_Baja_Empleado}` | Fecha de término laboral |
| **Otras** | `{@Motivo_Contratacion}` | Justificación técnica de la relación laboral |
| | `{@Periodo_Contratacion}` | Vigencia pactada |
| | `{@Fecha_De_Elaboracion}` | Fecha de emisión del documento |
| | `{@Cantidad_Letra}` | Salario diario convertido a texto |
| | `{@Cantidad_Letra_Mensual}` | Sueldo mensual convertido a texto |
| | `{@Fecha_Corta}` | Formato DD/MM/AAAA |

* **Herramientas del Editor:** Barra de formato (color, alineación, tipografía, tamaño de letra).
* **Acciones:** *Vista Previa*, *Guardar*, *Cancelar*, *Exportar a PDF*, *Imprimir*.

### 3.2 Importar Datos de Subcontratación (Layout SAT)
Interfaz para declarar informativas sobre trabajadores tercerizados para timbrado de nómina.

#### Filtros de Procesamiento
* Registro Patronal
* Sucursal
* Año
* Tipo de Nómina
* Periodo a procesar (No. __)
* Nómina a Calcular

#### Flujo de Operación
1. Descarga del formato base de captura.
2. Llenado obligatorio de campos: **RFC** (de la empresa cliente que subcontrata) y **Porcentaje de tiempo** (Valor entero sin el símbolo `%`).
3. Guardado en formato estricto: `Libro de Excel 97-2003 (.xls)` conservando el nombre original.
4. Carga mediante inputs de *Examinar* y *Cargar*.

---

## 4. Gestión de Documentación y Expedientes

### 4.1 Expediente Documental del Centro de Trabajo
Repositorio digital para archivos en formato PDF o Imagen corporativa:
* [ ] Escritura: Acta Constitutiva y/o Modificaciones Notariales
* [ ] Escritura: Poderes Notariales de los Representantes
* [ ] Constancia de Situación Fiscal (Actualizada)
* [ ] Organigrama Vigente
* [ ] Manual de Descripción de Puestos
* [ ] Reglamento Interior de Trabajo (Registrado ante la autoridad)

### 4.2 Carga Masiva de Datos de Trabajadores (Layouts .CSV)
* **Formato Obligatorio:** Archivo de texto plano delimitado por comas (`.csv`).
* **Estructura Visual:** Encabezados en **Azul** (Campos obligatorios) y **Gris** (Campos opcionales).
* **Filtros de Destino:** Selección de *Registro Patronal* y *Sucursal* antes de la carga.

#### Nombres de Archivo Estrictos para Importación
* Trabajadores: `Trabajadores.csv`
* Incapacidades: `Incapacidades.csv`
* ISR: `ISR.csv`
* Movimientos IMSS: `MovIMSS.csv`
* Movimientos de Nómina Histórico: `MovNominaHistorico.csv`
* Faltas: `Faltas.csv`
* Cambio Sucursal: `CambioSucursal.csv`
* Movimientos de Nómina por Periodo: `MovNominaMasivo.csv`
* Movimientos de Nómina por Fecha: `MovNominaFecha.csv`

#### Actualización Masiva de Colaboradores
1. Descargar Formatos.
2. El campo `TRAB_ID` (Clave de trabajador original) es el identificador único obligatorio.
3. Modificar únicamente las columnas con datos a actualizar.
4. Exportar como `.csv` (Delimitado por comas) y ejecutar acción *Cargar*.

---

## 5. Administrador de Colaboradores y Trabajadores

### Panel de Control Cuantitativo
* Filtros rápidos: `Trabajadores Vigentes` / `Trabajadores No Vigentes`.
* Contadores automáticos en tiempo real de plantillas vigentes e inactivas.
* Acciones globales: *Ordenar por nombre*, *Exportar a PDF*, *Exportar a EXCEL*.

### Perfil Detallado del Trabajador (Campos de Formulario)
* **Fotografía:** Archivo digital en formato de imagen, tamaño máximo **50kb** (Carga local).
* **Datos de Identidad:** Nombre(s), Apellido Paterno, Apellido Materno, Sexo, Fecha de Nacimiento, Lugar de Nacimiento.
* **Seguridad Social:** NSS (Número de Seguridad Social), UMF (Unidad Médica Familiar).
* **Validación Fiscal:** 
  * Dirección de Domicilio y C.P. (Conforme a la Constancia de Situación Fiscal).
  * RFC (Incluye botón nativo de **Validar RFC**).
  * CURP (Incluye botón nativo de **Validar CURP**).
* **Asignación Organizacional:** Registro Patronal, Clave de Colaborador, Puesto, Departamento, Área, Centro de Trabajo / Sucursal, Fecha de Ingreso, Fecha de Alta/Reingreso IMSS.
* **Tipo de Trabajador IMSS:** `Permanente`, `Eventual`, `Construcción`.
* **Esquema de Prestaciones:** `De ley`, `Personalizadas`.
* **Esquema de Jornada:**
  * *Jornada/Semana Reducida:* Completa, 1 día, 2 días, 3 días, 4 días, 5 días, 6 días, Jornada Reducida.
  * *Tipo de Jornada:* Diurna, Nocturna, Mixta, Por Hora, Reducida, Por turnos, Otra Jornada (Campo abierto).
* **Configuración Económica:**
  * *Tipo de Salario:* Fijo, Variable, Mixto.
  * Salario Diario (SD).
  * Salario Diario Integrado (SDI / SBC). *Alerta en UI:* "Recuerda actualizar el SBC a más tardar 5 días hábiles después de su aniversario".
  * *Forma de Pago:* Efectivo, Cheque, Transferencia (Con selector de Banco), Depósito (Con selector de Banco).
  * *Régimen de Contratación:* Sueldos y Salarios, Ingresos Asimilados a Salarios, Actividades Profesionales Honorarios.
  * *Tipo de Contrato:* Lista con las 9 modalidades contractuales oficiales de la LFT.
* **Contacto:** Correo Electrónico, No. de Celular.
* **Sección Exclusiva para Trabajadores No Vigentes:** Conserva la ficha histórica y adiciona los campos obligatorios de: *Último día pagado* y *Fecha de Baja IMSS*.

---

## 6. Relaciones Laborales y Expediente Individual

### Matriz del Expediente Laboral (Estructura de Datos)
Cada documento adjunto debe registrarse bajo la siguiente estructura relacional:
`[Norma] | [Requisito] | [Evidencia] | [Responsable] | [Frecuencia] | [Riesgo] | [Estatus (Sí/No)] | [Acción Correctiva] | [Fecha Compromiso] | [Sanción Mínima/Máxima] | [Fundamento Legal de Sanción]`

#### Etapa de Ingreso
* **Contrato Individual Firmado:** LFT Art 24 y Art 804 F I. (Metadata: Tipo de contrato seleccionado).
* **Documentación de Identidad e Historial:** LFT Art 24 y Art 804 F I.
  * Acta de Nacimiento, Identificación Oficial Vigente, CURP, Constancia de Situación Fiscal, Curriculum Vitae, Solicitud de empleo, Título Profesional.
* **Cumplimiento de Reglamento Interior:** LFT Reglamento Interior de Trabajo.
  * Certificado de estudios, Cédula Profesional, NSS, Alta en IMSS oportuna, Hoja de retención INFONAVIT, Comprobante de domicilio (< 2 meses), Cartas de recomendación, Licencia de conducir, Designación de beneficiarios en caso de defunción.

#### Etapa de Permanencia (Vigente)
* **Seguridad Social:** LSS Art 15 (Modificaciones salariales).
* **Salud Laboral:** LFT Art 42 F II (Certificados médicos e Incapacidades IMSS).
* **Operación Diaria:** LFT (Descripción de puesto, Carta de confidencialidad, Notificación del RIT Art 425).
* **Controles y Pagos:** LFT Art 804 F III (Control de asistencia), LFT Art 101 y 804 F II (Recibos de nómina timbrados).
* **Derechos Laborales:** LFT Art 81 (Constancia de antigüedad con vacaciones), LFT Art 76 (Solicitud y registro de vacaciones), LFT Art 80 y 804 F IV (Recibo de Prima Vacacional y Aguinaldo).
* **Privacidad y Derechos de Autor:** 
  * LFPDPPP Art 3 FI, 8, 17, 21 (Aviso de Privacidad, Consentimiento de datos, Contrato de Confidencialidad).
  * Ley de Derechos de Autor Art 87 (Uso de imagen).
* **Herramientas de Control Interno:** Cartas responsivas de equipo, Actas administrativas.

#### Etapa de Egreso / Baja
* **Documentos de Desvinculación:** LFT 484 B.
  * Convenio de Terminación Laboral, Desglose de Finiquito o Liquidación, Carta de renuncia voluntaria, Copia de cheque o método de pago, Recibo de nómina timbrado de Finiquito/Liquidación, Ratificación ante el Centro de Conciliación Laboral.
* **Seguridad Social:** LSS Art 15 (Baja del IMSS en tiempo y forma).

### Consultas Especializadas de Personal
Filtro avanzado por Estatus (`Vigente` o `Egreso/Baja`) cruzado con: Rango de fechas, Puestos, Áreas y Sucursales.

---

## 7. Módulo: Seguridad y Salud en el Trabajo (Capa Técnico)

### 7.1 Evaluación Inicial (Diagnóstico de NOMs Aplicables)
Formulario tipo checklist con respuestas exclusivas (**SÍ / NO**) que activa de forma condicional la aplicabilidad de las Normas Oficiales Mexicanas.

#### Bloques de Preguntas del Formulario

```text
1. Edificios, locales e instalaciones (NOM-001-STPS)
   - ¿Desarrolla actividades de producción, comercialización, transporte, almacenamiento o servicios en instalaciones o áreas exteriores (pasillos, carga/descarga, tránsito de vehículos)?
   - Elementos presentes: Escaleras, Rampas, Escalas, Puentes/Plataformas elevadas, Áreas de tránsito vehicular, Espuelas de ferrocarril activas, Sistemas de ventilación artificial.
2. Maquinaria y Equipo
   - ¿En su centro de trabajo se utiliza maquinaria o equipo?
3. Agentes Físicos (Ruido, Radiaciones, Presión, Temperatura, Vibraciones)
   - Ruido: ¿Áreas con niveles > 80 dB? / ¿Los trabajadores necesitan levantar la voz a 1 metro de distancia en operación normal?
   - Radiaciones no ionizantes: ¿Actividades cerca de subestaciones eléctricas, torres de telecomunicaciones o motores de alto voltaje?
   - Presiones ambientales anormales: ¿Actividades a > 3000 metros sobre el nivel del mar? / ¿Actividades de buceo?
   - Condiciones térmicas extremas: ¿Exposición a fuentes que bajen la temperatura corporal a < 36°C o la eleven a > 38°C (climáticas o de proceso)?
   - Vibraciones: ¿Exposición del personal de mando oaxiales?
4. Procesos Peligrosos (Soldadura, Altura, Espacios Confinados, Electricidad)
   - Soldadura y corte: ¿Se realizan estas actividades?
   - Trabajos en altura: ¿Mantenimiento, limpieza, demolición o tareas a > 1.80 metros de altura o riesgo de caída en aberturas (pozos, cubos)?
   - Construcción: ¿El centro de trabajo ejecuta obras de construcción?
   - Mantenimiento eléctrico: ¿Tareas en líneas eléctricas aéreas, subterráneas o energizadas? / ¿Existen instalaciones eléctricas permanentes o provisionales?
   - Recipientes sujetos a presión y calderas: ¿Uso de compresores, intercambiadores, marmitas, autoclaves o calderas? (Excepciones parametrizadas según visual de la NOM-020-STPS-2011). ¿Uso de recipientes criogénicos?
   - Radiaciones ionizantes: ¿Uso de equipos de radiografía industrial/médica, medición por rayos gamma, ultrasonido o materiales radiactivos específicos (Americio 241, Cobalto 60, Tritio, etc.)?
   - Espacios confinados: ¿Se realizan trabajos en estas condiciones?
   - Electricidad estática: ¿Materiales, sustancias o equipos que almacenen cargas estáticas o generen fricción?
5. Factores Organizacionales y Humano
   - Discapacidad: ¿Laboran trabajadores con discapacidad en el centro? (NOM-034-STPS).
   - Riesgo Ergonómico (Manejo de cargas): ¿Manejo manual de cargas > 3kg de forma cotidiana (más de una vez al día)?
   - Teletrabajo: ¿Personal bajo la modalidad de Teletrabajo en domicilios particulares? (NOM-037-STPS).
   - Maquinaria de materiales: ¿Uso de maquinaria para mover materias primas, productos o residuos?
   - Protección contra Incendios (NOM-002-STPS): Cuestionario complementario de carga de fuego (Mismos campos numéricos de sección 2.2). ¿Inventario de materiales pirofóricos o explosivos?
   - Sustancias químicas: ¿Manejo, transporte o almacenamiento de sustancias capaces de contaminar el medio ambiente laboral o alterar la salud?
   - Organización Interna (Checklist rápido): ¿Cuenta con Comisión de Seguridad e Higiene? ¿Servicios preventivos de salud? ¿Identificación de riesgos psicosociales? ¿Señales de seguridad? ¿Iluminación insuficiente? ¿Dotación y capacitación en EPP?
```

#### Motor de Reportes del Diagnóstico
Al guardar las respuestas, el sistema genera automáticamente un **Reporte en PDF** con el título formal: `Respuestas proporcionadas por el Centro de Trabajo a las preguntas formuladas para identificar las Normas Oficiales Mexicanas de Seguridad y Salud en el Trabajo aplicables.`
* **Encabezado:** Logotipo institucional en la esquina superior izquierda y fecha/hora exacta de guardado.
* **Metadata del Centro:** Razón Social, Área/Departamento (Valor predeterminado: *Todo el Centro de Trabajo*), Dirección Matriz, Teléfono, Correo electrónico.
* **Entregables Vinculados:**
  1. *Documento de NOMs Aplicables por secciones:* Contiene el Número de la Norma, Título, Obligaciones del Patrón y del Trabajador, y Disposiciones específicas. Leyenda al final: *"Información que sustenta la evaluación, se anexa el documento de cumplimiento de indicadores proporcionado por el Centro de Trabajo y la puntuación por capítulo, apartado e indicador."*
  2. *Documento de NOMs Aplicables por tipo de requisito.*
  3. *Documento de Límites, Medición y riesgos de sustancias químicas.*
  4. *Documento de resultados por sección y por requisito.*

### 7.2 Tablero de Control y Matriz de Autogestión
* **Tablero Ejecutivo:** Indicadores de Cumplimiento Global, Número de NOMs evaluadas, Acciones abiertas y conteo de Riesgos altos.
* **Ficha por NOM:** Porcentaje de cumplimiento, indicadores específicos, repositorio de evidencia, nivel de riesgo y acciones preventivas/correctivas asociadas.

#### Algoritmo de Evaluación STPS
El motor de evaluación utiliza la lógica oficial del programa de autogestión de la STPS:
* **Acciones Preventivas:** Otorgan **5 / 4 / 3 puntos** según el grado de cumplimiento.
* **Acciones Correctivas:** Otorgan **2 / 1 / 0 puntos** según la severidad del rezago.

#### Matriz de Evaluación de NOMs (Estructura de Base de Datos)
`[NOM] | [Indicador] | [Medio de Verificación] | [Resultado] | [Puntuación] | [Riesgo] | [Acción] | [Fecha] | [Responsable]`

#### Plan de Acción Automático
Cualquier incumplimiento (Respuesta 'No' o baja puntuación) genera en automático un registro en el catálogo de acciones correctivas/preventivas con la siguiente estructura de seguimiento:
`[Estrategia (Conservar/Mejorar/Actualizar/Complementar/Corregir/Realizar)] | [Responsable asignado] | [Fecha de Inicio] | [Fecha de Término] | [Estatus del Ticket]`

---

## 8. Catálogo y Clasificación Automática de NOMs

El sistema activa bitácoras digitales y carpetas de evidencia técnica divididas en 4 grandes categorías de acuerdo con el resultado del diagnóstico:

```text
├── NORMAS DE SEGURIDAD (Color Rojo)
│   ├── NOM-001-STPS (Edificios, Locales e Instalaciones)
│   ├── NOM-002-STPS (Prevención y Protección contra Incendios)
│   ├── NOM-022-STPS (Electricidad Estática)
│   ├── NOM-029-STPS (Mantenimiento de Instalaciones Eléctricas)
│   └── NOM-034-STPS (Trabajadores con Discapacidad)
│
├── NORMAS DE SALUD (Color Azul)
│   ├── NOM-025-STPS (Condiciones de Iluminación)
│   └── NOM-035-STPS (Factores de Riesgo Psicosocial)
│
├── NORMAS DE ORGANIZACIÓN (Color Naranja)
│   ├── NOM-017-STPS (Equipo de Protección Personal - EPP)
│   ├── NOM-019-STPS (Comisiones de Seguridad e Higiene)
│   ├── NOM-026-STPS (Colores y Señales de Seguridad)
│   └── NOM-030-STPS (Servicios Preventivos de Seguridad y Salud)
│
└── NORMAS ESPECÍFICAS (Color Amarillo)
    └── NOM-037-STPS (Teletrabajo - Condiciones de Seguridad)
```

---

## 9. Módulo de Capacitación y Evidencia Documental

### 9.1 Gestión de Cursos (Programa de Capacitación Anual)
Formulario de alta: Nombre del Curso, Modalidad (`Presencial`, `Virtual`, `Mixta`), Fecha de Impartición. Clasificación automática por categorías:

* **Competencias Socioemocionales:** Comunicación efectiva en el trabajo, Trabajo en Equipo.
* **Estándar de Competencia:** Integración y funcionamiento de comisiones mixtas de Capacitación.
* **Formación Adicional:** Hábitos y estilos de vida saludable en el trabajo (Aprende con Reyhan), Violencia contra las mujeres en el ámbito laboral, Prevención de adicciones en el ámbito laboral.
* **Formación Empresarial:** Curso de competencias de liderazgo y trabajo en equipo para la Alta Dirección Pública.
* **Productividad Laboral:** Administración de la capacitación y desarrollo de los recursos humanos I y II.
* **Seguridad y Salud en el Trabajo:** Catálogo completo de inducción a las NOMs vigentes (NOM-035, NOM-002 Uso de extintores, NOM-022, NOM-029, NOM-034, NOM-025, NOM-017 Uso de EPP, NOM-019 Brigadas, NOM-026, NOM-030 Seguridad Vial, NOM-037 Teletrabajo).

### 9.2 Repositorio de Evidencias (Estructura de Nomenclatura de Archivos)
Los documentos cargados para validar un curso deben seguir la estructura de sufijo de fecha de ejemplo (`-CURSO-DDMMAA`):
* `LISTA_DE_VERIFICACION-CURSO-010126`
* `PLANEACION-CURSO-010126`
* `PRESENTACION-CURSO-010126`
* **Instrumentos de Evaluación:** `EVALUACION_DIAGNOSTICA-`, `GUIA_OBSERVACION-`, `LISTA_COTEJO-`, `CUESTIONARIO-`, `CUESTIONARIO_FINAL-`.
* `INFORME_FINAL-CURSO-010126`
* `ENCUESTA_DE_SATISFACCION-CURSO-010126`
* `FOTOGRAFIAS-CURSO-010126`

### 9.3 Constancias y Certificados
* **Formatos Disponibles:** Constancia de competencias laborales (Formato Oficial **DC-3**), DC-3 Capacitación Uso de EPP, Certificado de Competencias Laborales (`COMPLAB-CURSO-DDMMAAAA-NOMBRE`), Reconocimiento al Desempeño (`REC-CURSO-DDMMAAAA-AÑO`).

### 9.4 Capacitación Especializada de Brigadas (NOM-002)
Estructuras de capacitación específicas para las brigadas de emergencia del centro de trabajo:
* **Brigada de Evacuación:** Procedimientos de evacuación y Control de personal.
* **Brigada de Primeros Auxilios:** Atención primaria, RCP básico y Uso de botiquines.
* **Brigada Contra Incendios:** Uso y manejo de extintores, Prevención de incendios.
* **Brigada de Comunicación:** Manejo de emergencias y Canales de comunicación interna.

---

## 10. Módulo de Bitácoras Digitales y Alertas de Mantenimiento

### 10.1 Alertas Automáticas de Mantenimiento Preventivo (NOM-004 / NOM Básicas)
El sistema gestiona e interrumpe con notificaciones las siguientes tareas críticas:
* **NOM-002:** Alerta de mantenimiento preventivo para Extintores, Hidrantes y Alarmas contra incendio.
* **NOM-022:** Alerta anual de prueba de resistencia de red de puesta a tierra y pararrayos.
* **NOM-029:** Alerta de mantenimiento preventivo eléctrico anual.
* **NOM-025:** Alerta de mantenimiento preventivo a luminarias e iluminación de emergencia.
* **NOM-030:** Alerta de mantenimiento preventivo al equipo de transporte corporativo.

### 10.2 Bitácoras de Evidencia Fotográfica
Estructura de carga indexada para validación visual en campo:
* `FOTO-EPP-ENTREGA_O_REPOSICION`: Registro de entrega de EPP firmado por el trabajador.
* `FOTO-PROGRAMA_DE_ORDEN_Y_LIMPIEZA`: Evidencia de las condiciones del entorno.
* `FOTO-PROGRAMA_DE_MANTENIMIENTO_PREVENTIVO`: Reporte fotográfico de Órdenes de Trabajo (**Antes y Después**).
* `FOTO-AREA_DE_TELETRABAJO`: Validación de las condiciones seguras del hogar del teletrabajador.
* `NOM026-Acta`: Acta de verificación de cierre e inspección de señales de seguridad.

---

## 11. Comisiones Mixtas y Simulacros

### 11.1 Comisión de Seguridad e Higiene (NOM-019)
* **Requisito Obligatorio:** Acta de Integración cargada en el expediente electrónico.
* **Flujos Operativos:** Registro de *Recorridos de Verificación* e *Investigación de Accidentes y Enfermedades*.
* **Módulo de Siniestros (Historial):** Accidentes de trabajo, Enfermedades laborales, Incapacidades permanentes, Defunciones, Días subsidiados por el centro de trabajo.

### 11.2 Comisión de Atención y Diagnóstico (NOM-030)
* Carga del Diagnóstico de Seguridad y Salud.
* **Simulacros (NOM-002 / NOM-033):** Programación obligatoria semestral de simulacros contra incendio. Campos: *Evaluación de tiempos de respuesta* y carga de *Evidencia Fotográfica*.

---

## 12. Métricas y KPIs Centrales de Cumplimiento

El sistema procesa la información operativa de manera interna y muestra en pantalla un set de KPIs generales con metas estrictas fijadas por la metodología STPS:

* **Colaboradores Capacitados:** $	ext{Meta} = 100\%$
* **Cursos Impartidos conforme a Programa:** $	ext{Meta} = 100\%$
* **Accidentes Eléctricos Registrados:** $	ext{Meta} = 0\%$
* **Incidentes Reportados con Seguimiento:** $	ext{Meta} = 100\%$
* **Inspecciones Realizadas a Tiempo:** $	ext{Meta} \ge 95\%$
* **Mantenimiento Programado Cumplido:** $	ext{Meta} \ge 95\%$

---

## 13. Auditoría y Diagnóstico Integral Final

### Módulo de Inspección y Recorridos Físicos
* **Acta de Recorrido de Campo:** Datos desglosados por área analizando el estado de Extintores, Señalización, Rutas de Evacuación, Salidas de Emergencia, Alarmas, Botiquines, Brigadas, Simulacros y Reporte de Hallazgos/Condiciones Inseguras.
* **Entrevistas de Constatación:** Cuestionario digital para el personal. Campos obligatorios: *ID del Trabajador*, *Puesto*, *Antigüedad*, *Área*, *Nivel de conocimiento de rutas de evacuación*, *Nivel de conocimiento sobre cómo reportar riesgos*, *Pregunta abierta* y *Observaciones del auditor*.

### Entregable Ejecutivo Final
Genera un documento consolidado exportable a formatos **PDF/Word/Impresión**, estructurado con:
1. Portada institucional personalizada con el logotipo de la Cooperativa y datos del Centro de Trabajo.
2. Semáforo ejecutivo de cumplimiento global.
3. Desglose analítico de porcentajes e indicadores por cada NOM evaluada.
4. Registro consolidado de hallazgos, entrevistas y accidentes de trabajo.
5. Plan de acción automatizado con matriz de riesgos integrada.