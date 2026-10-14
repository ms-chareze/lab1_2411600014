<?php

namespace App\Policies;

use App\Models\EnrollmentTransaction;
use App\Models\User;

/**
 * Auto-discovered by Laravel (App\Models\X -> App\Policies\XPolicy).
 * "Admin" = users.is_admin = true.
 */
class EnrollmentTransactionPolicy
{
    /** Only admins may delete (and thereby reverse) a transaction. */
    public function delete(User $user, EnrollmentTransaction $transaction): bool
    {
        return (bool) $user->is_admin;
    }

    /** Only admins may post stock (unit) adjustments. */
    public function adjust(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
