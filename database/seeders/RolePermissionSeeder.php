<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'student']);
        Role::create(['name' => 'hte']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'guest']);

        $permissions = [
            'manage users',           // C1: SIP Coordinator manages accounts
            'login',                  // C2, H1, S2: All users can login
            'manage profiles',        // C3: SIP Coordinator manages profiles
            'approve internships',     // C4: SIP Coordinator approves listings
            'manage assessments',     // C5: SIP Coordinator oversees assessments
            'monitor applications',   // C6: SIP Coordinator monitors progress
            'view dashboard metrics', // C7: SIP Coordinator views metrics
            'edit company profile',   // H2: HTE edits profile
            'post internships',       // H3: HTE offers internships
            'set assessment criteria',// H4: HTE sets criteria
            'reset password',         // H5, S6: HTE and Student reset password
            'register account',       // S1: Student registers
            'edit student profile',   // S3: Student edits profile
            'apply internships',      // S4: Student browses/applies internships
            'complete assessments',   // S5: Student completes assessments
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        Role::findByName('admin')->givePermissionTo([
            'manage users',
            'login',
            'manage profiles',
            'approve internships',
            'manage assessments',
            'monitor applications',
            'view dashboard metrics',
        ]);

        Role::findByName('hte')->givePermissionTo([
            'login',
            'edit company profile',
            'post internships',
            'set assessment criteria',
            'reset password',
        ]);

        Role::findByName('student')->givePermissionTo([
            'login',
            'register account',
            'edit student profile',
            'apply internships',
            'complete assessments',
            'reset password',
        ]);
    }
}
