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
use Exception;
class ClothesService
{
    public function setClothes($openid,$clothes)
    {
        $now = Carbon::now();
        $clothes['owner'] = $openid;
        $clothes['created_at'] = $now;
        $clothes['updated_at'] = $now;


        DB::beginTransaction();
        try {

            DB::table('clothes')->insertGetId($clothes);
            DB::table('users')->increment('total');
            DB::commit();
        } catch ( Exception $e){
//            echo $e->getMessage();
            DB::rollBack();
        }

    }

    public function getOrderClothes($openid)
    {
        $data = array();
        for ($c=1 ; $c<=4 ; $c++)
        {
            $data[] = $this->getClothes($openid,$c);
        }
        return $data;
    }

    public function getClothes($openid,$category)
    {
        $clothes = DB::table('clothes')->where('owner',$openid)->where('category',$category)->get();
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
        if ($clothes != null && $clothes->owner != $owner)
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }

    public function deleteClothes($id)
    {

        DB::beginTransaction();
        try {
            DB::table('clothes')->where('id',$id)->delete();
            DB::table('users')->decrement('total');
            DB::commit();
        } catch ( Exception $e){
            echo $e->getMessage();
            DB::rollBack();
        }
    }


    public function getPrice($id)
    {
        $price = DB::table('clothes')->where('id',$id)->pluck('price');
        if ($price->isEmpty())
        {
            return 0;
        }
        return $price[0];
    }

    public function setSuit($suitInfo,$userInfo)
    {
        $totalPrice = 0;
        foreach ($suitInfo as $s)
        {
            $totalPrice += $this->getPrice($s['id']);
        }
        $suitInfo = json_encode($suitInfo);
        DB::table('suits')->insert([
            'total_price'   =>  $totalPrice,
            'clothes'   =>  $suitInfo,
            'owner' =>  $userInfo->openid,
            'created_at'    =>  Carbon::now(),
            'updated_at'    =>  Carbon::now()
        ]);
    }

    public function getSuit($openid)
    {
        $suits = DB::table('suits')->where('owner',$openid)->get();
        foreach ($suits as $suit)           //多条套装记录
        {
            $suit->clothes = json_decode($suit->clothes);
            $suit = $this->addPic2clothes($suit->clothes);
        }
        return $suits;
    }

    public function addPic2clothes($clothes)
    {
        foreach ($clothes as $c)        //一个套装里面多个衣服
        {
            $c->pic_url = DB::table('clothes')
                ->where('id',$c->id)->pluck('pic_url');
            if ($c->pic_url->isEmpty())
            {
                continue;      //TODO::释放衣服中被删除的衣服
            }
            $c->pic_url = $c->pic_url[0];
        }
        return $clothes;
    }

    public function getSuitOwner($suitId)
    {
        $owner = DB::table('suits')->where('id',$suitId)->pluck('owner');
        if ($owner->isEmpty())
        {
            return -1;
        }
        return $owner[0];
    }

    public function deleteSuit($suitId)
    {
        DB::table('suits')->where('id',$suitId)->delete();
    }

    public function wearSuit($suitId)
    {
        DB::beginTransaction();
        try {
            $perPrice = DB::table('suits')->where('id',$suitId)->pluck('total_price')[0];
            $count = DB::table('suits')->where('id',$suitId)->pluck('count')[0];
            $perPrice /= $count+1;
            DB::table('suits')->where('id',$suitId)->increment('count');
            DB::table('suits')->where('id',$suitId)->update([
                'updated_at'    =>  Carbon::now(),
                'per_price'     =>   $perPrice
            ]);

            $clothes = json_decode(DB::table('suits')->where('id',$suitId)->pluck('clothes')[0]);

            foreach ($clothes as $c)
            {
                DB::table('clothes')->where('id',$c->id)->increment('count');
            }
            DB::commit();
        } catch ( Exception $e){
            echo $e->getMessage();
            DB::rollBack();
        }
    }

//    public function checkFree($openid)
//    {
//        $total = DB::table('users')->where('openid',$openid)->pluck('total');
//        if ($total > )
//    }

}