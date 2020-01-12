<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Tests;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Technote\CrudHelper\Providers\CrudHelperServiceProvider;

/**
 * Class TestCase
 * @package Technote\CrudHelper\Tests
 */
class TestCase extends BaseTestCase
{
    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'test');
        $app['config']->set('database.connections.test', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $app['config']->set('crud-helper.namespace', 'Technote\CrudHelper\Tests');
        $app['translator']->addLines(['database.items.name' => 'Name'], 'en');
    }

    /**
     * @param  Application  $app
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app)
    {
        return [
            CrudHelperServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
        Schema::create('user_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable(false)->unique()->comment('test1');
            $table->string('name', 128)->nullable(false)->comment('test2');
            $table->string('name_kana', 128)->nullable(false)->comment('test3');
            $table->string('zip_code', 16)->nullable(false)->comment('test4');
            $table->string('address', 128)->nullable(false)->comment('test5');
            $table->string('phone', 16)->nullable(false)->comment('test6');
            $table->string('mobile_phone', 16)->nullable(true)->comment('test7');
            $table->string('home_url', 100)->nullable(true)->comment('test8');
            $table->string('email', 100)->nullable(false)->comment('test9');
            $table->unsignedSmallInteger('age')->nullable(false)->comment('test10');
            $table->boolean('bool_test')->nullable(true)->comment('test11');
            $table->float('float_test')->nullable(true)->comment('test12');
            $table->date('date_test')->nullable(true)->comment('test13');
            $table->time('time_test1')->nullable(true)->comment('test14');
            $table->time('time_test2')->nullable(true)->comment('test15');
            $table->time('time_test3')->nullable(true)->comment('test16');
            $table->unsignedBigInteger('item_id')->nullable(true)->unique()->comment('test17');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 128)->nullable(false)->comment('test20');
            $table->timestamps();
        });

        $faker = Factory::create('ja_JP');
        collect(range(1, 50))->each(function ($index) use ($faker) {
            $this->userFactory("name${index}test", "address{$index}test", $faker);
            $this->itemFactory("name${index}test");
        });
    }

    private function userFactory(string $name, string $address, Generator $faker)
    {
        $this->userDetailFactory(User::create(), $name, $address, $faker);
    }

    private function userDetailFactory(User $user, string $name, string $address, Generator $faker)
    {
        UserDetail::create([
            'user_id'   => $user->id,
            'name'      => $name,
            'name_kana' => $faker->kanaName,
            'zip_code'  => substr_replace($faker->postcode, '-', 3, 0),
            'address'   => $address,
            'phone'     => '0'.$faker->numberBetween(10, 99).'-'.$faker->numberBetween(10, 9999).'-'.$faker->numberBetween(100, 9999),
            'email'     => $faker->email,
            'age'       => $faker->numberBetween(0, 100),
        ]);
    }

    private function itemFactory(string $name)
    {
        Item::create(['name' => $name]);
    }
}
