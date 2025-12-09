<?php

namespace App\Models\Traits;

trait UserTrait
{
    /**
     * Relación muchos a muchos con roles
     * Un usuario puede tener múltiples roles
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role')->withTimestamps();
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     * 
     * @param string $permission Slug del permiso a verificar
     * @return bool True si el usuario tiene el permiso, false en caso contrario
     */
    public function havePermission($permission)
    {
        foreach ($this->roles as $role) {
            // Si el rol tiene acceso completo, retornar true
            if ($role['full-access'] == 'yes') {
                return true;
            }

            // Verificar permisos específicos del rol
            foreach ($role->permissions as $perm) {
                if ($perm->slug == $permission) {
                    return true;
                }
            }
        }
        
        return false;
    }
}

