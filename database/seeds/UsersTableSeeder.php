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
	        'role' =>	'admin'	
    	]);

        User::create([
            'name' => 'Paciente 1',
            'email' => 'paciente1@gmail.com',
            'password' => bcrypt('agux1994'), // password
            'role' =>   'patient' 
        ]);

        User::create([
            'name' => 'Medico 1',
            'email' => 'medico1@gmail.com',
            'password' => bcrypt('agux1994'), // password
            'role' =>   'doctor' 
        ]);
       	factory(App\User::class, 50)->states('patient')->create();
    }
}
