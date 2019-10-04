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
        $clothes = DB::table('clothes')->where('owner',$user_id)->orderby('created_at','desc')->paginate(15);
        $clothes = json_decode(json_encode($clothes), true);
        foreach ($clothes['data'] as &$c)
        {
            $c['tags'] = json_decode($c['tags']);
        }
        return $clothes;
    }

    public function getClothesByWord($user_id,$word)
    {
        $clothes = DB::table('clothes')->where('owner',$user_id)->where('category','like','%'.$word.'%')->orderby('created_at','desc')->paginate(15);
        $clothes = json_decode(json_encode($clothes), true);
        foreach ($clothes['data'] as &$c)
        {
            $c['tags'] = json_decode($c['tags']);
        }
        return $clothes;
    }

    public function updateClothes($clothes,$owner)
    {
        if($this->getClothesOwnerById($clothes['id']) != $owner)
        {
            return -1;
        }
        else
        {
            DB::table('clothes')->where('id',$clothes['id'])->update($clothes);
            return 0;
        }
    }

    public function getClothesOwnerById($clothesId)
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

    public function clothesCount($id)
    {
        DB::table('clothes')->where('id',$id)->increment('count');
    }
    public function clothesDeCount($id)
    {
        DB::table('clothes')->where('id',$id)->decrement('count');
    }

    /**
     * @param $suitInfo
     * @param $userInfo
     * @param $ids 各个衣物的id
     * @return int
     */
    public function setSuit($suitInfo, $userInfo, $ids)
    {
        foreach ($ids as $id)       //减少下方事务中的工作量
        {
            if ($userInfo->user_id != $this->getClothesOwnerById($id))
            {
                return -1;
            }
        }
        $suitInfo = array_merge($suitInfo,[
            'owner' =>  $userInfo->user_id,
            'created_at'    =>  Carbon::now(),
            'updated_at'    =>  Carbon::now()
        ]);

        DB::beginTransaction();         //tags 的count ， clothes 的count
        try {
            $this->touchTags($suitInfo['tags'],$userInfo->user_id);
            $suitInfo['tags'] = json_encode($suitInfo['tags']);
            DB::table('suits')->insert($suitInfo);
            foreach ($ids as $id)
            {
                $this->clothesCount($id);
            }

            DB::commit();
        } catch ( Exception $e){
            dd($e->getMessage());
            DB::rollBack();

            return -1;
        }
        return 0;
    }

    public function getAllSuit($user_id)
    {
        $suits = DB::table('suits')->where('owner',$user_id)->orderby('created_at','desc')->paginate(30);
        $suits = json_decode(json_encode($suits),true);
        foreach ($suits['data'] as &$suit)           //多条套装记录
        {
            $suit['tags'] = json_decode($suit['tags']);
            $this->checkSuitRequest($suit,0);
        }
        return $suits;
    }

    /**
     * 如果需要添加搭配师的名字 , 就会添加 helper字段
     * @param $suit 数组
     * @param $opt
     */
    public function checkSuitRequest(&$suit, $opt)        //todo 测试
    {
        $request_id = $suit['request_id'];
        if ($request_id)
        {
            $query = DB::table('suits_request')->where('request_id',$request_id);
            if ($opt == 0)
            {
                $suit['helper'] = $query->pluck('to')[0];
            }
        }
        unset($suit['request_id']);
    }
    public function getSuitByWord($user_id,$word)
    {
        $clothes = DB::table('suits')->where('owner',$user_id)->where('category','like','%'.$word.'%')->orderby('created_at','desc')->paginate(15);
        $clothes = json_decode(json_encode($clothes), true);
        foreach ($clothes['data'] as &$c)
        {
            $c['tags'] = json_decode($c['tags']);
        }
        return $clothes;
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

    public function deleteSuit($suitId,$userInfo) //tags的count ， 衣物的count
    {
        $suitInfo = $this->getSuitById($suitId);
//        dd($suitInfo);
        DB::beginTransaction();
        try {
            $this->deTags($suitInfo->tags,$userInfo->user_id);
            foreach ($suitInfo->clothes_ids as $id)
            {
                $this->clothesDeCount($id);
            }

            DB::table('suits')->where('id',$suitId)->delete();
            DB::commit();
        } catch ( Exception $e){
            dd($e->getMessage());
            DB::rollBack();

            return -1;
        }
        return 0;

    }

    public function getSuitById($suitId)
    {
        $data = DB::table('suits')->where('id',$suitId)->first();
        $data->tags = json_decode($data->tags);
        $data->clothes_ids = explode(',',$data->clothes_ids);
        return $data;
    }

    public function deTags($tags,$user_id)
    {
        $now = Carbon::now();
        foreach ($tags as $tag)
        {
            $tag->tag_owner = $user_id;
            $query = DB::table('tags')->where([
                ['tag_owner','=',$tag->tag_owner],
                ['tag_name','=',$tag->tag_name],
                ['tag_type','=',$tag->tag_type]
            ]);
//            dd($tag['tag_name']);
            if ($query->pluck('tag_count')[0] > 1)
            {
                $query->decrement('tag_count');
                $query->update(['updated_at' => $now] );
            }
        }
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