<?php

namespace Tests\Unit;

use App\Customer;
use App\Group;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use DatabaseMigrations;

    private function validParams($overrides = [])
    {
        return array_merge([
            'name' => 'John Smith',
            'phone' => '111 222 333',
            'email' => 'test@example.com',
            'address' => '111 Blue St',
            'group_id' => 0,
            'note' => 'blah blah',
        ], $overrides);
    }

    /** @test */
    public function canGetFirstNCustomers()
    {
        foreach (range(1, 30) as $i) {
            factory(Group::class)->create();
        }
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create([
                'group_id' => rand(0, 30),
            ]);
        }

        $res = $this->get("/api/customers");

        $res->assertStatus(200)
            ->assertJson([
                'data' => Customer::take(Customer::getDefaultPageSize())->get()->toArray(),
                'total' => 100,
                'current_page' => 1,
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

        $res->assertStatus(200)
            ->assertJson([
                'data' => Customer::take($pageSize)->get()->toArray(),
                'total' => 100,
                'current_page' => 1,
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
        $c4 = factory(Customer::class)->create(['name' => 'Johannes']); // This is irrelevant
        $c5 = factory(Customer::class)->create(['name' => 'Elton John']);

        $keyword = "john";

        $res = $this->get("/api/customers?keyword=$keyword");

        $res->assertStatus(200)
            ->assertJson([
                'data' => Customer::where('name', 'LIKE', $keyword)->take(Customer::getDefaultPageSize())->get()->toArray(),
                'total' => 4,
                'current_page' => 1,
            ]);
    }

    /** @test */
    public function canGetCustomersWithPage()
    {
        $irrelevantNames = collect(array_fill(0, 60, null))->map(function ($i) {
            return ['Alice', 'Bob', 'Carol'][rand(0, 2)];
        })->all();
        $relevantNames = collect(array_fill(0, 40, null))->map(function ($i) {
            return ['john', 'Johny', 'Elton John'][rand(0, 2)];
        })->all();
        $names = array_merge($irrelevantNames, $relevantNames);
        collect($names)->shuffle()->each(function ($name) {
            return factory(Customer::class)->create(['name' => $name]);
        });

        $page = 3;
        $keyword = "john";
        $pageSize = 7;
        $res = $this->get("/api/customers?page=$page&keyword=$keyword&pageSize=$pageSize");

        $expectedArray = Customer::where('name', 'LIKE', "%$keyword%")
            ->get()
            ->filter(function ($item, $i) use ($page, $pageSize) {
                return ($page - 1) * $pageSize <= $i;
            })
            ->take($pageSize)
            ->values()
            ->toArray();

        $res->assertStatus(200)
            ->assertJson([
                'current_page' => $page,
                'data' => $expectedArray,
                'total' => 40,
            ]);
    }

    /** @test */
    public function canGetCustomerDetails()
    {
        $gourp = factory(Group::class)->create();
        $c1 = factory(Customer::class)->create(['name' => 'Alice']);
        $c2 = factory(Customer::class)->create(['name' => 'Bob', 'group_id' => $gourp->id]);
        $c3 = factory(Customer::class)->create(['name' => 'Carol']);

        $res = $this->get("/api/customers/2");

        $res->assertStatus(200)
            ->assertJson(['customer' => [
                'id' => $c2->id,
                'name' => $c2->name,
                'group_id' => $gourp->id,
                'group' => ['name' => $gourp->name],
            ]]);
    }

    /** @test */
    public function canReturnNullIfInvalidIdIsProvided()
    {
        $c1 = factory(Customer::class)->create(['name' => 'Alice']);
        $c2 = factory(Customer::class)->create(['name' => 'Bob']);
        $c3 = factory(Customer::class)->create(['name' => 'Carol']);

        $res = $this->get("/api/customers/4");

        $res->assertStatus(200)
            ->assertJson(['customer' => null]);
    }

    /** @test */
    public function canDeleteCustomer()
    {
        $c1 = factory(Customer::class)->create(['name' => 'Alice']);
        $this->assertEquals(Customer::find(1)->id, $c1->id);

        $res = $this->delete("/api/customers/1");

        $res->assertStatus(200);
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
        $res = $this->post("/api/customers", $this->validParams());
        $customer = Customer::find(1);

        $res->assertStatus(200);
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

        $res->assertStatus(200);
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

    /** @test */
    public function canUpdateCustomer()
    {
        $c1 = factory(Customer::class)->create($this->validParams());

        $res = $this->put("/api/customers/1", [
            'name' => 'John Doe',
            'phone' => '444 555 666',
            'email' => 'updated@example.com',
            'address' => '222 Green St',
            'group_id' => 1,
            'note' => 'I\'m, testing',
        ]);

        $customer = Customer::find(1);

        $res->assertStatus(200);
        $this->assertEquals($customer->id, 1);
        $this->assertEquals($customer->name, 'John Doe');
        $this->assertEquals($customer->phone, '444 555 666');
        $this->assertEquals($customer->email, 'updated@example.com');
        $this->assertEquals($customer->address, '222 Green St');
        $this->assertEquals($customer->group_id, 1);
        $this->assertEquals($customer->note, 'I\'m, testing');
    }

    /** @test */
    public function cannotUpdateCustomerWithInvalidInput()
    {
        $c1 = factory(Customer::class)->create($this->validParams());
        $res = $this->put("/api/customers/1", $this->validParams([
            'name' => null,
            'email' => 'test',
        ]));

        $customer = Customer::find(1);

        $res->assertStatus(400)->assertJson([
            'messages' => [
                'name' => ['The name field is required.'],
                'email' => ['The email must be a valid email address.'],
            ],
        ]);
        $this->assertEquals($customer->id, 1);
        $this->assertEquals($customer->name, 'John Smith');
        $this->assertEquals($customer->phone, '111 222 333');
        $this->assertEquals($customer->email, 'test@example.com');
        $this->assertEquals($customer->address, '111 Blue St');
        $this->assertEquals($customer->group_id, 0);
        $this->assertEquals($customer->note, 'blah blah');
    }

    /** @test */
    public function updatingWithInvalidIdReturnsError()
    {
        $this->assertEquals(Customer::find(1), null);

        $res = $this->put("/api/customers/1", $this->validParams());

        $res->assertStatus(404);
    }
}
