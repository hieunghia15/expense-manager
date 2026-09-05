<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthGuard;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Session;
use App\Core\View;
use App\Requests\LoginRequest;
use App\Services\AuthService;

final class AuthController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $authService
    ) {
        parent::__construct($view);
    }

    public function showLoginForm(): void
    {
        if (AuthGuard::check()) {
            $this->redirect('/categories');
        }

        $this->render(
            'auth/login.twig',
            [
                'title' => 'Login',
                'csrf_token' => Csrf::token(),
                'flash' => Flash::pull(),
                'session' => $_SESSION ?? [],
            ]
        );
    }

    public function login(): void
    {
        Csrf::validateOrFail($_POST['_csrf_token'] ?? null);

        $request = LoginRequest::createFromGlobals();
        $validator = $request->validate();

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors)[0] ?? 'Validation failed.';
            Flash::error($firstError);
            $this->redirect('/');
        }

        $user = $this->authService->verifyCredentials(
            $request->getEmail(),
            $request->getPassword()
        );

        if ($user === false) {
            Flash::error('Invalid email or password.');
            $this->redirect('/');
        }

        Session::regenerate();
        Session::put('user_id', $user['id']);
        Session::put('user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
        ]);

        $this->redirect('/categories');
    }

    public function logout(): void
    {
        Session::start();
        Session::forget('user_id');
        Session::forget('user');
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->redirect('/');
    }
}
