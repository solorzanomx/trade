<?php

namespace App\Policies;

use App\Models\ReportTemplate;
use App\Models\User;

class ReportTemplatePolicy
{
    public function update(User $user, ReportTemplate $template): bool
    {
        return $user->id === $template->user_id;
    }
}
