<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/19
 * Time: 下午9:48
 */

namespace App\Services;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClothesService
{
    public function setClothes($openid,$clothes)
    {
        $now = Carbon::now();
        $clothes['owner'] = $openid;
        $clothes['created_at'] = $now;
        $clothes['updated_at'] = $now;

        DB::table('clothes')->insertGetId($clothes);
    }

    public function getClothes($openid)
    {
        $clothes = DB::table('clothes')->where('owner',$openid)->get();
        return $clothes;
    }

    public function updateClothes($clothes,$owner)
    {
        if($this->checkOwner($clothes['id'],$owner) == -1)
        {
            return -1;
        }
        else
        {
            DB::table('clothes')->where('id',$clothes['id'])->update($clothes);
            return 0;
        }
    }

    public function checkOwner($clothesId,$owner)
    {
        $clothes = DB::table('clothes')->where('id',$clothesId)->first();
        if ($clothes->owner != $owner)
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }
}