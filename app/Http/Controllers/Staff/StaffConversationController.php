<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffConversationController extends Controller
{
    /**
     * List all conversations for staff
     */
    public function index()
    {
        $conversations = Conversation::where('staff_id', auth()->id())
            ->with(['citizen', 'serviceRequest', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Add unread count for each conversation
        foreach ($conversations as $conversation) {
            $conversation->unread_count = $conversation->unreadCountForUser(auth()->id());
        }

        return view('staff.conversations.index', compact('conversations'));
    }

    /**
     * Show a single conversation with messages
     */
    public function show(Conversation $conversation)
    {
        // Ensure staff owns this conversation
        if ($conversation->staff_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        // Mark all unread messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        $messages = $conversation->messages()
            ->with('sender')
            ->paginate(50);

        $conversation->load(['citizen', 'serviceRequest']);

        return view('staff.conversations.show', compact('conversation', 'messages'));
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        if ($conversation->staff_id !== auth()->id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        if ($conversation->status !== 'active') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Conversation is closed'], 400);
            }
            return back()->with('error', 'This conversation is closed.');
        }

        DB::beginTransaction();
        try {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'receiver_id' => $conversation->citizen_id,
                'sender_type' => 'staff',
                'message' => $request->message,
                'is_read' => false
            ]);

            $conversation->update(['last_message_at' => now()]);
            DB::commit();

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $message
                ], 201);
            }

            // Return redirect for normal form submissions
            return redirect()->route('staff.conversations.show', $conversation->id)
                ->with('success', 'Message sent successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to send message'], 500);
            }
            
            return back()->with('error', 'Failed to send message.');
        }
    }

    /**
     * Close a conversation
     */
    public function close(Conversation $conversation)
    {
        if ($conversation->staff_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $conversation->update(['status' => 'closed']);

        return redirect()
            ->route('staff.conversations.index')
            ->with('success', 'Conversation closed successfully.');
    }

    public function reopen($id)
    {
        $conversation = Conversation::findOrFail($id);

        // Optional: authorize that this staff member owns/handles this conversation
        // $this->authorize('update', $conversation);

        $conversation->update([
            'status' => 'active',
            'closed_at' => null, // if you have this column
        ]);

        return redirect()
            ->route('staff.conversations.show', $conversation->id)
            ->with('success', 'Conversation reopened.');
    }

    /**
 * Poll for new messages (web version using session auth)
 */
    public function poll(Request $request, Conversation $conversation)
    {
        if ($conversation->staff_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $lastMessageId = $request->input('last_message_id', 0);
        
        $newMessages = Message::where('conversation_id', $conversation->id)
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Auto-mark messages as read
        foreach ($newMessages as $message) {
            if ($message->receiver_id === auth()->id() && !$message->is_read) {
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
                'has_new' => $newMessages->isNotEmpty()
            ]
        ]);
    }
}