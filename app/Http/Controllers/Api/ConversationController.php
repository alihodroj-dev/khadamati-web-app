<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    
    /**
     * Get citizen's conversations
     * GET /api/conversations/my
     */
    public function myConversations(Request $request)
    {
        $conversations = Conversation::where('citizen_id', $request->user()->id)
            ->with(['serviceRequest', 'staff', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();
        
        // Add unread count for each conversation
        $conversations->each(function ($conversation) use ($request) {
            $conversation->unread_count = $conversation->unreadCountForUser($request->user()->id);
        });
        
        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }
    
    /**
     * Get or create conversation for a service request
     * GET /api/conversations/my/{serviceRequest}
     */
    public function getOrCreate(Request $request, ServiceRequest $serviceRequest)
    {
        // Verify citizen owns this request
        if ($serviceRequest->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - This is not your service request'
            ], 403);
        }
        
        // Check if staff is assigned
        if (!$serviceRequest->assigned_staff_id) {
            return response()->json([
                'success' => false,
                'message' => 'No staff has been assigned to this request yet. Please wait for assignment.'
            ], 400);
        }
        
        $conversation = Conversation::firstOrCreate(
            [
                'service_request_id' => $serviceRequest->id,
                'citizen_id' => $request->user()->id,
            ],
            [
                'status' => 'active',
                'staff_id' => $serviceRequest->assigned_staff_id,
            ]
        );
        
        // If staff is assigned but conversation doesn't have staff_id, update it
        if ($serviceRequest->assigned_staff_id && !$conversation->staff_id) {
            $conversation->update(['staff_id' => $serviceRequest->assigned_staff_id]);
        }
        
        $conversation->load(['serviceRequest', 'staff', 'lastMessage']);
        $conversation->unread_count = $conversation->unreadCountForUser($request->user()->id);
        
        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }
    
    /**
     * Get staff's conversations
     * GET /api/conversations/staff
     */
    public function staffConversations(Request $request)
    {
        // Verify user is staff
        if (!in_array($request->user()->role, ['staff', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Staff access only'
            ], 403);
        }
        
        $conversations = Conversation::where('staff_id', $request->user()->id)
            ->with(['citizen', 'serviceRequest', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();
        
        // Add unread count for each conversation
        $conversations->each(function ($conversation) use ($request) {
            $conversation->unread_count = $conversation->unreadCountForUser($request->user()->id);
        });
        
        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }
    
    /**
     * Show single conversation details
     * GET /api/conversations/staff/{conversation}
     */
    public function show(Request $request, Conversation $conversation)
    {
        // Check authorization
        if ($request->user()->id !== $conversation->citizen_id && 
            $request->user()->id !== $conversation->staff_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $conversation->load(['citizen', 'staff', 'serviceRequest']);
        $conversation->unread_count = $conversation->unreadCountForUser($request->user()->id);
        
        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }
    
    /**
     * Close a conversation
     * PATCH /api/conversations/{conversation}/close
     */
    public function close(Request $request, Conversation $conversation)
    {
        // Check authorization (only staff or admin can close)
        if ($request->user()->id !== $conversation->staff_id && 
            $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Only staff can close conversations'
            ], 403);
        }
        
        $conversation->update(['status' => 'closed']);
        
        return response()->json([
            'success' => true,
            'message' => 'Conversation closed successfully',
            'data' => $conversation
        ]);
    }
}