<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getTopPosts(){
        try{
            $posts = $this->send('GET','https://jsonplaceholder.typicode.com/posts');
            $comments = $this->send('GET','https://jsonplaceholder.typicode.com/comments'); 
            $result = []; 
            if(isset($comments['data']) && !empty($comments['data'])){
                foreach($comments['data'] as $val){
                    if(!isset($result[$val['postId']])){
                        $result[$val['postId']]=['post_id'=>$val['postId'],'total_number_of_comments'=>0];
                    }
                    ++$result[$val['postId']]['total_number_of_comments'];
                    $result[$val['postId']]['post_title'] = isset($result[$val['postId']]['post_title']) ? $result[$val['postId']]['post_title'] : $this->send('GET','https://jsonplaceholder.typicode.com/posts/'.$val['postId'])['data']['title'];
                    $result[$val['postId']]['post_body'] = isset($result[$val['postId']]['post_body']) ? $result[$val['postId']]['post_body'] : $this->send('GET','https://jsonplaceholder.typicode.com/posts/'.$val['postId'])['data']['body'];
                }
                $collection = collect(
                    $result
                );
                $sorted = $collection->sortBy('total_number_of_comments');
                
                $result = $sorted->values()->all();
            } 
            return response()->json([
                'status'=>true,
                'message'=>'Data fetched successfully',
                'data'=>$result ?? []
            ]);
        }catch(\Exception $e){
            return [
                'status'=>false,
                'message'=>'Something went wrong',
                'error'=>$e->getMessage()
            ];
        }
    }
    //
    public function filterComments(Request $request){ 
        try{
            $response['status'] = 'true';
            $response['message'] = 'Data fetched successfully';
            $comments = $this->createCollectionForComments();   
            if ($request->has('name')) {
                
                // $response['data'] = $comments->where('name', $request->input('name'));
                $name = $request->input('name');
                $response['data'] = $comments->filter(function ($item) use ($name) { 
                    return false !== stristr($item['name'], $name);
                });
            }
    
            if ($request->has('email')) {
                $email = $request->input('email');
                $response['data'] = $comments->filter(function ($item) use ($email) { 
                    return false !== stristr($item['email'], $email);
                });
            }
    
            if ($request->has('body')) {
                $body = $request->input('body');
                $response['data'] = $comments->filter(function ($item) use ($body) { 
                    return false !== stristr($item['body'], $body);
                });
            }

 
            return response()->json($response);
            }catch(\Exception $e){
                return [
                    'status'=>false,
                    'error'=>$e->getMessage()
                ];
            }
    }
    private function createCollectionForComments(){
        try{
            $comments = $this->send('GET','https://jsonplaceholder.typicode.com/comments'); 
            $collection = collect($comments['data']);
            return $collection;
        }catch(\Exception $e){
            return [
                'status'=>false,
                'error'=>$e->getMessage()
            ];
        }
    }
    private function send($method,$url,$params = []){
        try{
            $client = new Client();
            $res = $client->request($method, $url, $params);
            $response = json_decode($res->getBody()->getContents(),true);
            return [
                'status'=>true,
                'data'=>$response
            ];
        }catch(\Exception $e){
            return [
                'status'=>false,
                'error'=>$e->getMessage()
            ];
        }
    }
}
