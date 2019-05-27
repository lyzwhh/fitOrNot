<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/26
 * Time: 下午4:13
 */

namespace App\Services;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class MomentService
{

    public function createMoment($momentInfo)
    {
        DB::table('moments')->insert($momentInfo);
    }

    public function getNewestMoment()
    {
//        $momentData = DB::table('moments')->where('status',0)
//                            ->select('id','writer','pics_url','content','likes_num','comments_num')
//                            ->orderBy('created_at', 'desc')
//                            ->paginate(24);

        $momentData = DB::table('moments')->where('status',0)
            ->join('users','moments.writer','=','users.openid')
            ->select('moments.pics_url','moments.content','moments.likes_num','moments.comments_num',
                    'moments.writer','users.avatar_url','moments.id','users.nickname')
            ->orderBy('moments.created_at', 'desc')
            ->paginate(24);
        foreach ($momentData as $m)
        {
            $m->pics_url = json_decode($m->pics_url);
        }
        return $momentData;
    }

    public function getMomentDetail()
    {

    }

    public function getMomentByOpenid($openid)
    {
        $momentData = DB::table('moments')->where('status',0)->where('openid',$openid)
            ->join('users','moments.writer','=','users.openid')
            ->select('moments.pics_url','moments.content','moments.likes_num','moments.comments_num',
                'moments.writer','users.avatar_url','moments.id','users.nickname')
            ->orderBy('moments.created_at', 'desc')
            ->paginate(24);
        foreach ($momentData as $m)
        {
            $m->pics_url = json_decode($m->pics_url);
        }
        return $momentData;
    }


    public function createLike($from,$to)    //from 为openid ,to 为moment id
    {
        $flag = $this->checkIfLiked($from,$to);
        if ($flag == 1)
        {
            DB::beginTransaction();
            try {

                DB::table('likes')->insert([
                    'from'  =>  $from,
                    'to'    =>  $to,
                    'created_at'    =>  Carbon::now(),
                    'updated_at'    =>  Carbon::now()
                ]);
                DB::table('users')->where('openid',$from)->increment('liked');
                DB::table('moments')->where('id',$to)->increment('likes_num');

                DB::commit();
            } catch ( Exception $e){
//            echo $e->getMessage();
                DB::rollBack();
            }

            return 1;
        }
        else
        {
            return -1;
        }
    }

    public function checkIfLiked($from,$to)
    {
        $flag = DB::table('likes')->where([
            'from'  =>  $from,
            'to'    =>  $to
        ])->first();
        return ($flag == null);     //没喜欢返回1

    }

    public function deleteLike($from,$to)       //from 为openid ,to 为moment id
    {
        DB::beginTransaction();
        try {

            DB::table('likes')->where([
                'from'  =>  $from,
                'to'    =>  $to
            ])->delete();
            DB::table('users')->where('openid',$from)->decrement('liked');
            DB::table('moments')->where('id',$to)->decrement('likes_num');

            DB::commit();
        } catch ( Exception $e){
//            echo $e->getMessage();
            DB::rollBack();
        }
    }

    public function createComment($data)
    {
        DB::beginTransaction();
        try {
            $data = array_merge($data,[
                'created_at'    =>  Carbon::now(),
                'updated_at'    =>  Carbon::now()
            ]);
            DB::table('comments')->insert($data);
            DB::table('moments')->where('id',$data['to'])->increment('comments_num');

            DB::commit();
        } catch ( Exception $e){
//            echo $e->getMessage();
            DB::rollBack();
        }
    }
}