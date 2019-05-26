<?php

use Illuminate\Database\Seeder;

class TokensTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tokens')->insert([
            'token' =>  '6dbfba62471fa1c2ee88a03fb619b117',
            'user_id'   =>1,
            'expires_at'    =>  '2019-06-18 20:23:02',
            'created_at'    =>  '2019-05-19 20:23:02',
            'updated_at'    =>  '2019-05-19 20:23:02'
        ]);
    }
}
