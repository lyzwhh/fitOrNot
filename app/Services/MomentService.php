<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/26
 * Time: 下午4:13
 */

namespace App\Services;


use App\User;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use Carbon\Carbon;
class MomentService
{

    public function createMoment($momentInfo)
    {
        $momentInfo = array_merge($momentInfo,[
            'created_at'    =>  Carbon::now(),
            'updated_at'    =>  Carbon::now()
        ]);
        DB::table('moments')->insert($momentInfo);
    }

    //获取最新所有人的Moment
    public function getNewestMoment($user_id)
    {
//        $momentData = DB::table('moments')->where('status',0)
//                            ->select('id','writer','pics_url','content','likes_num','comments_num')
//                            ->orderBy('created_at', 'desc')
//                            ->paginate(24);

        $momentData = DB::table('moments')->where('status',0)
            ->join('users','moments.writer','=','users.user_id')
            ->join('suits','moments.suit_id','=','suits.id')
            ->select('moments.content','moments.writer','users.avatar_url','moments.id','users.nickname'
                ,'moments.likes_num','moments.comments_num','moments.views_num','suits.clothes as pic_url',
                'suits.request_id','suits.tags')
            ->orderBy('moments.created_at', 'desc')
            ->paginate(24);
        $momentData = json_decode(json_encode($momentData),true);
        foreach ($momentData['data'] as &$data)
        {
            $data['tags'] = json_decode($data['tags']);

            $data['notFollowed'] = $user_id == $data['writer'] ? -1 : UserService::checkIfFollowed($user_id,$data['writer']);
        }
        return $momentData;
    }

    // 返回朋友圈的作者的user_id , 用于删除朋友圈
    public function getMomentOwner($momentId)
    {
        $user_id = DB::table('moments')->where('id',$momentId)->pluck('writer');
        if ($user_id != null)
        {
            return $user_id[0];
        }
        return null;
    }

    public function deleteMoment($id)       //TODO : 微信云里面的图片删除
    {
        DB::table('moments')->where('id',$id)->update([
            'status'    =>  '-1'
        ]);
    }
    public function getMomentDetail()
    {

    }

    //获取某人所有moment
    public function getMomentByUserId($my_user_id , $user_id)
    {
        $momentData = DB::table('moments')->where('status',0)->where('user_id',$user_id)->where('status',0)
            ->join('users','moments.writer','=','users.user_id')
            ->join('suits','moments.suit_id','=','suits.id')
            ->select('moments.content','moments.writer','users.avatar_url','moments.id','users.nickname'
                ,'moments.likes_num','moments.comments_num','moments.views_num','suits.clothes as pic_url',
                'suits.request_id','suits.tags')
            ->orderBy('moments.created_at', 'desc')
            ->paginate(24);
        $momentData = json_decode(json_encode($momentData),true);
        foreach ($momentData['data'] as &$data)
        {
            $data['tags'] = json_decode($data['tags']);
            $data['notFollowed'] = $my_user_id == $data['writer'] ? -1 : UserService::checkIfFollowed($my_user_id,$data['writer']);
        }

        return $momentData;
    }

    public function getAllMyLikedMoment($user_id)   //todo 优化
    {
//        $momentData = DB::table('moments')->where('status',0)     // 无法用like创建时间排序 ，废弃
//            ->whereExists(function ($query) use ($user_id) {
//                $query->select(DB::raw(1))
//                    ->from('likes')
//                    ->where('likes.from',$user_id)
//                    ->whereRaw('likes.to = moments.id');    //一定用 Raw 否则查找不到 ， 这个whereExists没错 ，可以参考
//            })
//            ->join('users','moments.writer','=','users.user_id')
//            ->join('suits','moments.suit_id','=','suits.id')
        //如果在这里join like ， likes.from = user.user_id 这时某user的别的没被like的也会被连接进去
//            ->select('moments.content','moments.writer','users.avatar_url','moments.id','users.nickname'
//                ,'moments.likes_num','moments.comments_num','moments.views_num','suits.clothes as pic_url',
//                'suits.request_id','suits.tags')
//            ->orderBy('moments.created_at', 'desc')
//            ->paginate(24);

        $momentData = DB::table('likes')
            ->where('from',$user_id)->where('status',0)
            ->join('moments','moments.id','=','likes.to')
            ->join('users','moments.writer','=','users.user_id')
            ->join('suits','moments.suit_id','=','suits.id')
            ->select('moments.content','moments.writer','users.avatar_url','moments.id','users.nickname'
                ,'moments.likes_num','moments.comments_num','moments.views_num','suits.clothes as pic_url',
                'suits.request_id','suits.tags')
            ->orderBy('likes.created_at', 'desc')
            ->paginate(24);
        $momentData = json_decode(json_encode($momentData),true);
        foreach ($momentData['data'] as &$data)
        {
            $data['tags'] = json_decode($data['tags']);
            $data['notFollowed'] = $user_id == $data['writer'] ? -1 : UserService::checkIfFollowed($user_id,$data['writer']);
        }
        return $momentData;
    }

