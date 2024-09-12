<?php

namespace App\Enums;

enum ShellMeta: string
{
    // Docker metadata
    case IS_DOCKER_CONTEXT = 'is_docker_context';
    case DOCKER_CONTAINER = 'docker_container';
    case DOCKER_WORKDIR = 'docker_workdir';

    // Remote metadata
    case IS_REMOTE_CONTEXT = 'is_remote_context';
    case REMOTE_HOST = 'remote_host';
    case REMOTE_PORT = 'remote_port';
    case REMOTE_USER = 'remote_user';
    case REMOTE_PASSWORD = 'remote_password';
    case REMOTE_PASSWORD_TYPE = 'remote_password_type';

    // Generic metadata
    case SETTINGS_OPEN = 'settings_open';
    case WORD_WRAP = 'word_wrap';
    case SHOWING_HIDDEN = 'showing_hidden';
}
