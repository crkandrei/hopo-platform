<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Models\Role;
use App\Models\Child;
use App\Models\Guardian;
use App\Models\PlaySession;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIsolationTest extends TestCase
{
    // Roles are created in parent TestCase::setUp()

    /** @test */
    public function users_can_only_access_their_location_data()
    {
        // Create companies and locations
        $company1 = Company::factory()->create(['name' => 'Company 1']);
        $company2 = Company::factory()->create(['name' => 'Company 2']);
        
        $location1 = Location::factory()->create(['company_id' => $company1->id, 'name' => 'Location 1']);
        $location2 = Location::factory()->create(['company_id' => $company2->id, 'name' => 'Location 2']);
        
        // Create users
        $staff1 = User::factory()->create([
            'location_id' => $location1->id,
            'role_id' => Role::where('name', 'STAFF')->first()->id,
        ]);
        
        $staff2 = User::factory()->create([
            'location_id' => $location2->id,
            'role_id' => Role::where('name', 'STAFF')->first()->id,
        ]);
        
        // Create guardians and children for each location
        $guardian1 = Guardian::factory()->create(['location_id' => $location1->id]);
        $guardian2 = Guardian::factory()->create(['location_id' => $location2->id]);
        
        $child1 = Child::factory()->create(['location_id' => $location1->id, 'guardian_id' => $guardian1->id]);
        $child2 = Child::factory()->create(['location_id' => $location2->id, 'guardian_id' => $guardian2->id]);
        
        // Authenticate as staff1
        $this->actingAs($staff1);
        
        // Staff1 should only see location1's children
        $children = Child::where('location_id', $staff1->location_id)->get();
        $this->assertCount(1, $children);
        $this->assertEquals($child1->id, $children->first()->id);
        $this->assertNotContains($child2->id, $children->pluck('id'));
    }

    /** @test */
    public function company_admin_can_access_all_locations_in_their_company()
    {
        // Create company with multiple locations
        $company = Company::factory()->create(['name' => 'Test Company']);
        
        $location1 = Location::factory()->create(['company_id' => $company->id, 'name' => 'Location 1']);
        $location2 = Location::factory()->create(['company_id' => $company->id, 'name' => 'Location 2']);
        $location3 = Location::factory()->create(['company_id' => $company->id, 'name' => 'Location 3']);
        
        // Create company admin
        $companyAdmin = User::factory()->create([
            'company_id' => $company->id,
            'location_id' => null,
            'role_id' => Role::where('name', 'COMPANY_ADMIN')->first()->id,
        ]);
        
        // Create children in different locations
        $guardian1 = Guardian::factory()->create(['location_id' => $location1->id]);
        $guardian2 = Guardian::factory()->create(['location_id' => $location2->id]);
        $guardian3 = Guardian::factory()->create(['location_id' => $location3->id]);
        
        $child1 = Child::factory()->create(['location_id' => $location1->id, 'guardian_id' => $guardian1->id]);
        $child2 = Child::factory()->create(['location_id' => $location2->id, 'guardian_id' => $guardian2->id]);
        $child3 = Child::factory()->create(['location_id' => $location3->id, 'guardian_id' => $guardian3->id]);
        
        // Authenticate as company admin
        $this->actingAs($companyAdmin);
        
        // Company admin should be able to access all locations in their company
        $this->assertTrue($companyAdmin->canAccessLocation($location1->id));
        $this->assertTrue($companyAdmin->canAccessLocation($location2->id));
        $this->assertTrue($companyAdmin->canAccessLocation($location3->id));
        
        // But not locations from other companies
        $otherCompany = Company::factory()->create(['name' => 'Other Company']);
        $otherLocation = Location::factory()->create(['company_id' => $otherCompany->id]);
        $this->assertFalse($companyAdmin->canAccessLocation($otherLocation->id));
    }

    /** @test */
    public function super_admin_can_access_all_locations()
    {
        // Create multiple companies and locations
        $company1 = Company::factory()->create(['name' => 'Company 1']);
        $company2 = Company::factory()->create(['name' => 'Company 2']);
        
        $location1 = Location::factory()->create(['company_id' => $company1->id]);
        $location2 = Location::factory()->create(['company_id' => $company2->id]);
        
        // Create super admin
        $superAdmin = User::factory()->create([
            'company_id' => null,
            'location_id' => null,
            'role_id' => Role::where('name', 'SUPER_ADMIN')->first()->id,
        ]);
        
        // Authenticate as super admin
        $this->actingAs($superAdmin);
        
        // Super admin should be able to access all locations
        $this->assertTrue($superAdmin->canAccessLocation($location1->id));
        $this->assertTrue($superAdmin->canAccessLocation($location2->id));
    }

    /** @test */
    public function play_sessions_are_isolated_by_location()
    {
        // Create locations
        $location1 = Location::factory()->create(['name' => 'Location 1']);
        $location2 = Location::factory()->create(['name' => 'Location 2']);
        
        // Create guardians and children
        $guardian1 = Guardian::factory()->create(['location_id' => $location1->id]);
        $guardian2 = Guardian::factory()->create(['location_id' => $location2->id]);
        
        $child1 = Child::factory()->create(['location_id' => $location1->id, 'guardian_id' => $guardian1->id]);
        $child2 = Child::factory()->create(['location_id' => $location2->id, 'guardian_id' => $guardian2->id]);
        
        // Create sessions for each location
        $session1 = PlaySession::factory()->create([
            'location_id' => $location1->id,
            'child_id' => $child1->id,
            'bracelet_code' => 'BRACELET001',
        ]);
        
        $session2 = PlaySession::factory()->create([
            'location_id' => $location2->id,
            'child_id' => $child2->id,
            'bracelet_code' => 'BRACELET002',
        ]);
        
        // Verify sessions are isolated
        $location1Sessions = PlaySession::where('location_id', $location1->id)->get();
        $this->assertCount(1, $location1Sessions);
        $this->assertEquals($session1->id, $location1Sessions->first()->id);
        
        $location2Sessions = PlaySession::where('location_id', $location2->id)->get();
        $this->assertCount(1, $location2Sessions);
        $this->assertEquals($session2->id, $location2Sessions->first()->id);
    }

    /** @test */
    public function products_are_isolated_by_location()
    {
        // Create locations
        $location1 = Location::factory()->create(['name' => 'Location 1']);
        $location2 = Location::factory()->create(['name' => 'Location 2']);
        
        // Create products for each location
        $product1 = Product::factory()->create([
            'location_id' => $location1->id,
            'name' => 'Product 1',
            'price' => 10.00,
        ]);
        
        $product2 = Product::factory()->create([
            'location_id' => $location2->id,
            'name' => 'Product 2',
            'price' => 20.00,
        ]);
        
        // Verify products are isolated
        $location1Products = Product::where('location_id', $location1->id)->get();
        $this->assertCount(1, $location1Products);
        $this->assertEquals($product1->id, $location1Products->first()->id);
        
        $location2Products = Product::where('location_id', $location2->id)->get();
        $this->assertCount(1, $location2Products);
        $this->assertEquals($product2->id, $location2Products->first()->id);
    }
}