    public function getAllMyFollowingMoment($user_id)
    {
        $momentData = DB::table('follows')
            ->where('from',$user_id)->where('status',0)
            ->join('users','follows.to','=','users.user_id')
            ->join('moments','moments.writer','=','users.user_id')      //分叉
            ->join('suits','moments.suit_id','=','suits.id')
            ->select('moments.content','moments.writer','users.avatar_url','moments.id','users.nickname'
                ,'moments.likes_num','moments.comments_num','moments.views_num','suits.clothes as pic_url',
                'suits.request_id','suits.tags')
            ->orderBy('moments.created_at', 'desc')
            ->paginate(24);
        $momentData = json_decode(json_encode($momentData),true);
        foreach ($momentData['data'] as &$data)
        {
            $data['tags'] = json_decode($data['tags']);
            $data['notFollowed'] = $user_id == $data['writer'] ? -1 : UserService::checkIfFollowed($user_id,$data['writer']);
        }
        return $momentData;
    }

    public function getMomentById($moment_id)
    {
        $data = DB::table('moments')->where('id',$moment_id)->where('status',0)->first();
        return $data;
    }


    public function createLike($from,$to)    //from 为user_id ,to 为moment id
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
                DB::table('moments')->where('id',$to)
                    ->join('users','moments.writer','=','users.user_id')
                    ->increment('users.liked');
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

    public function deleteLike($from,$to)       //from 为user_id ,to 为moment id
    {
        $flag = $this->checkIfLiked($from,$to);
        if ($flag == 1)
        {
            return 0;
        }
        DB::beginTransaction();
        try {

            DB::table('likes')->where([
                'from'  =>  $from,
                'to'    =>  $to
            ])->delete();
            DB::table('moments')->where('id',$to)
                ->join('users','moments.writer','=','users.user_id')
                ->decrement('users.liked');
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

    // 返回评论的作者的user_id,用于判断是否为评论主人
    public function getCommentOwner($commentId)
    {
        $user_id = DB::table('comments')->where('id',$commentId)->pluck('from');
//        return $user_id;
        if ($user_id->isNotEmpty())
        {
            return $user_id[0];
        }
        return null;
    }

    public function deleteComment($commentId)
    {
        DB::beginTransaction();
        try {
            $momentId = DB::table('comments')->where('id',$commentId)->pluck('to');
            DB::table('comments')->where('id',$commentId)->delete();
            DB::table('moments')->where('id',$momentId)->decrement('comments_num');

            DB::commit();
        } catch ( Exception $e){
//            echo $e->getMessage();
            DB::rollBack();
        }
    }



    public function getCommentByMoment($momentId)
    {
        $data = DB::table('comments')->where('to',$momentId)->select('id','from','refer','content','created_at')
                             ->orderBy('created_at','asc')
                             ->get();
        foreach ($data as $d) {
            $fromName = DB::table('users')->where('user_id',$d->from)->pluck('nickname');
            $d->fromName = $fromName[0];
            if ($d->refer != null)
            {
                $referName = DB::table('users')->where('user_id',$d->refer)->pluck('nickname');
                $d->referName = $referName[0];
            }
            $d->avatar_url = DB::table('users')->where('user_id',$d->from)->pluck('avatar_url')[0];
        }
        DB::table('moments')->where('id',$momentId)->increment('views_num');
        return $data;
    }

    public function refreshMoment($momentId)
    {
        $data = DB::table('moments')->where('id',$momentId)->where('status',0)->select('likes_num','views_num','comments_num')->first();
        return $data;
    }
}