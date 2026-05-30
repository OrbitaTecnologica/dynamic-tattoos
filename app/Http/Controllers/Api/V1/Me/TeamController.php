<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InviteTeamMemberRequest;
use App\Http\Requests\Api\V1\UpdateTeamMemberRequest;
use App\Http\Resources\Api\V1\TeamMemberResource;
use App\Mail\TeamInvitationMail;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $this->ensureOwnerRow($user);

        $members = TeamMember::query()
            ->where('owner_id', $user->id)
            ->orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->get();

        return TeamMemberResource::collection($members);
    }

    public function store(InviteTeamMemberRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $member = TeamMember::query()->create([
            'owner_id' => $user->id,
            'name' => $request->input('name'),
            'email' => (string) $request->input('email'),
            'role' => (string) $request->input('role'),
            'status' => 'pending',
            'invitation_token' => Str::random(64),
            'invited_at' => now(),
        ]);

        Mail::to($member->email)->send(new TeamInvitationMail($member));

        activity('account')
            ->causedBy($user)
            ->event('team_invited')
            ->withProperties(['detail' => $member->email.' · '.$member->role])
            ->log('Invitación de equipo enviada');

        return response()->json([
            'data' => new TeamMemberResource($member),
        ], 201);
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $member = TeamMember::query()
            ->where('invitation_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $member->forceFill([
            'member_user_id' => $user->id,
            'status' => 'active',
            'invitation_token' => null,
            'last_active_at' => now(),
        ])->save();

        return response()->json([
            'data' => new TeamMemberResource($member),
        ]);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $member): JsonResponse
    {
        $this->authorize('update', $member);

        $member->update(['role' => (string) $request->input('role')]);

        return response()->json([
            'data' => new TeamMemberResource($member),
        ]);
    }

    public function destroy(Request $request, TeamMember $member): JsonResponse
    {
        $this->authorize('delete', $member);

        $member->delete();

        return response()->json([], 204);
    }

    private function ensureOwnerRow(User $user): void
    {
        TeamMember::query()->firstOrCreate(
            ['owner_id' => $user->id, 'email' => (string) $user->email],
            [
                'member_user_id' => $user->id,
                'name' => $user->name,
                'role' => 'owner',
                'status' => 'active',
                'last_active_at' => now(),
            ],
        );
    }
}
