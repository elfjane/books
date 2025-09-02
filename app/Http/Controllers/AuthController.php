<?php
/*
    2025/8/21 elfjane test
*/
namespace App\Http\Controllers;
use Lib\Code\JsonCode;

abstract class AuthController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }    
}