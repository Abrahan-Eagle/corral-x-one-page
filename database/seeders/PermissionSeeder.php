<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


    //CREATE ROLES OF USER ADMIN

    $rolAdmin = Role::create([
    'name' => 'Admin',
    'slug' => 'admin',
    'description' => 'Admin',
    'full-access' => 'yes',
    ]);


    $var_permission = [];

    $permissions = [

    //Users_own

    [
    "name" => "View Own User",
    "slug" => "usersown.show",
    "description" => "Ver en detalle cada usuario del sistema",
    ],
    [
    "name" => "Edit Own User",
    "slug" => "usersown.edit",
    "description" => "Editar cualquier dato de un usuario del sistema",
    ],

    //Users
    [
    "name" => "List User",
    "slug" => "users.index",
    "description" => "Lista y navega todos los usuarios del sistema",
    ],
    [
    "name" => "View User",
    "slug" => "users.show",
    "description" => "Ver en detalle cada usuario del sistema",
    ],
    [
    "name" => "Edit User",
    "slug" => "users.edit",
    "description" => "Editar cualquier dato de un usuario del sistema",
    ],
    [
    "name" => "Delete User",
    "slug" => "users.destroy",
    "description" => "Eliminar cualquier usuario del sistema",
    ],



    //Roles
    [
    "name" => "List role",
    "slug" => "roles.index",
    "description" => "Lista y navega todos los roles del sistema",
    ],
    [
    "name" => "View role",
    "slug" => "roles.show",
    "description" => "Ver en detalle cada rol del sistema",
    ],
    [
    "name" => "Create role",
    "slug" => "roles.create",
    "description" => "Ver en detalle cada rol del sistema",
    ],
    [
    "name" => "Edit role",
    "slug" => "roles.edit",
    "description" => "Editar cualquier dato de un rol del sistema",
    ],
    [
    "name" => "Delete role",
    "slug" => "roles.destroy",
    "description" => "Eliminar cualquier rol del sistema",
    ],


    //Product
    [
    "name" => "List product",
    "slug" => "products.index",
    "description" => "Lista y navega todos los productos del sistema",
    ],
    [
    "name" => "View product",
    "slug" => "products.show",
    "description" => "Ver en detalle cada product del sistema",
    ],
    [
    "name" => "Create products",
    "slug" => "products.create",
    "description" => "Ver en detalle cada product del sistema",
    ],
    [
    "name" => "Edit products",
    "slug" => "products.edit",
    "description" => "Editar cualquier dato de un product del sistema",
    ],
    [
    "name" => "Delete product",
    "slug" => "products.destroy",
    "description" => "Eliminar cualquier product del sistema",
    ],

    ];

    foreach ($permissions as $permission2) {
    $permission = Permission::create( $permission2 );
    $var_permission_all[] = $permission -> id;


    //table permission_role
    //$rolAdmin->permissions()->sync($var_permission_all);
    }

    }
}
