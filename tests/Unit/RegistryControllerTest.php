<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Inverted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCheckItemExists()
    {
        Item::create(['name' => 'red']);
        $response = $this->get('/api/check/red');
        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }

    public function testCheckItemDoesNotExist()
    {
        $response = $this->get('/api/check/blue');
        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function testCheckItemWithInversion()
    {
        Inverted::create(['inverted' => true]);
        $response = $this->get('/api/check/red');
        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);

        Item::create(['name' => 'red']);
        $response = $this->get('/api/check/red');
        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function testAddNewItem()
    {
        $response = $this->postJson('/api/add', ['item' => 'yellow']);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
        $this->assertDatabaseHas('items', ['name' => 'yellow']);
    }

    public function testAddExistingItem()
    {
        Item::create(['name' => 'yellow']);
        $response = $this->postJson('/api/add', ['item' => 'yellow']);
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Item already exists']);
    }

    public function testAddInvalidItem()
    {
        $response = $this->postJson('/api/add', ['item' => 'blue#']);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'NOT OK']);
    }

    public function testRemoveItem()
    {
        Item::create(['name' => 'yellow']);
        $response = $this->deleteJson('/api/remove', ['item' => 'yellow']);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
        $this->assertDatabaseMissing('items', ['name' => 'yellow']);
    }

    public function testRemoveNonExistentItem()
    {
        $response = $this->deleteJson('/api/remove', ['item' => 'purple']);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
    }

    public function testRemoveInvalidItemName()
    {
        $response = $this->deleteJson('/api/remove', ['item' => 'r@d#']);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'NOT OK']);
    }

    public function testDiffItems()
    {
        Item::create(['name' => 'red']);
        Item::create(['name' => 'blue']);
        $response = $this->postJson('/api/diff', ['items' => ['red', 'green']]);
        $response->assertStatus(200);
        $response->assertJson(['diff' => ['green']]);
    }

    public function testDiffWithEmptySet()
    {
        $response = $this->postJson('/api/diff', ['items' => []]);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'NOT OK']);
    }

    public function testDiffWithInvalidFormat()
    {
        $response = $this->postJson('/api/diff', ['items' => 'invalid string']);
        $response->assertStatus(500);
        $response->assertJson(['message' => 'NOT OK']);
    }

    public function testDiffWithDuplicateItemsInSet()
    {
        Item::create(['name' => 'red']);
        $response = $this->postJson('/api/diff', ['items' => ['red', 'red', 'blue']]);
        $response->assertStatus(200);
        $response->assertJson(['diff' => ['blue']]);
    }

    public function testInvertState()
    {
        $response = $this->putJson('/api/invert');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
        $this->assertDatabaseHas('registry_inverted', ['inverted' => true]);

        $response = $this->putJson('/api/invert');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
        $this->assertDatabaseHas('registry_inverted', ['inverted' => false]);
    }

    public function testInitialInvertState()
    {
        Inverted::truncate();
        $response = $this->putJson('/api/invert');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'OK']);
        $this->assertDatabaseHas('registry_inverted', ['inverted' => true]);
    }
}
