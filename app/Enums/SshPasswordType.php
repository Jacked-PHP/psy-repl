<?php

namespace App\Enums;

enum SshPasswordType: string
{
    case PASSWORD = 'password';
    case PRIVATE_KEY = 'private';
}
