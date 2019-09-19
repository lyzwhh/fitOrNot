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
use mysql_xdevapi\Collection;

class ClothesService
{
    public function setClothes($user_id,$clothes)
    {
        $now = Carbon::now();
        $clothes['owner'] = $user_id;
        $clothes['created_at'] = $now;
        $clothes['updated_at'] = $now;


        DB::beginTransaction();
        try {
            $this->touchTags($clothes['tags'],$user_id);
            $clothes['tags'] = json_encode($clothes['tags']);
            DB::table('clothes')->insertGetId($clothes);
//            dd($clothes);
            DB::table('users')->where('user_id',$user_id)->increment('total');
            DB::commit();
        } catch ( Exception $e){
            echo $e->getMessage();
            DB::rollBack();
        }

    }

    public function touchTags($tags,$user_id)
    {
        $now = Carbon::now();
        foreach ($tags as $tag)
        {
            $tag['tag_owner'] = $user_id;
            $query = DB::table('tags')->where([
                ['tag_owner','=',$tag['tag_owner']],
                ['tag_name','=',$tag['tag_name']],
                ['tag_type','=',$tag['tag_type']]
            ]);
//            dd($tag['tag_name']);
            if ($query->first() != null)
            {
                $query->increment('tag_count');
                $query->update(['updated_at' => $now] );
            }
            else
            {
                $tag['created_at'] = $now;
                $tag['updated_at'] = $now;
                DB::table('tags')->insert($tag);
            }
        }
    }
//    public function getOrderClothes($user_id)     //这三都是小程序的  1
//    {
//        $data = array();
//        $map = [
//            1 =>  'a',
//            2 =>  'b',
//            3 =>  'c',
//            4 =>  'd'
//        ];
//        for ($c=1 ; $c<=4 ; $c++)
//        {
//            $data[$map[$c]] = $this->getClothes($user_id,$c);
//        }
//        return $data;
//    }
//
//    public function getOrderClothes2($user_id)       //前端内部吵架的结果      2
//    {
//        $data = array();
//        for ($c=1 ; $c<=4 ; $c++)
//        {
//            $data[] = $this->getClothes($user_id,$c);
//        }
//        return $data;
//    }
//
//
//    public function getClothes($user_id,$category)        //  3
//    {
//        $clothes = DB::table('clothes')->where('owner',$user_id)->where('category',$category)->get();
//        return $clothes;
//    }

    public function getAllClothes($user_id)
    {

    }


    public function updateClothes($clothes,$owner)
    {
        if($this->getOwner($clothes['id']) != $owner)
        {
            return -1;
        }
        else
        {
            DB::table('clothes')->where('id',$clothes['id'])->update($clothes);
            return 0;
        }
    }

    public function getOwner($clothesId)
    {
        $clothes = DB::table('clothes')->where('id',$clothesId)->first();
        return $clothes->owner ?? -1;       //不存在user_id为-1的用户

    }

    public function deleteClothes($id,$user_id)
    {

        DB::beginTransaction();
        try {
            DB::table('clothes')->where('id',$id)->delete();
            DB::table('users')->where('user_id',$user_id)->decrement('total');
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
            'owner' =>  $userInfo->user_id,
            'created_at'    =>  Carbon::now(),
            'updated_at'    =>  Carbon::now()
        ]);
    }

    public function getSuit($user_id)
    {
        $suits = DB::table('suits')->where('owner',$user_id)->get();
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

}