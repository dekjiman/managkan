<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

/*
 * Segment mapping for CI4:
 * api/auth/*          → segment 2 = auth
 * api/workspaces/*    → segment 2 = workspaces
 * api/boards/*        → segment 2 = boards
 * etc.
 *
 * For nested routes, we use closures to handle parameter extraction
 * since CI4 auto-routing doesn't support nested param capture well.
 */

// ─── Health ───
$routes->get('api/health', '\Sites\Endpoint\Controllers\HealthController::index');

// API Documentation
$routes->get('docs', '\Sites\Endpoint\Controllers\ApiDocsController::index');

// ─── Auth (no JWT) ───
$routes->group('api/auth', ['namespace' => 'Sites\Endpoint\Controllers\Auth'], function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('sign-in/email', 'AuthController::login');
    $routes->post('sign-in/social', 'AuthController::signInSocial');
    $routes->post('register', 'AuthController::register');
    $routes->post('sign-up/email', 'AuthController::register');
    $routes->post('verify-email', 'AuthController::verifyEmail');
    $routes->post('send-verification-email', 'AuthController::sendVerificationEmail');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->post('request-password-reset', 'AuthController::forgotPassword');
    $routes->post('reset-password', 'AuthController::resetPassword');
    $routes->post('refresh', 'AuthController::refresh');
    $routes->get('google', 'OAuthController::google');
    $routes->get('github', 'OAuthController::github');
    $routes->get('callback/(:any)', 'OAuthController::callback/$1');
});

// ─── Auth (JWT required) ───
$routes->group('api/auth', ['namespace' => 'Sites\Endpoint\Controllers\Auth', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('me', 'AuthController::me');
    $routes->get('get-session', 'AuthController::getSession');
    $routes->post('logout', 'AuthController::logout');
    $routes->post('sign-out', 'AuthController::logout');
});

// ─── User Profile (JWT required) ───
$routes->group('api/users', ['namespace' => 'Sites\Endpoint\Controllers\User', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('me', 'UserController::me');
    $routes->patch('me', 'UserController::updateMe');
    $routes->delete('me', 'UserController::deleteMe');
});

// ─── Plans (public) ───
$routes->group('api/plans', ['namespace' => 'Sites\Endpoint\Controllers\Plan'], function ($routes) {
    $routes->get('/', 'PlanController::index');
    $routes->get('(:any)', 'PlanController::show/$1');
});

// ─── Workspaces (JWT required) ───
$routes->group('api/workspaces', ['namespace' => 'Sites\Endpoint\Controllers\Workspace', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'WorkspaceController::index');
    $routes->get('check-slug-availability', 'WorkspaceController::checkSlugAvailability');
    $routes->post('/', 'WorkspaceController::store');

    // Boards nested under workspace (BEFORE catch-all)
    $routes->get('(:any)/boards', '\Sites\Endpoint\Controllers\Board\BoardController::index/$1');
    $routes->get('(:any)/boards/check-slug-availability', '\Sites\Endpoint\Controllers\Board\BoardController::checkSlugAvailability/$1');
    $routes->post('(:any)/boards', '\Sites\Endpoint\Controllers\Board\BoardController::store/$1');

    // Workspace Members (BEFORE catch-all)
    $routes->get('(:any)/members', 'MemberController::index/$1');
    $routes->post('(:any)/members', 'MemberController::store/$1');
    $routes->patch('(:any)/members/(:any)', 'MemberController::updateRole/$1/$2');
    $routes->delete('(:any)/members/(:any)', 'MemberController::destroy/$1/$2');
    $routes->post('(:any)/members/(:any)/resend-invite', 'MemberController::resendInvite/$1/$2');

    // API Keys (BEFORE catch-all)
    $routes->get('(:any)/api-keys', 'ApiKeyController::index/$1');
    $routes->post('(:any)/api-keys', 'ApiKeyController::store/$1');
    $routes->delete('(:any)/api-keys/(:any)', 'ApiKeyController::destroy/$1/$2');

    // Webhooks (BEFORE catch-all)
    $routes->get('(:any)/webhooks', 'WebhookController::index/$1');
    $routes->post('(:any)/webhooks', 'WebhookController::store/$1');
    $routes->delete('(:any)/webhooks/(:any)', 'WebhookController::destroy/$1/$2');
    $routes->post('(:any)/webhooks/(:any)/test', 'WebhookController::test/$1/$2');

    // Integrations (BEFORE catch-all)
    $routes->get('(:any)/integrations', 'IntegrationController::index/$1');
    $routes->put('(:any)/integrations', 'IntegrationController::toggle/$1');

    // Billing (BEFORE catch-all)
    $routes->get('(:any)/billing', 'BillingController::index/$1');
    $routes->post('(:any)/billing/checkout', 'BillingController::checkout/$1');

    // Dashboard (BEFORE catch-all)
    $routes->get('(:any)/dashboard', '\Sites\Endpoint\Controllers\Dashboard\DashboardController::index/$1');

    // Catch-all workspace routes (must be last)
    $routes->get('(:any)', 'WorkspaceController::show/$1');
    $routes->patch('(:any)', 'WorkspaceController::update/$1');
    $routes->delete('(:any)', 'WorkspaceController::destroy/$1');

    // Invite routes (outside catch-all, uses special segments)
    $routes->get('invite/(:any)', 'MemberController::inviteInfo/$1');
    $routes->post('invite-signup', 'MemberController::acceptInvite');
});

