<?php
declare(strict_types=1);

/**
 * Maps a role to its dashboard entry point, relative to the project root.
 */
function dashboardRelativePath(string $role): string
{
    return match ($role) {
        'Admin'   => 'admin/dashboard.php',
        'Manager' => 'manager/dashboard.php',
        default   => 'employee/dashboard.php',
    };
}

/**
 * Guards a page for the given roles. Unauthenticated users are sent to
 * login; authenticated users without access are sent to their own dashboard.
 * Must be called before any HTML output.
 */
function requireRole(array $allowedRoles): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        header('Location: ../' . dashboardRelativePath($_SESSION['role']));
        exit();
    }
}

/**
 * Stores a one-time flash message in the session.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieves and clears the pending flash message, if any.
 */
function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Derives a certification's badge class and label from its expiration date.
 */
function certStatus(string $expirationDate): array
{
    $daysLeft = (new DateTime())->diff(new DateTime($expirationDate))->days
        * (new DateTime($expirationDate) < new DateTime() ? -1 : 1);

    if ($daysLeft < 0) {
        return ['class' => 'expired', 'label' => 'Expired'];
    }

    if ($daysLeft <= 30) {
        return ['class' => 'nearing', 'label' => 'Expiring Soon'];
    }

    return ['class' => 'valid', 'label' => 'Valid'];
}
