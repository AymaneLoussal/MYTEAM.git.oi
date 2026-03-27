<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;

class InvitationController extends Controller
{
    //
    public function send(Request $request, $teamId)
    {
        $user = auth_user();
        $team = Team::findOrFail($teamId);

        // Only coach can send invitations
        if($team->coach_id !== user->id){
            return response()->json(['message' => 'forbidden'], 403);
        }
        // validate traget user
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        $targetUser = User::find($request->user_id);

        // check if already in team
        if($team->players()->where('user_id', $targetUser->id)->exists()){
            return response()->json(['message' => 'User already in team'], 400);
        }
        // check if already invited
        if(Invitation::where('team_id', $team->id)
            ->where('user_id', $targetUser->id)
            ->where('status', 'pending')
            ->exists()){
            return response()->json(['message' => 'User already invited'], 400);
        }
        Invitation::create([
            'team_id' => $team->id,
            'user_id' => $targetUser->id,
            'status' => 'pending'
        ]);
        return response()->json(['message' => 'Invitation sent successfully']);
    }
    public function index()
    {
        $user = auth_user();

        $invitations = Invitation::where('user_id', $user->id)
        ->where('status', 'pending')
        ->get();

        return response()->json($invitations);
    }

    public function accept($id)
    {
        $user = auth_user();
        $invitations = Invitation::findOrFail($id);

        //not your invitation
        if($invitations->user_id !== $user->id){
            return response()->json(['message' => 'forbidden'], 403);
        }

        //already handled
        if($invitations->status !== "pending"){
            return response()->json(['message' => 'already processed'], 400);
    
    }

    //update invitation status
    $invitations->update(['status' => 'accepted']);
    //add user to team
    $invitations->team->players()->attach($user->id);

    return response()->json(['message' => ' joined team successfully']);
    }

    public function reject($id)
    {
        $user = auth_user();
        
        $invitations = Invitation::findOrFail($id);

        if($invitations->user_id !== $user->id) {
            return response()->json(['message' => 'forbidden'], 403);
        }
         
        if($invitations->status !== "pending"){
            return response()->json(['message' => 'already processed'], 400);
        }

        $invitations->update(['status' => 'rejected']);

        return response()->json(['message' => 'Inviatation rejected successfully']);
    }
}
