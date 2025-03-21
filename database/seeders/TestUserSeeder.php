<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user if it doesn't exist
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Test user created: test@example.com / password123');
        } else {
            $this->command->info('Test user already exists');
        }
        
        // Create test business if it doesn't exist
        $business = Business::where('name', 'Test Business')->first();
        
        if (!$business) {
            $business = Business::create([
                'name' => 'Test Business',
                'address_line1' => '123 Test St',
                'city' => 'Test City',
                'state' => 'TS',
                'postal_code' => '12345',
                'country' => 'Test Country',
                'email' => 'business@example.com',
                'phone' => '123-456-7890',
                'currency' => 'USD',
                'is_active' => true
            ]);
            
            $this->command->info('Test business created');
        }
        
        // Get Administrator role
        $adminRole = Role::where('name', 'Administrator')->first();
        
        if ($adminRole) {
            // Assign admin role to test user for test business
            if (!$user->roles()->where('role_id', $adminRole->id)->where('business_id', $business->id)->exists()) {
                $user->roles()->attach($adminRole->id, ['business_id' => $business->id]);
                $this->command->info('Administrator role assigned to test user for test business');
            }
            
            // Also create users with different roles for testing
            $this->createRoleTestUsers($business);
        } else {
            $this->command->error('Administrator role not found. Make sure RoleSeeder has been run before TestUserSeeder.');
        }
    }
    
    /**
     * Create test users with different roles
     */
    private function createRoleTestUsers($business)
    {
        $roles = Role::where('name', '!=', 'Administrator')->get();
        
        foreach ($roles as $role) {
            $email = strtolower($role->name) . '@example.com';
            
            if (!User::where('email', $email)->exists()) {
                $user = User::create([
                    'name' => $role->name . ' User',
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]);
                
                // Assign role to user for test business
                $user->roles()->attach($role->id, ['business_id' => $business->id]);
                
                $this->command->info("Test user created with {$role->name} role: $email / password123");
            }
        }
    }
}
