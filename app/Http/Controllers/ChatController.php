<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get Chat
     * @param GetChatRequest $request
     * @return JsonResponse
     */
    public function index(GetChatRequest $request): JsonResponse
    {
        $data = $request->validated();
        $isPrivate = 1;
        if($request->has('is_private'))
        {
            $isPrivate = (int)$data['is_private'];
        }
        $userId=auth()->id();
        $chats = Chat::query()->where('is_private', $isPrivate)
            ->whereHas('participants', function ($q) use ($userId){
                $q->where('user_id', $userId);
            })
            ->whereHas('message')
            ->with(['lastMessage.user', 'participants.user'])
            ->latest('updated_at')
            ->get();
        return $this->success($chats);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(StoreChatRequest $request)
    {
        $data = $this->storedata($request);
        if($data['userId'] == $data['otherUserId']){
            return $this->error("you can not created a chat this yur own");
        }
        $previousChat = $this->getPreviousChat($data['otherUserId']);
        if (!$previousChat){
            $chat = Chat::create($data['data']);
            $chat->participants()->createMany([
                [
                    'user_id' => $data['userId'],
                ],
                [
                    'user_id' => $data['otherUserId']
                ]
            ]);
            $chat->refresh()->load('lastMessage.user','participants.user');
            return $this->success($chat);
        }
        return $this->success($previousChat->load('lastMessage.user','participants.user'));
    }

    /**
     * @param int $otherUserId
     * @return
     */
    private function getPreviousChat(int $otherUserId):mixed
    {
        $userId = auth()->id();
        return Chat::where('is_private',1)
            ->whereHas('participants', function ($query) use($userId) {
               $query->where('user_id', $userId);
            })
            ->whereHas('participants', function ($query) use($otherUserId) {
                $query->where('user_id', $otherUserId);
            })->first();
    }

    /**
     * Prepares data for store a chat
     * @param StoreChatRequest $request
     * @return array
     */
    private function storedata(StoreChatRequest $request)
    {
        $data = $request->validated();
        $otherUserId = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->id();
        return [
            'otherUserId' => $otherUserId,
            'userId'=> auth()->id(),
            'data' =>$data
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param Chat $chat
     * @return JsonResponse
     */
    public function show(Chat $chat)
    {
        $chat->load('lastMessage.user','participants.user');
        return $this->success($chat);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
