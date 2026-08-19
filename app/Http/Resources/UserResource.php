<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roleMap = [
            'Ketua YMBPK' => 'ketua',
            'Sekretaris' => 'sekretaris',
            'Bendahara' => 'bendahara',
        ];
        $roleSlug = $roleMap[$this->role] ?? 'custom';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: '-',
            'role' => $this->role,
            'roleSlug' => $roleSlug,
            'status' => $this->status,
            'permissions' => $this->permissions ?: [],
            'lastLogin' => $this->last_login_at ? Carbon::parse($this->last_login_at)->diffForHumans() : 'Belum pernah login',
            'lastLoginTs' => $this->last_login_at ? Carbon::parse($this->last_login_at)->timestamp : 0,
        ];
    }
}
