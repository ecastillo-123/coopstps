<?php

namespace App\Policies;

use App\Models\User;
use App\Support\CentroTrabajoContext;
use Illuminate\Support\Facades\App;

class SensitiveDataPolicy
{
    /** @var list<string> */
    private const PERSONAL_POSITIONS = [
        'Gerente de Recursos Humanos',
        'Analista de Recursos Humanos',
        'Gerente General',
    ];

    /** @var list<string> */
    private const NOM035_POSITIONS = [
        'Gerente de Recursos Humanos',
        'Analista de Recursos Humanos',
        'Gerente General',
        'Responsable de sucursal',
    ];

    public function registerPersonal(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('personal.registrar')
            && $this->positionAllows($user, self::PERSONAL_POSITIONS);
    }

    public function modifyPersonal(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('personal.modificar')
            && $this->positionAllows($user, self::PERSONAL_POSITIONS);
    }

    public function deletePersonal(User $user): bool
    {
        return false;
    }

    public function approvePersonal(User $user): bool
    {
        return false;
    }

    public function registerNom035(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('nom035.registrar')
            && $this->positionAllows($user, self::NOM035_POSITIONS);
    }

    public function modifyNom035(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('nom035.modificar')
            && $this->positionAllows($user, self::NOM035_POSITIONS);
    }

    public function registerTraining(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('capacitacion.registrar');
    }

    public function modifyTraining(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('capacitacion.modificar');
    }

    public function writeAuditInspection(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasRole('Auditor')
            && $user->hasAnyPermission(['auditorias.registrar', 'auditorias.modificar']);
    }

    public function exportReports(User $user): bool
    {
        return $this->hasActiveCenter($user)
            && $user->hasPermissionTo('reportes.exportar');
    }

    private function hasActiveCenter(User $user): bool
    {
        return App::make(CentroTrabajoContext::class)->activo() !== null;
    }

    /**
     * @param  list<string>  $allowedNames
     */
    private function positionAllows(User $user, array $allowedNames): bool
    {
        $user->loadMissing('puesto');

        return $user->puesto !== null
            && in_array($user->puesto->nombre, $allowedNames, true);
    }
}
