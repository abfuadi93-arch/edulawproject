<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    private const ROLES = [
        'super_admin',
        'editor',
        'writer',
        'program_admin',
        'media_opportunity_admin',
    ];

    private const PERMISSIONS = [
        'view users',
        'create users',
        'update users',
        'delete users',
        'view roles',
        'create roles',
        'update roles',
        'delete roles',
        'view insights',
        'create insights',
        'update own insights',
        'update all insights',
        'delete own insights',
        'delete all insights',
        'submit insights',
        'review insights',
        'publish insights',
        'archive insights',
        'view_all_editorial_insights',
        'view_assigned_editorial_insights',
        'view_editorial_dashboard',
        'view_all_editorial_submissions',
        'view_assigned_editorial_submissions',
        'view_own_editorial_submissions',
        'assign_editor',
        'reassign_editor',
        'accept_editor_assignment',
        'start_editorial_review',
        'complete_editor_assignment',
        'cancel_editor_assignment',
        'access_editorial_workspace',
        'view_editorial_decisions',
        'create_editorial_decision',
        'view_editorial_audit_history',
        'publish_approved_insight',
        'start_review',
        'add_editorial_note',
        'request_revision',
        'resubmit_revision',
        'approve_insight',
        'reject_insight',
        'publish_insight',
        'archive_insight',
        'view_status_history',
        'create_section_comment',
        'reply_editorial_comment',
        'mark_comment_addressed',
        'resolve_editorial_comment',
        'reopen_editorial_comment',
        'view_revision_history',
        'compare_insight_revisions',
        'set_editor_deadline',
        'set_writer_deadline',
        'extend_editor_deadline',
        'extend_writer_deadline',
        'view_editorial_notifications',
        'override_unresolved_comments',
        'view publications',
        'create publications',
        'update publications',
        'delete publications',
        'publish publications',
        'archive publications',
        'view authors',
        'create authors',
        'update authors',
        'delete authors',
        'view tags',
        'create tags',
        'update tags',
        'delete tags',
        'view programs',
        'create programs',
        'update programs',
        'delete programs',
        'publish programs',
        'archive programs',
        'view program categories',
        'create program categories',
        'update program categories',
        'delete program categories',
        'view multimedia',
        'create multimedia',
        'update multimedia',
        'delete multimedia',
        'publish multimedia',
        'archive multimedia',
        'view opportunities',
        'create opportunities',
        'update opportunities',
        'delete opportunities',
        'publish opportunities',
        'close opportunities',
        'archive opportunities',
        'view collaboration submissions',
        'update collaboration submissions',
        'view contact messages',
        'update contact messages',
    ];

    private const ROLE_PERMISSIONS = [
        'editor' => [
            'view insights',
            'create insights',
            'update all insights',
            'delete all insights',
            'review insights',
            'publish insights',
            'archive insights',
            'view_assigned_editorial_insights',
            'view_editorial_dashboard',
            'view_assigned_editorial_submissions',
            'accept_editor_assignment',
            'start_editorial_review',
            'complete_editor_assignment',
            'access_editorial_workspace',
            'view_editorial_decisions',
            'create_editorial_decision',
            'view_editorial_audit_history',
            'start_review',
            'add_editorial_note',
            'request_revision',
            'approve_insight',
            'reject_insight',
            'publish_insight',
            'archive_insight',
            'view_status_history',
            'create_section_comment',
            'resolve_editorial_comment',
            'reopen_editorial_comment',
            'view_revision_history',
            'compare_insight_revisions',
            'set_writer_deadline',
            'extend_writer_deadline',
            'view_editorial_notifications',
            'view publications',
            'create publications',
            'update publications',
            'delete publications',
            'publish publications',
            'archive publications',
            'view authors',
            'create authors',
            'update authors',
            'delete authors',
            'view tags',
            'create tags',
            'update tags',
            'delete tags',
        ],
        'writer' => [
            'view insights',
            'create insights',
            'update own insights',
            'delete own insights',
            'submit insights',
            'view_own_editorial_submissions',
            'access_editorial_workspace',
            'view_editorial_decisions',
            'view_editorial_audit_history',
            'resubmit_revision',
            'view_status_history',
            'reply_editorial_comment',
            'mark_comment_addressed',
            'view_revision_history',
            'view_editorial_notifications',
            'view authors',
        ],
        'program_admin' => [
            'view programs',
            'create programs',
            'update programs',
            'delete programs',
            'publish programs',
            'archive programs',
            'view program categories',
            'create program categories',
            'update program categories',
            'delete program categories',
        ],
        'media_opportunity_admin' => [
            'view multimedia',
            'create multimedia',
            'update multimedia',
            'delete multimedia',
            'publish multimedia',
            'archive multimedia',
            'view opportunities',
            'create opportunities',
            'update opportunities',
            'delete opportunities',
            'publish opportunities',
            'close opportunities',
            'archive opportunities',
        ],
    ];

    private const LEGACY_ROLE_MAP = [
        'Super Admin' => 'super_admin',
        'SuperAdmin' => 'super_admin',
        'Editor' => 'editor',
        'Writer' => 'writer',
        'Program Admin' => 'program_admin',
        'Media Opportunity Admin' => 'media_opportunity_admin',
        'Media & Opportunity Admin' => 'media_opportunity_admin',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = $this->syncPermissions();
        $roles = $this->syncRoles($permissions);

        $this->assignLegacyUsersToNewRoles($roles);
        $this->assignPrimaryAdmins($roles['super_admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return Collection<string, Permission>
     */
    private function syncPermissions(): Collection
    {
        return collect(self::PERMISSIONS)
            ->mapWithKeys(fn (string $permission): array => [
                $permission => Permission::updateOrCreate([
                    'name' => $permission,
                    'guard_name' => self::GUARD,
                ]),
            ]);
    }

    /**
     * @param  Collection<string, Permission>  $permissions
     * @return Collection<string, Role>
     */
    private function syncRoles(Collection $permissions): Collection
    {
        $roles = collect(self::ROLES)
            ->mapWithKeys(fn (string $role): array => [
                $role => Role::updateOrCreate([
                    'name' => $role,
                    'guard_name' => self::GUARD,
                ]),
            ]);

        $roles['super_admin']->givePermissionTo($permissions->values());

        foreach (self::ROLE_PERMISSIONS as $role => $rolePermissions) {
            $roles[$role]->givePermissionTo($permissions->only($rolePermissions)->values());
        }

        return $roles;
    }

    /**
     * @param  Collection<string, Role>  $roles
     */
    private function assignLegacyUsersToNewRoles(Collection $roles): void
    {
        foreach (self::LEGACY_ROLE_MAP as $legacyRoleName => $newRoleName) {
            $legacyRole = Role::query()
                ->where('name', $legacyRoleName)
                ->where('guard_name', self::GUARD)
                ->first();

            if (! $legacyRole || ! isset($roles[$newRoleName])) {
                continue;
            }

            User::query()
                ->whereHas('roles', fn ($query) => $query->whereKey($legacyRole->getKey()))
                ->each(fn (User $user) => $user->assignRole($roles[$newRoleName]));
        }
    }

    private function assignPrimaryAdmins(Role $superAdminRole): void
    {
        $adminEmails = collect([
            config('edulaw.admin_email'),
            config('mail.from.address'),
            'admin@edulaw.test',
            'projectedulaw@gmail.com',
        ])
            ->filter()
            ->map(fn (string $email): string => mb_strtolower(trim($email)))
            ->unique()
            ->values();

        $adminUsers = User::query()
            ->whereIn('email', $adminEmails)
            ->get();

        $firstUser = User::query()->oldest('id')->first();

        collect([$firstUser])
            ->merge($adminUsers)
            ->filter()
            ->unique('id')
            ->each(fn (User $user) => $user->assignRole($superAdminRole));
    }
}