// ─── Billing (standalone, uses query param workspaceSlug) ───
$routes->get('api/billing', '\Sites\Endpoint\Controllers\Workspace\BillingController::index', ['filter' => 'jwtAuth']);
$routes->post('api/billing/checkout', '\Sites\Endpoint\Controllers\Workspace\BillingController::checkout', ['filter' => 'jwtAuth']);

// ─── Dashboard (global) ───
$routes->get('api/dashboard', '\Sites\Endpoint\Controllers\Dashboard\DashboardController::index', ['filter' => 'jwtAuth']);

// ─── Members standalone ───
$routes->get('api/members/invite/(:any)', '\Sites\Endpoint\Controllers\Workspace\MemberController::inviteInfo/$1');
$routes->post('api/members/invite-signup', '\Sites\Endpoint\Controllers\Workspace\MemberController::acceptInvite');
$routes->get('api/members', '\Sites\Endpoint\Controllers\Workspace\MemberController::index', ['filter' => 'jwtAuth']);
$routes->post('api/members', '\Sites\Endpoint\Controllers\Workspace\MemberController::store', ['filter' => 'jwtAuth']);
$routes->patch('api/members/(:any)', '\Sites\Endpoint\Controllers\Workspace\MemberController::updateRole/$1', ['filter' => 'jwtAuth']);
$routes->delete('api/members/(:any)', '\Sites\Endpoint\Controllers\Workspace\MemberController::destroy/$1', ['filter' => 'jwtAuth']);
$routes->post('api/members/(:any)/resend-invite', '\Sites\Endpoint\Controllers\Workspace\MemberController::resendInvite/$1', ['filter' => 'jwtAuth']);

// ─── API Keys standalone (JWT, uses query param workspaceSlug) ───
$routes->get('api/api-keys', '\Sites\Endpoint\Controllers\Workspace\ApiKeyController::index', ['filter' => 'jwtAuth']);
$routes->post('api/api-keys', '\Sites\Endpoint\Controllers\Workspace\ApiKeyController::store', ['filter' => 'jwtAuth']);
$routes->delete('api/api-keys/(:any)', '\Sites\Endpoint\Controllers\Workspace\ApiKeyController::destroy', ['filter' => 'jwtAuth']);

// ─── Webhooks standalone (JWT, uses query param workspaceSlug) ───
$routes->get('api/webhooks', '\Sites\Endpoint\Controllers\Workspace\WebhookController::index', ['filter' => 'jwtAuth']);
$routes->post('api/webhooks', '\Sites\Endpoint\Controllers\Workspace\WebhookController::store', ['filter' => 'jwtAuth']);
$routes->delete('api/webhooks/(:any)', '\Sites\Endpoint\Controllers\Workspace\WebhookController::destroy/$1', ['filter' => 'jwtAuth']);
$routes->post('api/webhooks/(:any)/test', '\Sites\Endpoint\Controllers\Workspace\WebhookController::test/$1', ['filter' => 'jwtAuth']);

