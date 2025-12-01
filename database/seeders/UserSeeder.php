<?php

namespace Database\Seeders;

use App\Models\Users\Institution;
use Illuminate\Database\Seeder;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use App\Models\Users\Department;
use App\Models\Users\Workstation;
use App\Models\Users\Career;
use App\Models\Users\Period; 
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\Payment;
use App\Models\Users\Address;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // --- 1. Buscar Roles Necesarios ---
            $masterRole = Role::where('name', 'master')->firstOrFail();
            $alumnoRole = Role::where('name', 'estudiante')->firstOrFail();
            $anfitrionRole = Role::where('name', 'anfitrion')->firstOrFail();

            // --- 2. Buscar Instituciones Necesarias ---
            $universidadMI = Institution::where('name', 'Universidad Mundo Imperial')->firstOrFail();
            $palacioMI = Institution::where('name', 'Palacio Mundo Imperial')->firstOrFail();
            
            $allInstitutions = Institution::whereIn('name', [
                'Palacio Mundo Imperial',
                'Universidad Mundo Imperial',
                'Princess Mundo Imperial',
                'Pierre Mundo Imperial',
            ])->get();

            if ($allInstitutions->isEmpty()) {
                 throw new \Exception('Error Crítico: No se encontró ninguna institución para asignar al usuario Master.');
            }

            // --- 3. Crear Carreras, Departamentos y Puestos ---
            $carreraSistemas = Career::firstOrCreate(
                ['name' => 'Ingenieria en Sistemas', 'institution_id' => $universidadMI->id],
                ['official_id' => 'ING-SYS', 'semesters' => 9, 'type' => 'Presencial']
            );
            Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $universidadMI->id]);
            
            $talentoHumanoDep = Department::firstOrCreate(
                ['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]
            );
            Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);

            $reclutadorWorkstation = Workstation::firstOrCreate(
                ['name' => 'Reclutador', 'department_id' => $talentoHumanoDep->id, 'institution_id' => $palacioMI->id]
            );
            Workstation::firstOrCreate(
                ['name' => 'Analista de Nomina', 'department_id' => $talentoHumanoDep->id, 'institution_id' => $palacioMI->id]
            );

            // --- 4. Crear Usuario Master (Esteban) ---
            $masterUser = User::firstOrCreate(
                ['email' => 'master@UMI.com'],
                [
                    'nombre' => 'Esteban',
                    'apellido_paterno' => 'Rivera',
                    'apellido_materno' => 'Molina',
                    'password' => Hash::make('master1234'),
                    'RFC' => 'XAXX010101000',
                    'role_id' => $masterRole->id,
                    'institution_id' => $allInstitutions->first()->id, 
                    'is_active' => 1
                ]
            );

            foreach ($allInstitutions as $institution) {
                DB::table('user_roles_institution')->updateOrInsert(
                   ['user_id' => $masterUser->id, 'institution_id' => $institution->id],
                   ['role_id' => $masterRole->id, 'created_at' => now(), 'updated_at' => now()]
                );
                $masterUser->institutions()->syncWithoutDetaching($institution->id);
            }

            // --- 5. Crear Usuario Multi-Rol (Tribilin) ---
            $multiRoleUser = User::firstOrCreate(
                ['email' => 'multirol@UMI.com'],
                [
                    'nombre' => 'Tribilin',
                    'apellido_paterno' => 'Cobo',
                    'apellido_materno' => 'Loquendo',
                    'password' => Hash::make('contrasena'),
                    'RFC' => 'GAPX010101XYZ',
                    'role_id' => $alumnoRole->id,
                    'institution_id' => $universidadMI->id,
                    'department_id' => $talentoHumanoDep->id, 
                    'workstation_id' => $reclutadorWorkstation->id,
                    'is_active' => 1
                ]
            );

            // Asignar rol estudiante
            DB::table('user_roles_institution')->updateOrInsert(
                ['user_id' => $multiRoleUser->id, 'institution_id' => $universidadMI->id],
                ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
            );
            $multiRoleUser->institutions()->syncWithoutDetaching($universidadMI->id);
            AcademicProfile::updateOrInsert(
                ['user_id' => $multiRoleUser->id],
                ['career_id' => $carreraSistemas->id, 'semestre' => 1, 'status' => 'Aspirante']
            );

            // Asignar rol anfitrión
            DB::table('user_roles_institution')->updateOrInsert(
                ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
                ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
            );
            $multiRoleUser->institutions()->syncWithoutDetaching($palacioMI->id);
            CorporateProfile::updateOrInsert(
                ['user_id' => $multiRoleUser->id],
                ['department_id' => $talentoHumanoDep->id, 'workstation_id' => $reclutadorWorkstation->id]
            );

            // ====================================================================
            // 6. GENERACIÓN DE ALUMNOS DE PRUEBA (CORREGIDO)
            // ====================================================================
            
            // Asegurar Periodo Activo CON institución
            $periodo = Period::firstOrCreate(
                ['is_active' => 1],
                [
                    'name' => 'AGO 2025 - DIC 2025', 
                    'start_date' => now(), 
                    'end_date' => now()->addMonths(6),
                    'institution_id' => $universidadMI->id // <--- ¡AQUÍ ESTABA EL ERROR!
                ]
            );

            // A) CASO: ASPIRANTES NUEVOS (SIN PAGAR)
            for ($i = 1; $i <= 3; $i++) {
                $this->crearAlumnoPrueba(
                    "Aspirante", "Nuevo $i", 'aspirante'.$i.'@umi.edu.mx', 
                    $carreraSistemas->id, $periodo, 'Pendiente', $universidadMI->id, $alumnoRole->id
                );
            }

            // B) CASO: LISTOS PARA MATRÍCULA (YA PAGARON)
            for ($i = 1; $i <= 3; $i++) {
                $this->crearAlumnoPrueba(
                    "Pagador", "Listo $i", 'pagado'.$i.'@umi.edu.mx', 
                    $carreraSistemas->id, $periodo, 'Pagada', $universidadMI->id, $alumnoRole->id
                );
            }

            // C) CASO: ALUMNOS ACTIVOS
            for ($i = 1; $i <= 3; $i++) {
                $u = $this->crearAlumnoPrueba(
                    "Alumno", "Activo $i", 'activo'.$i.'@umi.edu.mx', 
                    $carreraSistemas->id, $periodo, 'Pagada', $universidadMI->id, $alumnoRole->id
                );
                $u->academicProfile->update([
                    'matricula' => '2025-SYS-00' . $i,
                    'status' => 'Alumno Activo'
                ]);
            }
            
            $this->command->info('UserSeeder ejecutado exitosamente! (Incluyendo alumnos de prueba)');

        } catch (ModelNotFoundException $e) {
            $this->command->error("Error en UserSeeder: " . $e->getMessage());
        } catch (\Exception $e) {
            $this->command->error("Error inesperado en UserSeeder: " . $e->getMessage());
            \Log::error("Error en UserSeeder: " . $e->getMessage());
        }
    }

    // Función auxiliar
    private function crearAlumnoPrueba($nombre, $apellido, $email, $carreraId, $periodo, $statusFactura, $instId, $roleId)
    {
        $addr = Address::create(['calle' => 'Calle Test', 'colonia' => 'Colonia Test', 'ciudad' => 'Acapulco', 'estado' => 'GRO', 'codigo_postal' => '39000']);

        // RFC Único
        $rfcAleatorio = 'TEST' . strtoupper(substr(uniqid(), -9));

        $user = User::create([
            'nombre' => $nombre,
            'apellido_paterno' => $apellido,
            'apellido_materno' => 'Prueba',
            'email' => $email,
            'password' => Hash::make('password'),
            'RFC' => $rfcAleatorio, 
            'telefono' => '7440000000',
            'fecha_nacimiento' => '2000-01-01',
            'edad' => 25,
            'address_id' => $addr->id,
            'institution_id' => $instId,
            'is_active' => 1,
            'role_id' => $roleId
        ]);

        $user->roles()->attach($roleId, ['institution_id' => $instId]);

        AcademicProfile::create([
            'user_id' => $user->id,
            'career_id' => $carreraId,
            'semestre' => 1,
            'status' => 'Aspirante',
            'is_anfitrion' => false,
        ]);

        $monto = 1500.00;
        $billing = Billing::create([
            'user_id' => $user->id,
            'period_id' => $periodo->id,
            'factura_uid' => 'INS-' . strtoupper(uniqid()),
            'concepto' => 'Inscripción Nuevo Ingreso',
            'monto' => $monto,
            'fecha_vencimiento' => now()->addDays(7),
            'status' => $statusFactura,
        ]);

        if ($statusFactura === 'Pagada') {
            Payment::create([
                'billing_id' => $billing->id,
                'user_id' => 1, 
                'monto' => $monto,
                'fecha_pago' => now(),
                'nota' => 'Pago automático por seeder'
            ]);
        }

        return $user;
    }
}