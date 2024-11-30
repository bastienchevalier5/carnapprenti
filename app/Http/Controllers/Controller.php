<?php

namespace App\Http\Controllers;

use Hash;
use Request;

abstract class Controller
{
    public function verifyPassword(Request $request)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $hash = $_POST['hash'];

            if (password_verify($password, $hash)) {
                echo "Password is valid!";
            } else {
                echo "Invalid password.";
            }
        }

    }

}
