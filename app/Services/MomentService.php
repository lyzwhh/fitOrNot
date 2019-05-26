<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/26
 * Time: 下午4:13
 */

namespace App\Services;


use Illuminate\Support\Facades\DB;

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
            ->select('moments.pics_url','moments.content','moments.likes_num','moments.comments_num','users.id as writerId','moments.id')
            ->orderBy('moments.created_at', 'desc')
            ->paginate(24);
        return $momentData;
    }
}