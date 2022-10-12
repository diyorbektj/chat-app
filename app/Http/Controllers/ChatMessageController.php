<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Http\Requests\GetChatRequest;
use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    /**
     * Chat Messages
     * @param GetChatRequest $request
     * @return JsonResponse
     */
    public function index(GetMessageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $chatId = $data['chat_id'];
        $currentPage = $data['page'] ?? 1;
        $pageSize = $data['page_size'] ?? 15;
        $messages = ChatMessage::query()
            ->where('chat_id', $chatId)
            ->with('user')
            ->latest('created_at')
            ->simplePaginate(
                $pageSize,
                ['*'],
                'page',
                $currentPage
            );
        return $this->success($messages->getCollection());
    }

    public function store(StoreMessageRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $chatMessage = ChatMessage::query()->create($data);
        $chatMessage->load("user");

        //// Send
        $this->sendNotificationToOther($chatMessage);
        return $this->success($chatMessage, "Message has been send successfuly");
    }

    private function sendNotificationToOther(ChatMessage $chatMessage):void
    {
//        $chat_id = $chatMessage->chat_id;
        broadcast(new NewMessageSent($chatMessage))->toOthers();
        $user = auth()->user();
        $userId = $user->id;
        $chat = Chat::query()->where('id', $chatMessage->chat_id)
            ->with(['participants' => function($query) use($userId){
                $query->where('user_id', '!=', $userId);
            }])->first();
        if(count($chat->participants) > 0)
        {
            $otherUserId = $chat->participants[0]->user_id;
            $otherUser = User::query()->where('id', $otherUserId)->first();
            $otherUser->sendNewMessageNotificaion([
                'messageData' => [
                    'senderName' => $user->username,
                    'message' => $chatMessage->message,
                    'chatId' => $chatMessage->chat_id
                ]
            ]);
        }
    }
}