// ─── Integrations standalone (JWT, uses query param workspaceSlug) ───
$routes->get('api/integrations', '\Sites\Endpoint\Controllers\Workspace\IntegrationController::index', ['filter' => 'jwtAuth']);
$routes->put('api/integrations', '\Sites\Endpoint\Controllers\Workspace\IntegrationController::toggle', ['filter' => 'jwtAuth']);

// ─── Board detail routes (JWT required) ───
$routes->group('api/boards', ['namespace' => 'Sites\Endpoint\Controllers\Board', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('(:any)', 'BoardController::show/$1');
    $routes->patch('(:any)', 'BoardController::update/$1');
    $routes->delete('(:any)', 'BoardController::destroy/$1');
});

// ─── Labels (JWT required) ───
$routes->group('api/labels', ['namespace' => 'Sites\Endpoint\Controllers\Board', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'LabelController::index');
    $routes->post('/', 'LabelController::store');
    $routes->patch('(:any)', 'LabelController::update/$1');
    $routes->delete('(:any)', 'LabelController::destroy/$1');
});

// ─── Lists (JWT required) ───
$routes->group('api/lists', ['namespace' => 'Sites\Endpoint\Controllers\Card', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'ListController::index');
    $routes->post('/', 'ListController::store');
    $routes->put('reorder', 'ListController::reorder');
    $routes->patch('(:any)', 'ListController::update/$1');
    $routes->delete('(:any)', 'ListController::destroy/$1');
});

// ─── Cards (JWT required) ───
$routes->group('api/cards', ['namespace' => 'Sites\Endpoint\Controllers\Card', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'CardController::index');
    $routes->post('/', 'CardController::store');
    $routes->get('(:any)', 'CardController::show/$1');
    $routes->patch('(:any)', 'CardController::update/$1');
    $routes->put('(:any)/move', 'CardController::move/$1');
    $routes->post('(:any)/duplicate', 'CardController::duplicate/$1');
    $routes->delete('(:any)', 'CardController::destroy/$1');
    $routes->put('(:any)/labels/(:any)', 'CardController::toggleLabel/$1/$2');
    $routes->put('(:any)/members/(:any)', 'CardController::toggleMember/$1/$2');
});

// ─── Checklists (JWT required) ───
$routes->group('api/checklists', ['namespace' => 'Sites\Endpoint\Controllers\Card', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'ChecklistController::index');
    $routes->post('/', 'ChecklistController::store');
    $routes->patch('(:any)', 'ChecklistController::update/$1');
    $routes->delete('(:any)', 'ChecklistController::destroy/$1');
    $routes->post('(:any)/items', 'ChecklistController::addItem/$1');
    $routes->patch('items/(:any)', 'ChecklistController::updateItem/$1');
    $routes->delete('items/(:any)', 'ChecklistController::deleteItem/$1');
});

// ─── Comments (JWT required) ───
$routes->group('api/comments', ['namespace' => 'Sites\Endpoint\Controllers\Card', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'CommentController::index');
    $routes->post('/', 'CommentController::store');
    $routes->patch('(:any)', 'CommentController::update/$1');
    $routes->delete('(:any)', 'CommentController::destroy/$1');
});

// ─── Activities (JWT required) ───
$routes->group('api/activities', ['namespace' => 'Sites\Endpoint\Controllers\Card', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'ActivityController::index');
});

// ─── Attachments (JWT required, except download) ───
$routes->get('api/attachments/download/(:any)', '\Sites\Endpoint\Controllers\Card\AttachmentController::download/$1');
$routes->group('api/attachments', ['namespace' => 'Sites\Endpoint\Controllers\Card', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->post('(:any)', 'AttachmentController::upload/$1');
    $routes->get('(:any)', 'AttachmentController::index/$1');
    $routes->delete('(:any)', 'AttachmentController::destroy/$1');
});

// ─── Notifications (JWT required) ───
$routes->group('api/notifications', ['namespace' => 'Sites\Endpoint\Controllers\Notification', 'filter' => 'jwtAuth'], function ($routes) {
    $routes->get('/', 'NotificationController::index');
    $routes->get('unread-count', 'NotificationController::unreadCount');
    $routes->patch('read-all', 'NotificationController::markAllRead');
    $routes->patch('(:any)/read', 'NotificationController::markRead/$1');
});

// ─── Billing Webhook (no auth) ───
$routes->post('api/billing/notification', '\Sites\Endpoint\Controllers\Workspace\BillingController::notification');
