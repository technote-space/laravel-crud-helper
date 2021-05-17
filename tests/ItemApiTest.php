<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Tests;

use Faker\Factory;

/**
 * Class ItemApiTest
 * @package Technote\CrudHelper\Tests
 */
class ItemApiTest extends TestCase
{
    public function testIndex(): void
    {
        $response = $this->json('GET', route('items.index'));
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'path',
                     'to',
                     'total',
                 ])
                 ->assertJsonCount(15, 'data');
    }

    public function testIndexWithPerPage(): void
    {
        $response = $this->json('GET', route('items.index', [
            'per_page' => 5,
        ]));
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'path',
                     'to',
                     'total',
                 ])
                 ->assertJsonCount(5, 'data');
    }

    public function testShow(): void
    {
        $item     = Item::first();
        $response = $this->json('GET', route('items.show', [
            'item' => $item->id,
        ]));
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => $item->name,
                 ]);
    }

    public function testStore(): void
    {
        self::assertFalse(Item::where('name', 'abc')->exists());

        $faker    = Factory::create('ja_JP');
        $response = $this->json('POST', route('items.store', [
            'items' => [
                'name'      => 'abc',
                'name_kana' => $faker->kanaName,
                'zip_code'  => substr_replace($faker->postcode, '-', 3, 0),
                'address'   => preg_replace('#\A\d+\s+#', '', $faker->address),
                'phone'     => '0'.$faker->numberBetween(10, 99).'-'.$faker->numberBetween(10, 9999).'-'.$faker->numberBetween(100, 9999),
                'email'     => $faker->email,
                'age'       => $faker->numberBetween(0, 100),
            ],
        ]));
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'created_at',
                 ]);
        self::assertTrue(Item::where('name', 'abc')->exists());
    }

    public function testUpdate(): void
    {
        self::assertFalse(Item::where('name', 'xyz')->exists());
        $item = Item::first();

        $response = $this->json('PATCH', route('items.update', [
            'item' => $item->id,
        ]), [
            'items' => [
                'name' => 'xyz',
            ],
        ]);
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'created_at',
                 ]);
        self::assertTrue(Item::where('name', 'xyz')->exists());
    }

    public function testDestroy(): void
    {
        $item = Item::first();

        $response = $this->json('DELETE', route('items.destroy', [
            'item' => $item->id,
        ]));
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'result' => 1,
                 ]);

        self::assertFalse(Item::where('id', $item->id)->exists());
    }

    public function testSearchIsInvalid(): void
    {
        $response = $this->json(
            'GET',
            route('items.index', [
                's' => 'name1test',
            ])
        );
        $response->assertStatus(200)
                 ->assertJsonCount(15, 'data');
    }
}
