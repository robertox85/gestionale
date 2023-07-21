<?php

namespace App\Controllers;

use App\Libraries\ErrorHandler;
use App\Libraries\Helper;
use App\Models\Utente;

class AuthController extends BaseController
{
    // Views
    public function signInView(): void
    {
        echo $this->view->render('signin.html.twig');
    }

    public function forgotPasswordView(): void
    {
        echo $this->view->render('forgot-password.html.twig');
    }

    // Actions
    public function signInUser(): void
    {
        try {
            $csrf_token = $_POST['csrf_token'] ?? '';
            $login = trim($_POST['username-or-email']) ?? '';
            $password = trim($_POST['password']) ?? '';
            $remember_me = isset($_POST['remember']);

            if (!Helper::validateToken('signin', $csrf_token)) {
                Helper::addError('Invalid token');
                Helper::redirect('sign-in');
            }

            //$user = Utente::getUserByUsernameOrEmail($email);

            $user = Utente::getByPropertyName('email', $login);

            if (!$user) {
                $user = Utente::getByPropertyName('username', $login);

                if (!$user) {
                    Helper::addError('Login failed');
                    Helper::redirect('sign-in');
                }
            }


            if (!password_verify($password, $user->getPassword())) {
                Helper::addError('Login failed');
                Helper::redirect('sign-in');
            }

            if ($remember_me) {
                $user->rememberMe();
            }

            $_SESSION['utente'] = array_merge($user->toArray(),$user->getAnagrafica()->toArray());
            $_SESSION['utente']['id'] = $user->getId();

            if (isset($_POST['returnUrl']) && !empty($_POST['returnUrl'])) {
                if (parse_url($_POST['returnUrl'], PHP_URL_PATH) === '/') {
                    Helper::redirect('/');
                }
                Helper::redirect($_POST['returnUrl']);
            }

            Helper::redirect('/');
        } catch (\Exception $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handleException($e);
        }
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

    public function forgotPassword(): void
    {
        try {
            $email = $_POST['email'] ?? '';

            $user = Utente::getByPropertyName('email', $email);

            if (!$user) {
                Helper::addError('Email not found');
                Helper::redirect('forgot-password');
            }

            $user->generateNewPasswordAndSendEmail();
            Helper::addSuccess('Please check your email for password reset instructions');
            Helper::redirect('sign-in');
        } catch (\Exception $e) {
            ErrorHandler::getInstance()->handleException($e);
        }
    }


    public function signUpView(): void
    {
        // if user is logged in, redirect to home
        if (isset($_SESSION['utente'])) {
            Helper::redirect('/');
        }

        echo $this->view->render('signup.html.twig');
    }

    public function signUpUser(): void
    {
        try {
            $_csrf_token = $_POST['_csrf_token'] ?? '';


            $data = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'password_confirm' => $_POST['password_confirm'] ?? '',
            ];

            if (!Helper::validateToken('sign-up', $_csrf_token)) {
                Helper::addError('Invalid CSRF token');
                header('Location: /sign-up');
                exit();
            }

            $validationErrors = $this->userRepository->validateSignUpData($data);

            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    Helper::addError($error);
                }

                Helper::redirect();
            }

            $user = $this->userRepository->registerUser($data);

            if (!$user) {
                Helper::addError('Something went wrong');
                Helper::redirect();
            }

            Helper::addSuccess('You have successfully registered');
            Helper::addWarning('Please check your email to activate your account');
            Helper::redirect('sign-in');
        } catch (\Exception $e) {
            ErrorHandler::getInstance()->handleException($e);
        }
    }

    // createUser
    public function createUser(): void
    {
        try {
            $_csrf_token = $_POST['_csrf_token'] ?? '';

            $data = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'password_confirm' => $_POST['password_confirm'] ?? '',
                'role' => $_POST['role'] ?? '',
                'status' => $_POST['status'] ?? '',
            ];

            if (!Helper::validateToken('create-user', $_csrf_token)) {
                Helper::addError('Invalid CSRF token');
                Helper::redirect();
            }

            $validationErrors = $this->userRepository->validateSignUpData($data);

            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    Helper::addError($error);
                }

                Helper::redirect();
            }

            $user = $this->userRepository->registerUser($data);

            if (!$user) {
                Helper::addError('Something went wrong');
                Helper::redirect();
            }

            Helper::addSuccess('You have successfully created a new user');
            Helper::redirect();
        } catch (\Exception $e) {
            ErrorHandler::getInstance()->handleException($e);
        }
    }

}