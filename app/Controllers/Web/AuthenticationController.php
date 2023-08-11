<?php

namespace App\Controllers\Web;

use App\Libraries\Helper;
use App\Models\RememberMe;
use App\Models\Utenti;

class AuthenticationController extends BaseController
{
    // Views
    public function signInView(): void
    {
        echo $this->view->render('sign-in.html.twig');
    }

    public function signUpView(): void
    {
        // if user is logged in, redirect to home
        if (isset($_SESSION['utente'])) {
            Helper::addSuccess('You are already logged in');
            Helper::redirect('/');
        }

        echo $this->view->render('sign-up.html.twig');
    }

    // Actions
    public function signInUser(): void
    {

        $data = [
            'email' => trim($_POST['email']) ?? '',
            'password' => trim($_POST['password']) ?? '',
            'remember_me' => isset($_POST['remember']),
        ];



        if (!Helper::validateToken('authenticate', $_POST['csrf_token'])) {
            Helper::addError('Invalid token');
            Helper::redirect('sign-in');
            exit;
        }

        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        $user = (new Utenti())->getByField('email', $data['email']);

        if (!$user) {
            Helper::addError('Login failed');
            Helper::redirect('sign-in');
        }

        if (!password_verify($data['password'], $user->getPassword())) {
            Helper::addError('Login failed');
            Helper::redirect('sign-in');
        }

        $this->initUserSession($user, $data['remember_me']);

        Helper::addSuccess('Login successful');
        Helper::redirect('/');

    }

    public function signUpUser(): void
    {
        if (!Helper::validateToken('sign-up', $_POST['csrf_token'])) {
            Helper::addError('Invalid CSRF token');
            header('Location: /sign-up');
            exit();
        }

        $data = [
            'email' => trim($_POST['email']) ?? '',
            'password' => trim($_POST['password']) ?? '',
            'password_confirm' => trim($_POST['password_confirm']) ?? '',
            'ruolo' => 'dipendente',
        ];

        $validationErrors = $this->validateSignUpData($data);

        if (!empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                Helper::addError($error);
            }
            Helper::redirect();
        }


        $utente = new Utenti();

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // prevent SQL injection
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        $user_id = $utente->create($data);

        if (!$user_id) {
            Helper::addError('Something went wrong');
            Helper::redirect();
        }

        $user = $utente::get($user_id);

        $this->initUserSession($user, false);

        Helper::addSuccess('Account created successfully');
        Helper::redirect('utenti/edit/' . $user->getId());
        exit;
    }

    public function signOutUser(): void
    {
        session_destroy();

        // delete remember me cookie, if exists
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        Helper::redirect('/');
    }

    private function initUserSession($user, bool $remember_me): void
    {
        $_SESSION['utente'] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nome' => $user->getNome(),
            'cognome' => $user->getCognome(),
            'ruolo' => $user->getRuolo(),
        ];

        if ($remember_me) {
            $this->rememberMe();
        }

    }

    private function validateSignUpData(array $data): array
    {
        $validationErrors = [];

        if (empty($data['email'])) {
            $validationErrors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = 'Invalid email';
        } elseif ((new Utenti)->getByField('email', $data['email'])) {
            $validationErrors[] = 'Email already exists';
        }

        if (empty($data['password'])) {
            $validationErrors[] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $validationErrors[] = 'Password must be at least 6 characters long';
        }

        if (empty($data['password_confirm'])) {
            $validationErrors[] = 'Password confirmation is required';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $validationErrors[] = 'Passwords do not match';
        }

        return $validationErrors;
    }

    private function rememberMe(): void
    {
        $token = Helper::generateToken('remember_me');

        $cookie_expiry = time() + (86400 * 30); // 30 days

        setcookie('remember_me', $token, $cookie_expiry, '/');

        $data = [
            'id_utente' => $_SESSION['utente']['id'],
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', $cookie_expiry),
        ];

        $this->saveRememberMeData($data);
    }

    private function saveRememberMeData(array $data): void
    {
        $remember_me = new RememberMe();
        $remember_me->create($data);
    }
}