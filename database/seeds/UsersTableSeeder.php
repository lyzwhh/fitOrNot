<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'openid'    =>  'o9i844xvJ4C2WiLe3ZAwz3nSWe3g',
            'session_key'   =>  '7YSynB/wJNzEUFi5NoETAg==',
            'created_at'    =>  \Carbon\Carbon::now(),
            'updated_at'    =>  \Carbon\Carbon::now()
        ]);
        DB::table('users')->insert([
            'openid'    =>  'o9i844xpeqNEzTN97J3cAzUN369A',
            'session_key'   =>  'KHG3aDWMctCRXXajUhIcgg==',
            'created_at'    =>  \Carbon\Carbon::now(),
            'updated_at'    =>  \Carbon\Carbon::now()
        ]);
    }
}
