<?php

namespace App\Controllers;

use App\Libraries\Helper;
use App\Models\Utente;

class AuthController extends BaseController
{
    public function signInView(): void
    {
        // if user is already logged in, redirect to home
        if (isset($_SESSION['utente'])) {
            Helper::addWarning('You are already logged in');
        }
        echo $this->view->render('signin.html.twig');
    }

    public function signInUser(): void
    {
        try {
            $csrf_token = $_POST['csrf_token'] ?? '';
            $email = $_POST['username-or-email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember_me = isset($_POST['remember']);

            if (!Helper::validateToken('signin', $csrf_token)) {
                Helper::addError('Invalid token');
                Helper::redirect('sign-in');
            }

            //$user = Utente::getUserByUsernameOrEmail($email);

            $user = Utente::getByPropertyName('email', $email);

            if (!$user) {
                Helper::addError('Login failed');
                Helper::redirect('sign-in');
            }

            /*
            if (!password_verify($password, $user->getPassword())) {
                Helper::addError('Login failed');
                Helper::redirect('sign-in');
            }*/

            if ($remember_me) {
                $user->rememberMe();
            }

            $_SESSION['utente'] = array_merge($user->toArray(),$user->getAnagrafica()->toArray());
            $_SESSION['utente']['id'] = $user->getId();

            // if url contains returnUrl, redirect to that url
            if (isset($_POST['returnUrl']) && !empty($_POST['returnUrl'])) {
                // if return url is empty after removing host name, redirect to home
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