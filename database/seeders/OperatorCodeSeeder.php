<?php

namespace Database\Seeders;

use App\Models\OperatorCode;
use Illuminate\Database\Seeder;

/**
 * Seeder para el modelo OperatorCode
 * 
 * Pobla la base de datos con códigos de operadora
 * telefónica venezolanos.
 */
class OperatorCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operatorCodes = [
            // Movistar
            ['code' => '414', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '416', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '424', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '426', 'name' => 'Movistar', 'is_active' => true],
            
            // Digitel
            ['code' => '412', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '414', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '416', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '424', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '426', 'name' => 'Digitel', 'is_active' => true],
            
            // Movilnet
            ['code' => '412', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '414', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '416', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '424', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '426', 'name' => 'Movilnet', 'is_active' => true],
            
            // Códigos adicionales (simulados)
            ['code' => '413', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '415', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '417', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '418', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '419', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '420', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '421', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '422', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '423', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '425', 'name' => 'Movistar', 'is_active' => true],
            ['code' => '427', 'name' => 'Digitel', 'is_active' => true],
            ['code' => '428', 'name' => 'Movilnet', 'is_active' => true],
            ['code' => '429', 'name' => 'Movistar', 'is_active' => true],
            
            // Códigos inactivos (simulados)
            ['code' => '410', 'name' => 'Operadora Antigua', 'is_active' => false],
            ['code' => '411', 'name' => 'Operadora Antigua', 'is_active' => false],
            ['code' => '430', 'name' => 'Operadora Temporal', 'is_active' => false],
            ['code' => '431', 'name' => 'Operadora Temporal', 'is_active' => false],
        ];

        foreach ($operatorCodes as $operatorCode) {
            OperatorCode::updateOrCreate(
                ['code' => $operatorCode['code']],
                $operatorCode
            );
        }
    }
}