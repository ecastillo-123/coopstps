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
    | 'color' elige la paleta del botón superior (blue, emerald, amber, violet, rose).
    |
    */

    'modulos' => [
        'administracion' => [
            'nombre' => 'Administrador',
            'ubicacion' => 'superior',
            'color' => 'blue',
            'descripcion' => 'Centros de trabajo, usuarios y preferencias de avisos.',
            'icono' => 'settings',
            'permiso' => 'administracion.gestionar',
            'items' => [
                ['nombre' => 'Centros de trabajo', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Usuarios y permisos', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Preferencias de avisos', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'configuracion' => [
            'nombre' => 'Configuración',
            'ubicacion' => 'superior',
            'color' => 'emerald',
            'descripcion' => 'Registros patronales, sucursales y estructura organizacional.',
            'icono' => 'cog',
            'permiso' => 'configuracion.gestionar',
            'items' => [
                ['nombre' => 'Registros patronales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Centros de trabajo / Sucursales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Estructura organizacional', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'documentacion' => [
            'nombre' => 'Documentación',
            'ubicacion' => 'superior',
            'color' => 'amber',
            'descripcion' => 'Expediente documental del centro de trabajo y cargas masivas.',
            'icono' => 'archive',
            'permiso' => 'documentacion.gestionar',
            'items' => [
                ['nombre' => 'Expediente del centro', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Carga masiva de datos', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'contractual' => [
            'nombre' => 'Contractual',
            'ubicacion' => 'superior',
            'color' => 'violet',
            'descripcion' => 'Contratos individuales y subcontratación (layout SAT).',
            'icono' => 'document',
            'permiso' => 'contractual.gestionar',
            'items' => [
                ['nombre' => 'Contratos individuales', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Subcontratación (SAT)', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'colaboradores' => [
            'nombre' => 'Colaboradores y Trabajadores',
            'ubicacion' => 'superior',
            'color' => 'rose',
            'descripcion' => 'Administrador de colaboradores y trabajadores.',
            'icono' => 'users',
            'permiso' => 'colaboradores.gestionar',
            'items' => [
                ['nombre' => 'Trabajadores', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Consultas especializadas', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Cargas masivas', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'relaciones' => [
            'nombre' => 'Relaciones Laborales',
            'descripcion' => 'Matriz del expediente laboral individual.',
            'icono' => 'folder',
            'permiso' => 'relaciones.gestionar',
            'items' => [
                ['nombre' => 'Expediente individual', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'cumplimiento' => [
            'nombre' => 'Cumplimiento',
            'descripcion' => 'Núcleo de cumplimiento: tablero, indicadores, alertas y reportes.',
            'icono' => 'gauge',
            'permiso' => 'cumplimiento.ver',
            'items' => [
                ['nombre' => 'Tablero', 'ruta' => 'dashboard', 'implementado' => true],
                ['nombre' => 'Indicadores y KPIs', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Alertas', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Reportes', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'seguridad' => [
            'nombre' => 'Seguridad y Salud',
            'descripcion' => 'Diagnóstico de NOMs aplicables, indicadores y sustancias químicas.',
            'icono' => 'shield',
            'permiso' => 'seguridad.ver',
            'items' => [
                ['nombre' => 'Diagnóstico de NOMs', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'NOMs aplicables', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Indicadores por NOM', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Sustancias químicas', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'capacitacion' => [
            'nombre' => 'Capacitación',
            'descripcion' => 'Programa anual de cursos, bitácoras y evaluaciones.',
            'icono' => 'academic',
            'permiso' => 'capacitacion.ver',
            'items' => [
                ['nombre' => 'Cursos', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Bitácoras', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Evaluaciones', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Constancias y DC-3', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'mantenimiento' => [
            'nombre' => 'Mantenimiento',
            'descripcion' => 'Mantenimiento preventivo y bitácoras de evidencia.',
            'icono' => 'wrench',
            'permiso' => 'mantenimiento.ver',
            'items' => [
                ['nombre' => 'Equipos y activos', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Revisiones programadas', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Bitácoras fotográficas', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'auditorias' => [
            'nombre' => 'Auditorías STPS',
            'descripcion' => 'Inspecciones, entrevistas de constatación e informes.',
            'icono' => 'clipboard',
            'permiso' => 'auditorias.ver',
            'items' => [
                ['nombre' => 'Inspecciones y recorridos', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Entrevistas', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Informes', 'ruta' => null, 'implementado' => false],
            ],
        ],

        'simulacros' => [
            'nombre' => 'Simulacros',
            'descripcion' => 'Programación y evaluación de simulacros NOM-002 y NOM-033.',
            'icono' => 'fire',
            'permiso' => 'simulacros.ver',
            'items' => [
                ['nombre' => 'Simulacros de incendio', 'ruta' => null, 'implementado' => false],
                ['nombre' => 'Evidencia fotográfica', 'ruta' => null, 'implementado' => false],
            ],
        ],
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
                'cumplimiento.ver',
                'seguridad.ver',
                'capacitacion.ver',
                'mantenimiento.ver',
                'auditorias.ver',
                'simulacros.ver',
                'colaboradores.gestionar',
                'relaciones.gestionar',
                'contractual.gestionar',
                'documentacion.gestionar',
                'configuracion.gestionar',
            ],
        ],
        'Auditor' => [
            'descripcion' => 'Consulta y auditoría, sin capacidad de gestión.',
            'permisos' => [
                'cumplimiento.ver',
                'seguridad.ver',
                'capacitacion.ver',
                'mantenimiento.ver',
                'auditorias.ver',
                'simulacros.ver',
            ],
        ],
    ],
];
