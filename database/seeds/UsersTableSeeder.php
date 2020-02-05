<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	User::create([
	    	'name' => 'Agustin Blanco',
	        'email' => 'agustin.e.blanco@gmail.com',
	        'password' => bcrypt('agux1994'), // password
	        'dni' => '38464553',
	        'address' => '',
	        'phone' =>	'',
	        'role' =>	'admin'	
    	]);
       	factory(App\User::class, 50)->create();
    }
}
