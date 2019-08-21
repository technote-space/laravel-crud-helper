<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Tests;

use Faker\Factory;

/**
 * Class UserApiTest
 * @package Technote\CrudHelper\Tests
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 */
class UserApiTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->json('GET', route('users.index'));
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'path',
                     'to',
                     'total',
                 ])
                 ->assertJsonCount(15, 'data');
    }

    public function testIndexWithPerPage()
    {
        $response = $this->json('GET', route('users.index', [
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

    public function testIndexWithoutCountLimit()
    {
        $response = $this->json('GET', route('users.index', [
            'count' => 0,
        ]));
        $response->assertStatus(200)
                 ->assertJsonCount(50);
    }

    public function testIndexWithOffset()
    {
        $response = $this->json('GET', route('users.index', [
            'count'  => 20,
            'offset' => 45,
        ]));
        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }

    public function testShow()
    {
        $user     = User::first();
        $response = $this->json('GET', route('users.show', [
            'user' => $user->id,
        ]));
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => $user->detail->name,
                 ]);
    }

    public function testStore()
    {
        $this->assertFalse(UserDetail::where('name', 'abc')->exists());

        $faker    = Factory::create('ja_JP');
        $response = $this->json('POST', route('users.store', [
            'user_details' => [
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
        $this->assertTrue(UserDetail::where('name', 'abc')->exists());
    }

    public function testUpdate()
    {
        $this->assertFalse(UserDetail::where('name', 'xyz')->exists());
        $user = User::first();

        $response = $this->json('PATCH', route('users.update', [
            'user' => $user->id,
        ]), [
            'user_details' => [
                'name' => 'xyz',
            ],
        ]);
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'created_at',
                 ]);
        $this->assertTrue(UserDetail::where('name', 'xyz')->exists());
    }

    public function testFailUpdate()
    {
        $user = User::first();

        $response = $this->json('PATCH', route('users.update', [
            'user' => $user->id,
        ]), [
            'user_details' => [
                'name_kana'  => '000',
                'zip_code'   => 'aaa',
                'address'    => '',
                'phone'      => 'bbb',
                'home_url'   => 'ccc',
                'email'      => 'ddd',
                'float_test' => 'eee',
                'date_test'  => 'fff',
                'time_test'  => 'ggg',
                'item_id'    => -1,
            ],
        ]);
        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'The name_kana must be a valid Katakana.',
                 ])
                 ->assertJsonFragment([
                     'The zip_code must be a valid ZIP Code.',
                 ])
                 ->assertJsonFragment([
                     'The address field must have a value.',
                 ])
                 ->assertJsonFragment([
                     'The phone must be a valid Phone number.',
                 ])
                 ->assertJsonFragment([
                     'The home_url format is invalid.',
                 ])
                 ->assertJsonFragment([
                     'The email must be a valid email address.',
                 ])
                 ->assertJsonFragment([
                     'The float_test must be a number.',
                 ])
                 ->assertJsonFragment([
                     'The date_test is not a valid date.',
                 ])
                 ->assertJsonFragment([
                     'The time_test does not match the format H:i.',
                 ])
                 ->assertJsonFragment([
                     'The selected item_id is invalid.',
                 ]);
    }

    public function testDestroy()
    {
        $user = User::first();

        $response = $this->json('DELETE', route('users.destroy', [
            'user' => $user->id,
        ]));
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'result' => 1,
                 ]);

        $this->assertFalse(User::where('id', $user->id)->exists());
    }

    public function testSearch1()
    {
        $response = $this->json(
            'GET',
            route('users.index', [
                's' => '　',
            ])
        );
        $response->assertStatus(200)
                 ->assertJsonCount(15, 'data');
    }

    public function testSearch2()
    {
        $response = $this->json(
            'GET',
            route('users.index', [
                's' => 'name',
            ])
        );
        $response->assertStatus(200)
                 ->assertJsonCount(15, 'data');
    }

    public function testSearch3()
    {
        $response = $this->json(
            'GET',
            route('users.index', [
                's' => 'name1test',
            ])
        );
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function testSearch4()
    {
        $response = $this->json(
            'GET',
            route('users.index', [
                's' => 'name1test address1',
            ])
        );
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function testSearch5()
    {
        $response = $this->json(
            'GET',
            route('users.index', [
                's' => 'name1test address1 address2',
            ])
        );
        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data');
    }

    public function testSearchValidation()
    {
        $response = $this->json(
            'GET',
            route('users.index', [
                'count'  => 'abc',
                'offset' => 'xyz',
                'phone'  => '-1',
            ])
        );
        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'The Count must be an integer.',
                 ])
                 ->assertJsonFragment([
                     'The Offset must be an integer.',
                 ])
                 ->assertJsonFragment([
                     'The given data was invalid.',
                 ]);
    }

    public function testAppends()
    {
        $response = $this->json('GET', route('users.index'));
        $response->assertStatus(200);
        $json = json_decode($response->content(), true);
        $this->assertArrayHasKey('test1', $json['data'][0]);
        $this->assertArrayNotHasKey('test2', $json['data'][0]);

        $user     = User::first();
        $response = $this->json('GET', route('users.show', [
            'user' => $user->id,
        ]));
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'test1',
                     'test2',
                 ]);
    }
}
