<?php

namespace App\Providers;

use App\Filament\Support\ViteAlpineComponent;
use App\Models\Author;
use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Tag;
use App\Models\User;
use App\Policies\AuthorPolicy;
use App\Policies\CollaborationSubmissionPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\InsightCategoryPolicy;
use App\Policies\InsightPolicy;
use App\Policies\MultimediaPolicy;
use App\Policies\OpportunityPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProgramCategoryPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\PublicationPolicy;
use App\Policies\PublicationTypePolicy;
use App\Policies\RolePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use App\Support\EdulawSite;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\ViteException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        try {
            $tinyMceAsset = Vite::asset('resources/js/filament/tinymce-editor.js');
        } catch (ViteException) {
            $tinyMceAsset = null;
        }

        if ($tinyMceAsset) {
            FilamentAsset::register([
                ViteAlpineComponent::make('edulaw-tinymce-editor', $tinyMceAsset),
            ]);
        }

        Gate::before(function (User $user): ?bool {
            return $user->hasRole('super_admin') ? true : null;
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Insight::class, InsightPolicy::class);
        Gate::policy(Publication::class, PublicationPolicy::class);
        Gate::policy(Author::class, AuthorPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(ProgramCategory::class, ProgramCategoryPolicy::class);
        Gate::policy(Multimedia::class, MultimediaPolicy::class);
        Gate::policy(Opportunity::class, OpportunityPolicy::class);
        Gate::policy(CollaborationSubmission::class, CollaborationSubmissionPolicy::class);
        Gate::policy(ContactMessage::class, ContactMessagePolicy::class);
        Gate::policy(InsightCategory::class, InsightCategoryPolicy::class);
        Gate::policy(PublicationType::class, PublicationTypePolicy::class);

        View::composer('*', function ($view): void {
            static $siteSettings = null;

            $siteSettings ??= EdulawSite::settings();

            $view->with('siteSettings', $siteSettings);
        });
    }
}
