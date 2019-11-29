<?php

namespace Tests\Unit;

use App\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use DatabaseMigrations;

    private $defaultPageSize = 10;

    /** @test */
    public function canGetFirstNCustomers()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }

        $res = $this->get("/api/customers");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::take($this->defaultPageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function canGetCustomersWithSize()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }
        $pageSize = 15;

        $res = $this->get("/api/customers?pageSize=$pageSize");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::take($pageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function canGetCustomersWithIndex()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }
        $start = 10;

        $res = $this->get("/api/customers?start=$start");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::where('id', '>=', $start)->take($this->defaultPageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function canGetCustomersWithKeyword()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create(['name' => ['Alice', 'Bob', 'Carol'][rand(0, 2)]]);
        }
        $c1 = factory(Customer::class)->create(['name' => 'john']);
        $c2 = factory(Customer::class)->create(['name' => 'JOHN']);
        $c3 = factory(Customer::class)->create(['name' => 'Johny']);
        $c4 = factory(Customer::class)->create(['name' => 'Johannes']);
        $c5 = factory(Customer::class)->create(['name' => 'Elton John']);

        $keyword = "john";

        $res = $this->get("/api/customers?keyword=$keyword");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::where('name', 'LIKE', $keyword)->take($this->defaultPageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function canGetCustomerDetails()
    {
        $c1 = factory(Customer::class)->create(['name' => 'Alice']);
        $c2 = factory(Customer::class)->create(['name' => 'Bob']);
        $c3 = factory(Customer::class)->create(['name' => 'Carol']);

        $res = $this->get("/api/customers/2");

        $res->assertStatus(201)
            ->assertJson(['data' => [
                'id' => $c2->id,
                'name' => $c2->name,
            ]]);
    }

    /** @test */
    public function canReturnNullIfInvalidIdIsProvided()
    {
        $c1 = factory(Customer::class)->create(['name' => 'Alice']);
        $c2 = factory(Customer::class)->create(['name' => 'Bob']);
        $c3 = factory(Customer::class)->create(['name' => 'Carol']);

        $res = $this->get("/api/customers/4");

        $res->assertStatus(201)
            ->assertJson(['data' => null]);
    }

    /** @test */
    public function canDeleteCustomer()
    {
        $c1 = factory(Customer::class)->create(['name' => 'Alice']);
        $this->assertEquals(Customer::find(1)->id, $c1->id);

        $res = $this->delete("/api/customers/1");

        $res->assertStatus(201);
        $this->assertEquals(Customer::find(1), null);
    }

    /** @test */
    public function deleteingWithInvalidIdReturnsError()
    {
        $this->assertEquals(Customer::all()->count(), 0);

        $res = $this->delete("/api/customers/1");

        $res->assertStatus(404);
    }

    /** @test */
    public function canCreateCustomer()
    {
        $this->assertEquals(Customer::find(1), null);
        $input = [
            'name' => 'John Smith',
            'phone' => '111 222 333',
            'email' => 'test@example.com',
            'address' => '111 Blue St',
            'group_id' => 0,
            'note' => 'blah blah',
        ];

        $res = $this->post("/api/customers", $input);
        $customer = Customer::find(1);

        $res->assertStatus(201);
        $this->assertEquals($customer->id, 1);
        $this->assertEquals($customer->name, 'John Smith');
        $this->assertEquals($customer->phone, '111 222 333');
        $this->assertEquals($customer->email, 'test@example.com');
        $this->assertEquals($customer->address, '111 Blue St');
        $this->assertEquals($customer->group_id, 0);
        $this->assertEquals($customer->note, 'blah blah');
    }

    /** @test */
    public function canCreateCustomerWithNameOnly()
    {
        $this->assertEquals(Customer::find(1), null);
        $input = [
            'name' => 'John Smith',
        ];

        $res = $this->post("/api/customers", $input);
        $customer = Customer::find(1);

        $res->assertStatus(201);
        $this->assertEquals($customer->id, 1);
        $this->assertEquals($customer->name, 'John Smith');
        $this->assertEquals($customer->phone, null);
        $this->assertEquals($customer->email, null);
        $this->assertEquals($customer->address, null);
        $this->assertEquals($customer->group_id, null);
        $this->assertEquals($customer->note, null);
    }

    /** @test */
    public function cannotCreateCustomerWithInvalidInput()
    {
        $this->assertEquals(Customer::find(1), null);
        $input = [
            'email' => 'test',
        ];

        $res = $this->post("/api/customers", $input);

        $res->assertStatus(400)->assertJson([
            'messages' => [
                'name' => ['The name field is required.'],
                'email' => ['The email must be a valid email address.'],
            ],
        ]);
        $this->assertEquals(Customer::find(1), null);
    }
}
