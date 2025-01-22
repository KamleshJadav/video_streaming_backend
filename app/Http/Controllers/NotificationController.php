<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    protected $notification;

    public function __construct()
    {
        $this->notification = Firebase::messaging();
    }

    public function add(Request $request)
    {
        try {
            // $user_ids = $request->input('user_ids');
            $user_ids = json_decode($request->input("user_ids"), true);
            $request->validate([
                "title" => "required|string|max:255",
                "message" => "required|string|max:5000",
                "is_active" => "nullable|boolean",
                "redirect_url" => "nullable|string",
                "user_ids" => "nullable|json",
            ]);

            if ($user_ids && count($user_ids) > 0) {
                $validUserIds = User::whereIn("id", $user_ids)
                    ->where("is_active", 1)
                    ->pluck("id")
                    ->toArray();

                if (count($validUserIds) === 0) {
                    return response()->json(
                        [
                            "success" => false,
                            "message" =>
                                "No active users found for the given user IDs.",
                        ],
                        400
                    );
                }
                $user_ids = $validUserIds;
            } else {
                $user_ids = [];
            }

            $firstValidUserId = $validUserIds[0];

            // Retrieve the FCM token of the first valid user
            $firstValidUser = User::find($firstValidUserId);

            if ($firstValidUser) {
                $fcmToken = $firstValidUser->fcm_token; // Assuming the fcm_token field exists in your 'users' table
                if ($fcmToken) {
                    $message = CloudMessage::fromArray([
                        "token" => $fcmToken,
                        "notification" => [
                            "title" => $request->title,
                            "body" => $request->message,
                        ],
                    ]);

                    try {
                        // Send the notification
                        $this->notification->send($message);
                    } catch (\Exception $e) {
                        // If sending fails, return an error response
                        return response()->json(
                            [
                                "success" => false,
                                "message" => "Failed to send the notification.",
                                "error" => $e->getMessage(),
                               
                            ],
                            500
                        );
                    }
                }
            }

            // Create the notification in your database
            $notification = Notification::create([
                "title" => $request->title,
                "message" => $request->message,
                "is_active" => $request->is_active ?? 1,
                "redirect_url" => $request->redirect_url ?? "",
                "user_ids" => $user_ids,
            ]);

            // Return success response
            return response()->json(
                [
                    "success" => true,
                    "message" => "Notification sent successfully",
                    "data" => $notification,
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "user_ids" => $user_ids,
                    "error" => "Failed to add notification",
                    "message" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function addAllUser(Request $request)
    {
        try {
            $user_ids = $request->input("user_ids");

            $request->validate([
                "title" => "required|string|max:255",
                "message" => "required|string|max:5000",
                "is_active" => "nullable|boolean",
                "redirect_url" => "nullable|string",
            ]);

            $users = User::where("is_active", 1)->get([
                "id as user_id",
                "fcm_token",
            ]);

            $userIds = $users->pluck("user_id")->toArray();
            $fcmTokens = $users->pluck("fcm_token")->toArray();

            try {
                $message = CloudMessage::new()
                    ->withNotification([
                        "title" => $request->title,
                        "body" => $request->message,
                    ])
                    ->withData([
                        "screenType" => "noti",
                    ]);

                $response = $this->notification->sendMulticast(
                    $message,
                    $fcmTokens
                );
            } catch (\Exception $e) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to send the notification.",
                        "error" => $e->getMessage(),
                        "user_ids" => $userIds,
                        "fcmToken" => $fcmTokens,
                    ],
                    500
                );
            }

            $notification = Notification::create([
                "title" => $request->title,
                "message" => $request->message,
                "is_active" => $request->is_active ?? 1,
                "redirect_url" => $request->redirect_url ?? "",
                "user_ids" => $userIds,
            ]);

            // Return success response
            return response()->json(
                [
                    "success" => true,
                    "message" => "Notification sent successfully",
                    "data" => $notification,
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "user_ids" => $user_ids,
                    "error" => "Failed to add notification",
                    "message" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getPaginated(Request $request)
    {
        try {
            $page = $request->get("page", 1);
            $perPage = $request->get("pageSize", 7);

            // Build the query
            $notification = Notification::orderBy("created_at", "desc")->paginate(
                $perPage,
                ["*"],
                "page",
                $page
            );

            return response()->json([
                "success" => true,
                "message" => "Notification fetched successfully",
                "current_page" => $notification->currentPage(),
                "data" => $notification->items(),
                "total_records" => $notification->total(),
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to fetched the notification.",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
       
    }

    public function getById($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Notification not found",
                ],
                500
            );
        }

        return response()->json(
            [
                "success" => true,
                "message" => "Notification retrieved successfully",
                "data" => $notification,
            ],
            200
        );
    }

    public function update(Request $request)
    {
        // Validation first - this will automatically throw an exception if validation fails
        $request->validate([
            "id" => "required|integer|exists:notifications,id",
            "title" => "required|string|max:255",
            "message" => "required|string|max:5000",
            "is_active" => "nullable|boolean",
            "redirect_url" => "nullable|string",
            "user_ids" => "nullable|json",
        ]);

        try {
            // Decode user_ids
            $user_ids = json_decode($request->input("user_ids"), true);
            // Find the notification record
            $notification = Notification::find($request->id);
            if (!$notification) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Notification not found",
                    ],
                    404
                ); 
            }

            $validUserIds = User::whereIn("id", $user_ids)
                ->where("is_active", 1)
                ->get(["id as user_id", "fcm_token"]);

            if ($validUserIds->isEmpty()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "No valid active users found.",
                    ],
                    400
                ); 
            }

            // Extract user ids and fcm tokens
            $userIds = $validUserIds->pluck("user_id")->toArray();
            $fcmTokens = $validUserIds->pluck("fcm_token")->toArray();

            // Send notification
            try {
                $message = CloudMessage::new()
                    ->withNotification([
                        "title" => $request->title,
                        "body" => $request->message,
                    ])
                    ->withData([
                        "screenType" => "noti",
                    ]);

                $response = $this->notification->sendMulticast(
                    $message,
                    $fcmTokens
                );

               
            } catch (\Exception $e) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Failed to send the notification.",
                        "error" => $e->getMessage(),
                    ],
                    500
                );
            }

            // Update the notification record
            $notification->update([
                "title" => $request->title,
                "message" => $request->message,
                "is_active" => $request->is_active ?? 1,
                "redirect_url" => $request->redirect_url ?? "",
                "user_ids" => $userIds,
            ]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Notification updated successfully",
                    "data" => $notification,
                ],
                200
            );
        } catch (\Exception $e) {
            // General error handler for unexpected issues
          
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to update notification",
                    "error" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function delete($id)
    {
        try {
            $notification = Notification::find($id);

            if (!$notification) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Notification not found",
                    ],
                    500
                );
            }

            $notification->delete();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Notification deleted successfully",
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to delete notification",
                    "message" => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function getAll()
    {
        $notification = Notification::orderBy("created_at", "desc")->get();
        return response()->json(
            [
                "success" => true,
                "message" => "Notification retrieved successfully",
                "data" => $notification,
            ],
            200
        );
    }

    public function userAllNotification(Request $request)
    {
        try {
            $page = $request->get("page", 1); // Current page
            $perPage = $request->get("pageSize", 7); // Items per page
            $userId = $request->get("user_id"); // User ID (passed in the request)
    
            if (!$userId) {
                return response()->json([
                    "success" => false,
                    "message" => "User ID is required.",
                ], 400);
            }
    
            $query = Notification::orderBy("created_at", "desc");
            $query->where('is_active',1);
            // Build the query to filter by user_id
            if ($userId) {
                $query->whereJsonContains('user_ids', (int)$userId);
            }
    
            $notification = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Format the response data
            $notifications = $notification->items();
            
            $formattedNotifications = array_map(function ($notif) use ($userId) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'is_active' => $notif->is_active,
                    'redirect_url' => $notif->redirect_url,
                    'user_id' => in_array((int)$userId, $notif->user_ids) ? (int)$userId : null,
                    'user_ids' => $notif->user_ids,
                    'created_at' => $notif->created_at,
                    'updated_at' => $notif->updated_at,
                ];
            }, $notifications);
            
            return response()->json([
                "success" => true,
                "message" => "Notification fetched successfully",
                "current_page" => $notification->currentPage(),
                "data" => $formattedNotifications,
                "total_records" => $notification->total(),
            ]);
    
        } catch (\Throwable $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to fetch the notifications.",
                    "error" => $e->getMessage(), 
                ],
                500
            );
        }
    }

}
