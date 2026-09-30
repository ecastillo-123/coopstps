<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Módulos del sistema
    |---------------------------------------------------------------------------
    |
    | Fuente única de verdad para la navegación, los permisos y los roles.
    | Cada módulo declara un permiso propio; cada ítem puede declarar el suyo.
    | Un ítem con 'ruta' nulo aún no está implementado y se muestra deshabilitado.
    |
    | El orden de este arreglo es el orden de aparición en cada barra.
    | 'ubicacion' => 'superior' coloca el módulo en la barra horizontal con
    | submenús desplegables al hacer clic; por defecto van al menú lateral.
    | 'color' elige la paleta del botón superior (blue, indigo, emerald, amber).
    | 'bloque' aplica un estilo distintivo a los módulos laterales (amber, sky).
    |
    */

    'modulos' => [
        'informacion_general' => [
            'nombre' => 'Información General del Centro de Trabajo',
            'ubicacion' => 'superior',
            'color' => 'blue',
            'descripcion' => 'Expediente documental del centro de trabajo.',
            'icono' => 'building',
            'permiso' => 'informacion-general.ver',
            'items' => [
                ['nombre' => 'Escritura / Acta constitutiva', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Poderes notariales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Constancia de situación fiscal', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Organigrama', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Descripción de puestos', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Reglamento interior', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'administrador' => [
            'nombre' => 'Administrador',
            'ubicacion' => 'superior',
            'color' => 'indigo',
            'descripcion' => 'Centros de trabajo, usuarios, preferencias y revisiones anuales.',
            'icono' => 'settings',
            'permiso' => 'administracion.gestionar',
            'items' => [
                ['nombre' => 'Centros de trabajo', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Usuarios y permisos', 'ruta' => 'admin.users.index', 'implementado' => true, 'rol' => 'Administrador'],
                ['nombre' => 'Preferencias de avisos', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Revisión anual de asignaciones', 'ruta' => null, 'implementado' => false],
                [
                    'nombre' => 'Evidencia jurídica del DOF',
                    'ruta' => 'admin.legal-evidence.index',
                    'implementado' => true,
                    'permiso' => 'legal-evidence.manage',
                ],
            ],
        ],

        'configuracion' => [
            'nombre' => 'Configuración',
            'ubicacion' => 'superior',
            'color' => 'emerald',
            'descripcion' => 'Registros patronales, sucursales, estructura organizacional y contratos.',
            'icono' => 'cog',
            'permiso' => 'configuracion.gestionar',
            'items' => [
                ['nombre' => 'Registros patronales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Centros de trabajo / Sucursales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Estructura organizacional', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Contratos individuales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Importar datos SAT', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'colaboradores' => [
            'nombre' => 'Colaboradores / Trabajadores',
            'ubicacion' => 'superior',
            'color' => 'amber',
            'descripcion' => 'Administración de trabajadores, contratos y cargas masivas.',
            'icono' => 'users',
            'permiso' => 'colaboradores.gestionar',
            'items' => [
                ['nombre' => 'Trabajadores', 'ruta' => 'workforce.workers', 'implementado' => true],
                ['nombre' => 'Contratos', 'ruta' => 'workforce.contracts', 'implementado' => true],
                ['nombre' => 'Importar CSV', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Actualización masiva', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'relaciones_laborales' => [
            'nombre' => 'Relaciones Laborales',
            'ubicacion' => 'lateral',
            'bloque' => 'amber',
            'descripcion' => 'Expediente laboral individual y consultas por estatus.',
            'icono' => 'folder',
            'permiso' => 'relaciones.gestionar',
            'items' => [
                ['nombre' => 'Expediente individual', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Consultas por estatus', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'seguridad_salud' => [
            'nombre' => 'Seguridad y Salud en el Trabajo',
            'ubicacion' => 'lateral',
            'bloque' => 'sky',
            'descripcion' => 'SST, capacitación, auditorías y diagnóstico integral.',
            'icono' => 'shield',
            'permiso' => 'seguridad.ver',
            'items' => [
                ['nombre' => 'Hallazgos', 'ruta' => 'sst.findings', 'implementado' => true],
                ['nombre' => 'Acciones correctivas', 'ruta' => 'sst.actions', 'implementado' => true],
                ['nombre' => 'Comisiones SST', 'ruta' => 'sst.commissions', 'implementado' => true],
                ['nombre' => 'Mantenimiento', 'ruta' => 'sst.maintenance', 'implementado' => true],
                ['nombre' => 'Capacitación', 'ruta' => 'training.courses', 'implementado' => true],
                ['nombre' => 'Inspecciones', 'ruta' => 'audit.inspections', 'implementado' => true],
                ['nombre' => 'Auditorías', 'ruta' => 'audit.audits', 'implementado' => true],
                ['nombre' => 'Diagnóstico integral', 'ruta' => 'audit.diagnosis', 'implementado' => true],
            ],
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Permisos de acción independientes de la navegación
    |---------------------------------------------------------------------------
    |
    | Acciones sensibles que no se renderizan como ítems de menú, pero que se
    | sincronizan con el catálogo de permisos de Spatie.
    |
    */

    'permisos' => [
        'legal-evidence.manage',
        'personal.registrar',
        'personal.modificar',
        'personal.eliminar',
        'personal.aprobar',
        'nom035.registrar',
        'nom035.modificar',
        'capacitacion.registrar',
        'capacitacion.modificar',
        'auditorias.registrar',
        'auditorias.modificar',
        'reportes.exportar',
    ],

    /*
    |---------------------------------------------------------------------------
    | Roles y sus permisos
    |---------------------------------------------------------------------------
    |
    | El comodín '*' otorga todos los permisos del catálogo.
    |
    */

    'roles' => [
        'Administrador' => [
            'descripcion' => 'Acceso total al sistema y a la administración.',
            'permisos' => ['*'],
        ],
        'Usuario' => [
            'descripcion' => 'Operación de los módulos de cumplimiento, sin administración.',
            'permisos' => [
                'informacion-general.ver',
                'administracion.gestionar',
                'configuracion.gestionar',
                'colaboradores.gestionar',
                'relaciones.gestionar',
                'seguridad.ver',
                'personal.registrar',
                'personal.modificar',
                'nom035.registrar',
                'nom035.modificar',
                'capacitacion.registrar',
                'capacitacion.modificar',
                'auditorias.registrar',
                'auditorias.modificar',
                'reportes.exportar',
            ],
        ],
        'Auditor' => [
            'descripcion' => 'Consulta y auditoría, sin capacidad de gestión.',
            'permisos' => [
                'informacion-general.ver',
                'seguridad.ver',
                'auditorias.registrar',
                'auditorias.modificar',
            ],
        ],
    ],
];
