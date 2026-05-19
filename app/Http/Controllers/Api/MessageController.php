<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    /**
     * Get messages with pagination (for initial load)
     * GET /api/conversations/{conversation}/messages
     */
    public function index(Request $request, Conversation $conversation)
    {
        // Authorization check
        if ($request->user()->id !== $conversation->citizen_id && 
            $request->user()->id !== $conversation->staff_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->paginate(50);
        
        // Mark messages as read if user is receiver
        $updatedCount = Message::where('conversation_id', $conversation->id)
            ->where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        return response()->json([
            'success' => true,
            'data' => $messages,
            'meta' => [
                'marked_as_read' => $updatedCount
            ]
        ]);
    }
    
    /**
     * POLLING ENDPOINT - Get new messages since last check
     * GET /api/conversations/{conversation}/poll
     */
    public function poll(Request $request, Conversation $conversation)
    {
        // Authorization check
        if ($request->user()->id !== $conversation->citizen_id && 
            $request->user()->id !== $conversation->staff_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get last message ID from request (client sends last received message ID)
        $lastMessageId = $request->input('last_message_id', 0);
        
        // Get new messages
        $newMessages = Message::where('conversation_id', $conversation->id)
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Get typing indicator (check if other user is typing)
        $otherUserId = $request->user()->id === $conversation->citizen_id 
            ? $conversation->staff_id 
            : $conversation->citizen_id;
        
        $isTyping = Cache::get("typing_{$conversation->id}_{$otherUserId}", false);
        
        // Auto-mark received messages as read
        foreach ($newMessages as $message) {
            if ($message->receiver_id === $request->user()->id && !$message->is_read) {
                $message->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $newMessages,
                'last_message_id' => $newMessages->isNotEmpty() ? $newMessages->last()->id : $lastMessageId,
                'has_new' => $newMessages->isNotEmpty(),
                'is_typing' => $isTyping,
                'timestamp' => now()->toIso8601String()
            ]
        ]);
    }
    
    /**
     * Send a new message
     * POST /api/conversations/{conversation}/messages
     */
    public function store(Request $request, Conversation $conversation)
    {
        // Authorization check
        if ($request->user()->id !== $conversation->citizen_id && 
            $request->user()->id !== $conversation->staff_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Check if conversation is active
        if ($conversation->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This conversation is closed. Cannot send new messages.'
            ], 400);
        }
        
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);
        
        // Determine sender type and receiver
        $senderType = $request->user()->id === $conversation->citizen_id ? 'citizen' : 'staff';
        $receiverId = $senderType === 'citizen' ? $conversation->staff_id : $conversation->citizen_id;
        
        if (!$receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'No staff assigned to this request yet. Please wait for assignment.'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'receiver_id' => $receiverId,
                'sender_type' => $senderType,
                'message' => $request->message,
                'is_read' => false
            ]);
            
            // Update conversation's last message timestamp
            $conversation->update([
                'last_message_at' => now()
            ]);
            
            DB::commit();
            
            // Load sender relationship for response
            $message->load('sender');
            
            // TODO: Send push notification to receiver
            // $this->sendPushNotification($receiverId, $message);
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to send message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Mark a single message as read
     * PATCH /api/conversations/messages/{message}/read
     */
    public function markAsRead(Request $request, Message $message)
    {
        // Check if user is the receiver
        if ($message->receiver_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }
    
    /**
     * Get unread messages count for current user
     * GET /api/conversations/unread/count
     */
    public function unreadCount(Request $request)
    {
        $count = Message::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }
    
    /**
     * Get unread count per conversation
     * GET /api/conversations/unread/per-conversation
     */
    public function unreadPerConversation(Request $request)
    {
        $conversations = Conversation::where('citizen_id', $request->user()->id)
            ->orWhere('staff_id', $request->user()->id)
            ->get();
        
        $result = [];
        foreach ($conversations as $conversation) {
            $result[$conversation->id] = $conversation->unreadCountForUser($request->user()->id);
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * Typing indicator (store in cache)
     * POST /api/conversations/{conversation}/typing
     */
    public function typing(Request $request, Conversation $conversation)
    {
        // Authorization check
        if ($request->user()->id !== $conversation->citizen_id && 
            $request->user()->id !== $conversation->staff_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $request->validate([
            'is_typing' => 'required|boolean'
        ]);
        
        $isTyping = $request->input('is_typing', false);
        $userId = $request->user()->id;
        $cacheKey = "typing_{$conversation->id}_{$userId}";
        
        if ($isTyping) {
            // Store typing status for 3 seconds
            Cache::put($cacheKey, true, 3);
        } else {
            Cache::forget($cacheKey);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_typing' => $isTyping
            ]
        ]);
    }
    
    /**
     * Delete a message (soft delete or hard delete based on permission)
     * DELETE /api/conversations/messages/{message}
     */
    public function destroy(Request $request, Message $message)
    {
        // Only sender can delete their own message, or admin
        if ($message->sender_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You can only delete your own messages'
            ], 403);
        }
        
        // Check if message is too old to delete (optional: only allow deletion within 5 minutes)
        $minutesSinceSent = now()->diffInMinutes($message->created_at);
        if ($minutesSinceSent > 5 && $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Messages can only be deleted within 5 minutes of sending'
            ], 400);
        }
        
        $message->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }
}