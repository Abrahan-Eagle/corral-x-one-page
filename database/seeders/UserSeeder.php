<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;



class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea 10 usuarios con perfil
        Profile::factory()->count(10)->create();






        $users = [
            [
            'name' => 'admin',
            'email' => 'admin@admin',
            'password' => bcrypt('admin'),
            ],
            [
            'name' => 'aipp',
            'email' => '123@123',
            'password' => bcrypt('123'),
            ],
            [
            'name' => 'xxx',
            'email' => 'xxx@xxx',
            'password' => Hash::make('xxx'),
            ]
            ];
        
            //DELETE USERS CREATE
        
            $del_user = User::where('email', 'admin@admin')->first();
        
            if ($del_user) {
            $del_user -> delete();
            }
        
        
            //CREATE USERS
        
            foreach ($users as $user) {
            User::create( $user );
            }
        
            $userAdmin = User::where('email', 'admin@admin')->first();
            //CREATE USERS OF TESTING
        
            //factory(User::class, 20)->create();
            //$user = User::factory()->create();
            User::factory()->times(20)->create();
        
        
        
            //CREATE ROLES OF USER ADMIN
            /*
            $rolAdmin = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin',
            'full-access' => 'yes',
            ]);
            */
            $rolAdmin = Role::where('slug', 'admin')->first();
            //table role_user
        
            $userAdmin->roles()->sync([ $rolAdmin -> id ]);
            }
    }
}
