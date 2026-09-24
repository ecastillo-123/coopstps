<?php

namespace App\Support;

use App\Models\CentroTrabajo;
use Illuminate\Support\Collection;

/**
 * Resuelve y mantiene el Centro de Trabajo activo de la sesión.
 */
final class CentroTrabajoContext
{
    private ?CentroTrabajo $activo = null;

    private bool $resuelto = false;

    /**
     * Centro de trabajo activo. Si no hay selección previa, toma el primero
     * asignado al usuario autenticado.
     */
    public function activo(): ?CentroTrabajo
    {
        if ($this->resuelto) {
            return $this->activo;
        }

        $this->resuelto = true;

        $user = auth()->user();

        if ($user === null) {
            return $this->activo = null;
        }

        $seleccionado = session('centro_trabajo_activo_id');

        $centro = $seleccionado !== null
            ? $user->centrosTrabajo()->whereKey($seleccionado)->first()
            : null;

        $this->activo = $centro ?? $user->centrosTrabajo()->orderBy('nombre')->first();

        if ($this->activo !== null) {
            session(['centro_trabajo_activo_id' => $this->activo->getKey()]);
        }

        return $this->activo;
    }

    /**
     * Establece explícitamente el Centro de Trabajo activo.
     */
    public function set(CentroTrabajo $centro): void
    {
        $this->activo = $centro;
        $this->resuelto = true;

        session(['centro_trabajo_activo_id' => $centro->getKey()]);
    }

    /**
     * Centros de trabajo disponibles para el usuario autenticado.
     *
     * @return Collection<int, CentroTrabajo>
     */
    public function disponibles(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        return $user->centrosTrabajo()->orderBy('nombre')->get();
    }

    /**
     * Limpia el estado resuelto (usado tras cambiar de usuario o de sesión).
     */
    public function reiniciar(): void
    {
        $this->activo = null;
        $this->resuelto = false;
    }
}
