<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Product;
use App\Services\Chat\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class ConversationController extends Controller
{
    public function __construct(protected ChatService $chatService) {}

    /**
     * Display chat inbox with list of conversations.
     */
    public function index(Request $request): View
    {
        $conversations = $this->chatService->getUserConversations($request->user());

        return view('chats.index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Start or open a conversation from a product page.
     */
    public function start(Request $request, Product $product): RedirectResponse
    {
        try {
            $conversation = $this->chatService->getOrCreateConversation($request->user(), $product);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('chats.show', $conversation);
    }

    /**
     * View conversation thread and mark unread messages as read.
     */
    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);

        $this->chatService->markAsRead($conversation, $request->user());

        $conversation->load([
            'product.images',
            'product.category',
            'buyer.student.university',
            'seller.student.university',
            'messages.sender',
        ]);

        return view('chats.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'currentUser' => $request->user(),
            'otherParticipant' => $conversation->otherParticipant($request->user()),
        ]);
    }

    /**
     * Send a new message in the conversation.
     */
    public function storeMessage(SendMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        Gate::authorize('sendMessage', $conversation);

        try {
            $this->chatService->sendMessage(
                $conversation,
                $request->user(),
                $request->validated('body')
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('chats.show', $conversation);
    }
}
